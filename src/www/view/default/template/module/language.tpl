<?php if (count($languages) > 1) { ?>
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="slt-lang">
	<div id="language">
		<?php echo $text_language; ?>:
		<?php foreach ($languages as $language) { ?>
		<a onclick="$('input[name=\'language_code\']').val('<?php echo $language['code']; ?>'); $('#slt-lang').submit();">
		<img src="img/flags/<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>" title="<?php echo $language['name']; ?>" /></a>
		<?php } ?>
		<input type="hidden" name="language_code" value="" />
		<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
	</div>
</form>
<?php } ?>
