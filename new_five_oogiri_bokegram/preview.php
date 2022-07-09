<?php
	session_start();
	include("config/config.php");
	include("function.php");
	try{
		$pdo = new PDO("mysql:dbname=$dbname;host=$dbhostname", "$dbuser", "$dbpass");
		$st_setting = $pdo->query("SELECT * FROM {$setting_table}");
	}catch (PDOException $e){
		$sitename = "Bokegram";
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("MySQLエラー","接続できませんでした");
		exit;
	}
	$setting = $st_setting->fetch();
	$sitename = $setting['site_name'];
	$pagetitle = $sitename;
	$ban_txt = $setting['ban'];
	$ip = getenv("REMOTE_ADDR");
	$post_body_limit = $setting['post_body_limit'];
	$comment_body_limit = $setting['comment_body_limit'];

	include("header.php");
	if(!empty($ban_txt)){ //	アクセス制限
		$ban_users = explode("\r\n", $ban_txt);
		foreach($ban_users as $ban_user){
			if($ban_user == $ip){
				echo error_message("アクセスエラー","投稿が制限されています");
				exit;
			}
		}
	}

	//トークンをセッションにセット
	function setToken(){
		$token = rtrim(base64_encode(openssl_random_pseudo_bytes(32)),'=');
		$_SESSION['token'] = $token;
	}


	// 投稿プレビューここから
	if ($_POST["SUBMIT_POST"]){
		setToken();
		$body = $_POST["BODY_PRE"];
		$name = $_POST["NAME_PRE"];
		$url = $_POST["URL_PRE"];
		$id = $_POST["INPUTID"];
		
		$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$id};");
		$topic = $st->fetch();
		
		if ($name=="" or $body=="") {	// 名前欄・本文の空欄チェック
			echo error_message("投稿エラー","お名前と本文は必ず入力してください");
			exit;
		}
		$mojinum = mb_strlen($body, 'UTF-8');	// 文字数チェック
		if (!empty($post_body_limit)){
			if ($mojinum > $post_body_limit) {
				echo error_message("投稿エラー","文字数が多すぎます");
				exit;
			}
		}
		// クオートエスケープ処理を削除
		if( get_magic_quotes_gpc() ) { $name = stripslashes("$name"); }
		if( get_magic_quotes_gpc() ) { $url = stripslashes("$url"); }
		if( get_magic_quotes_gpc() ) { $body = stripslashes("$body"); }
		

?>
		
<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>回答の送信</h2></div>
					<div class="titlelist-data">以下の内容で送信します。よろしければ投稿ボタンを押してください。</div>
			</div>
		</div>
	</div>
<div class="topic_box">
	<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
	</div>
</div>
<form method="post" action="post.php" accept-charset="UTF-8" class="form-horizontal" id="post_form">
	<div class="form_box">
		<div class="container">
		<div class="form-group">
			<label for="input_name" class="col-sm-3 control-label">お名前</label>
			<div class="col-sm-9 form_preview"><?php echo h($name); ?></div>
		</div>
		<div class="form-group">
			<label for="input_url" class="col-sm-3 control-label">ウェブサイト</label>
			<div class="col-sm-9 form_preview"><?php echo h($url); ?>　</div>
		</div>
		<div class="form-group">
			<label for="input_body" class="col-sm-3 control-label">回答</label>
			<div class="col-sm-9 form_preview"><?php 
				if($topic['topic_type']=="normal"){
		echo nl2br(h($body));
				}
				if($topic['topic_type']=="line"){
		if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($body);}
		else{echo h($topic['anaume_front'])."<span class='anaume'>".h($body)."</span>".h($topic['anaume_rear']);}
				}
				?></div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9 form_preview_button">
				<input type="submit" name="SUBMIT_POST" value="投稿" class="button">
				<input value="訂正" onclick="history.back();" type="button" class="button_c">
				<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
				<input type="hidden" name="name" value="<?php echo $name; ?>">
				<input type="hidden" name="url" value="<?php echo $url; ?>">
				<input type="hidden" name="body" value="<?php echo $body; ?>">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
			</div>
		</div>
		</div>
	</div>
</form>
</div>

<?php 
}elseif($_POST["SUBMIT_VOTE"]){	// ここまで投稿プレビュー、ここから採点プレビュー
		setToken();
		$name = $_POST["NAME_PRE"];
		$url = $_POST["URL_PRE"];
		$id = $_POST["INPUTID"];
		$ip = getenv("REMOTE_ADDR");
		$iplong = ip2long($ip);
		if ($name=="") {	// 名前欄空欄時
			echo error_message("採点エラー","お名前は必ず入力してください");
			exit;
		}
		// HTML取除き
		// クオートエスケープ処理を削除
		if( get_magic_quotes_gpc() ) { $name = stripslashes("$name"); }
		if( get_magic_quotes_gpc() ) { $url = stripslashes("$url"); }
		
		$point_a_array = $_POST["vno_1"];
		$point_b_array = $_POST["vno_2"];
		$point_c_array = $_POST["vno_3"];
		
		$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$id};");
		$topic = $st->fetch();
		
		if(!is_array($point_a_array) and !is_array($point_b_array) and !is_array($point_c_array)){
			echo error_message("採点エラー","回答が選択されていません");
			exit;
		}
		if (empty($topic['multiple_point'])){	// １作品への重複チェック制限ここから
			if(is_array($point_a_array) and is_array($point_b_array)) {
				$result_j1 = array_intersect($point_a_array, $point_b_array);
				if(sizeof($result_j1)>0) {
					echo error_message("採点エラー","1つの作品にチェックできる点数は1つまでです");
					exit;
				}
			}
			if(is_array($point_b_array) and is_array($point_c_array)) {
				$result_j2 = array_intersect($point_b_array, $point_c_array);
				if(sizeof($result_j2)>0) {
					echo error_message("採点エラー","1つの作品にチェックできる点数は1つまでです");
					exit;
				}
			}
			if(is_array($point_c_array) and is_array($point_a_array)) {
				$result_j3 = array_intersect($point_c_array, $point_a_array);
				if(sizeof($result_j3)>0) {
					echo error_message("採点エラー","1つの作品にチェックできる点数は1つまでです");
					exit;
				}
			}
		}	// １作品への重複チェック制限ここまで
		// 点数制限数チェックここから
		if($topic['point_a_limit']){	// 点aのチェック
			if(is_array($point_a_array)) {
			if(sizeof($point_a_array)>$topic['point_a_limit']) {
					echo error_message("採点エラー","{$topic['point_a']}点を付けられる作品は{$topic['point_a_limit']}つまでです");
					exit;
				}
			}
		}
		if($topic['point_b_limit']){	// 点bのチェック
			if(is_array($point_b_array)) {
			if(sizeof($point_b_array)>$topic['point_b_limit']) {
					echo error_message("採点エラー","{$topic['point_b']}点を付けられる作品は{$topic['point_b_limit']}つまでです");
					exit;
				}
			}
		}
		if($topic['point_c_limit']){	// 点cのチェック
			if(is_array($point_c_array)) {
			if(sizeof($point_c_array)>$topic['point_c_limit']) {
					echo error_message("採点エラー","{$topic['point_c']}点を付けられる作品は{$topic['point_c_limit']}つまでです");
					exit;
				}
			}
		}
		// 点数制限数チェックここまで
		// 二重採点チェックここから
		$st_voter = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND ip={$iplong}");
		if(!empty($st_voter)){
			$voter = $st_voter->fetch();
		}
		if(!empty($voter)){
			echo error_message("採点エラー","既に採点しています");
			exit;
		}
		// 二重採点チェックここまで
		// 自薦チェックここから
		if(empty($topic['self_recommendation'])){
			if(!empty($point_a_array)){
				foreach($point_a_array as $v_a){
					$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_a}");
					$post_va = $st->fetch();
					if($post_va['name']==$name or $post_va['ip']==$iplong){echo error_message("採点エラー","自薦禁止です");exit;}
				}
			}
			if(!empty($point_b_array)){
				foreach($point_b_array as $v_b){
					$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_b}");
					$post_vb = $st->fetch();
					if($post_vb['name']==$name or $post_vb['ip']==$iplong){echo error_message("採点エラー","自薦禁止です");exit;}
				}
			}
			if(!empty($point_c_array)){
				foreach($point_c_array as $v_c){
					$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_c}");
					$post_vc = $st->fetch();
					if($post_vc['name']==$name or $post_vc['ip']==$iplong){echo error_message("採点エラー","自薦禁止です");exit;}
				}
			}
		}
		// 自薦チェックここまで

		$_SESSION["point_a_array"] = $point_a_array;
		$_SESSION["point_b_array"] = $point_b_array;
		$_SESSION["point_c_array"] = $point_c_array;
		
?>




<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>採点の送信</h2></div>
					<div class="titlelist-data">以下の内容で送信します。よろしければ採点ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<div class="topic_box">
	<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
	</div>
</div>
<div class="voter_box">
	<div class="container">
		<table class="table report">
		<?php
		if(!empty($point_a_array)){
	foreach($point_a_array as $v_a){
		$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_a}");
		$post_va = $st->fetch();
		?>
	<tr class="list"><td class="point_info" nowrap><?php echo h($topic['point_a']); ?><span class="rank_unit">点</span></td><td>
		<?php
	if($topic['topic_type']=="normal"){echo nl2br(h($post_va['content']));}
	elseif($topic['topic_type']=="line" and empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_va['content']);}
	else{echo h($topic['anaume_front'])."<span class='anaume'>".h($post_va['content'])."</span>".h($topic['anaume_rear']);}
		?>
	</td></tr>
		<?php
	}
		}
		if(!empty($point_b_array)){
	if(!$topic['point_b']==0){
		foreach($point_b_array as $v_b){
			$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_b}");
			$post_vb = $st->fetch();
			?>
		<tr class="list"><td class="point_info" nowrap><?php echo h($topic['point_b']); ?><span class="rank_unit">点</span></td><td>
			<?php
	if($topic['topic_type']=="normal"){echo nl2br(h($post_vb['content']));}
	elseif($topic['topic_type']=="line" and empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_vb['content']);}
	else{echo h($topic['anaume_front'])."<span class='anaume'>".h($post_vb['content'])."</span>".h($topic['anaume_rear']);}
			?>
		</td></tr>
			<?php
		}
	}
		}
		if(!empty($point_c_array)){
	if(!$topic['point_c']==0){
		foreach($point_c_array as $v_c){
			$st = $pdo->query("SELECT * FROM {$post_table} WHERE no={$v_c}");
			$post_vc = $st->fetch();
			?>
			<tr class="list"><td class="point_info" nowrap><?php echo h($topic['point_c']); ?><span class="rank_unit">点</span></td><td>
			<?php
	if($topic['topic_type']=="normal"){echo nl2br(h($post_vc['content']));}
	elseif($topic['topic_type']=="line" and empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_vc['content']);}
	else{echo h($topic['anaume_front'])."<span class='anaume'>".h($post_vc['content'])."</span>".h($topic['anaume_rear']);}
			?>
			</td></tr>
			<?php
		}
	}
		}
		
		?>
		</table>
	</div>
</div>
<form method="post" action="post.php" accept-charset="UTF-8" class="form-horizontal" id="post_form">
		<div class="form_box">
		<div class="container">
	<div class="form-group">
		<label for="input_name" class="col-sm-3 control-label">お名前</label>
		<div class="col-sm-9 form_preview"><?php echo h($name); ?></div>
	</div>
	<div class="form-group">
		<label for="input_url" class="col-sm-3 control-label">ウェブサイト</label>
		<div class="col-sm-9 form_preview"><?php echo h($url); ?>　</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9 form_preview_button">
			<input type="submit" name="SUBMIT_VOTE" value="採点" class="button">
			<input value="訂正" onclick="history.back();" type="button" class="button_c">
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
			<input type="hidden" name="name" value="<?php echo $name; ?>">
			<input type="hidden" name="url" value="<?php echo $url; ?>">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
		</div>
	</div>
			</div>
	</div>
</form>
</div>

<?php

}elseif($_POST["SUBMIT_CMT"]){	// ここまで採点プレビュー、ここからコメント投稿プレビュー
		setToken();
		$body = $_POST["BODY_PRE"];
		$name = $_POST["NAME_PRE"];
		$url = $_POST["URL_PRE"];
		$id = $_POST["INPUTID"];
		$no = $_POST["INPUTNO"];
		
		$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$id};");
		$topic = $st->fetch();
		
		$post_no_st = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} AND no={$no};");
		$post_no = $post_no_st->fetch();

		
		if ($name=="" or $body=="") {	// 名前欄・本文の空欄チェック
			echo error_message("コメント投稿エラー","お名前と本文は必ず入力してください");
			exit;
		}
		$mojinum = mb_strlen($body, "UTF-8");	// 文字数チェック
		if (!empty($comment_body_limit)){
			if ($mojinum > $comment_body_limit) {
				echo error_message("コメント投稿エラー","文字数が多すぎます");
				exit;
			}
		}
		// クオートエスケープ処理を削除
		if( get_magic_quotes_gpc() ) { $name = stripslashes("$name"); }
		if( get_magic_quotes_gpc() ) { $url = stripslashes("$url"); }
		if( get_magic_quotes_gpc() ) { $body = stripslashes("$body"); }
		



?>
<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>コメントの投稿</h2></div>
					<div class="titlelist-data">以下の内容で送信します。よろしければ送信ボタンを押してください。</div>
			</div>
		</div>
	</div>
<div class="post_box">
	<div class="container">
		<div class="post_heading"><span class="label_org">回答</span></div>
		<div class="post_body"><h3><?php if($topic['topic_type']==normal){
					echo nl2br(h($post_no['content']));
							}
					if($topic['topic_type']==line){
						if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_no['content']);}
						else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post_no['content']); echo"</span>".h($topic['anaume_rear']);}
					}
			?></h3>
		</div>
	</div>
</div>
<form method="post" action="post.php" accept-charset="UTF-8" class="form-horizontal" id="post_form">
	<div class="comment_form_box">
	<div class="container">
	<div class="form-group">
		<label for="input_name" class="col-sm-3 control-label">お名前</label>
		<div class="col-sm-9 form_preview"><?php echo h($name); ?></div>
	</div>
	<div class="form-group">
		<label for="input_url" class="col-sm-3 control-label">ウェブサイト</label>
		<div class="col-sm-9 form_preview"><?php echo h($url); ?>　</div>
	</div>
	<div class="form-group">
		<label for="input_url" class="col-sm-3 control-label">コメント</label>
		<div class="col-sm-9 form_preview"><?php echo nl2br(h($body)); ?></div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9 form_preview_button">
			<input type="submit" name="SUBMIT_CMT" value="送信" class="button">
			<input value="訂正" onclick="history.back();" type="button" class="button_c">
			<input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
			<input type="hidden" name="name" value="<?php echo $name ?>">
			<input type="hidden" name="url" value="<?php echo $url ?>">
			<input type="hidden" name="body" value="<?php echo $body ?>">
			<input type="hidden" name="id" value="<?php echo $id ?>">
			<input type="hidden" name="no" value="<?php echo $no ?>">
		</div>
	</div>
		</div>
	</div>
</form>
</div>

<?php 
	}// ここまでコメント投稿プレビュー
	include("footer.php");
?>