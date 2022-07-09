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

	include("header.php");


if(!empty($_POST["id"])) {
	$id = $_POST["id"];
	$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$id};");
	$topic = $st->fetch();
	$today = date("Y-m-d H:i:s");

		if(empty($topic['topic_format'])){
			if(strtotime($topic['topic_post_date']) > strtotime($today)){$topic_format = 4;}	// 投稿開始日より前（準備）
			elseif(strtotime($topic['topic_vote_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_post_date'])){$topic_format = 1;}	// 採点開始日より前かつ投稿開始日より後（投稿）
			elseif(strtotime($topic['topic_result_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_vote_date'])){$topic_format = 2;}	// 結果開始日より前かつ採点開始日より後（採点）
			elseif(strtotime($today) >= strtotime($topic['topic_result_date'])){$topic_format = 3;}	// 結果開始日より後（結果）
		}else{
		$topic_format = $topic['topic_format'];
		}
}

// 投稿作業ここから
if($_POST["SUBMIT_POST"]) {
	if($_SESSION['token'] !== $_POST['token']){
		echo error_message("投稿エラー","不正なPOSTが行われました");
		exit;
	}
		$name = $_POST["name"];
		$url = $_POST["url"];
		$body = $_POST["body"];
		$ip = getenv("REMOTE_ADDR");
		$iplong = ip2long($ip);
		if($topic_format != 1){
			echo error_message("投稿エラー","投稿期間ではありません");
			exit;
		}
		$st_posts = $pdo->query("SELECT no FROM {$post_table} WHERE topic_id={$id} AND ip='{$iplong}' ORDER BY no;");
		if(!empty($st_posts)){
			$posts = $st_posts->fetchAll();
			$count_posts = count($posts);
			if($count_posts >= $topic['post_limit']){
				$del_no = reset($posts);
				$st_del = $pdo->prepare("DELETE FROM {$post_table} WHERE no=?");
				$st_del->execute(array($del_no[no]));
			}
		}
		$st_write = $pdo->prepare("INSERT INTO {$post_table}(topic_id,name,url,content,ip,score) VALUES(?,?,?,?,?,?)");
		$st_write->execute(array($id, $name, $url, $body, $iplong, 0));
		echo success_message("投稿されました","");
		session_destroy();// 投稿作業ここまで
	}elseif($_POST["SUBMIT_VOTE"]){// 採点作業ここから
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("採点エラー","不正なPOSTが行われました");
			exit;
		}
		$name = h($_POST["name"]);
		$url = h($_POST["url"]);
		$point_a_array = $_SESSION["point_a_array"];
		$point_b_array = $_SESSION["point_b_array"];
		$point_c_array = $_SESSION["point_c_array"];
		$ip = getenv("REMOTE_ADDR");
		$iplong = ip2long($ip);
		if($topic_format != 2){
			echo error_message("採点エラー","採点期間ではありません");
			exit;
		}

		if (is_array($point_a_array)) {
			foreach ($point_a_array as $v_a) {	// 点a追加
				$st_write = $pdo->prepare("INSERT INTO {$vote_table}(topic_id,post_no,name,url,point,ip) VALUES(?,?,?,?,?,?)");
				$st_write->execute(array($id, $v_a, $name, $url, $topic['point_a'], $iplong));
				$st_score = $pdo->prepare("UPDATE {$post_table} SET score = score + ? WHERE no=?");
				$st_score->execute(array($topic['point_a'], $v_a));
				
			}
		}
		if (is_array($point_b_array)) {
			foreach ($point_b_array as $v_b) {	// 点b追加
				$st_write = $pdo->prepare("INSERT INTO {$vote_table}(topic_id,post_no,name,url,point,ip) VALUES(?,?,?,?,?,?)");
				$st_write->execute(array($id, $v_b, $name, $url, $topic['point_b'], $iplong));
				$st_score = $pdo->prepare("UPDATE {$post_table} SET score = score + ? WHERE no=?");
				$st_score->execute(array($topic['point_b'], $v_b));
			}
		}
		if (is_array($point_c_array)) {
			foreach ($point_c_array as $v_c) {	// 点c追加
				$st_write = $pdo->prepare("INSERT INTO {$vote_table}(topic_id,post_no,name,url,point,ip) VALUES(?,?,?,?,?,?)");
				$st_write->execute(array($id, $v_c, $name, $url, $topic['point_c'], $iplong));
				$st_score = $pdo->prepare("UPDATE {$post_table} SET score = score + ? WHERE no=?");
				$st_score->execute(array($topic['point_c'], $v_c));
			}
		}
		echo success_message("採点が送信されました","");
		session_destroy();// 採点作業ここまで
	}elseif($_POST["SUBMIT_CMT"]){// コメント投稿作業ここから
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("採点エラー","不正なPOSTが行われました");
			exit;
		}
		$name = $_POST["name"];
		$url = $_POST["url"];
		$body = $_POST["body"];
		$no = $_POST["no"];
		$ip = getenv("REMOTE_ADDR");
		$iplong = ip2long($ip);


		if(!($topic_format = 2 or $topic_format = 3)){
			echo error_message("コメント投稿エラー","現在コメントを受け付けておりません");	// 採点期間でも結果表示中でもない場合
			exit;
		}elseif($topic_format = 2 and ($topic['comment_accept']==2 or $topic['comment_accept']==3)){	// 採点期間中にコメント拒否時
			echo error_message("コメント投稿エラー","現在コメントを受け付けておりません");
			exit;
		}elseif($topic_format = 3 and ($topic['comment_accept']==1 or $topic['comment_accept']==3)){	// 結果表示中にコメント拒否時
			echo error_message("コメント投稿エラー","現在コメントを受け付けておりません");
			exit;
		}

		$st_write = $pdo->prepare("INSERT INTO {$comment_table}(topic_id,post_no,name,url,content,ip) VALUES(?,?,?,?,?,?)");
		$st_write->execute(array($id, $no, $name, $url, $body, $iplong));
	
		echo success_message("コメントが投稿されました","");
		// コメント投稿作業ここまで
	}else{
		echo error_message("エラー","書き込みに失敗しました。Cookieをオフにしている場合はオンにしてください。");
}
?>