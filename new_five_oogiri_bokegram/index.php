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
	
	if(!empty($st_setting)){
		$setting = $st_setting->fetch();
		$sitename = $setting['site_name'];
	}else{
		$sitename = "Bokegram";
	}
		$pagetitle = $sitename;

	include("header.php");	//	ヘッダー読み込み
?>

<div id="main">
<?php
	$P=$_GET["p"];
	if(empty($P)){
		$P=1;
	}
	$Pd = $P-1;
	$per_topic = $setting['per_topic'];

	$dt = new DateTime();
	$dt->setTimeZone(new DateTimeZone("Asia/Tokyo"));
	$today = $dt->format("Y-m-d H:i:s");

	$st = $pdo->query("SELECT * FROM {$topic_table} WHERE topic_format NOT IN ('6') ORDER BY id DESC");
	if(empty($st)){
?>
	<div class="index_box">
		<div class="container">
		<a class="titlelists" href="login.php"; ?>
		<div class="titlelists-title"><h2>ボケグラムのセッティングを行います</h2></div>
		</a>
		<p class="content">右上の「管理」からログインしましょう</p>
		</div>
	</div>
<?php
	}else{
		$topics = $st->fetchAll();
	}
?>
<div class="top_index_box">
		<div class="container">
		<?php
	if(!empty($setting['explanation'])){
		?>
		<div class="titlelists">
			<div class="explanation_body"><?php echo nl2br($setting['explanation']); ?></div>
		</div>
		
		<?php
	}

	for($i=$Pd*$per_topic;$i<$Pd*$per_topic+$per_topic;$i++) {
		$topic = $topics[$i];
		if(empty($topic)){break;}
		$id = $topic['id'];
		$title = h($topic['title']);
		$topic_format = $topic['topic_format'];
		$deadline = h($topic['deadline']);
		if(empty($deadline)){$deadline = "--";}
		$icon_colored = false;
		if($topic_format == 0){
			if(strtotime($topic['topic_post_date']) > strtotime($today)){$topic_format = 4; $deadline = "--";}	// 投稿開始日より前（準備）
			elseif(strtotime($topic['topic_vote_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_post_date'])){$topic_format = 1; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_vote_date'])); $deadline = $deadline."締切";}	// 採点開始日より前かつ投稿開始日より後（投稿）
			elseif(strtotime($topic['topic_result_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_vote_date'])){$topic_format = 2; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_result_date'])); $deadline = $deadline."締切";}	// 結果開始日より前かつ採点開始日より後（採点）
			elseif(strtotime($today) >= strtotime($topic['topic_result_date'])){$topic_format = 3;}	// 結果開始日より後（結果）
		}
		if ($topic_format==1) {$info_message = "投稿"; $title_icon = "icon-comment"; $icon_colored = true;}	// 投稿中の表示名
		if ($topic_format==2) {$info_message = "採点"; $title_icon = "icon-check-square"; $icon_colored = true;}	// 採点中の表示名
		if ($topic_format==3) {$info_message = "結果"; $title_icon = "icon-list"; $deadline = "--";}	// 結果中の表示名
		if ($topic_format==4) {$info_message = "準備"; $title_icon = "icon-hourglass";}	// 準備中の表示名
		if ($topic_format==5) {$info_message = "掲示"; $title_icon = "icon-bullhorn";}	// 掲示中の表示名
		if ($topic_format==7) {$info_message = "アーカイブ"; $title_icon = "icon-archive";}	// アーカイブの表示名

?>
<a class="titlelists" href="page.php?id=<?php echo h($id); ?>">
	<div class="titlelist-icon<?php if($icon_colored){echo" icon_colored_block";} ?>"><span class="glyphicon <?php echo $title_icon ; if($icon_colored){echo" icon_colored";} ?>"></span></div>
	<div class="titlelist-group">
		<div class="titlelist-title"><h2><?php echo h($title); ?></h2></div>
		<div class="titlelist-data"><?php echo h($info_message)."　".h($deadline); ?></div>
	</div>
</a>
<?php
	}
?>
	</div>
	</div>
<?php
	if(!empty($st)){
	$Size=sizeof($topics);
	$limit = ceil($Size/$per_topic);	//最大ページ数
	if(isset($_GET["p"])){$page = $_GET["p"];}else{$page = 1;};	//ページ番号
?>
<div class="pagination_box">
	<div class="container">
		<ul class="pagination"><?php echo paging($limit, $page, NULL,NULL ); ?></ul>
	</div>
</div>
<?php } ?>
</div>
<?php
	include("footer.php");	//	フッター読み込み
?>