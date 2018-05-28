<?php echo $page_header; ?>
<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<table class="list">
	<?php foreach ($addresses as $result) { ?>
		<tr>
			<td><?php echo $result['address']; ?></td>
			<td style="text-align: right;"><a href="<?php echo $result['update']; ?>" class="button"><?php echo $button_edit; ?></a> &nbsp; <a href="<?php echo $result['delete']; ?>" class="button"><?php echo $button_delete; ?></a></td>
		</tr>
	<?php } ?>
	</table>
	<div class="buttons">
		<div class="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
		<div class="right"><a href="<?php echo $insert; ?>" class="button"><?php echo $button_new_address; ?></a></div>
	</div>
</div>
<?php echo $page_footer; ?>