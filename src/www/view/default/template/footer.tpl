</div>

<div id="footer">
	<?php if ($informations) { ?>
		<?php foreach ($informations as $c) { ?>
			<div class="column">
				<h3><?php echo $c['name']; ?></h3>
				<ul>
					<?php foreach ($c['res'] as $v) { ?>
					<li class="follow<?php echo $v['id']; ?>"><a href="<?php echo $v['href']; ?>" target="_blank"><span><?php echo $v['title']; ?></span></a></li>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>
	<?php } ?>
</div>

<div id="powered"><?php echo $powered; ?>　Powered by <a href="http://www.hecart.com/" target="_blank"><b style="color:#D61F1F">HE</b><b style="color:#17BCF0">Cart</b></a></div>
<div id="scrollTop" class="scroll-top"></div>
<script type="text/javascript">
$("#scrollTop").click(function(){$("html,body").animate({scrollTop:0});});
$(window).scroll(function(){if($(window).scrollTop() > 100){$("#scrollTop").fadeIn();}else{$("#scrollTop").fadeOut();}});
</script>
<?php echo $google_analytics; ?>
</body>
</html>