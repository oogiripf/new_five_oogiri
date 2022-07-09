<!DOCTYPE html>
<html lang="ja">

<head>
	<title><?php if ($admin) {
				echo "管理者モード - ";
			}
			echo h($pagetitle); ?></title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./dist/asset/css/style.css" type="text/css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>

	<?php
	if ($admin) {
	?>
		<link rel="stylesheet" href="./css/eonasdan-bootstrap-datetimepicker.min.css">
		<script src="./js/moment-with-locales.js"></script>
		<script src="./js/eonasdan-bootstrap-datetimepicker.min.js"></script>
	<?php
	}
	?>


	<!-- Bootstrap -->


	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
	<link rel="stylesheet" href="css/default.css" type="text/css" media="all">
	<?php if ($admin) { ?>
		<link rel="stylesheet" href="css/default_ad.css" type="text/css" media="all">
	<?php }
	if (empty($topic['multiple_point'])) {
	?>
		<script type="text/javascript">
			$(function() { //	チェックボックスを択一化
				$("label[class^='chk_']").click(function() {
					if (!$(this).hasClass("active")) {
						var targetClassName = $(this).get(0).className.split(" ")[0];
						$("." + targetClassName).prop('checked', false);
						$("." + targetClassName).removeClass("active");
					}

				});
				$("input[class^='chk_']").click(function() {
					if ($(this).is(':checked')) {
						var targetClassName = $(this).get(0).className.split(" ")[0];
						$("." + targetClassName).prop('checked', false);
						$("." + targetClassName).removeClass("active");
					}

				});
			});
		</script>
	<?php
	}
	?>


</head>

<body>
	<div id="fixed">
		<div class="mainHeader__body width__block">
			<div class="header-logo">
				<a href="" class="mainHeader__logo"></a>
			</div>
			<div class="right">
				<a href="./index.php" class="header-link">
					<div class="header-title">会場</div>
				</a>
				<a href="" class="header-link">
					<div class="header-title">ルール</div>
				</a>
				<a href="" class="header-link">
					<div class="header-title">ログイン</div>
				</a>
				<a href="" class="header-link">
					<div class="header-title">マイページ</div>
				</a>
			</div>
		</div>
	</div>