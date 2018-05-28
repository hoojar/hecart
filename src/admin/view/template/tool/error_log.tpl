<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<?php if ($success) { ?>
	<div class="success"><?php echo $success; ?></div>
	<?php } ?>

	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/log.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a href="<?php echo $clear; ?>" class="button"><?php echo $button_clear; ?></a><?php } ?>
			</div>
		</div>
		<div class="content" style="min-height:480px;overflow:auto;"><pre><?php echo $log; ?></pre></div>
	</div>
</div>
<?php echo $page_footer; ?>