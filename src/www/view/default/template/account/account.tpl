<?php echo $page_header; ?>
<?php if ($success) { ?><div class="success"><?php echo $success; ?></div><?php } ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?>
		<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>

	<div class="content">
		<h2 class="underline"><?php echo $text_my_account; ?></h2>
		<ul class="account">
			<li><a href="<?php echo $edit; ?>"><img src="/view/default/image/account/edit.png" /><br/><?php echo $text_edit; ?></a></li>
			<li><a href="<?php echo $password; ?>"><img src="/view/default/image/account/password.png" /><br/><?php echo $text_password; ?></a></li>
			<li><a href="<?php echo $address; ?>"><img src="/view/default/image/account/address.png" /><br/><?php echo $text_address; ?></a></li>
		</ul>
	</div>
	<div class="buttons"><a href="/account/logout" class="btn-red button"><?php echo $button_logout; ?></a></div>
</div>
<?php echo $page_footer; ?>