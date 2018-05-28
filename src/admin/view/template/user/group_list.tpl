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
			<h1><img src="/view/image/user-group.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?>
				<a onclick="location = '<?php echo $insert; ?>'" class="button btn-green"><?php echo $button_insert; ?></a>
				<a onclick="$('form').submit();" class="button btn-yellow"><?php echo $button_delete; ?></a>
				<?php } ?>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $delete; ?>" method="post" id="form">
				<table class="list">
					<thead>
						<tr>
							<td style="width:10px" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
							<td style="width:20%">
								<?php if ($sort == 'name') { ?>
								<a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?><i class="harrow"></i></a>
								<?php } ?>
							</td>
							<td><?php echo $column_description; ?></td>
							<td width="9%" class="center"><?php echo $column_action; ?></td>
						</tr>
					</thead>
					<tbody>
						<?php if ($groups) { ?>
						<?php foreach ($groups as $group) { ?>
						<tr>
							<td style="text-align: center;"><input type="checkbox" name="selected[]" value="<?php echo $group['group_id']; ?>" /></td>
							<td><?php echo $group['name']; ?></td>
							<td><?php echo $group['description']; ?></td>
							<td width="9%" class="center">
								<?php foreach ($group['action'] as $action) { ?><a class="button btn-blue" href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a><?php } ?>
							</td>
						</tr>
						<?php } ?>
						<?php } else { ?>
						<tr>
							<td class="center" colspan="3"><?php echo $text_no_results; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</form>
			<div class="pagination"><?php echo $pagination; ?></div>
		</div>
	</div>
</div>
<?php echo $page_footer; ?>