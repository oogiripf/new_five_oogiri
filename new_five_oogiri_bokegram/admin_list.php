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

	$id=$_GET["id"];

	if(empty($id)){
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("データに関するエラー","ログがありません");
		exit;
	}else{
		$st = $pdo->query("SELECT * FROM {$topic_table} WHERE id={$id};");
		$topic = $st->fetch();
	}
	if(empty($topic)){
		$pagetitle = "エラー - ".$sitename;
		include("header.php");
		echo error_message("データに関するエラー","ログがありません");
		exit;
	}
?>
<div id="main">
	<?php
		$P=$_GET["p"];
		if(empty($P)){
			$P=1;
		}
		$Pd = $P-1;
		$per_topic = 20;
			
	
	
		if($_GET["t"] == "p"){ //	回答一覧ページここから
			
		$st_posts = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} ORDER BY no DESC;");
		if(empty($st_posts)){
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>回答がありません</h2></div>
			</div>
		</div>
	</div>
	<?php
		}else{
			$posts = $st_posts->fetchAll();
			$articles = $posts;
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php echo h($topic['title']); ?>での回答一覧</h2></div>
			</div>
		</div>
	</div>
	<?php
		}
	?>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
	<?php
		for($i=$Pd*$per_topic;$i<$Pd*$per_topic+$per_topic;$i++) {
			$post = $posts[$i];
			if(empty($post)){break;}
			$no = $post['no'];
			
	?>
			<div class="panel-heading"><?php echo h($post['name']); ?>さんの回答</div>
			<div class="panel-body post_body">
				<h3>
				<?php
				if($topic['topic_type']=='normal'){
					echo nl2br(h($post['content']));
				}
				if($topic['topic_type']==line){
					if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
					else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post['content']); echo"</span>".h($topic['anaume_rear']);}
				}
				?>
				</h3>
			</div>
				<table class="table edit_post_info">
					<tr>
						<th>純得点</th>
						<td><?php echo h($post['score']); ?></td>
					</tr>
					<tr>
						<th>ウェブサイト</th>
						<td><?php if(!empty($post['url'])){ ?><a href="<?php echo h($post['url']); ?>" target="_blank"><?php echo h($post['url']); ?></a><?php } ?></td>
					</tr>
					<tr>
						<th>投稿日時</th>
						<td><?php echo h($post['time']); ?></td>
					</tr>
					<tr>
						<th>IPアドレス</th>
						<td><?php echo long2ip($post['ip']); ?></td>
					</tr>
				</table>
			<div class="panel-body post_body">
				<form method='post' action='admin_preview.php' accept-charset='UTF-8'>
					<ul class="edit_menu">
						<li><button type='submit' name='DELETE_POST'>この回答を削除</button><input type='hidden' name="NO" value="<?php echo h($no); ?>"></li>
					</ul>
				</form>
			</div>
	<?php
		}
	?>
		</div>
		</div>
	</div>
	<?php
			
		}elseif($_GET["t"] == "v"){ //	採点一覧ページここから
			
		$st_votes = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} ORDER BY vote_no DESC;");
		if(empty($st_votes)){
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>回答がありません</h2></div>
			</div>
		</div>
	</div>
	<?php
		}else{
			$votes = $st_votes->fetchAll();
			$articles = $votes;
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php echo h($topic['title']); ?>での採点一覧</h2></div>
			</div>
		</div>
	</div>
	<?php
		}
	?>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
	<?php
		for($i=$Pd*$per_topic;$i<$Pd*$per_topic+$per_topic;$i++) {
			$vote = $votes[$i];
			if(empty($vote)){break;}
			$no = $vote['vote_no'];
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE no={$vote['post_no']};");
			$post = $st_post->fetch();
	?>
			<div class="panel-heading"><?php echo h($vote['name']); ?>さんから<?php echo h($post['name']); ?>さんへの投票</div>
				<table class="table edit_post_info">
					<tr>
						<th>回答の内容</th>
						<td>
				<?php
				if($topic['topic_type']== ''normal''){
					echo nl2br(h($post['content']));
				}
				if($topic['topic_type']== 'line'){
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
			<div class="panel-body post_body">
				<form method='post' action='admin_preview.php' accept-charset='UTF-8'>
					<ul class="edit_menu">
						<li><button type='submit' name='DELETE_VOTE'>この採点を削除</button><input type='hidden' name="NO" value="<?php echo h($no); ?>"></li>
					</ul>
				</form>
			</div>
	<?php
		}
	?>
		</div>
		</div>
	</div>
	<?php
			
		}elseif($_GET["t"] == "c"){ //	コメント一覧ページここから
			
		$st_comments = $pdo->query("SELECT * FROM {$comment_table} WHERE topic_id={$id} ORDER BY no DESC;");
		if(empty($st_comments)){
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2>コメントがありません</h2></div>
			</div>
		</div>
	</div>
	<?php
		}else{
			$comments = $st_comments->fetchAll();
			$articles = $comments;
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php echo h($topic['title']); ?>でのコメント一覧</h2></div>
			</div>
		</div>
	</div>
	<?php
		}
	?>
	<div class="admin_list_box">
		<div class="container">
		<div class="panel panel-default">
	<?php
		for($i=$Pd*$per_topic;$i<$Pd*$per_topic+$per_topic;$i++) {
			$comment = $comments[$i];
			if(empty($comment)){break;}
			$no = $comment['no'];
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE no={$comment['post_no']};");
			$post = $st_post->fetch();
	?>
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
			<div class="panel-body post_body">
				<form method='post' action='admin_preview.php' accept-charset='UTF-8'>
					<ul class="edit_menu">
						<li><button type='submit' name='DELETE_COMMENT'>このコメントを削除</button><input type='hidden' name="NO" value="<?php echo h($no); ?>"></li>
					</ul>
				</form>
			</div>
	<?php
		}
	?>
		</div>
		</div>
	</div>
	<?php
			
		}
			
		$Size=sizeof($articles);
		$limit = ceil($Size/$per_topic);	//最大ページ数
		if(isset($_GET["p"])){$page = $_GET["p"];}else{$page = 1;};	//ページ番号
	?>
		<div class="pagination_box">
	<div class="container">
		<ul class="pagination"><?php echo paging($limit, $page,$id,$_GET["t"]); ?></ul>
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