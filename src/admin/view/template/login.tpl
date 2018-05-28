<?php echo $page_header; ?>
<style type="text/css">
body{background:url('/view/image/login<?php echo mt_rand(0, 6); ?>.jpg') no-repeat scroll 50% 100%;background-size:cover;}
.login-box{width:40%;max-width:380px;min-width:320px;margin:0 auto;position:relative;text-align:center;background:#FFF;border-radius:10px;padding:2px 0 10px 0;box-shadow:0 0 10px 10px rgba(0, 0, 0, 0.3);opacity:0.8;}
.login-box > h1{text-align:center;font-size:18px;font-weight:bold;margin:5px;padding:10px 0;border-bottom:1px dashed #DDD;}
.login-box input{width:80%;height:30px;font-size:18px;color:#78797c;padding-left:50px;}
.login-box .user{background:#f7f7f7 url('/view/image/loign-user.png') no-repeat 10px center;}
.login-box .pwd{background:#f7f7f7 url('/view/image/login-pwd.png') no-repeat 10px center;}
.login-box .cpc{background:#f7f7f7 url('/view/image/login-cpc.png') no-repeat 10px center;}
.login-box .button{display:block;background:#1d8fe9;color:#FFF;width:86%;height:30px;font-size:25px;line-height:30px;margin-left:10px;}
</style>
<div style="width:100%;height:100%;display:table;"><div style="display:table-cell;vertical-align:middle;">
	<div class="login-box">
		<h1><?php echo $text_login; ?></h1>
		<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
		<?php if ($error_warning || $error_captcha) { ?><div class="warning" style="margin:auto;margin:10px;"><?php echo $error_captcha, $error_warning; ?></div><?php } ?>
		<form action="<?php echo $action; ?>" method="post" onsubmit="pwdChecking(this)" enctype="multipart/form-data" id="form" style="margin:10px;padding:5px;">
		<input type="text" name="username" value="<?php echo $username; ?>" size="26" placeholder="<?php echo $entry_username; ?> " class="user"/><br /><br />
		<input type="password" name="password" value="<?php echo $password; ?>" salt="<?php echo $salt; ?>" size="26" placeholder="<?php echo $entry_password; ?> " class="pwd" /><br /><br />

		<?php if (isset($this->session->data['captcha'])) { ?>
		<input type="text" name="captcha" autocomplete="off" size="8" placeholder="<?php echo $entry_captcha; ?>" class="cpc" style="width:44%;"/>
		<img src="/common/login/captcha?" onclick="this.src=this.src+1;" style="cursor:pointer;vertical-align:middle;width:35%;" /><br /><br />
		<?php } ?>

		<a onclick="$('#form').submit();" class="button"><?php echo $button_login; ?></a><br/><a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a>　　
		<?php if ($redirect) { ?><input type="hidden" name="redirect" value="<?php echo $redirect; ?>" /><?php } ?>
		</form>
	</div>
</div></div>
<script type="text/javascript" src="/js/jquery.md5.js"></script>
<script type="text/javascript"><!--
function pwdChecking(obj)
{
	$(obj).find(':password').each(function ()
	{
		var salt = $(this).attr('salt');
		var pwd = $.md5($(this).val());
		$(this).val($.md5($.md5($.md5(pwd.substring(0, 9)) + pwd) + salt));
	});
}

$('#form input').keydown(function (e)
{
	if (e.keyCode == 13) {$('#form').submit();}
});
$('#form input[name="username"]').focus();
//-->
</script>
</body>
</html>