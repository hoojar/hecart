<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<?php if ($description) { ?><meta name="description" content="<?php echo $description; ?>" /><?php } ?>
<?php if ($keywords) { ?><meta name="keywords" content="<?php echo $keywords; ?>" /><?php } ?>
<?php if ($icon) { ?><link href="<?php echo $icon; ?>" rel="icon" /><?php } ?>
<?php foreach ($links as $link) { ?><link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" /><?php } ?>
<link rel="stylesheet" type="text/css" href="view/default/style.css" />
<link rel="stylesheet" type="text/css" href="js/css/gallery.css" />
<?php foreach ($styles as $style) { ?><link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" /><?php } ?>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="js/gallery.js"></script>
<?php foreach ($scripts as $script) { ?><script type="text/javascript" src="<?php echo $script; ?>"></script><?php } ?>
</head>
<body>
<div id="top-bar">
	<div class="header-top">
		<div class="left"><?php echo $language; ?></div>
		<div class="left"><?php echo $currency; ?></div>
		<div class="right">
			<span id="user-span">
				<span id="user-welcome" style="display:none;"><?php echo $text_welcome; ?></span>
				<span id="user-logged" style="display:none;"><?php echo $text_logged; ?></span>
			</span>
		</div>
	</div>
</div>

<div id="container">
