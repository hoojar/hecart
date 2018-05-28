<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<?php if ($warning) { ?><div class="warning"><?php echo $warning; ?></div><?php } ?>
	<?php if ($success) { ?>
	<div class="success"><?php echo $success; ?></div>
	<?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/user.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<a onclick="filter();" class="button btn-blue"><?php echo $button_filter; ?></a>
				<?php if ($mpermission) { ?><a href="<?php echo $insert; ?>" class="button btn-green"><?php echo $button_insert; ?></a><?php } ?>
				<?php if ($mpermission && $this->user->getGroupId() <= 1) { ?><a onclick="$('form').submit();" class="button btn-yellow"><?php echo $button_delete; ?></a><?php } ?>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $delete; ?>" method="post" id="form">
				<table class="list">
					<thead>
						<tr>
							<td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
							<td class="left"><?php if ($sort == 'username') { ?>
								<a href="<?php echo $sort_username; ?>"><?php echo $column_username; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_username; ?>"><?php echo $column_username; ?><i class="harrow"></i></a>
								<?php } ?>
							</td>
							<td><?php echo $column_firstname; ?></td>
							<td class="left"><?php if ($sort == 'group_id') { ?>
								<a href="<?php echo $sort_group; ?>"><?php echo $column_group; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_group; ?>"><?php echo $column_group; ?><i class="harrow"></i></a>
								<?php } ?></td>
							<td class="left"><?php if ($sort == 'org_id') { ?>
								<a href="<?php echo $sort_org; ?>"><?php echo $column_org; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_org; ?>"><?php echo $column_org; ?><i class="harrow"></i></a>
								<?php } ?></td>
							<td class="left"><?php if ($sort == 'date_added') { ?>
								<a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?><i class="harrow"></i></a>
								<?php } ?>
							</td>
							<td class="left"><?php if ($sort == 'date_last') { ?>
								<a href="<?php echo $sort_date_last; ?>"><?php echo $column_date_last; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_date_last; ?>"><?php echo $column_date_last; ?><i class="harrow"></i></a>
								<?php } ?>
							</td>
							<td class="center"><?php echo $column_last_ip; ?></td>
							<td class="center">
								<?php if ($sort == 'status') { ?>
								<a href="<?php echo $sort_status; ?>"><?php echo $text_manage; ?><i class="<?php echo strtolower($order); ?>"></i></a>
								<?php } else { ?>
								<a href="<?php echo $sort_status; ?>"><?php echo $text_manage; ?><i class="harrow"></i></a>
								<?php } ?>
							</td>
						</tr>
					</thead>
					<tbody>
					<tr class="filter center">
						<td></td>
						<td width="12%"><input type="text" name="username" value="<?php echo $username ? $username : ''; ?>" size="16" /></td>
						<td></td>
						<td>
							<select name="group_id">
								<option value="*"></option>
								<?php foreach ($groups as $v) { ?>
								<option value="<?php echo $v['group_id']; ?>" <?php if ($v['group_id'] == $group_id) { ?> selected=`"selected"<?php } ?>><?php echo $v['name']; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="org_select">
							<input type="hidden" name="org_id" value="<?php echo $org_id;?>" />
							<input type="text" name="org_name" value="<?php echo isset($orgs[$org_id]) ? $orgs[$org_id]['name'] : '';?>" />
							<div>
								<ul>
									<?php foreach ($orgs as $k => $v) { ?>
									<li class="<?php echo $k == $org_id ? 'org_li_select' : '';?>"
										onclick="orgSelect(this,'<?php echo $k; ?>','<?php echo $v['name']; ?>')"
										spell="<?php echo $v['spell']; ?>" org_id="<?php echo $k;?>"
										org_id="<?php echo $k;?>"><?php echo $v['name'] . ' ' . $v['spell']; ?><?php if ($k == $org_id) { ?>
										<span class="glyphicon glyphicon-ok"></span><?php } ?></li>
									<?php } ?>
								</ul>
							</div>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php if ($users) { foreach ($users as $user) { ?>
						<tr>
							<td style="text-align: center;"><input type="checkbox" name="selected[]" value="<?php echo $user['user_id']; ?>"<?php if ($user['selected']) { ?> checked="checked"<?php } ?> /></td>
							<td class="left"><?php echo $user['username']; ?></td>
							<td class="left"><?php echo $user['firstname']; ?></td>
							<td class="left"><?php echo isset($groups[$user['group_id']]) ? $groups[$user['group_id']]['name'] : $text_unviewable; ?></td>
							<td class="left"><?php echo isset($orgs[$user['org_id']]) ? $orgs[$user['org_id']]['name'] : ''; ?></td>
							<td class="left"><?php echo $user['date_added']; ?></td>
							<td class="left"><?php echo $user['date_last']; ?></td>
							<td class="center"><?php echo $user['ip']; ?></td>
							<td class="center" width="15%">
							<?php if (isset($orgs[$user['org_id']]) && isset($groups[$user['group_id']])) { ?>
								<?php foreach ($user['action'] as $action) { ?><a class="button btn-blue" href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a><?php } ?>
								<?php if ($user['status']) { ?>
								<a class="button btn-red" onclick="onoff(this,<?php echo $user['user_id']; ?>,0)"><?php echo $text_disabled; ?></a>
								<?php } else { ?>
								<a class="button btn-green" onclick="onoff(this,<?php echo $user['user_id']; ?>,1)"><?php echo $text_enabled; ?></a>
								<?php } ?>
							<?php } ?>
							</td>
						</tr>
						<?php } } else { ?>
						<tr>
							<td class="center" colspan="9"><?php echo $text_no_results; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</form>
			<div class="pagination"><?php echo $pagination; ?></div>
		</div>
	</div>
</div>
<script type="text/javascript">
function filter()
{
	var url = '/user/user?';

	var org_id = $('input[name=\'org_id\']').val();
	if (org_id != '0')
	{
		url += 'org_id=' + encodeURIComponent(org_id);
	}

	var username = $('input[name=\'username\']').val();
	if (username)
	{
		url += '&username=' + encodeURIComponent(username);
	}

	var group_id = $('select[name=\'group_id\']').val();
	if (group_id != '*')
	{
		url += '&group_id=' + encodeURIComponent(group_id);
	}

	location = url;
}

function onoff(obj, user_id, status)
{
	if (status)
	{
		var confirm_text = '<?php echo $text_confirm_start; ?>';
		var onclick_fun = "onoff(this," + user_id + ",0)";
	}
	else
	{
		var confirm_text = '<?php echo $text_confirm_stop; ?>';
		var onclick_fun = "onoff(this," + user_id + ",1)";
	}

	if (!confirm(confirm_text))
	{
		return;
	}

	$.post('/user/user/onoff', {'user_id': user_id, 'status': status}, function (res)
	{
		if (res != 'ok')
		{
			alert(res);
			return;
		}

		if (status)
		{
			$(obj).text('<?php echo $text_disabled; ?>');
			$(obj).removeClass('btn-green');
			$(obj).addClass('btn-red');
		}
		else
		{
			$(obj).text('<?php echo $text_enabled; ?>');
			$(obj).removeClass('btn-red');
			$(obj).addClass('btn-green');
		}
		$(obj).attr('onclick', onclick_fun);
	});
}
</script>
<?php echo $page_footer; ?>