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
			<h1><img src="/view/image/user.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?>
				<a onclick="filter();" class="button btn-blue"><?php echo $button_filter; ?></a>
				<a href="/user/org" class="button btn-red"><?php echo $button_reset; ?></a>
				<a href ='<?php echo $insert; ?>' class="button btn-green"><?php echo $button_insert; ?></a>
				<a onclick="$('form').submit();" class="button btn-yellow" style="display:none;"><?php echo $button_delete; ?></a>
				<?php } ?>
			</div>
		</div>
		<div class="content">
			<form action="/user/org/delete" method="post" id="form" onkeypress="if (event.keyCode==13||event.which==13){return false;}">
				<table class="list">
					<thead>
						<tr>
							<td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
							<td><?php echo $column_name; ?></td>
							<td><?php echo $column_partner_id; ?></td>
							<td><?php echo $column_partner_key; ?></td>
							<td><?php echo $column_notify_url; ?></td>
							<td><?php echo $column_user_total; ?></td>
							<td><?php echo $column_email; ?></td>
							<td><?php echo $column_tel; ?></td>
							<td><?php echo $column_memo; ?></td>
							<td class="center"><?php echo $column_action; ?></td>
						</tr>
					</thead>
					<tbody>
					<tr class="filter center">
						<td></td>
						<td class="org_select">
							<input type="hidden" name="org_id" value="<?php echo $org_id;?>" />
							<input type="text" name="org_name" value="<?php echo isset($orgs[$org_id]) ? $orgs[$org_id]['name'] : '';?>" />
							<div>
							<ul>
								<?php foreach ($orgs as $k => $v) { ?>
								<li class="<?php echo $k == $org_id ? 'org_li_select' : '';?>" onclick="orgSelect(this,'<?php echo $k; ?>','<?php echo $v['name']; ?>')" spell="<?php echo $v['spell']; ?>" org_id="<?php echo $k;?>"><?php echo $v['name'] . ' ' . $v['spell']; ?><?php if ($k == $org_id) { ?><span class="glyphicon glyphicon-ok"></span><?php } ?></li>
								<?php } ?>
							</ul>
							</div>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
						<?php if ($org_list) { ?>
						<?php foreach ($org_list as $org) { ?>
						<tr>
							<td style="text-align: center;"><input type="checkbox" name="selected[]" value="<?php echo $org['org_id']; ?>" /></td>
							<td><a href="/gprs/card?org_id=<?php echo $org['org_id']; ?>"><?php echo $orgs[$org['org_id']]['name']; ?></a></td>
							<td><?php echo $org['partner_id']; ?></td>
							<td><?php echo $org['partner_key']; ?></td>
							<td><?php echo $org['notify_url']; ?></td>
							<td><?php echo $org['user_total']; ?></td>
							<td><?php echo $org['email']; ?></td>
							<td><?php echo $org['tel']; ?></td>
							<td><?php echo $org['memo']; ?></td>
							<td class="center"><?php if ($mpermission) { ?><a class="button btn-blue" href="/user/org/update?org_id=<?php echo $org['org_id']; ?>"><?php echo $text_edit; ?></a><?php }?></td>
						</tr>
						<?php } ?>
						<?php } else { ?>
						<tr>
							<td class="center" colspan="10"><?php echo $text_no_results; ?></td>
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
	var url = '/user/org?';
	var org_id = $('input[name=\'org_id\']').val();
	if (org_id != '0')
	{
		url += '&org_id=' + encodeURIComponent(org_id);
	}

	location = url;
}</script>
<?php echo $page_footer; ?>