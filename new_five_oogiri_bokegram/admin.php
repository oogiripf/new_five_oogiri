<?php
	if(isset($_POST['logout'])){

		// セッション変数を全て解除する
		$_SESSION = array();

		// セッションを切断するにはセッションクッキーも削除する。
		// Note: セッション情報だけでなくセッションを破壊する。
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
					  $params["path"], $params["domain"],
					  $params["secure"], $params["httponly"]
					 );
		}

		//セッションを破壊してリダイレクト
		@session_destroy();
		header("Location:login.php");
		exit;
	}

	include("config/config.php");
	include("function.php");
	try{
		$pdo = new PDO("mysql:dbname=$dbname;host=$dbhostname", "$dbuser", "$dbpass");
	}catch (PDOException $e){
		$sitename = "Bokegram";
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("MySQLエラー","接続できませんでした");
		exit;
	}
		if(!table_check($topic_table, $pdo)){
			$query_t = "CREATE TABLE IF NOT EXISTS {$topic_table}(id SERIAL, title TEXT, content TEXT, time TIMESTAMP, topic_post_date DATETIME, topic_vote_date DATETIME, topic_result_date DATETIME, topic_type TEXT, post_limit INT, anaume_front TEXT, anaume_rear TEXT, point_a INT, point_b INT, point_c INT, point_a_limit INT, point_b_limit INT, point_c_limit INT, vote_bonus INT, topic_format INT, deadline TEXT, public_vote BIT, multiple_point BIT, self_recommendation BIT, comment_accept INT, report_rank BIT, img MEDIUMBLOB, ext varchar(5))";
			$pdo->query($query_t); //	topicテーブル作成
		}
		if(!table_check($post_table, $pdo)){
			$query_p = "CREATE TABLE IF NOT EXISTS {$post_table}(no SERIAL, topic_id INT, name TEXT, url TEXT, content TEXT, time TIMESTAMP, ip INT(10) UNSIGNED, score INT)";
			$pdo->query($query_p); //	postテーブル作成
		}
		if(!table_check($vote_table, $pdo)){
			$query_v = "CREATE TABLE IF NOT EXISTS {$vote_table}(vote_no SERIAL, topic_id INT, name TEXT, url TEXT, time TIMESTAMP, ip INT(10) UNSIGNED, post_no INT, point INT)";
			$pdo->query($query_v); //	voteテーブル作成
		}
		
		if(!table_check($comment_table, $pdo)){
			$query_c = "CREATE TABLE IF NOT EXISTS {$comment_table}(no SERIAL, topic_id INT, post_no INT, name TEXT, url TEXT, content TEXT, time TIMESTAMP, ip INT(10) UNSIGNED)";
			$pdo->query($query_c); //	commentテーブル作成
		}
		if(!table_check($setting_table, $pdo)){
			$query_s = "CREATE TABLE IF NOT EXISTS {$setting_table}(id SERIAL, site_name TEXT, per_topic INT, per_post INT, post_body_limit INT, comment_body_limit INT, ranking_player INT, ranking_post INT, ban TEXT, explanation TEXT)";
			$pdo->query($query_s); //	settingテーブル作成
			$st_write = $pdo->prepare("INSERT INTO {$setting_table}(site_name,per_topic,per_post,post_body_limit,comment_body_limit,ranking_player,ranking_post,ban,explanation) VALUES(?,?,?,?,?,?,?,?,?)");
			$st_write->execute(array("Bokegram", "15", "20", "2000", "2000", "50", "20", "", "")); //	初期値
		}

	$stmt_explanation = $pdo -> prepare("ALTER TABLE {$setting_table} ADD explanation TEXT;");
	$stmt_explanation->execute(); //	explanationカラム追加（ver3.0.1-）
		

	$st_setting = $pdo->query("SELECT * FROM {$setting_table}");
	if(!empty($st_setting)){
		$setting = $st_setting->fetch();
		$sitename = $setting['site_name'];
	}else{
		$sitename = "Bokegram";
	}
	$pagetitle = $sitename;

	define("PASSWORD", "$password");
	session_start();
	if(isset($_SESSION["TEST"]) && $_SESSION["TEST"] != null && md5(PASSWORD) === $_SESSION["TEST"]){
		$admin = TRUE;
		include("header.php");
?>
<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelists-title"><h2>管理メニュー</h2>
				<?php if(!empty($dataid)){ ?><div class="titlelists-data">データID：<?php echo h($dataid); ?></div><?php } ?></div>
			</div>
		</div>
	</div>
	<div class="index_box">
	<div class="container">
<?php
	if(!empty($st_setting)){
?>
		<a class="titlelists" href="admin_topic.php">
		<div class="titlelist-title"><h2>新しいお題の作成</h2></div>
		</a>
		
		<a class="titlelists" href="admin_edit.php">
		<div class="titlelist-title"><h2>データの編集・削除</h2></div>
		</a>
<?php
	}
?>
		<a class="titlelists" href="admin_setting.php">
		<div class="titlelist-title"><h2>環境設定</h2></div>
		</a>
			
		</div>
	</div>
	</div>
<?php
		include("footer.php");
	}else{
		session_destroy();
		include("header.php");
		echo error_message("ログインエラー","もう一度ログインし直してください");
		exit;
	}
?>