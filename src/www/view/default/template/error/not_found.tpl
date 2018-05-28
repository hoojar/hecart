<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<div class="content" style="text-align:center;padding:50px 10px;color:#CC0000;font-size:18px;"><?php echo $text_error; ?></div>
</div>
<?php echo $page_footer; ?>