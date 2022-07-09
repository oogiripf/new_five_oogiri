<?php

	if(empty($dataid)){$topic_table = "topic";}else{$topic_table = $dataid."_topic";}
	if(empty($dataid)){$post_table = "post";}else{$post_table = $dataid."_post";}
	if(empty($dataid)){$vote_table = "vote";}else{$vote_table = $dataid."_vote";}
	if(empty($dataid)){$comment_table = "comment";}else{$comment_table = $dataid."_comment";}
	if(empty($dataid)){$setting_table = "setting";}else{$setting_table = $dataid."_setting";}

	function error_message($error_head,$error_body){	// エラーメッセージここから
		$message = 1;
		$message_title = $error_head;
		$message_body = $error_body;
		include("message.php");
		exit;
	}	// エラーメッセージここまで

	function success_message($success_head,$success_body){	// 完了メッセージここから
		$message = 2;
		$message_title = $success_head;
		$message_body = $success_body;
		include("message.php");
	}	// 完了メッセージここまで

	function success_message_ad($success_head,$success_body){	// 完了メッセージ（管理人用）ここから
		$message = 2;
		$message_title = $success_head;
		$message_body = $success_body;
		$admin = true;
		include("message.php");
	}	// 完了メッセージここまで


	function report_rank($r,$val,$post){	// 途中経過順位表示ここから
?>
	<tr class="list">
		<td class="point_info"><?php echo h($r); ?><span class="rank_unit">位</span></td>
		<td class="point_info"><?php echo h($val); ?><span class="rank_unit">点</span></td>
		<td class="player"><?php echo h($post['name']); ?></td>
	</tr>
<?php
	}	// 途中経過順位表示ここまで


	function result($id,$r,$val,$post,$topic){	// 結果表示ここから
?>
	<tr class="list">
		<td class="point_info col-sm-1" nowrap>
			<div class="rank_num_box">
				<div>
					<span class="rank_num"><?php echo h($r); ?></span>
				</div>
				<div>
					<span class="point_num"><?php echo h($val); ?></span><span class="rank_unit">点</span>
				</div>
			</div>
		</td>
		<td class="post_cell col-sm-11">
			<ul class="meta">
				<li><span class="label_org"><?php if(!empty($post['url'])){ ?><a href="<?php echo h($post['url']); ?>">
		<?php }
		echo h($post['name']);
		if(!empty($post['url'])){ ?></a><?php } ?>さんの回答</span></li>
		<?php if(!empty($topic['vote_bonus'])){
			if(empty($post['vote_bonus'])){
				echo "<li>[未採点]</li>";	// 採点してない
			}else{
				echo "<li>[採点済]</li>";	// 採点した
			}
		}
			?>
			</ul>
			<div class="post_body">
				<a href="page.php?id=<?php echo h($id); ?>&amp;no=<?php echo h($post['no']); ?>" title="詳細を見る"><h3><?php
		if($topic['topic_type']=="normal"){
			echo nl2br(h($post['content']));
		}
		if($topic['topic_type']=="line"){
			if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
		else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post['content']); echo"</span>".h($topic['anaume_front']);}
		}
		?></h3></a>
			</div>
			<?php	foreach ($post['comments'] as $comment) {	?>
			<div class="comment">
			<p class="comment_name">
				<?php	if(!empty($comment['url'])){	?>
				<a href='<?php echo h($comment['url']); ?>'>
				<?php	}
				echo h($comment['name']);
				if(!empty($comment['url'])){?></a><?php } ?>
			</p>
			<p><?php echo nl2br(h($comment['content'])); ?></p>
			</div>
<?php
		}
?>

		</td>
		</tr>
<?php
	}	// 結果表示ここまで

	function ranking_post($r,$val,$post,$topic){	// 作品ランキング表示ここから
?>
	<tr class="list">
		<td class="point_info" nowrap><div class="ranking_posts_num"><?php echo h($r); ?><span class="rank_unit">位</span></div></td>
		<td class="point_info" nowrap><div class="ranking_posts_num"><?php echo h($val); ?><span class="rank_unit">点</span></div></td>
		<td class="ranking_content">
			<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
		<div class="post_heading"><span class="label_org"><?php	if(!empty($post['url'])){	?>
			<a href="<?php echo h($post['url']); ?>">
			<?php	}
				echo h($post['name']);
				if(!empty($post['url'])){	?>
			</a><?php } ?>さんの回答</span>
		</div>
		<div class="post_body"><a href="page.php?id=<?php echo h($post['topic_id']); ?>&amp;no=<?php echo h($post['no']); ?>" title="詳細を見る"><h3>
			<?php
				if($topic['topic_type']==normal){
				echo nl2br(h($post['content']));
				}
				if($topic['topic_type']==line){
					if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
					else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post['content']); echo"</span>".h($topic['anaume_rear']);}
				}
			?></h3></a>
		</div>
		</td>
		</tr>
<?php
	}	// 作品ランキング表示ここまで

	function ranking_player($r,$val,$name){	// プレイヤーランキング表示ここから
?>
	<tr class="list">
		<td class="point_info" nowrap><?php echo h($r); ?><span class="rank_unit">位</span></td>
		<td class="point_info" nowrap><?php echo h($val); ?><span class="rank_unit">点</span></td>
		<td class="player"><?php echo h($name); ?></td>
		</tr>
<?php
	}	// プレイヤーランキング表示ここまで


	function comment_form($no,$id){	// コメントフォームここから
?>
		<form method="post" action="preview.php" accept-charset="UTF-8" class="form-horizontal" id="post_form">
			<div class="comment_form_box">
				<div class="container">
			<div class="form-group">
				<label for="input_name" class="col-sm-3 control-label">お名前</label>
				<div class="col-sm-9">
					<input type="text" size="16" maxlength="30" name="NAME_PRE" class="form-control input-lg" id="input_name">
				</div>
			</div>
			<div class="form-group">
				<label for="input_url" class="col-sm-3 control-label">ウェブサイト</label>
				<div class="col-sm-9">
					<input type="text" size="30" maxlength="50" name="URL_PRE" class="form-control input-lg" id="input_url" placeholder="http://">
				</div>
			</div>
			<div class="form-group">
				<label for="input_body" class="col-sm-3 control-label">コメント</label>
				<div class="col-sm-9">
					<textarea wrap="soft" name="BODY_PRE" class="form-control input-lg" id="input_body" rows="5"></textarea>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
					<input type="hidden" value="<?php echo h($no); ?>" name="INPUTNO">
					<input type="hidden" value="<?php echo h($id); ?>" name="INPUTID">
					<input type="submit" name="SUBMIT_CMT" value="送信" class="button">
				</div>
			</div>
		</div>
	</div>
		</form>
<?php
	}	// コメントフォームここまで

	function paging($limit, $page, $id, $t){	// ページングここから
		$disp=5;
	//$dispはページ番号の表示数
		$next = $page+1;
		$prev = $page-1;
		//ページ番号リンク用
		$start =  ($page-floor($disp/2)> 0) ? ($page-floor($disp/2)) : 1;	//始点
		$end =  ($start> 1) ? ($page+floor($disp/2)) : $disp;	//終点
		$start = ($limit <$end)? $start-($end-$limit):$start;	//始点再計算
		if($page != 1 ) {
			if(empty($id) and empty($t)){echo '<li><a href="?p='.$prev.'">前へ</a></li>';}
			else{echo '<li><a href="?p='.$prev.'&amp;id='.$id.'&amp;t='.$t.'">前へ</a></li>';}
		}
		//最初のページへのリンク
		if($start>= floor($disp/2)){
			if(empty($id) and empty($t)){echo '<li><a href="?p=1">1</a></li>';}
			else{echo '<li><a href="?p=1'.$prev.'&amp;id='.$id.'&amp;t='.$t.'">1</a></li>';}
			if($start> floor($disp/2)) echo "..."; //ドットの表示
		}
		for($i=$start; $i <= $end ; $i++){	//ページリンク表示ループ
			$class = ($page == $i) ? ' class="active"':"";	//現在地を表すCSSクラス
			if($i <= $limit && $i> 0 )	//1以上最大ページ数以下の場合
			if(empty($id) and empty($t)){echo '<li'.$class.'><a href="?p='.$i.'">'.$i.'</a></li>';}
			else{echo '<li'.$class.'><a href="?p='.$i.'&amp;id='.$id.'&amp;t='.$t.'">'.$i.'</a></li>';}
		}
		//最後のページへのリンク
		if($limit> $end){
			if($limit-1> $end ) echo "...";	//ドットの表示
			if(empty($id) and empty($t)){echo '<li><a href="?p='.$limit.'">'.$limit.'</a></li>';}
			else{echo '<li><a href="?p='.$limit.'&amp;id='.$id.'&amp;t='.$t.'">'.$limit.'</a></li>';}
		}
		if($page <$limit){
			if(empty($id) and empty($t)){echo '<li><a href="?p='.$next.'">次へ</a></li>';}
			else{echo '<li><a href="?p='.$next.'&amp;id='.$id.'&amp;t='.$t.'">次へ</a></li>';}
		}
	}	// ページングここまで

	function table_check($tb_name,$pdo){	// テーブル存在チェックここから
		$rs =$pdo->query("SHOW TABLES"); // SHOWはMySQLでしか使えません
		$table = $rs->fetchAll(PDO::FETCH_COLUMN);
		if(in_array($tb_name,$table)){
			return true;
		}
		return false;
	}	// テーブル存在チェックここまで

	function column_check($tb_name,$column_name,$pdo){	// カラム存在チェックここから
		$rs =$pdo->query("DESCRIBE {$tb_name} {$column_name};");
		if($rs){
			return true;
		}else{
			return false;
		}
	}	// カラム存在チェックここまで


	function check_date($datetime){	// 日付の妥当性チェックここから
		list($date, $time) = explode(' ', $datetime);
		list($Y, $m, $d) = explode('/', $date);
		return checkdate($m, $d, $Y);
	}	// 日付の存在チェックここまで

	function check_time($datetime){	// 時間の妥当性チェックここから
		list($date, $time) = explode(' ', $datetime);
		list($hour, $min) = explode(':', $time);
		if ($hour < 0 or $hour > 23 or !is_numeric($hour)) {
			return false;
		}
		if ($min < 0 or $min > 59 or !is_numeric($min)) {
			return false;
		}
		return true;
	}	// 時間の妥当性チェックここまで

	function h($s){	// エスケープここから
		return htmlspecialchars($s, ENT_QUOTES);
	}	// エスケープここまで
?>
