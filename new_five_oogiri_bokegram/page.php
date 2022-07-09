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

	$per_post = $setting['per_post'];

	$id = $_GET["id"];
	$no = $_GET["no"];
	$vote_no = $_GET["v_no"];

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
		$today = date("Y-m-d H:i:s");
		$deadline = $topic['deadline'];
			if($topic['topic_format'] == 0){
				if(strtotime($topic['topic_post_date']) > strtotime($today)){$topic_format = 4; $deadline = "--";}	// 投稿開始日より前（準備）
				elseif(strtotime($topic['topic_vote_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_post_date'])){$topic_format = 1; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_vote_date']));}	// 採点開始日より前かつ投稿開始日より後（投稿）
				elseif(strtotime($topic['topic_result_date']) > strtotime($today) and strtotime($today) >= strtotime($topic['topic_vote_date'])){$topic_format = 2; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_result_date']));}	// 結果開始日より前かつ採点開始日より後（採点）
				elseif(strtotime($today) >= strtotime($topic['topic_result_date'])){$topic_format = 3; $deadline = date("Y年m月d日H時i分",strtotime($topic['topic_vote_date']));}	// 結果開始日より後（結果）
			}else{
			$topic_format = $topic['topic_format'];
			}

		$p_a_limit = $topic['point_a_limit'];
		$p_b_limit = $topic['point_b_limit'];
		$p_c_limit = $topic['point_c_limit'];

		$point_a = $topic['point_a'];
		$point_b = $topic['point_b'];
		$point_c = $topic['point_c'];

		// 非公開ページ
		if($topic_format == 6){
			$pagetitle = "エラー - ".$sitename;
			include("header.php");
			echo error_message("データに関するエラー","ログがありません");
			exit;
		}

	$pagetitle = $topic['title']." - ".$sitename;
	$page = true;
	include("header.php");

?>
<div id="main">
	<?php

		// 準備ページ
		if($topic_format == 4){
	?>
		<div class="article_box">
			<div class="container">
			<div class="titlelists">
				<div class="titlelists-title"><h2>準備中...</h2></div>
				<div class="article_body">投稿期間前です</div>
			</div>
			</div>
		</div>
	<?php
		}

		// 掲示ページ
		if($topic_format == 5){
	?>
<div class="topic_box">
	<div class="container">
		<div class="topic_heading"><span class="label_org">お知らせ</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
	</div>
</div>
	<?php
		}

		// 投稿ページ
		if($topic_format == 1) {
	?>
<div class="topic_box">
	<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
	</div>
</div>
<div class="notice_box">
	<div class="container padding_none">
	<ul class='notice'>
		<?php
			if(empty($topic['post_limit'])){echo "<li>投稿した回答が全て記録されます。</li>";}
			else{echo"<li>1人あたり{$topic['post_limit']}回答までが記録されます。</li><li>投稿数が{$topic['post_limit']}回を越えた場合、最新の{$topic['post_limit']}つが残ります。</li>";}
			?>
		<li>投稿締め切り：<span class='limit'><?php echo h($deadline); ?></span></li>
	</ul>
</div>
</div>
<form method="post" action="preview.php"  class="form-horizontal" id="post_form" accept-charset="UTF-8">
	<div class="post_form_box">
		<div class="container">
			<div class="form-group">
				<label for="input_name" class="col-sm-3 control-label">お名前</label>
				<div class="col-sm-9">
	<input type="text" name="NAME_PRE" id="input_name" class="form-control input-lg">
				</div>
			</div>
			<div class="form-group">
				<label for="input_body" class="col-sm-3 control-label">回答</label>
				<div class="col-sm-9">
		<?php
				if($topic['topic_type']=="normal"){
	?><textarea wrap="soft" name="BODY_PRE" id="input_body" class="form-control input-lg" rows="5"></textarea><?php
				}
				if($topic['topic_type']=="line"){
	?><input type="text" name="BODY_PRE" id="input_body" class="form-control input-lg">
			<?php if(!empty($topic['anaume_front']) or !empty($topic['anaume_rear'])){ ?><p class="help-block">穴埋めお題の場合は空欄に入る部分を入力してください。</p><?php
	}
				}
		?>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
	<input type="hidden" value="<?php echo h($id); ?>" name="INPUTID">
	<input type="submit" name="SUBMIT_POST" value="投稿" class="button">
				</div>
			</div>
		</div>
	</div>
</form>
	<div class="players_box">
		<div class="container">
		<?php
				$st_post_distinct = $pdo->query("SELECT DISTINCT NAME FROM {$post_table} WHERE topic_id={$id};");
				$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} ORDER BY name;");
				$players = $st_post_distinct->fetchAll();
				$count_p = sizeof($players);
		?>
				<div>
					<h3 class="players_heading"><span class="label_org">投稿して頂いた方：<?php echo h($count_p); ?>名</span></h3>
				</div>
				<div class="players_body">
		<?php
				if(!empty($st_post)){
	while ($row = $st_post->fetch()) {
		$name = h($row['name']);
		$url = h($row['url']);
		if(empty($pre_name)){$pre_name = $name; $pre_url = $url; $count_name = 1;}
		else{
			if($pre_name == $name){
				$count_name++;
			}else{
				if(empty($pre_url)){echo h($pre_name);}
				else{echo"<a href='".h($pre_url)."'>".h($pre_name)."</a>";}
				if($count_name >= 2){echo h($count_name);}
				echo" / ";
				$pre_name = $name;
				$pre_url = $url;
				$count_name = 1;
			}
		}
	}
	if(empty($pre_url)){echo h($pre_name);}
	else{echo"<a href='".h($pre_url)."'>".h($pre_name)."</a>";}
	if($count_name >= 2){echo h($count_name);}
				}
		?>
				</div>
			</div>
		</div>
	<?php
			}

		// 採点ページ
		if ($topic_format == 2) {
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id};");
			$posts = $st_post->fetchAll();
			if(!isset($no)) {	// 一覧ページ
	?>
			<script>	// リロード対策
				$(document).ready(function(){
				$("input:checkbox[class^='chk_']").prop({"checked":false});
				});
			</script>
			<div class="topic_box">
				<div class="container">
				<div class="topic_heading"><span class="label_org">お題</span></div>
				<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
				</div>
			</div>
			<div class="notice_box">
				<div class="container padding_none">
					<ul class="notice">
				<?php
						if(!empty($topic['multiple_point'])){echo"<li>1つの回答に複数のチェックを付けて送信できます。</li>";};
						if(!empty($topic['point_a_limit'])){echo"<li>{$topic['point_a']}点は{$topic['point_a_limit']}つの回答にしか付けられません。</li>";};
						if(!empty($topic['point_b_limit'])){echo"<li>{$topic['point_b']}点は{$topic['point_b_limit']}つの回答にしか付けられません。</li>";};
						if(!empty($topic['point_c_limit'])){echo"<li>{$topic['point_c']}点は{$topic['point_c_limit']}つの回答にしか付けられません。</li>";};
						if(!empty($topic['vote_bonus'])){echo"<li>投稿者が採点にも参加した場合、{$topic['vote_bonus']}点が入ります。</li>";};
						if(!empty($topic['public_vote'])){echo"<li>誰から点が入ったか結果発表後に公開されます。</li>";}
						else{echo"<li>誰から点が入ったか公開されません。</li>";};
						if(empty($topic['self_recommendation'])){echo"<li>自薦禁止です。</li>";};
				?>
					<li>採点締め切り：<span class="limit"><?php echo h($deadline); ?></span></li>
				</ul>
			</div>
		</div>
				<?php
						if(empty($posts)){	// 投稿無しだった場合
				?>
		<div class="article_box">
			<div class="container">
			<div class="noentry">この回の投稿はありませんでした。</div>
			</div>
		</div>
				<?php
						}else{	// 投稿がある場合
				?>
		<form method="post" action="preview.php" accept-charset="UTF-8" class="form-horizontal" id="post_form">
			<div class="form_box">
				<div class="container">
			<table class="table postlist">
				<tbody>
				<?php
						$total_entries = count($posts);
						$entries = 0;
						$b=0;
				        shuffle($posts);
						if (!empty($posts)){
						foreach ((array)$posts as $post) {
			$b++;
						?>
					<tr class="list row">
						<th class="point_info col-sm-3">
							<div id="mode" class="btn-group" data-toggle="buttons">
						<?php
			if(!empty($point_a)){echo"<label class='chk_".$post['no']." btn btn-default'><input type='checkbox' class='chk_".$post['no']."' name='vno_1[]' value='".$post['no']."'>".$point_a."</label>";}
			if(!empty($point_b)){echo"<label class='chk_".$post['no']." btn btn-default'><input type='checkbox' class='chk_".$post['no']."' name='vno_2[]' value='".$post['no']."'>".$point_b."</label>";}
			if(!empty($point_c)){echo"<label class='chk_".$post['no']." btn btn-default'><input type='checkbox' class='chk_".$post['no']."' name='vno_3[]' value='".$post['no']."'>".$point_c."</label>";}
						?>
							</div>
						</th>
						<td class='post_cell col-sm-9'>
						<?php
			if($topic['topic_type'] == "normal"){
				if($topic['comment_accept']==0 or $topic['comment_accept']==1){echo"<a href='page.php?id=".$id."&amp;no=".$post['no']."' target='_blank'>";}
				echo nl2br(h($post['content']));
				if($topic['comment_accept']==0 or $topic['comment_accept']==1){echo"</a>";}
			}
			if($topic['topic_type'] == "line"){
				if($topic['comment_accept']==0 or $topic['comment_accept']==1){echo"<a href='page.php?id=".$id."&amp;no=".$post['no']."' target='_blank'>";}
				if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post['content']);}
				else{echo h($topic['anaume_front'])."<span class='anaume'>";echo h($post['content']); echo"</span>".h($topic['anaume_rear']);}
				if($topic['comment_accept']==0 or $topic['comment_accept']==1){echo"</a>";}
			}
						?>
						</td>
					</tr>
						<?php
			$entries++;
			if($b==$per_post and $total_entries != $entries){
			?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="topic_box">
		<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
		</div>
	</div>
					<div class="form_box">
						<div class="container">
					<table class="table postlist">
						<tbody>
			<?php
				$b=0;
			}
						}
						}
				?>
				</tbody>
			</table>

			<div class="form-group">
				<label for="input_name" class="col-sm-3 control-label">お名前</label>
				<div class="col-sm-9">
					<input type="text" name="NAME_PRE" class="form-control input-lg" id="input_name">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
					<input type="hidden" value="<?php echo h($id); ?>" name="INPUTID">
					<input type="submit" name="SUBMIT_VOTE" value="採点" class="button">
				</div>
			</div>
		</div>
	</div>
	</form>
				<?php
						$st_vote_distinct = $pdo->query("SELECT DISTINCT NAME FROM {$vote_table} WHERE topic_id={$id};");
						$st_vote = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} ORDER BY name;");
						$voters = $st_vote_distinct->fetchAll();
						$count_v = sizeof($voters);
				?>
				<div class="players_box">
					<div class="container">
						<div>
						<h3 class="players_heading"><span class="label_org">採点して頂いた方：<?php echo h($count_v); ?>名</span></h3>
					</div>
					<div class="players_body">
									<?php
						if(!empty($st_vote)){
								while ($row = $st_vote->fetch()) {
									$name = h($row['name']);
									$url = h($row['url']);
									if(empty($pre_name)){$pre_name = $name; $pre_url = $url; $count_name = 1;}
									else{
					if($pre_name == $name){
						$count_name++;
					}else{
						if(empty($pre_url)){echo h($pre_name);}
						else{echo"<a href='".$pre_url."'>".h($pre_name)."</a>";}
						echo" / ";
						$pre_name = $name;
						$pre_url = $url;
						$count_name = 1;
					}
									}
								}
								if(empty($pre_url)){echo h($pre_name);}
								else{echo"<a href='".h($pre_url)."'>".h($pre_name)."</a>";}
						}
									?>
						</div>
					</div>
				</div>
				<?php
					}
						}else{	// 採点期間中個別ページ
			$st_post_no = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} AND no={$no};");
			$post_no = $st_post_no->fetch();
				?>
				<div class="topic_box">
					<div class="container">
					<div class="topic_heading"><span class="label_org">お題</span></div>
					<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
					</div>
				</div>
			<?php
						if(empty($posts)){	// 投稿無しだった場合
			?>
		<div class="article_box">
			<div class="container">
			<div class="noentry">この回の投稿はありませんでした。</div>
			</div>
		</div>
			<?php
					}else{
			?>
			<div class="post_box">
				<div class="container">
				<div class="post_heading"><span class="label_org">回答</span></div>
				<div class="post_body"><h3>
				<?php
							if($topic['topic_type']== "normal"){
				echo nl2br(h($post_no['content']));
							}
							if($topic['topic_type']== "line"){
				if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_no['content']);}
				else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post_no['content']); echo"</span>".h($topic['anaume_rear']);}
							}
				?>
				</h3></div>
				</div>
			</div>
			<?php
			if($topic['comment_accept']==0 or $topic['comment_accept']==1){
				echo comment_form($no, $id);
			}
						}
			?>
				<?php
						}
					// 採点期間中　途中経過公開モード
						if(!empty($posts) and !isset($no) and !empty($topic['report_rank'])){
				?>
					<div class="report_box">
						<div class="container">
							<div>
								<h3 class="players_heading"><span class="label_org">現在の時点での得点順位</span></h3>
							</div>
	<table class="table report">
				<?php
						$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id};");
						$posts = $st_post->fetchAll();
						$st_vote = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id};");
						$votes = $st_vote->fetchAll();

						for ($i = 0; $i < count($posts); $i++){
			$st_point = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND post_no={$posts[$i]['no']} ;");
			$points_array = $st_point->fetchAll();
			if(empty($points_array)){$points = 0;}
			else{
				foreach($points_array as $point_array){
					$points += $point_array['point'];
				}
			}
			if(!empty($topic['vote_bonus'])){
				$st_vote_bonus = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND name='{$posts[$i]['name']}' ");   // 名前が一致するプレイヤーが採点に参加していた場合にボーナス加点
				if(!empty($st_vote_bonus)){$vote_bonus_array = $st_vote_bonus->fetchAll();}
				if(!empty($vote_bonus_array)){
					$points += $topic['vote_bonus'];
					$posts[$i]['vote_bonus'] = $topic['vote_bonus'];
				}else{
					$posts[$i]['vote_bonus'] = 0;
				}
			}
			$posts[$i]['points'] = $points;
			$points = 0;
			$st = $pdo->query("SELECT * FROM {$comment_table} WHERE post_no={$posts[$i]['no']} ORDER BY no DESC");
			$posts[$i]['comments'] = $st->fetchAll(); //	回答ごとにコメント一覧を格納
						}

						$point_sort = array();
						foreach ($posts as $v) $point_sort[] = $v['points'];
						array_multisort($point_sort, SORT_DESC, SORT_NUMERIC, $posts);
						$r=1;
						$c=0;

						foreach($posts as $post){
			$val = $post['points'];
			$vote_bonus = $post['vote_bonus'];
			$post_no = $post['no'];
			$c++;
			$r_pre=$r-1;
			if(isset($val_pre)){
				if($val_pre == $val) {	// 前回の得点と同じ場合（＝２位以降）
					echo report_rank($r_pre,$val,$post);
				}else{	// 前回の得点と同じではない場合
					$r=$c;
					echo report_rank($r,$val,$post);
					$r++;
				}
			}else{	// 1位の表示
				echo report_rank($r,$val,$post);
				$r++;
			}
				$val_pre = $val;
			}

				?>
					</table>
				</div>
			</div>
				<?php
						}
				?>
	<?php
		}


		// 結果ページ or アーカイブページ
		if ($topic_format == 3 or $topic_format == 7) {
			$st_post = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id};");
			$posts = $st_post->fetchAll();
			$st_vote = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id};");
			$votes = $st_vote->fetchAll();

			for ($i = 0; $i < count($posts); $i++){
				$st_point = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND post_no={$posts[$i]['no']} ;");
				$points_array = $st_point->fetchAll();
				if(empty($points_array)){$points = 0;}
				else{
					foreach($points_array as $point_array){
						$points += $point_array['point'];
					}
				}
				if(!empty($topic['vote_bonus'])){
					$st_vote_bonus = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND name='{$posts[$i]['name']}' ");   // 名前が一致するプレイヤーが採点に参加していた場合にボーナス加点
					if(!empty($st_vote_bonus)){$vote_bonus_array = $st_vote_bonus->fetchAll();}
					if(!empty($vote_bonus_array)){
						$points += $topic['vote_bonus'];
						$posts[$i]['vote_bonus'] = $topic['vote_bonus'];
					}else{
						$posts[$i]['vote_bonus'] = 0;
					}
				}
				$posts[$i]['points'] = $points;
				$points = 0;
				$st = $pdo->query("SELECT * FROM {$comment_table} WHERE post_no={$posts[$i]['no']} ORDER BY no DESC");
				$posts[$i]['comments'] = $st->fetchAll(); //	回答ごとにコメント一覧を格納
			}

			$point_sort = array();
			foreach ($posts as $v) $point_sort[] = $v['points'];
			array_multisort($point_sort, SORT_DESC, SORT_NUMERIC, $posts);



			if(!empty($posts)){
				$count_p = sizeof($posts);
				$r=1;
				$c=0;
			}else{
				$count_p = 0;
			}
			if(!isset($no) and !isset($vote_no)) {	// 結果一覧ページ
	?>
	<div class="topic_box">
		<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
		<div class="topic_body"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></div>
		</div>
	</div>
	<div class="notice_box">
		<div class="container padding_none">
			<ul class="notice">
				<li>総投稿数：<?php echo h($count_p); ?></li>
				<li>終了日時：<?php echo h($deadline); ?></li>
				<?php if(!empty($topic['vote_bonus'])){echo"<li>採点に参加した回答者には{$topic['vote_bonus']}点が加算されます。</li>";}?>
			</ul>
	</div>
	</div>
				<?php if(empty($posts)){
				?>
		<div class="article_box">
			<div class="container">
			<div class="noentry">この回の投稿はありませんでした。</div>
			</div>
		</div>
				<?php
						}else{
				?>
		<div class="table_box">
			<div class="container">
			<table class="table result">
				<tbody>
				<?php
			foreach($posts as $post){
				$val = $post['points'];

				$c++;
				$r_pre=$r-1;
				if(isset($val_pre)){
					if($val_pre == $val) {	// 前回の得点と同じ場合（＝２位以降）
						echo result($id,$r_pre,$val,$post,$topic);
					}else{	// 前回の得点と同じではない場合
						$r=$c;
						echo result($id,$r,$val,$post,$topic);
						$r++;
					}
				}else{	// 1位の表示
					echo result($id,$r,$val,$post,$topic);
					$r++;
				}
				$val_pre = $val;
			}
				?>
				</tbody>
			</table>
		</div>
	</div>
				<?php
			}
			$st_vote_distinct = $pdo->query("SELECT DISTINCT NAME FROM {$vote_table} WHERE topic_id={$id};");
			$st_vote = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} ORDER BY name;");
			$voters = $st_vote_distinct->fetchAll();
			$count_v = sizeof($voters);
			if(!empty($posts)){
				?>
				<div class="players_box">
					<div class="container">
						<div>
							<h3 class="players_heading"><span class="label_org">採点して頂いた方：<?php echo h($count_v); ?>名</span></h3>
					</div>
					<div class="players_body">

				<?php
			if(!empty($st_vote)){
				while ($row = $st_vote->fetch()) {
					$name = h($row['name']);
					$url = h($row['url']);
					if(empty($pre_name)){$pre_name = $name; $pre_url = $url; $count_name = 1;}
					else{
						if($pre_name == $name){
							$count_name++;
						}else{
							if(empty($pre_url)){echo h($pre_name);}
							else{echo"<a href='".h($pre_url)."'>".h($pre_name)."</a>";}
							echo" / ";
							$pre_name = $name;
							$pre_url = $url;
							$count_name = 1;
						}
					}
				}
				if(empty($pre_url)){echo h($pre_name);}
				else{echo"<a href='".h($pre_url)."'>".h($pre_name)."</a>";}
			}
							?>
						</div>
					</div>
				</div>
	<?php
				}


			}elseif(isset($no) and !isset($vote_no)){	//結果表示中個別ページ
				$st_post_no = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} AND no={$no};");
				$post_no = $st_post_no->fetch();
	?>
	<div class="topic_box">
		<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
			<div class="topic_body"><a href="page.php?id=<?php echo h($id); ?>" title="このお題の回答一覧を見る"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></a></div>
		</div>
	</div>
						<?php
									if(empty($posts)){	// 投稿無しだった場合
						?>
		<div class="article_box">
			<div class="container">
			<div class="noentry">この回の投稿はありませんでした。</div>
			</div>
		</div>
						<?php
									}else{
						?>
						<div class="post_box">
							<div class="container">
							<div class="post_heading"><span class="label_org"><?php	if(!empty($post_no['url'])){	?>
			<a href="<?php echo h($post_no['url']); ?>">
							<?php	}
							echo h($post_no['name']);
							if(!empty($post_no['url'])){	?>
				</a><?php } ?>さんの回答</span></div>
							<div class="post_body"><h3>
							<?php
										if($topic['topic_type']== "normal"){
							echo nl2br(h($post_no['content']));
										}
										if($topic['topic_type']== "line"){
							if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_no['content']);}
							else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post_no['content']); echo"</span>".h($topic['anaume_rear']);}
										}
							?>
							</h3>
						</div>
							</div>
						</div>
									<?php
									if(!empty($topic['public_vote'])){
									$st_vote_no = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND post_no={$no};");
									$votes_no = $st_vote_no->fetchALL();
									if(!empty($votes_no)){
									?>
									<div class="voter_box">
										<div class="container">
										<div>
						<h3 class="players_heading"><span class="label_org">この回答への投票</span></h3>
					</div>
					<table class="table report">
									<?php	foreach($votes_no as $vote_no){	?>
						<tr class="list">
							<td class="point_info"><?php echo $vote_no['point']; ?><span class="rank_unit">点</span></td>
							<td class="player"><a href="page.php?id=<?php echo h($id); ?>&amp;v_no=<?php echo h($vote_no['vote_no']); ?>" title="このお題での<?php echo h($vote_no['name']); ?>さんの投票先一覧"><?php echo h($vote_no['name']); ?></a></td>
						</tr>
									<?php
					}
					?></table></div>
			</div><?php
									}
									}
								if($topic['comment_accept']==0 or $topic['comment_accept']==2){
									echo comment_form($no, $id);
								}
	}
			}elseif(isset($vote_no)){	//採点者の投票先の一覧ページ
				$st_vote_no = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND vote_no={$vote_no};");
				$vote_no = $st_vote_no->fetch();
				$vote_name = $vote_no['name']; //	採点者の名前を取得
				$st_voter = $pdo->query("SELECT * FROM {$vote_table} WHERE topic_id={$id} AND name='{$vote_name}';");
				$voter = $st_voter->fetchALL();
	?>
	<div class="title_box">
		<div class="container">
			<div class="titlelists">
					<div class="titlelist-title"><h2><?php echo h($topic['title']); ?>での<?php echo h($vote_name); ?>さんの投票先一覧</h2></div>
			</div>
		</div>
	</div>
	<div class="topic_box">
		<div class="container">
		<div class="topic_heading"><span class="label_org">お題</span></div>
			<div class="topic_body"><a href="page.php?id=<?php echo h($id); ?>" title="このお題の回答一覧を見る"><?php if($topic['img']){$base64 = base64_encode($topic['img']);echo"<p><img src='data:image/".$topic['ext'].";base64,".$base64."' class='img_view' /></p>";} ?><h2><?php echo nl2br($topic['content']) ?></h2></a></div>
		</div>
	</div>

	<?php
				if(!empty($topic['public_vote'])){
					?>
					
							
					<?php
					foreach($voter as $vote){
					$st_post_no = $pdo->query("SELECT * FROM {$post_table} WHERE topic_id={$id} AND no={$vote['post_no']};");
					$post_no = $st_post_no->fetch();
				?>
				<div class="post_box">
				<div class="container">
					<div class="post_heading">
				<span class="label_org"><?php	if(!empty($post_no['url'])){	?>
					<a href="<?php echo h($post_no['url']); ?>">
				<?php	}
				echo h($post_no['name']);
				if(!empty($post_no['url'])){	?>
					</a><?php } ?>さんの回答</span></div>
						<div class="post_body">
							<a href="page.php?id=<?php echo h($id); ?>&amp;no=<?php echo h($post_no['no']); ?>" title="回答の詳細を見る"><h3>
				<?php
				if($topic['topic_type']== "normal"){
					echo nl2br(h($post_no['content']));
				}
				if($topic['topic_type']== "line"){
					if(empty($topic['anaume_front']) and empty($topic['anaume_rear'])){echo h($post_no['content']);}
					else{echo h($topic['anaume_front'])."<span class='anaume'>"; echo h($post_no['content']); echo"</span>".h($topic['anaume_rear']);}
				}
				?>
							</h3></a>
						</div>
					<table class="table report voter_page">
						<tr class="list">
							<td class="point_info" nowrap><?php echo $vote['point']; ?><span class="rank_unit">点</span></td>
							<td class="player"><?php echo h($vote['name']); ?></td>
						</tr>
					</table>
				</div>
			</div>
	<?php
					}
					?>

				<?php } ?>
				<?php
			}
		}
	?></div>
<?php
	include("footer.php");
?>
