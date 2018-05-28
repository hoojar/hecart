<?php echo $page_header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
		<div class="content" style="text-align:center;">
			<?php echo $entry_email; ?> <input type="text" name="email" value="" size="70"/>
			<p><?php echo $text_email; ?></p>
		</div>
		<div class="buttons">
			<div class="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
			<div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
		</div>
	</form>
</div>
<?php echo $page_footer; ?>