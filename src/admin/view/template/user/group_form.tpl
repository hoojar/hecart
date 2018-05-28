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
			<h1><img src="/view/image/user-group.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-darkblue"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" id="form">
				<table class="form">
					<tr>
						<td><span class="required">*</span> <?php echo $entry_name; ?></td>
						<td>
							<input type="text" name="name" value="<?php echo $name; ?>" />
							<?php if ($error_name) { ?><span class="error"><?php echo $error_name; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_orgpos; ?></td>
						<td>
							<label for="org-all" class="button btn-blue"><input type="radio" name="orgpos" value="*" id="org-all" <?php if ($orgpos == '*'){echo "checked";}?>/> <?php echo $entry_org_all; ?></label>　
							<label for="org-stl" class="button btn-yellow"><input type="radio" name="orgpos" value="<?php echo $orgpos != '*' ? $orgpos : '';?>" id="org-stl" <?php if ($orgpos && $orgpos != '*'){echo "checked";}?>/> <?php echo $entry_org_stl; ?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_description; ?></td>
						<td><input type="text" name="description" maxlength="222" value="<?php echo $description; ?>" /></td>
					</tr>
					<tr>
						<td><?php echo $entry_access; ?></td>
						<td>
							<div class="scrollbox">
								<?php foreach ($permissions as $permission) { ?>
								<div>
									<?php if (in_array($permission, $access)) { ?>
									<input type="checkbox" name="permission[access][]" value="<?php echo $permission; ?>" checked="checked" /> <?php echo isset($permissions_data[$permission]) ? $permissions_data[$permission] : $permission; ?>
									<?php } else { ?>
									<input type="checkbox" name="permission[access][]" value="<?php echo $permission; ?>" /> <?php echo isset($permissions_data[$permission]) ? $permissions_data[$permission] : $permission; ?>
									<?php } ?>
								</div>
								<?php } ?>
							</div>
							<div style="text-align:right;margin:8px 0 0;">
								<a onclick="$(this).parent().parent().find(':checkbox').prop('checked', true);" style="cursor:pointer"><?php echo $text_select_all; ?></a>
								/ <a onclick="$(this).parent().parent().find(':checkbox').prop('checked', false);" style="cursor:pointer"><?php echo $text_unselect_all; ?></a>
							</div>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_modify; ?></td>
						<td>
							<div class="scrollbox">
								<?php foreach ($permissions as $permission) { ?>
								<div>
									<?php if (in_array($permission, $modify)) { ?>
									<input type="checkbox" name="permission[modify][]" value="<?php echo $permission; ?>" checked="checked" /> <?php echo isset($permissions_data[$permission]) ? $permissions_data[$permission] : $permission; ?>
									<?php } else { ?>
									<input type="checkbox" name="permission[modify][]" value="<?php echo $permission; ?>" /> <?php echo isset($permissions_data[$permission]) ? $permissions_data[$permission] : $permission; ?>
									<?php } ?>
								</div>
								<?php } ?>
							</div>
							<div style="text-align:right;margin:8px 0 0;">
								<a onclick="$(this).parent().parent().find(':checkbox').prop('checked', true);" style="cursor:pointer"><?php echo $text_select_all; ?></a>
								/ <a onclick="$(this).parent().parent().find(':checkbox').prop('checked', false);" style="cursor:pointer"><?php echo $text_unselect_all; ?></a>
							</div>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<script type="text/html" id="org-html">
<div class="scrollbox" style="height:320px;">
	<?php foreach ($orgs as $org) { ?>
	<div><input type="checkbox" name="org_list" value="<?php echo $org['org_id']; ?>" /><?php echo $org['name'];?></div>
	<?php } ?>
</div>
<div style="text-align:center">
	<button class="button btn-red" onclick="orgReset(this)"><?php echo $text_reset;?></button> &emsp;&emsp;
	<button class="button btn-blue" onclick="orgSave(this)"><?php echo $button_save;?></button>
</div>
</script>
<script type="text/javascript">
$("#org-stl").on('click', function ()
{
	$.fn.tboxy({
		title    : '<?php echo $text_orgs; ?>', width: '750px', value: $("#org-html").html(),
		closeExec: function () {$(this).parent().parent().css({'background': '', 'color': ''})},
		afterShow: function ()
		{
			var orgpos = $("#org-stl").val();
			if (orgpos)
			{
				$.each(orgpos.split(','), function (k, v)
				{
					$("input[name='org_list'][value=" + v + "]").prop('checked', true);
				});
			}
		}
	});
});

function orgReset(obj)
{
	$(obj).parent().siblings('div').find(':checkbox').prop('checked', false);
}

function orgSave(obj)
{
	var orgpos = '';
	$("input[name='org_list']:checked").each(function ()
	{
		orgpos += $(this).val() + ',';
	});
	if (orgpos == '')
	{
		$("#org-stl").attr('checked', false);
	}
	$("#org-stl").val(orgpos.substr(0, orgpos.length - 1));
	$(".touch-boxy").find('.close').trigger('click');
}
</script>
<?php echo $page_footer; ?>