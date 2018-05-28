<?php echo $page_header; ?>
<script type="text/javascript" src="/js/jquery.md5.js"></script>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/user.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-darkblue"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" onsubmit="pwdSetting(this)" enctype="multipart/form-data" id="form">
				<table class="form">
					<tr>
						<td><span class="required">*</span> <?php echo $entry_org; ?></td>
						<td>
							<select name="org_id">
								<?php foreach ($orgs as $org) { ?>
								<option value="<?php echo $org['org_id']; ?>" <?php if ($org['org_id'] == $org_id) { ?> selected="selected"<?php } ?>><?php echo $org['name']; ?> [ <?php echo sprintf($entry_user_total, $org['user_count'], $org['user_total']-$org['user_count']); ?> ] </option>
								<?php } ?>
							</select>
							<?php if ($error_org) { ?><span class="error"><?php echo $error_org; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_username; ?></td>
						<td>
							<input type="text" name="username" value="<?php echo $username; ?>" />
							<?php if ($error_username) { ?><span class="error"><?php echo $error_username; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_firstname; ?></td>
						<td>
							<input type="text" name="firstname" value="<?php echo $firstname; ?>" />
							<?php if ($error_firstname) { ?><span class="error"><?php echo $error_firstname; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_lastname; ?></td>
						<td>
							<input type="text" name="lastname" value="<?php echo $lastname; ?>" />
							<?php if ($error_lastname) { ?><span class="error"><?php echo $error_lastname; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_user_lang; ?></td>
						<td>
							<select name="lang">
							<?php foreach ($languages as $v) { ?>
							<option value="<?php echo $v['code']; ?>"<?php if ($v['code'] == $lang) { ?> selected<?php } ?>><?php echo $v['name']; ?></option>								
							<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_user_group; ?></td>
						<td>
							<select name="group_id">
								<?php foreach ($groups as $group) { ?>
								<option value="<?php echo $group['group_id']; ?>"<?php if ($group['group_id'] == $group_id) { ?> selected<?php } ?>><?php echo $group['name']; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php if (empty($user_id)) { ?><span class="required">*</span><?php } ?><?php echo $entry_password; ?></td>
						<td>
							<input type="password" name="password" value=""	/>
							<?php if ($error_password) { ?><span class="error"><?php echo $error_password; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php if (empty($user_id)) { ?><span class="required">*</span><?php } ?><?php echo $entry_confirm; ?></td>
						<td>
							<input type="password" name="confirm" value="" />
							<?php if ($error_confirm) { ?><span class="error"><?php echo $error_confirm; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_email; ?></td>
						<td>
							<input type="text" name="email" value="<?php echo $email; ?>" />
							<?php if ($error_email) { ?><span class="error"><?php echo $error_email; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_tel; ?></td>
						<td>
							<input type="text" name="tel" value="<?php echo $tel; ?>" />
							<?php if ($error_tel) { ?><span class="error"><?php echo $error_tel; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_status; ?></td>
						<td>
							<select name="status">
								<?php if ($status) { ?>
								<option value="0"><?php echo $text_disabled; ?></option>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<?php } else { ?>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<option value="1"><?php echo $text_enabled; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
function pwdSetting(obj)
{
	$(obj).find(':password').each(function ()
	{
		if ($(this).val())
		{
			if ($(this).val().length < 4)
			{
				return false;
			}
			$(this).val($.md5($(this).val()));
		}
	});
}
</script>
<?php echo $page_footer; ?>