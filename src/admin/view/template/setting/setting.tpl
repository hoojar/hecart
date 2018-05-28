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
			<h1><img src="/view/image/setting.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-darkblue"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<div id="tabs" class="htabs">
				<a href="#tab-general"><?php echo $tab_general; ?></a>
				<a href="#tab-store"><?php echo $tab_store; ?></a>
				<a href="#tab-local"><?php echo $tab_local; ?></a>
				<a href="#tab-mail"><?php echo $tab_mail; ?></a>
				<a href="#tab-server"><?php echo $tab_server; ?></a>
			</div>
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
				<div id="tab-general">
					<table class="form">
						<tr>
							<td><span class="required">*</span> <?php echo $entry_name; ?></td>
							<td><input type="text" name="config_name" value="<?php echo $config_name; ?>" size="40" />
								<?php if ($error_name) { ?>
								<span class="error"><?php echo $error_name; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_owner; ?></td>
							<td><input type="text" name="config_owner" value="<?php echo $config_owner; ?>" size="40" />
								<?php if ($error_owner) { ?>
								<span class="error"><?php echo $error_owner; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_address; ?></td>
							<td><textarea name="config_address" cols="40" rows="5"><?php echo $config_address; ?></textarea>
								<?php if ($error_address) { ?>
								<span class="error"><?php echo $error_address; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_email; ?></td>
							<td><input type="text" name="config_email" value="<?php echo $config_email; ?>" size="40" />
								<?php if ($error_email) { ?>
								<span class="error"><?php echo $error_email; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_telephone; ?></td>
							<td><input type="text" name="config_telephone" value="<?php echo $config_telephone; ?>" />
								<?php if ($error_telephone) { ?>
								<span class="error"><?php echo $error_telephone; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_fax; ?></td>
							<td><input type="text" name="config_fax" value="<?php echo $config_fax; ?>" /></td>
						</tr>
					</table>
				</div>
				<div id="tab-store">
					<table class="form">
						<tr>
							<td><span class="required">*</span> <?php echo $entry_title; ?></td>
							<td><input type="text" name="config_title" value="<?php echo $config_title; ?>" />
								<?php if ($error_title) { ?>
								<span class="error"><?php echo $error_title; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_meta_description; ?></td>
							<td><textarea name="config_meta_description" cols="40" rows="5"><?php echo $config_meta_description; ?></textarea></td>
						</tr>
						<tr>
							<td><?php echo $entry_template; ?></td>
							<td><select name="config_template" onchange="$('#template').load('/setting/setting/template?template=' + encodeURIComponent(this.value));">
									<?php foreach ($templates as $template) { ?>
									<?php if ($template == $config_template) { ?>
									<option value="<?php echo $template; ?>" selected="selected"><?php echo $template; ?></option>
									<?php } else { ?>
									<option value="<?php echo $template; ?>"><?php echo $template; ?></option>
									<?php } ?>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td></td>
							<td id="template"></td>
						</tr>
					</table>
				</div>
				<div id="tab-local">
					<table class="form">
						<tr>
							<td><?php echo $entry_language; ?></td>
							<td><select name="config_language">
									<?php foreach ($languages as $language) { ?>
									<?php if ($language['code'] == $config_language) { ?>
									<option value="<?php echo $language['code']; ?>" selected="selected"><?php echo $language['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $language['code']; ?>"><?php echo $language['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select></td>
						</tr>
						<tr>
							<td><?php echo $entry_admin_language; ?></td>
							<td><select name="config_admin_language">
									<?php foreach ($languages as $language) { ?>
									<?php if ($language['code'] == $config_admin_language) { ?>
									<option value="<?php echo $language['code']; ?>" selected="selected"><?php echo $language['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $language['code']; ?>"><?php echo $language['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select></td>
						</tr>
						<tr>
							<td><?php echo $entry_currency; ?></td>
							<td><select name="config_currency">
									<?php foreach ($currencies as $currency) { ?>
									<?php if ($currency['code'] == $config_currency) { ?>
									<option value="<?php echo $currency['code']; ?>" selected="selected"><?php echo $currency['title']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $currency['code']; ?>"><?php echo $currency['title']; ?></option>
									<?php } ?>
									<?php } ?>
								</select></td>
						</tr>
						<tr>
							<td><?php echo $entry_currency_auto; ?></td>
							<td><?php if ($config_currency_auto) { ?>
								<input type="radio" name="config_currency_auto" value="1" checked="checked" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_currency_auto" value="0" />
								<?php echo $text_no; ?>
								<?php } else { ?>
								<input type="radio" name="config_currency_auto" value="1" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_currency_auto" value="0" checked="checked" />
								<?php echo $text_no; ?>
								<?php } ?></td>
						</tr>
					</table>
					<h2><?php echo $text_items; ?></h2>
					<table class="form">
						<tr>
							<td><span class="required">*</span> <?php echo $entry_catalog_limit; ?></td>
							<td><input type="text" name="config_catalog_limit" value="<?php echo $config_catalog_limit; ?>" size="3" />
								<?php if ($error_catalog_limit) { ?>
								<span class="error"><?php echo $error_catalog_limit; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_admin_limit; ?></td>
							<td><input type="text" name="config_admin_limit" value="<?php echo $config_admin_limit; ?>" size="3" />
								<?php if ($error_admin_limit) { ?>
								<span class="error"><?php echo $error_admin_limit; ?></span>
								<?php } ?></td>
						</tr>
					</table>
				</div>

				<div id="tab-mail">
					<table class="form">
						<tr>
							<td><?php echo $entry_mail_protocol; ?></td>
							<td><select name="config_mail_protocol">
									<?php if ($config_mail_protocol == 'mail') { ?>
									<option value="mail" selected="selected"><?php echo $text_mail; ?></option>
									<?php } else { ?>
									<option value="mail"><?php echo $text_mail; ?></option>
									<?php } ?>
									<?php if ($config_mail_protocol == 'smtp') { ?>
									<option value="smtp" selected="selected"><?php echo $text_smtp; ?></option>
									<?php } else { ?>
									<option value="smtp"><?php echo $text_smtp; ?></option>
									<?php } ?>
								</select></td>
						</tr>
						<tr>
							<td><?php echo $entry_mail_parameter; ?></td>
							<td><input type="text" name="config_mail_parameter" value="<?php echo $config_mail_parameter; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_smtp_host; ?></td>
							<td><input type="text" name="config_smtp_host" value="<?php echo $config_smtp_host; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_smtp_username; ?></td>
							<td><input type="text" name="config_smtp_username" value="<?php echo $config_smtp_username; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_smtp_password; ?></td>
							<td><input type="text" name="config_smtp_password" value="<?php echo $config_smtp_password; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_smtp_port; ?></td>
							<td><input type="text" name="config_smtp_port" value="<?php echo $config_smtp_port; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_smtp_timeout; ?></td>
							<td><input type="text" name="config_smtp_timeout" value="<?php echo $config_smtp_timeout; ?>" /></td>
						</tr>
					</table>
				</div>
				<div id="tab-server">
					<table class="form">
						<tr>
							<td><?php echo $entry_use_ssl; ?></td>
							<td><?php if ($config_use_ssl) { ?>
								<input type="radio" name="config_use_ssl" value="1" checked="checked" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_use_ssl" value="0" />
								<?php echo $text_no; ?>
								<?php } else { ?>
								<input type="radio" name="config_use_ssl" value="1" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_use_ssl" value="0" checked="checked" />
								<?php echo $text_no; ?>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_login_count_max; ?></td>
							<td><input type="text" name="config_login_count_max" value="<?php echo $config_login_count_max; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_login_locked_hours; ?></td>
							<td><input type="text" name="config_login_locked_hours" value="<?php echo $config_login_locked_hours; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_file_extension_allowed; ?></td>
							<td><textarea name="config_file_extension_allowed" cols="40" rows="5"><?php echo $config_file_extension_allowed; ?></textarea></td>
						</tr>
						<tr>
							<td><?php echo $entry_file_mime_allowed; ?></td>
							<td><textarea name="config_file_mime_allowed" cols="60" rows="5"><?php echo $config_file_mime_allowed; ?></textarea></td>
						</tr>              
						<tr>
							<td><?php echo $entry_maintenance; ?></td>
							<td><?php if ($config_maintenance) { ?>
								<input type="radio" name="config_maintenance" value="1" checked="checked" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_maintenance" value="0" />
								<?php echo $text_no; ?>
								<?php } else { ?>
								<input type="radio" name="config_maintenance" value="1" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_maintenance" value="0" checked="checked" />
								<?php echo $text_no; ?>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_encryption; ?></td>
							<td><input type="text" name="config_encryption" value="<?php echo $config_encryption; ?>" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_compression; ?></td>
							<td><input type="text" name="config_compression" value="<?php echo $config_compression; ?>" size="3" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_error_display; ?></td>
							<td><?php if ($config_error_display) { ?>
								<input type="radio" name="config_error_display" value="1" checked="checked" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_error_display" value="0" />
								<?php echo $text_no; ?>
								<?php } else { ?>
								<input type="radio" name="config_error_display" value="1" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_error_display" value="0" checked="checked" />
								<?php echo $text_no; ?>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_error_log; ?></td>
							<td><?php if ($config_error_log) { ?>
								<input type="radio" name="config_error_log" value="1" checked="checked" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_error_log" value="0" />
								<?php echo $text_no; ?>
								<?php } else { ?>
								<input type="radio" name="config_error_log" value="1" />
								<?php echo $text_yes; ?>
								<input type="radio" name="config_error_log" value="0" checked="checked" />
								<?php echo $text_no; ?>
								<?php } ?></td>
						</tr>
						<tr>
							<td><span class="required">*</span> <?php echo $entry_error_filename; ?></td>
							<td><input type="text" name="config_error_filename" value="<?php echo $config_error_filename; ?>" />
								<?php if ($error_error_filename) { ?>
								<span class="error"><?php echo $error_error_filename; ?></span>
								<?php } ?></td>
						</tr>
						<tr>
							<td><?php echo $entry_google_analytics; ?></td>
							<td><textarea name="config_google_analytics" cols="40" rows="5"><?php echo $config_google_analytics; ?></textarea></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
$('#tabs a').easyTabs();
$('#template').load('/setting/setting/template?template=' + encodeURIComponent($("select[name='config_template']").val()));
</script>
<?php echo $page_footer; ?>