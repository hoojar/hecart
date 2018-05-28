<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<?php if ($success) { ?>
	<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/backup.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?>
				<a onclick="$('#restore').submit();" class="button"><?php echo $button_restore; ?></a>
				<a onclick="$('#backup').submit();" class="button"><?php echo $button_backup; ?></a>
				<?php } ?>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $restore; ?>" method="post" enctype="multipart/form-data" id="restore">
				<table class="form">
					<tr>
						<td><?php echo $entry_restore; ?></td>
						<td><input type="file" name="import" /></td>
					</tr>
				</table>
			</form>
			<form action="<?php echo $backup; ?>" method="post" enctype="multipart/form-data" id="backup">
				<table class="form">
					<tr>
						<td><?php echo $entry_backup; ?></td>
						<td><div class="scrollbox" style="margin-bottom: 5px;height:500px;">
								<?php $class = 'odd'; ?>
								<?php foreach ($tables['tables'] as $table) { ?>
								<?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
								<div class="<?php echo $class; ?>">
									<input type="checkbox" name="backup[]" value="<?php echo $table; ?>" checked="checked" />
									<?php echo isset($tables['comments'][$table]) ? $tables['comments'][$table] : $table; ?></div>
								<?php } ?>
							</div>
							<a style="cursor:pointer;" onclick="$(this).parent().find(':checkbox').prop('checked', true);"><?php echo $text_select_all; ?></a> /
							<a style="cursor:pointer;" onclick="$(this).parent().find(':checkbox').prop('checked', false);"><?php echo $text_unselect_all; ?></a>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php echo $page_footer; ?>