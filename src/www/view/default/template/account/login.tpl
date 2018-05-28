<?php echo $page_header; ?>
<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
<script type="text/javascript" src="/js/jquery.md5.js"></script>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<div class="content login-content">
		<div class="right">
			<h2 class="underline"><?php echo $text_new_customer; ?></h2>
			<p><?php echo $text_register; ?></p>
			<p><?php echo $text_register_account; ?></p>
			<div style="text-align:right;"><a href="<?php echo $register; ?>" class="button" style="min-width:130px;"><?php echo $button_continue; ?></a></div>
		</div>

		<div class="left">
			<h2 class="underline"><?php echo $text_returning_customer; ?></h2>
			<form action="<?php echo $action; ?>" method="post" onsubmit="pwdChecking(this)" enctype="multipart/form-data" id="login">
				<p><?php echo $text_i_am_returning_customer; ?></p>
				<b><?php echo $entry_email; ?></b>
				<input type="text" name="email" value="<?php echo $email; ?>" size="28" />
				<br /><br />

				<b><?php echo $entry_password; ?></b>
				<input type="password" name="password" value="<?php echo $password; ?>" salt="<?php echo $salt; ?>" size="28" />
				<br /><br />

				<?php if (isset($this->session->data['captcha'])) { ?>
				<b><?php echo $entry_captcha; ?></b>
				<input type="text" name="captcha" autocomplete="off" value="" size="10" />
				<img src="/common/home/captcha?" onclick="this.src=this.src+1;" style="cursor:pointer;vertical-align:middle;" />
				<br /><br />
				<?php } ?>

				<input type="submit" value="<?php echo $button_login; ?>" class="button" style="min-width:130px;" /> &nbsp;&nbsp;　<a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a>
				<?php if ($redirect) { ?><input type="hidden" name="redirect" value="<?php echo $redirect; ?>" /><?php } ?>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">$('#login input').keydown(function(e) {if (e.keyCode == 13) {$('#login').submit();}});$('#login input[name="email"]').focus();</script>
<?php echo $page_footer; ?>