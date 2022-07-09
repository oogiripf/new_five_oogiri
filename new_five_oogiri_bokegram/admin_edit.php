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
	session_start();
	if(isset($_SESSION["TEST"]) && $_SESSION["TEST"] != null && md5(PASSWORD) === $_SESSION["TEST"]){
		$admin = TRUE;
		include("header.php");
?>

<div id="main">
<?php
	$P=$_GET["p"];
	if(empty($P)){
		$P=1;
	}
	$Pd = $P-1;
	$per_topic = 20;

	$dt = new DateTime();
	$dt->setTimeZone(new DateTimeZone("Asia/Tokyo"));
	$today = $dt->format("Y-m-d H:i:s");

	$st = $pdo->query("SELECT * FROM {$topic_table} ORDER BY id DESC");
	if(empty($st)){
?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelists-title"><h2>お題がありません</h2></div>
			</div>
		</div>
	</div>
<?php
	}else{
		$topics = $st->fetchAll();
	}
?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
				<div class="titlelists-title"><h2>データの編集・削除</h2></div>
			</div>
		</div>
	</div>
<?php
	for($i=$Pd*$per_topic;$i<$Pd*$per_topic+$per_topic;$i++) {
		$topic = $topics[$i];
		if(empty($topic)){break;}
		$id = $topic['id'];
		$title = h($topic['title']);
		$topic_format = $topic['topic_format'];
		$deadline = h($topic['deadline']);
		
		$st_posts = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id};");
			if(!empty($st_posts)){
				$posts = $st_posts->fetchALL();
			}
		$st_votes = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id};");
			if(!empty($st_votes)){
				$votes = $st_votes->fetchALL();
			}
		$st_comments = $pdo->query("SELECT * FROM {$comment_table} WHERE topic_id={$id};");
			if(!empty($st_comments)){
				$comments = $st_comments->fetchALL();
			}
		
		if($topic_format == 0){
			if(strtotime($topic['topic_post_date']) > strtotime($today)){$topic_format = 4; $deadline = "--";}	// 投稿開始日より前（準備）
			elseif(strtotime($topic['topic_vote_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_post_date'])){$topic_format = 1; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_vote_date'])); $deadline = $deadline."締切";}	// 採点開始日より前かつ投稿開始日より後（投稿）
			elseif(strtotime($topic['topic_result_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_vote_date'])){$topic_format = 2; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_result_date'])); $deadline = $deadline."締切";}	// 結果開始日より前かつ採点開始日より後（採点）
			elseif(strtotime($today) >= strtotime($topic['topic_result_date'])){$topic_format = 3; $deadline ="";}	// 結果開始日より後（結果）
		}
		if ($topic_format==1) {$info_message = "投稿";}	// 投稿中の表示名
		if ($topic_format==2) {$info_message = "採点";}	// 採点中の表示名
		if ($topic_format==3) {$info_message = "結果"; $deadline = "";}	// 結果中の表示名
		if ($topic_format==4) {$info_message = "準備";}	// 準備中の表示名
		if ($topic_format==5) {$info_message = "掲示";}	// 掲示中の表示名
		if ($topic_format==6) {$info_message = "非公開";}	// 非公開中の表示名
		if ($topic_format==7) {$info_message = "アーカイブ";}	// アーカイブの表示名
?>
<div class="index_box">
	<div class="container">
		<div class="titlelists">
			<div class="titlelist-title"><h2><?php echo h($title); ?></h2></div>
			<div class="titlelist-data"><?php echo h($info_message)." ".h($deadline); ?></div>
			<div class="titlelist-data">
				<form method="post" action="admin_preview.php" accept-charset="UTF-8">
					<ul class="edit_menu">
						<li><a href="admin_topic.php?id=<?php echo h($id); ?>">お題の編集</a></li>
						<?php if(!empty($posts)){ ?><li><a href="admin_list.php?id=<?php echo $id; ?>&amp;t=p">回答一覧</a></li><?php } ?>
						<?php if(!empty($votes)){ ?><li><a href="admin_list.php?id=<?php echo $id; ?>&amp;t=v">採点一覧</a></li><?php } ?>
						<?php if(!empty($comments)){ ?><li><a href="admin_list.php?id=<?php echo $id; ?>&amp;t=c">コメント一覧</a></li><?php } ?>
						<li><button type="submit" name="DELETE_TOPIC">お題の削除</button><input type='hidden' name="ID" value="<?php echo h($id); ?>"></li>
					</ul>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
	}
?>
	

<?php
	$Size=sizeof($topics);
	$limit = ceil($Size/$per_topic);	//最大ページ数
	if(isset($_GET["p"])){$page = $_GET["p"];}else{$page = 1;};	//ページ番号
?>
<div class="pagination_box">
	<div class="container">
		<ul class="pagination"><?php echo paging($limit, $page,NULL,NULL); ?></ul>
	</div>
</div>
</div>
<?php
	include("footer.php");	//	フッター読み込み
	}else{
		session_destroy();
		include("header.php");
		echo error_message("ログインエラー","もう一度ログインし直してください");
		exit;
	}
?>