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
			<h1><img src="/view/image/profile.png" /><?php echo $text_profile; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" onsubmit="pwdSetting(this)" id="form">
				<table class="form">
					<tr>
						<td><?php echo $entry_username; ?></td>
						<td><?php echo $username; ?></td>
					</tr>
					<tr>
						<td><?php echo $entry_org; ?></td>
						<td><?php echo $org_name; ?></td>
					</tr>
					<tr>
						<td><?php echo $entry_user_group; ?></td>
						<td><?php echo $group_name; ?></td>
					</tr>
					<tr>
					<tr>
						<td><?php if (empty($user_id)) { ?><span class="required">*</span><?php } ?><?php echo $entry_oldpwd; ?></td>
						<td><input type="password" name="oldpwd" value=""	/>
							<?php if ($error_oldpwd) { ?>
							<span class="error"><?php echo $error_oldpwd; ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php if (empty($user_id)) { ?><span class="required">*</span><?php } ?><?php echo $entry_newpwd; ?></td>
						<td><input type="password" name="password" value=""	/>
							<?php if ($error_password) { ?>
							<span class="error"><?php echo $error_password; ?></span>
							<?php } ?></td>
					</tr>
					<tr>
						<td><?php if (empty($user_id)) { ?><span class="required">*</span><?php } ?><?php echo $entry_confirm; ?></td>
						<td><input type="password" name="confirm" value="" />
							<?php if ($error_confirm) { ?>
							<span class="error"><?php echo $error_confirm; ?></span>
							<?php } ?></td>
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