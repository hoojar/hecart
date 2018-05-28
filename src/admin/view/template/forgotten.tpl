<?php echo $page_header; ?>
<div style="max-width:1024px;margin:0 auto;text-align:center;">
	<img src="/view/image/pwd-forgotten.jpg" style="max-width:100%" />
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/user.png" /><?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<a onclick="$('#forgotten').submit();" class="button"><?php echo $button_reset; ?></a>
				<a onclick="location='<?php echo $cancel; ?>'" class="button"><?php echo $button_cancel; ?></a>
			</div>
		</div>

		<div class="content" style="text-align:center;padding:50px;">
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="forgotten">
			<p><?php echo $text_email; ?></p><br/><br/>
			<?php echo $entry_email; ?> <input type="text" name="email" value="<?php echo $email; ?>" size="70"/>
		</form>
		</div>
	</div>
</div>
</body>
</html>