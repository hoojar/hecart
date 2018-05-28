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
			<h1><img src="/view/image/user.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#form').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-darkblue"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
				<table class="form">
					<tr>
						<td><span class="required">*</span> <?php echo $entry_name; ?></td>
						<td>
							<input type="text" name="name" value="<?php echo $name; ?>" />
							<?php if ($error_name) { ?><span class="error"><?php echo $error_name; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_parent; ?></td>
						<td>
							<select name="parent_id">
								<?php if ($this->user->getOrgPos() == '*') { ?><option value="0"><?php echo $text_none; ?></option><?php } ?>
								<?php foreach ($orgs as $org) { if ($org_id != 0 && $org['org_id'] == $org_id) {continue;} ?>
								<option value="<?php echo $org['org_id']; ?>" <?php if ($org['org_id'] == $parent_id) { ?> selected="selected"<?php } ?>><?php echo $org['name']; ?></option>
								<?php } ?>
							</select><?php if ($error_parent_id) { ?><span class="error"><?php echo $error_parent_id; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_user_total; ?></td>
						<td>
							<input type="text" name="user_total" value="<?php echo $user_total; ?>" />
							<?php if ($error_user_total) { ?><span class="error"><?php echo $error_user_total; ?></span><?php } ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_memo; ?></td>
						<td>
							<input type="text" name="memo" value="<?php echo $memo; ?>" />
							<?php if ($error_memo) { ?><span class="error"><?php echo $error_memo; ?></span><?php } ?>
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
						<td><?php echo $entry_notify_url; ?></td>
						<td>
							<input type="text" name="notify_url" value="<?php echo $notify_url; ?>" />
							<?php if ($error_notify_url) { ?><span class="error"><?php echo $error_notify_url; ?></span><?php } ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php echo $page_footer; ?>