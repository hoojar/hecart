<?php echo $page_header;?>
<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
<script type="text/javascript" src="/js/jquery.md5.js"></script>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<form action="<?php echo $action; ?>" method="post" onsubmit="pwdSetting(this)" enctype="multipart/form-data">
		<div class="content">
		<p style="text-align:right;"><?php echo $text_account_already; ?></p>
		<h2 class="underline"><?php echo $text_your_details; ?></h2>
		<table class="form">
			<tr>
				<td><span class="required">*</span> <?php echo $entry_email; ?></td>
				<td><input type="text" name="email" value="<?php echo $email; ?>" />
					<?php if ($error_email) { ?>
					<span class="error"><?php echo $error_email; ?></span>
					<?php } ?></td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_firstname; ?></td>
				<td><input type="text" name="firstname" value="<?php echo $firstname; ?>" />
					<?php if ($error_firstname) { ?>
					<span class="error"><?php echo $error_firstname; ?></span>
					<?php } ?></td>
			</tr>
			<tr>
				<td><?php echo $entry_lastname; ?></td>
				<td><input type="text" name="lastname" value="<?php echo $lastname; ?>" />
					<?php if ($error_lastname) { ?>
					<span class="error"><?php echo $error_lastname; ?></span>
					<?php } ?></td>
			</tr>
			<tr>
				<td><?php echo $entry_telephone; ?></td>
				<td><input type="text" name="telephone" value="<?php echo $telephone; ?>" />
					<?php if ($error_telephone) { ?>
					<span class="error"><?php echo $error_telephone; ?></span>
					<?php } ?></td>
			</tr>
		</table>

		<h2 class="underline"><?php echo $text_your_password; ?></h2>

		<table class="form">
			<tr>
				<td><span class="required">*</span> <?php echo $entry_password; ?></td>
				<td colspan="2"><input type="password" name="password" value="<?php echo $password; ?>" />
					<?php if ($error_password) { ?>
					<span class="error"><?php echo $error_password; ?></span>
					<?php } ?></td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_confirm; ?></td>
				<td colspan="2"><input type="password" name="confirm" value="<?php echo $confirm; ?>" />
					<?php if ($error_confirm) { ?>
					<span class="error"><?php echo $error_confirm; ?></span>
					<?php } ?></td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_captcha; ?></td>
				<td>
					<input type="text" value="" name="captcha" autocomplete="off" size="2" class="required captcha" minlength="4" maxlength="15" />
				</td>
				<td>
					<img src="/common/home/captcha?" onclick="this.src=this.src+1;" style="cursor: pointer;" />
					<?php if ($error_captcha) { ?><br /><span class="error"><?php echo $error_captcha; ?></span><?php } ?>
				</td>
			</tr>
		</table>

		<h2 class="underline"><?php echo $text_newsletter; ?></h2>

		<table class="form">
			<tr>
				<td><?php echo $entry_newsletter; ?></td>
				<td><?php if ($newsletter) { ?>
					<input type="radio" name="newsletter" value="1" checked="checked" />
					<?php echo $text_yes; ?>
					<input type="radio" name="newsletter" value="0" />
					<?php echo $text_no; ?>
					<?php } else { ?>
					<input type="radio" name="newsletter" value="1" />
					<?php echo $text_yes; ?>
					<input type="radio" name="newsletter" value="0" checked="checked" />
					<?php echo $text_no; ?>
					<?php } ?></td>
			</tr>
		</table>
		</div>

		<?php if ($text_agree) { ?>
		<div class="buttons">
			<div class="left"><a class="button" href="/account/login"><?php echo $button_back; ?></a></div>
			<div class="right"><?php echo $text_agree; ?>
				<?php if ($agree) { ?>
				<input type="checkbox" name="agree" value="1" checked="checked" />
				<?php } else { ?>
				<input type="checkbox" name="agree" value="1" />
				<?php } ?>
				<input type="submit" value="<?php echo $button_continue; ?>" class="button" />
			</div>
		</div>
		<?php } else { ?>
		<div class="buttons">
			<div class="left"><a class="button" href="/account/login"><?php echo $button_back; ?></a>
			</div>
			<div class="right">
				<?php echo $text_agree; ?>
				<?php if ($agree) { ?>
				<input type="checkbox" name="agree" value="1" checked="checked" />
				<?php } else { ?>
				<input type="checkbox" name="agree" value="1" />
				<?php } ?>
				<input type="submit" value="<?php echo $button_continue; ?>" class="button" />
			</div>
		</div>
		<?php } ?>
	</form>
</div>
<?php echo $page_footer; ?>