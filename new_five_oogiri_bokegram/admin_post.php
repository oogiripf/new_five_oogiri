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
	$st_setting = $pdo->query("SELECT * FROM {$setting_table}");
	$setting = $st_setting->fetch();
	$sitename = $setting['site_name'];
	$pagetitle = $sitename;

	define("PASSWORD", "$password");
	session_start();
	if(isset($_SESSION["TEST"]) && $_SESSION["TEST"] != null && md5(PASSWORD) === $_SESSION["TEST"]){
		$admin = TRUE;
		include("header.php");

		

	if ($_POST["POST_TOPIC"]){	// お題投稿・編集
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("投稿エラー","不正なPOSTが行われました");
			exit;
		}

		if(empty($_SESSION['id'])){ //	新規お題

		$st_write = $pdo->prepare("INSERT INTO {$topic_table}(title,content,topic_vote_date,topic_result_date,topic_type,anaume_front,anaume_rear,topic_post_date,topic_format,deadline,post_limit,point_a,point_b,point_c,point_a_limit,point_b_limit,point_c_limit,multiple_point,comment_accept,vote_bonus,self_recommendation,report_rank,img,ext,public_vote) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$st_write->execute(array($_SESSION['title'], $_SESSION['body'], $_SESSION['topic_vote_date'], $_SESSION['topic_result_date'], $_SESSION['topic_type'], $_SESSION['anaume_front'], $_SESSION['anaume_rear'], $_SESSION['topic_post_date'], $_SESSION['topic_format'], $_SESSION['deadline'], $_SESSION['post_limit'], $_SESSION['point_a'], $_SESSION['point_b'], $_SESSION['point_c'], $_SESSION['point_a_limit'], $_SESSION['point_b_limit'], $_SESSION['point_c_limit'], $_SESSION['multiple_point'], $_SESSION['comment_accept'], $_SESSION['vote_bonus'], $_SESSION['self_recommendation'], $_SESSION['report_rank'], $_SESSION['img'], $_SESSION['ext'], $_SESSION['public_vote']));
		echo success_message_ad("投稿されました","");
		
		}else{ //	過去お題
			$st_write = $pdo->prepare("UPDATE {$topic_table} SET title=?, content=?, topic_vote_date=?, topic_result_date=?, topic_type=?, anaume_front=?, anaume_rear=?, topic_post_date=?, topic_format=?, deadline=?, post_limit=?, point_a=?, point_b=?, point_c=?, point_a_limit=?, point_b_limit=?, point_c_limit=?, multiple_point=?, comment_accept=?, vote_bonus=?, self_recommendation=?, report_rank=?, img=?, ext=?, public_vote=? WHERE id=?");
			$st_write->execute(array($_SESSION['title'], $_SESSION['body'], $_SESSION['topic_vote_date'], $_SESSION['topic_result_date'], $_SESSION['topic_type'], $_SESSION['anaume_front'], $_SESSION['anaume_rear'], $_SESSION['topic_post_date'], $_SESSION['topic_format'], $_SESSION['deadline'], $_SESSION['post_limit'], $_SESSION['point_a'], $_SESSION['point_b'], $_SESSION['point_c'], $_SESSION['point_a_limit'], $_SESSION['point_b_limit'], $_SESSION['point_c_limit'], $_SESSION['multiple_point'], $_SESSION['comment_accept'], $_SESSION['vote_bonus'], $_SESSION['self_recommendation'], $_SESSION['report_rank'], $_SESSION['img'], $_SESSION['ext'], $_SESSION['public_vote'], $_SESSION['id']));
		echo success_message_ad("更新されました","");
			
		}
		
		
	}elseif ($_POST["DELETE_TOPIC"]){ //	お題を削除
		
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("編集エラー","不正なPOSTが行われました");
			exit;
		}
		
		if(empty($_SESSION['id'])){
			echo error_message("編集エラー","お題が選択されていません");
			exit;
		}else{
			$st = $pdo->prepare("DELETE FROM {$topic_table} WHERE id=?");
			$st->execute(array($_SESSION['id']));
			
			$st_post = $pdo->prepare("DELETE FROM {$post_table} WHERE topic_id=?");
			$st_post->execute(array($_SESSION['id']));
			
			$st_vote = $pdo->prepare("DELETE FROM {$vote_table} WHERE topic_id=?");
			$st_vote->execute(array($_SESSION['id']));
			
			$st_comment = $pdo->prepare("DELETE FROM {$comment_table} WHERE topic_id=?");
			$st_comment->execute(array($_SESSION['id']));
			
			echo success_message_ad("削除されました","");
		}
		
	}elseif ($_POST["DELETE_POST"]){ //	回答を削除
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("編集エラー","不正なPOSTが行われました");
			exit;
		}
		
		if(empty($_SESSION['no'])){
			echo error_message("編集エラー","回答が選択されていません");
			exit;
		}else{
			$st = $pdo->prepare("DELETE FROM {$post_table} WHERE no=?");
			$st->execute(array($_SESSION['no']));
			
			$st_vote = $pdo->prepare("DELETE FROM {$vote_table} WHERE post_no=?");
			$st_vote->execute(array($_SESSION['no']));
						
			$st_comment = $pdo->prepare("DELETE FROM {$comment_table} WHERE post_no=?");
			$st_comment->execute(array($_SESSION['no']));
			
			echo success_message_ad("削除されました","");
		}
	}elseif ($_POST["DELETE_VOTE"]){ //	採点を削除
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("編集エラー","不正なPOSTが行われました");
			exit;
		}
		
		if(empty($_SESSION['no'])){
			echo error_message("編集エラー","採点が選択されていません");
			exit;
		}else{
			$st = $pdo->prepare("DELETE FROM {$vote_table} WHERE vote_no=?");
			$st->execute(array($_SESSION['no']));
			
			$st_score = $pdo->prepare("UPDATE post SET score = score - ? WHERE no=?");
			$st_score->execute(array($_SESSION['point'], $_SESSION['post_no']));
			
			echo success_message_ad("削除されました","");
		}
	}elseif ($_POST["DELETE_COMMENT"]){ //	コメントを削除
		if($_SESSION['token'] !== $_POST['token']){
			echo error_message("編集エラー","不正なPOSTが行われました");
			exit;
		}
		
		if(empty($_SESSION['no'])){
			echo error_message("編集エラー","コメントが選択されていません");
			exit;
		}else{
			$st = $pdo->prepare("DELETE FROM {$comment_table} WHERE no=?");
			$st->execute(array($_SESSION['no']));
			
			echo success_message_ad("削除されました","");
		}
	}
	}else{
		session_destroy();
		include("header.php");
		echo error_message("ログインエラー","もう一度ログインし直してください");
		exit;
	}
?>