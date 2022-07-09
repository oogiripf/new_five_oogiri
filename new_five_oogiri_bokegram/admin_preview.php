<?php
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

	define("PASSWORD", "$password");
	session_cache_limiter('none');
	session_start();
	if(isset($_SESSION["TEST"]) && $_SESSION["TEST"] != null && md5(PASSWORD) === $_SESSION["TEST"]){
		$admin = TRUE;
		include("header.php");
		
	//トークンをセッションにセット
	function setToken(){
		$token = rtrim(base64_encode(openssl_random_pseudo_bytes(32)),'=');
		$_SESSION['token'] = $token;
	}

		
	// お題プレビューここから
	if ($_POST["POST_TOPIC"]){
		setToken();
		
		$title = $_POST["TITLE"];
		$body = $_POST["BODY"];
		$anaume_front = $_POST["ANAUME_FRONT"];
		$anaume_rear = $_POST["ANAUME_REAR"];
		$deadline = $_POST["DEADLINE"];

		if ($title=="" or $body=="" or $_POST["POINT_A"]=="") {	// 空欄チェック
			echo error_message("投稿エラー","必須入力項目に空欄があります");
			exit;
		}
		
		if( get_magic_quotes_gpc() ) { $title = stripslashes("$title"); }
		if( get_magic_quotes_gpc() ) { $body = stripslashes("$body"); }
		
		if($_POST["TOPIC_TYPE"]=="line"){
			if(!empty($anaume_front)){if( get_magic_quotes_gpc() ) { $anaume_front = stripslashes("$anaume_front"); }}
			if(!empty($anaume_rear)){if( get_magic_quotes_gpc() ) { $anaume_rear = stripslashes("$anaume_rear"); }}
		}
		
		if(!empty($deadline)){if( get_magic_quotes_gpc() ) { $deadline = stripslashes("$deadline"); }}

		
		if($_POST["TOPIC_FORMAT"]==0 and (empty($_POST["TOPIC_VOTE_DATE"]) or empty($_POST["TOPIC_RESULT_DATE"]))){
			echo error_message("投稿エラー","締切日時が入力されていません");
			exit;
		}
		
		$check_d=0;
		$check_a=0;
		
		
		if(!preg_match("/^[0-9]*$/",$_POST["POST_LIMIT"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_A"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_B"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_C"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_A_LIMIT"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_B_LIMIT"]) or !preg_match("/^[0-9]*$/",$_POST["POINT_C_LIMIT"]) or !preg_match("/^[0-9]*$/",$_POST["VOTE_BONUS"])){
				echo error_message("投稿エラー","数値欄には数値のみを入力してください");
				exit;
		}
		
		if($_POST["TOPIC_FORMAT"]==0){
			if(!empty($_POST["TOPIC_POST_DATE"])){
				if(!check_date($_POST["TOPIC_POST_DATE"]) or !check_time($_POST["TOPIC_POST_DATE"])){
					$check_a++;
				}
				if (!strtotime($_POST["TOPIC_VOTE_DATE"]) <= strtotime($_POST["TOPIC_POST_DATE"])){
					$check_d++;
				}
			}
			if(!check_date($_POST["TOPIC_VOTE_DATE"]) or !check_date($_POST["TOPIC_RESULT_DATE"]) or !check_time($_POST["TOPIC_VOTE_DATE"]) or !check_time($_POST["TOPIC_RESULT_DATE"])){
				$check_a++;
			}
			if (strtotime($_POST["TOPIC_RESULT_DATE"]) <= strtotime($_POST["TOPIC_VOTE_DATE"])){
				$check_d++;
			}

			if(!empty($check_a)){
				echo error_message("投稿エラー","無効な日時です");
				exit;
			}
			
			if(!empty($check_d)){
				echo error_message("投稿エラー","日時の前後関係に誤りがあります");
				exit;
			}
		}
		
		$img = NULL;
		$ext = NULL;
		
		if (is_uploaded_file($_FILES["TOPIC_IMG"]["tmp_name"])) { //	ファイルがアップロードされた場合
			//ファイルのパス
			$file_pass = $_FILES["TOPIC_IMG"]["tmp_name"];
			//まずファイルの存在を確認し、その後画像形式を確認する
			if(file_exists($file_pass) && $type = exif_imagetype($file_pass)){
				switch($type){
					//gifの場合
					case IMAGETYPE_GIF:
					$ext = "gif";
					break;
					//jpgの場合
					case IMAGETYPE_JPEG:
					$ext = "jpeg";
					break;
					//pngの場合
					case IMAGETYPE_PNG:
					$ext = "png";
					break;
					//どれにも該当しない場合
					default:
					echo error_message("投稿エラー","未対応の画像ファイルです");
					exit;
				}
			}else{
				echo error_message("投稿エラー","画像ファイルではありません");
				exit;
			}
			$img = file_get_contents($_FILES["TOPIC_IMG"]["tmp_name"]);
			//セッションにも格納
			// BASE64エンコード
			$base64 = base64_encode($img);
		}elseif($_POST["ID"] and $_POST["CLEAR_FILE"]){ //	新しくアップロードされず、過去画像削除後
				$img = NULL;
				$ext = NULL;
		}elseif($_POST["ID"]){ //	新しくアップロードされず、画像そのまま使用
			$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$_POST['ID']};");
			$topic = $st->fetch();
			if(!empty($topic['img'])){
				$img = $topic['img'];
				$ext = $topic['ext'];
				$base64 = base64_encode($img);
			}
		}
		
		$_SESSION['img'] = $img ;
		$_SESSION['ext'] = $ext ;
		
		if($_POST["ID"]){
			$_SESSION['id'] = $_POST["ID"];
		}else{
			$_SESSION['id'] = NULL;
		}
		
		$_SESSION['title'] = $title;
		$_SESSION['body'] = $body;
		$_SESSION['topic_vote_date'] = $_POST["TOPIC_VOTE_DATE"];
		$_SESSION['topic_result_date'] = $_POST["TOPIC_RESULT_DATE"];
		$_SESSION['topic_type'] = $_POST["TOPIC_TYPE"];
		$_SESSION['anaume_front'] = $anaume_front;
		$_SESSION['anaume_rear'] = $anaume_rear;
		$_SESSION['topic_post_date'] = $_POST["TOPIC_POST_DATE"];
		$_SESSION['topic_format'] = $_POST["TOPIC_FORMAT"];
		$_SESSION['deadline'] = $deadline;
		$_SESSION['post_limit'] = $_POST["POST_LIMIT"];
		$_SESSION['point_a'] = $_POST["POINT_A"];
		$_SESSION['point_b'] = $_POST["POINT_B"];
		$_SESSION['point_c'] = $_POST["POINT_C"];
		$_SESSION['point_a_limit'] = $_POST["POINT_A_LIMIT"];
		$_SESSION['point_b_limit'] = $_POST["POINT_B_LIMIT"];
		$_SESSION['point_c_limit'] = $_POST["POINT_C_LIMIT"];
		if(!empty($_POST["MULTIPLE_POINT"])){$_SESSION['multiple_point'] = $_POST["MULTIPLE_POINT"];}
		else{$_SESSION['multiple_point'] = NULL;}
		$_SESSION['comment_accept'] = $_POST["COMMENT_ACCEPT"];
		$_SESSION['vote_bonus'] = $_POST["VOTE_BONUS"];
		if(!empty($_POST["SELF_RECOMMENDATION"])){$_SESSION['self_recommendation'] = $_POST["SELF_RECOMMENDATION"];}
		else{$_SESSION['self_recommendation'] = NULL;}
		if(!empty($_POST["REPORT_RANK"])){
		$_SESSION['report_rank'] = $_POST["REPORT_RANK"];}
		else{$_SESSION['report_rank'] = NULL;}
		if(!empty($_POST["PUBLIC_VOTE"])){
		$_SESSION['public_vote'] = $_POST["PUBLIC_VOTE"];}
		else{$_SESSION['public_vote'] = NULL;}
		
?>
	<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php if(empty($_POST["ID"])){ ?>新しいお題の作成<?php }else{ ?>お題の編集<?php } ?></h2></div>
					<div class="titlelist-data">以下の内容で送信します。よろしければ送信ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<form method="post" action="admin_post.php" accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal" id="post_form">
		<div class="admin_form_box">
		<div class="container">
			<div class="form-group">
				<label for="input_title" class="col-sm-3 control-label">タイトル</label>
				<div class="col-sm-9 form_preview"><?php echo h($title); ?>　</div>
			</div>
			<div class="form-group">
				<label for="input_body" class="col-sm-3 control-label">本文</label>
				<div class="col-sm-9 form_preview"><?php echo nl2br($body) ?>　</div>
			</div>
			<div class="form-group">
				<label for="topic_img" class="col-sm-3 control-label">画像</label>
				<div class="col-sm-9 form_preview"><?php if ($img){echo "<img src='data:image/".$ext.";base64,".$base64."' class='img_preview' />";} ?>　</div>
			</div>
			<div class="form-group">
				<label for="input_vote_date" class="col-sm-3 control-label">投稿締め切り</label>
				<div class="col-sm-9 form_preview"><?php echo h($_POST["TOPIC_VOTE_DATE"]); ?>　</div>
			</div>
			<div class="form-group">
				<label for="input_result_date" class="col-sm-3 control-label">採点締め切り</label>
				<div class="col-sm-9 form_preview"><?php echo h($_POST["TOPIC_RESULT_DATE"]); ?>　</div>
			</div>
			<div class="form-group">
				<label for="topic_type" class="col-sm-3 control-label">お題の形式</label>
				<div class="col-sm-9 form_preview"><?php 
						if($_POST["TOPIC_TYPE"]=="normal"){echo"普通";}
						elseif($_POST["TOPIC_TYPE"]=="line"){echo"穴埋め・ひと言";}
					?></div>
			</div>
			<?php if($_POST["TOPIC_TYPE"]=="line"){ ?>
			<div class="form-group">
				<label for="anaume_front" class="col-sm-3 control-label">前方に挿入</label>
				<div class="col-sm-9 form_preview"><?php echo h($_POST["ANAUME_FRONT"]); ?>　</div>
			</div>
			<div class="form-group">
				<label for="anaume_rear" class="col-sm-3 control-label">後方に挿入</label>
				<div class="col-sm-9 form_preview"><?php echo h($_POST["ANAUME_REAR"]); ?>　</div>
			</div>
			<?php } ?>
			<div class="panel-group" id="accordion">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">詳細</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse">
						<div class="panel-body">
								<div class="form-group">
									<label for="input_post_date" class="col-sm-3 control-label">投稿開始日時</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["TOPIC_POST_DATE"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="topic_format" class="col-sm-3 control-label">表示の切り替え</label>
									<div class="col-sm-9 form_preview"><?php 
						if($_POST["TOPIC_FORMAT"]=="0"){echo"自動";}
						elseif($_POST["TOPIC_FORMAT"]=="1"){echo"投稿";}
						elseif($_POST["TOPIC_FORMAT"]=="2"){echo"採点";}
						elseif($_POST["TOPIC_FORMAT"]=="3"){echo"結果";}
						elseif($_POST["TOPIC_FORMAT"]=="4"){echo"準備";}
						elseif($_POST["TOPIC_FORMAT"]=="5"){echo"掲示";}
						elseif($_POST["TOPIC_FORMAT"]=="6"){echo"非公開";}
						elseif($_POST["TOPIC_FORMAT"]=="7"){echo"アーカイブ";}
					?></div>
								</div>
								<div class="form-group">
									<label for="deadline" class="col-sm-3 control-label">締め切り</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["DEADLINE"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="post_limit" class="col-sm-3 control-label">投稿採用数</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POST_LIMIT"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_a" class="col-sm-3 control-label">点数Aの値</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_A"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_b" class="col-sm-3 control-label">点数Bの値</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_B"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_c" class="col-sm-3 control-label">点数Cの値</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_C"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_a_limit" class="col-sm-3 control-label">点数Aの制限数</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_A_LIMIT"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_b_limit" class="col-sm-3 control-label">点数Bの制限数</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_B_LIMIT"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="point_c_limit" class="col-sm-3 control-label">点数Cの制限数</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["POINT_C_LIMIT"]); ?>　</div>
								</div>
								<div class="form-group">
									<label for="multiple_point" class="col-sm-3 control-label">点数の重複</label>
									<div class="col-sm-9 form_preview"><?php 
						if($_POST["MULTIPLE_POINT"]=="1"){echo"承諾";}
						elseif(empty($_POST["MULTIPLE_POINT"])){echo"拒否";}
					?></div>
								</div>
								<div class="form-group">
									<label for="comment_accept" class="col-sm-3 control-label">コメントの受付</label>
									<div class="col-sm-9 form_preview"><?php 
						if($_POST["COMMENT_ACCEPT"]=="0"){echo"常に受付";}
						elseif($_POST["COMMENT_ACCEPT"]=="1"){echo"採点中のみ受付";}
						elseif($_POST["COMMENT_ACCEPT"]=="2"){echo"結果中のみ受付";}
						elseif($_POST["COMMENT_ACCEPT"]=="3"){echo"常に禁止";}
					?></div>
								</div>
								<div class="form-group">
									<label for="vote_bonus" class="col-sm-3 control-label">採点ボーナス</label>
									<div class="col-sm-9 form_preview"><?php echo h($_POST["VOTE_BONUS"]); ?></div>
								</div>
								<div class="form-group">
									<label for="self_recommendation" class="col-sm-3 control-label">自薦</label>
									<div class="col-sm-9 form_preview"><?php 
						if(empty($_POST["SELF_RECOMMENDATION"])){echo"拒否";}
						elseif($_POST["SELF_RECOMMENDATION"]=="1"){echo"承諾";}
					?></div>
								</div>
								<div class="form-group">
									<label for="report_rank" class="col-sm-3 control-label">採点期間の順位</label>
									<div class="col-sm-9 form_preview"><?php 
						if(empty($_POST["REPORT_RANK"])){echo"表示しない";}
						elseif($_POST["REPORT_RANK"]=="1"){echo"表示する";}
					?></div>
								</div>
								<div class="form-group">
									<label for="public_vote" class="col-sm-3 control-label">採点者の公開</label>
									<div class="col-sm-9 form_preview"><?php 
						if($_POST["PUBLIC_VOTE"]=="1"){echo"公開";}
						elseif($_POST["PUBLIC_VOTE"]=="0"){echo"非公開";}
					?></div>
								</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
					<input type="submit" name="POST_TOPIC" value="送信" class="button">
					<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
				</div>
			</div>
			</div>
		</div>
		</form>
	</div>
<?php
	}elseif(isset($_POST['DELETE_TOPIC'])){ //	お題を削除
		setToken();
		if($_POST["ID"]){
			$_SESSION['id'] = $_POST["ID"];
			$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$_POST['ID']};");
			$topic = $st->fetch();
		}else{
			echo error_message("編集エラー","お題が選択されていません");
			exit;
		}
		
?>
	<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>お題の削除</h2></div>
					<div class="titlelist-data">以下のお題を削除します。よろしければ削除ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<div class="topic_box">
	<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
	</div>
	</div>
		<form method="post" action="admin_post.php" accept-charset="UTF-8">
				<div class="admin_form_box">
				<div class="container">
					<div class="form_delete_button">
						<input type="submit" name="DELETE_TOPIC" value="削除" class="button">
						<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
					</div>
				</div>
			</div>
		</form>
	</div>


<?php
	}elseif(isset($_POST['DELETE_POST'])){ //	回答を削除
		setToken();
		if($_POST["NO"]){
			$_SESSION['no'] = $_POST["NO"];
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE no={$_POST['NO']};");
			$post = $st_post->fetch();
			$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$post['topic_id']};");
			$topic = $st->fetch();
		}else{
			echo error_message("編集エラー","お題が選択されていません");
			exit;
		}

		
?>
	<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelist-title"><h2>回答の削除</h2></div>
				<div class="titlelist-data">以下の回答を削除します。よろしければ削除ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo h($post['name']); ?>さんの回答</div>
			<div class="panel-body post_body">
					<h3>
		<?php
		if($topic['topic_type']==normal){
			echo nl2br(h($post['content']));
		}
		if($topic['topic_type']==line){
			if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
			else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post['content']); echo"</span>".h($topic['anaume_rear']);}
		}
		?>
					</h3>
				</div>
		</div>
		</div>
		</div>
			<form method="post" action="admin_post.php" accept-charset="UTF-8">
				<div class="admin_form_box">
				<div class="container">
					<div class="form_delete_button">
					<input type="submit" name="DELETE_POST" value="削除" class="button">
					<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
					</div>
				</div>
			</div>
			</form>
	</div>


<?php
		
	}elseif(isset($_POST['DELETE_VOTE'])){ //	採点を削除
		setToken();
		if($_POST["NO"]){
			$_SESSION['no'] = $_POST["NO"];
			$st_vote = $pdo->query("SELECT * FROM {$vote_table} WHERE vote_no={$_POST['NO']};");
			$vote = $st_vote->fetch();
			$_SESSION['point'] = $vote['point'];
			$_SESSION['post_no'] = $vote['post_no'];
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE no={$vote['post_no']};");
			$post = $st_post->fetch();
			$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$post['topic_id']};");
			$topic = $st->fetch();
		}else{
			echo error_message("編集エラー","お題が選択されていません");
			exit;
		}

		
?>
		
	<div id="main">
		<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelist-title"><h2>採点の削除</h2></div>
				<div class="titlelist-data">以下の採点を削除します。よろしければ削除ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo h($vote['name']); ?>さんから<?php echo h($post['name']); ?>さんへの投票</div>
		<table class="table edit_post_info">
			<tr>
				<th>回答の内容</th>
				<td>
		<?php
		if($topic['topic_type']==normal){
			echo nl2br(h($post['content']));
		}
		if($topic['topic_type']==line){
			if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
			else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post['content']); echo"</span>".h($topic['anaume_rear']);}
		}
		?>
				</td>
			</tr>
			<tr>
				<th>点数</th>
				<td><?php echo h($vote['point']); ?></td>
			</tr>
			<tr>
				<th>ウェブサイト</th>
				<td><?php if(!empty($vote['url'])){ ?><a href="<?php echo h($vote['url']); ?>" target="_blank"><?php echo h($vote['url']); ?></a><?php } ?></td>
			</tr>
			<tr>
				<th>採点日時</th>
				<td><?php echo h($vote['time']); ?></td>
			</tr>
			<tr>
				<th>IPアドレス</th>
				<td><?php echo long2ip($vote['ip']); ?></td>
			</tr>
		</table>
		</div>
		</div>
		</div>
			<form method="post" action="admin_post.php" accept-charset="UTF-8">
				<div class="admin_form_box">
				<div class="container">
					<div class="form_delete_button">
						<input type="submit" name="DELETE_VOTE" value="削除" class="button">
					<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
					</div>
				</div>
			</div>
			</form>
	</div>


<?php
		
	}elseif(isset($_POST['DELETE_COMMENT'])){ //	コメントを削除
		setToken();
		if($_POST["NO"]){
			$_SESSION['no'] = $_POST["NO"];
			$st_comment = $pdo->query("SELECT * FROM {$comment_table} WHERE no={$_POST['NO']};");
			$comment = $st_comment->fetch();
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE no={$comment['post_no']};");
			$post = $st_post->fetch();
		}else{
			echo error_message("編集エラー","コメントが選択されていません");
			exit;
		}

		
?>
	<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelist-title"><h2>コメントの削除</h2></div>
				<div class="titlelist-data">以下のコメントを削除します。よろしければ削除ボタンを押してください。</div>
			</div>
		</div>
	</div>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo h($comment['name']); ?>さんから<?php echo h($post['name']); ?>さんへのコメント</div>
		<table class="table edit_post_info">
			<tr>
				<th>コメントの内容</th>
				<td><?php echo nl2br(h($comment['content'])); ?></td>
			</tr>
			<tr>
				<th>ウェブサイト</th>
				<td><?php if(!empty($comment['url'])){ ?><a href="<?php echo h($comment['url']); ?>" target="_blank"><?php echo h($comment['url']); ?></a><?php } ?></td>
			</tr>
			<tr>
				<th>コメント投稿日時</th>
				<td><?php echo h($comment['time']); ?></td>
			</tr>
			<tr>
				<th>IPアドレス</th>
				<td><?php echo long2ip($comment['ip']); ?></td>
			</tr>
		</table>
		</div>
		</div>
	</div>
			<form method="post" action="admin_post.php" accept-charset="UTF-8">
				<div class="admin_form_box">
				<div class="container">
					<div class="form_delete_button">
						<input type="submit" name="DELETE_COMMENT" value="削除" class="button">
					<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
					</div>
				</div>
			</div>
			</form>
	</div>


<?php
		
	}
		include("footer.php");
	}else{
		session_destroy();
		include("header.php");
		echo error_message("ログインエラー","もう一度ログインし直してください");
		exit;
	}
?>