<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/user.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a onclick="$('#reset').submit();" class="button btn-green"><?php echo $button_save; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-yellow"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="reset">
				<p><?php echo $text_password; ?></p>
				<table class="form">
					<tr>
						<td><?php echo $entry_password; ?></td>
						<td><input type="password" name="password" value="<?php echo $password; ?>" />
							<?php if ($error_password) { ?>
							<span class="error"><?php echo $error_password; ?></span>
							<?php } ?></td>
					</tr>
					<tr>
						<td><?php echo $entry_confirm; ?></td>
						<td><input type="password" name="confirm" value="<?php echo $confirm; ?>" />
							<?php if ($error_confirm) { ?>
							<span class="error"><?php echo $error_confirm; ?></span>
							<?php } ?></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php echo $page_footer; ?>