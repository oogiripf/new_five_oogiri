<div id="main">
	<div class="message_box">
	<div id="<?php if($message == 1){echo"error-box";}elseif($message == 2){echo"success-box";} ?>">
		<h2><?php echo h($message_title); ?></h2>
		<span class="icon_message"><span class="<?php if($message == 1){echo"icon-warning";}elseif($message == 2){echo"icon-check";} ?>"></span></span>
		<?php if(!empty($message_body)){ ?><p><?php echo h($message_body); ?></p><?php } ?>
		<p><a href="<?php
			if($message == 1){echo"javascript:history.back()";}
			elseif($message == 2){if(!$admin){echo"./index.php";}else{echo"./admin.php";}} ?>">戻る</a></p>
	</div>
	</div>
</div>
<?php
	include("footer.php");
?>