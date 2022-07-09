<?php
	include("config/config.php");
	include("function.php");
	try{
		$pdo = new PDO("mysql:dbname=$dbname;host=$dbhostname", "$dbuser", "$dbpass");
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (PDOException $e){
		$sitename = "Bokegram";
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("MySQLエラー","接続できませんでした");
		exit;
	}
	define("PASSWORD", "$password");
	session_start();
	if(isset($_SESSION["TEST"]) && $_SESSION["TEST"] != null && md5(PASSWORD) === $_SESSION["TEST"]){
		$admin = TRUE;
		$msg = array();
		if ($_POST["SETTING"]){
			if(!preg_match("/^[0-9]+$/",$_POST["PER_TOPIC"]) or !preg_match("/^[0-9]+$/",$_POST["PER_POST"]) or !preg_match("/^[0-9]*$/",$_POST["POST_BODY_LIMIT"]) or !preg_match("/^[0-9]*$/",$_POST["COMMENT_BODY_LIMIT"]) or !preg_match("/^[0-9]+$/",$_POST["RANKING_PLAYER"]) or !preg_match("/^[0-9]+$/",$_POST["RANKING_POST"])){
				$msg = array('message_error','無効な数値です');
			}
			if(empty($msgs)){
				$st_write = $pdo->prepare("UPDATE {$setting_table} SET site_name=?, per_topic=?, per_post=?, post_body_limit=?, comment_body_limit=?, ranking_player=?, ranking_post=?, ban=?, explanation=? WHERE id=?");
				$st_write->execute(array($_POST["SITE_NAME"], $_POST["PER_TOPIC"], $_POST["PER_POST"], $_POST["POST_BODY_LIMIT"], $_POST["COMMENT_BODY_LIMIT"], $_POST["RANKING_PLAYER"], $_POST["RANKING_POST"], $_POST["BAN"], $_POST["EXPLANATION"], $_POST["ID"]));
				$msg = array('message','設定が更新されました');
			}
		}
		
		$st_setting = $pdo->query("SELECT * FROM {$setting_table}");
		$setting = $st_setting->fetch();
		
		$per_topic = $setting['per_topic'];
		$per_post = $setting['per_post'];
		$post_body_limit = $setting['post_body_limit'];
		$comment_body_limit = $setting['comment_body_limit'];
		$ranking_player = $setting['ranking_player'];
		$ranking_post = $setting['ranking_post'];
		$ban = $setting['ban'];
		$explanation = $setting['explanation'];
		$id = $setting['id'];

		$sitename = $setting['site_name'];
		$pagetitle = $sitename;
		include("header.php");

?>
<div id="main">
	<div class="title_box">
	<div class="container">
		<div class="titlelists">
				<div class="titlelist-title"><h2>環境設定</h2></div>
			<?php if (!empty($msg)){ ?>
				<div class="titlelist-data"><span class="<?=h($msg[0])?>"><?=h($msg[1])?></span></div>
			<?php } ?>
		</div>
	</div>
	</div>
		<form method="post" action="" accept-charset="UTF-8" class="form-horizontal" id="post_form">
		<div class="admin_form_box">
		<div class="container">
			<div class="form-group">
				<label for="site_name" class="col-sm-3 control-label">サイト名</label>
				<div class="col-sm-9">
					<input type="text" name="SITE_NAME" id="site_name" class="form-control" value="<?php echo $sitename; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">主に上部ナビゲーションバーとページタイトルに表示されます。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="per_topic" class="col-sm-3 control-label">ページネーション</label>
				<div class="col-sm-9">
					<input type="text" name="PER_TOPIC" id="per_topic" class="form-control onlynum" value="<?php echo $per_topic; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">トップページ（index.php）でお題を何件ごとに表示するかを設定します。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="per_post" class="col-sm-3 control-label">お題の表示間隔</label>
				<div class="col-sm-9">
					<input type="text" name="PER_POST" id="per_post" class="form-control onlynum" value="<?php echo $per_post; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">採点画面で何件の回答ごとにお題を挟むかを設定します。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="post_body_limit" class="col-sm-3 control-label">回答文字数</label>
				<div class="col-sm-9">
					<input type="text" name="POST_BODY_LIMIT" id="post_body_limit" class="form-control onlynum" value="<?php echo $post_body_limit; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">回答で入力できる最大文字数です。空欄か0の場合は無制限。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="comment_body_limit" class="col-sm-3 control-label">コメント文字数</label>
				<div class="col-sm-9">
					<input type="text" name="COMMENT_BODY_LIMIT" id="comment_body_limit" class="form-control onlynum" value="<?php echo $comment_body_limit; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">コメントで入力できる最大文字数です。空欄か0の場合は無制限。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="ranking_player" class="col-sm-3 control-label">総合ランキング数</label>
				<div class="col-sm-9">
					<input type="text" name="RANKING_PLAYER" id="ranking_player" class="form-control onlynum" value="<?php echo $ranking_player; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">総合得点ランキングで表示する上位数です。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="ranking_post" class="col-sm-3 control-label">回答ランキング数</label>
				<div class="col-sm-9">
					<input type="text" name="RANKING_POST" id="ranking_post" class="form-control onlynum" value="<?php echo $ranking_post; ?>">
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">回答ランキングで表示する上位数です。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="ban" class="col-sm-3 control-label">制限ユーザー</label>
				<div class="col-sm-9">
					<textarea wrap="soft" name="BAN" id="ban" class="form-control" rows="5"><?php echo $ban;  ?></textarea>
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">回答・採点・コメント投稿を禁止したいユーザーのIPアドレスを１行ずつ入力してください。</p>
				</div>
			</div>
			<div class="form-group">
				<label for="explanation" class="col-sm-3 control-label">説明欄</label>
				<div class="col-sm-9">
					<textarea wrap="soft" name="EXPLANATION" id="explanation" class="form-control" rows="5"><?php echo $explanation;  ?></textarea>
				</div>
				<div class="col-sm-offset-3 col-sm-9">
					<p class="help-block">ここに入力した情報はトップページに表示されます。（HTMLタグ使用可）</p>
				</div>
			</div>
<script>
// 正の整数
	$(".onlynum").keyup(function(){
		var s = new Array();
		$.each( $(this).val().split(""), function(i, v){
			if( v.match(/[0-9]/gi) ) s.push(v);
		} );
		if(s.length > 0) 
			$(this).val( s.join("") );
		else $(this).val(""); 
});
// *「右クリック貼り付け」対応
	$(".onlynum").change(function() {
		$(this).keyup();
	});
 </script>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
					<input type="submit" name="SETTING" value="送信" class="button">
					<input type="hidden" name="ID" value="<?php echo h($id); ?>" class="button">
				</div>
			</div>
			</div>
			</div>
		</form>
	</div>
<?php
		include("footer.php");
	}else{
		include("header.php");
		session_destroy();
		echo error_message("ログインエラー","もう一度ログインし直してください");
		exit;
	}
?>