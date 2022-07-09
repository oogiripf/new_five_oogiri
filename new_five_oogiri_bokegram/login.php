<?php	//	設定読み込み
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
	if(!empty($st_setting)){
		$setting = $st_setting->fetch();
		$sitename = $setting['site_name'];
	}else{
		$sitename = "Bokegram";
	}
	$pagetitle = $sitename;

	define("PASSWORD", "$password");	//ログイン作業
	session_start();
	if(isset($_POST["action"])&&$_POST["action"]==="ログイン"){

		if(PASSWORD === $_POST["password"]){//パスワード確認
			$_SESSION["TEST"] = md5(PASSWORD);//暗号化してセッションに保存
			header("Location:admin.php");
			exit;
		}else{
			session_destroy();//セッション破棄
			$login_error = TRUE;
		}
	}
	include("header.php");
	
?>
<div id="main">
	<div class="message_box">
	<div id="login-box">
		<h2>管理者としてログイン</h2>
		<span class="icon_message"><span class="icon-lock"></span></span>
		<?php
			if(!empty($login_error)){	//ログインエラーの場合
		?>
				<p class="error-message">パスワードが違います</p>
		<?php
			}
		?>
		<form action="" method="post">
			<div class="input_pass_box">
				<input name="password" type="password" value="" class="form-control input-lg">
			</div>
			<div>
				<input name="action" type="submit" value="ログイン" class="button">
			</div>
		</form>
	</div>
	</div>
</div>
<?php
	include("footer.php");
?>