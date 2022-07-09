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
	$ranking_player = $setting['ranking_player'];
	$ranking_post = $setting['ranking_post'];
	$pagetitle = "ランキング - ".$sitename;
	include("header.php");


?>
<div id="main">
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php 
		if($_GET["t"] != "post" or $_GET["id"] == "player"){ ?>総合得点ランキングTOP<?php echo $ranking_player; ?> <span class="annotation">（採点ボーナス点を除く）</span><?php }elseif($_GET["t"] == "post"){ ?>作品ランキングTOP<?php echo $ranking_post; ?> <span class="annotation">（採点ボーナス点を除く）</span><?php } ?></h2></div>
			</div>
		</div>
	</div>
	<div class="ranking_box">
	<div class="container">
		<div class="panel panel-default">
<?php

	$st = $pdo->query("SELECT id,topic_result_date,topic_format FROM {$topic_table};");
	if(!empty($st)){
		$topics = $st->fetchALL();
	}

	$today = date("Y-m-d H:i:s");

	if(!empty($topics)){
		foreach($topics as $i => $topic){ //	結果が出ているお題だけにする
			if($topic['topic_format'] == 0){
				if(strtotime($topic['topic_result_date']) > strtotime($today)){
					unset($topics[$i]);
					break;
				}
			}elseif($topic['topic_format'] != 3){
				unset($topics[$i]);
				break;
			}
			$str .= $topic['id'].",";
		}
		
	}

	if(!empty($str)){$str = substr($str, 0, -1);}


//	総合得点ランキングここから
if($_GET["t"] != "post" or $_GET["id"] == "player"){
	$st_players = $pdo->query("SELECT name, SUM(score) FROM {$post_table} WHERE topic_id IN({$str}) GROUP BY name ORDER BY SUM(score) DESC LIMIT {$ranking_player};");
	if(!empty($st_players)){
		$players = $st_players->fetchALL();
	}
?>
			<table class="table report">
				<tr class="post_info">
				<th class="point_info" nowrap>順位</th>
				<th class="point_info" nowrap>得点</th>
				<th class="ranking_content_head" nowrap>名前</th>
				</tr>
				
	<?php
	if(!empty($players)){
			$r=1;
			$c=0;
			foreach($players as $player){
				$val = $player['SUM(score)'];
				$c++;
				$r_pre=$r-1;
				if(isset($val_pre)){
					if($val_pre == $val) {	// 前回の得点と同じ場合（＝２位以降）
						echo ranking_player($r_pre,$val,$player['name']);
					}else{	// 前回の得点と同じではない場合
						$r=$c;
						echo ranking_player($r,$val,$player['name']);
						$r++;
					}
				}else{	// 1位の表示
					echo ranking_player($r,$val,$player['name']);
					$r++;
				}
				$val_pre = $val;
			}

	}
?>
			</table>
<?php
}
//	総合得点ランキングここまで
//	回答ランキングここから
if($_GET["t"] == "post"){
	$st_posts = $pdo->query("SELECT no, topic_id, name, url, content, score FROM {$post_table} WHERE topic_id IN({$str})  ORDER BY score DESC LIMIT {$ranking_post};");
	if(!empty($st_posts)){
		$posts = $st_posts->fetchALL();
	}
?>
			<table class="table report">
				<tr class="post_info">
				<th class="point_info" nowrap>順位</th>
				<th class="point_info" nowrap>得点</th>
				<th class="ranking_content_head" nowrap>回答</th>
				</tr>
				
	<?php
	if(!empty($posts)){
			$r=1;
			$c=0;
			foreach($posts as $post){
				$val = $post['score'];
				$c++;
				$r_pre=$r-1;
				$st_topic = $pdo->query("SELECT title, content, topic_type, anaume_front, anaume_rear, img, ext FROM {$topic_table} WHERE id = {$post['topic_id']};");
				$topic = $st_topic->fetch();
				if(isset($val_pre)){
					if($val_pre == $val) {	// 前回の得点と同じ場合（＝２位以降）
						echo ranking_post($r_pre,$val,$post,$topic);
					}else{	// 前回の得点と同じではない場合
						$r=$c;
						echo ranking_post($r,$val,$post,$topic);
						$r++;
					}
				}else{	// 1位の表示
					echo ranking_post($r,$val,$post,$topic);
					$r++;
				}
				$val_pre = $val;
			}

	}
?>
			</table>
<?php
}
//	回答ランキングここまで
?>
			
			
		</div>
	</div>
	</div>
</div>
<?php
	include("footer.php");
?>
