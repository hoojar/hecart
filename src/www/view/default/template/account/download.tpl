<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<table class="list">
		<thead>
		<tr>
			<td><?php echo $text_order; ?></td>
			<td><?php echo $text_name; ?></td>
			<td><?php echo $text_date_added; ?></td>
			<td><?php echo $text_size; ?></td>
			<td><?php echo $text_remaining; ?></td>
			<td></td>
		</tr>
		</thead>
		<tbody>

		<?php foreach ($downloads as $download) { ?>
		<tr>
			<td><?php echo $download['osn']; ?></td>
			<td><?php echo $download['name']; ?><br />
			<td><?php echo $download['date_added']; ?></td>
			<td><?php echo $download['size']; ?></td>
			<td><?php echo $download['remaining']; ?></td>
			<td>
				<?php if ($download['remaining'] > 0) { ?>
				<a href="<?php echo $download['href']; ?>"><img src="/view/default/image/download.png" alt="<?php echo $button_download; ?>" title="<?php echo $button_download; ?>" /></a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<div class="pagination"><?php echo $pagination; ?></div>
	<div class="buttons"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
</div>
<?php echo $page_footer; ?>