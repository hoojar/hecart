<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/information.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-yellow"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
				<table class="form">
					<tr>
						<td><?php echo $entry_link_url; ?></td>
						<td><input type="text" name="link_url" value="<?php echo $link_url; ?>" /></td>
					</tr>
					<tr>
						<td><?php echo $entry_information_group; ?></td>
						<td><select name="information_group_id">
								<?php foreach ($information_groups as $information_group) { ?>
								<?php if ($information_group['information_group_id'] == $information_group_id) { ?>
								<option value="<?php echo $information_group['information_group_id']; ?>" selected="selected"><?php echo $information_group['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $information_group['information_group_id']; ?>"><?php echo $information_group['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select></td>
					</tr>
					<tr>
						<td><?php echo $entry_status; ?></td>
						<td><select name="status">
								<?php if ($status) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select></td>
					</tr>
					<tr>
						<td><?php echo $entry_sort_order; ?></td>
						<td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="1" /></td>
					</tr>
				</table>
				<div id="tab-general">
					<div id="languages" class="htabs">
						<?php foreach ($languages as $language) { ?>
						<a href="#language<?php echo $language['language_id']; ?>"><img src="/view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
						<?php } ?>
					</div>
					<?php foreach ($languages as $language) { ?>
					<div id="language<?php echo $language['language_id']; ?>">
						<table class="form">
							<tr>
								<td><span class="required">*</span> <?php echo $entry_title; ?></td>
								<td><input type="text" name="information_description[<?php echo $language['language_id']; ?>][title]" size="100" value="<?php echo isset($information_description[$language['language_id']]) ? $information_description[$language['language_id']]['title'] : ''; ?>" />
									<?php if (isset($error_title[$language['language_id']])) { ?>
									<span class="error"><?php echo $error_title[$language['language_id']]; ?></span>
									<?php } ?></td>
							</tr>
							<tr>
								<td><span class="required">*</span> <?php echo $entry_description; ?></td>
								<td><textarea name="information_description[<?php echo $language['language_id']; ?>][description]" id="description<?php echo $language['language_id']; ?>"><?php echo isset($information_description[$language['language_id']]) ? $information_description[$language['language_id']]['description'] : ''; ?></textarea>
									<?php if (isset($error_description[$language['language_id']])) { ?>
									<span class="error"><?php echo $error_description[$language['language_id']]; ?></span>
									<?php } ?></td>
							</tr>
						</table>
					</div>
					<?php } ?>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description<?php echo $language['language_id']; ?>', {
	language: '<?php echo $language['code']; ?>',
	filebrowserBrowseUrl: '/common/filemanager',
	filebrowserImageBrowseUrl: '/common/filemanager',
	filebrowserFlashBrowseUrl: '/common/filemanager',
	filebrowserUploadUrl: '/common/filemanager',
	filebrowserImageUploadUrl: '/common/filemanager',
	filebrowserFlashUploadUrl: '/common/filemanager'
});
<?php } ?>

$('#tabs a').easyTabs();
$('#languages a').easyTabs();
//--></script>
<?php echo $page_footer; ?>