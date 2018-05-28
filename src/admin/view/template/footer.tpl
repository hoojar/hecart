				<footer id="footer-bar" class="row">
					<p id="footer-copyright" class="col-xs-12"><?php echo $text_footer; ?></p>
				</footer>
			</div>
		</div>
	</div>
</div>

<div id="config-tool" class="closed">
	<a id="config-tool-cog"><i class="fa fa-cog"></i></a>
	<div id="config-tool-options">
		<h4><?php echo $text_layout_optoins; ?></h4>
		<ul>
			<li><div class="checkbox-nice"><input type="checkbox" id="config-fixed-header" /><label for="config-fixed-header"><?php echo $text_fixed_header; ?></label></div></li>
			<li><div class="checkbox-nice"><input type="checkbox" id="config-fixed-sidebar" /><label for="config-fixed-sidebar"><?php echo $text_fixed_left_menu; ?></label></div></li>
			<li><div class="checkbox-nice"><input type="checkbox" id="config-fixed-footer" /><label for="config-fixed-footer"><?php echo $text_fixed_footer; ?></label></div></li>
			<li><div class="checkbox-nice"><input type="checkbox" id="config-boxed-layout" /><label for="config-boxed-layout"><?php echo $text_boxed_layout; ?></label></div></li>
			<li><div class="checkbox-nice"><input type="checkbox" id="config-rtl-layout" /><label for="config-rtl-layout"><?php echo $text_rtl_layout; ?></label></div></li>
		</ul><br/>
		<h4><?php echo $text_skin_color; ?></h4>
		<ul id="skin-colors" class="clearfix">
			<li><a class="skin-changer blue-gradient" data-skin="theme-blue-gradient" title="Gradient"></a></li>
			<li><a class="skin-changer" data-skin="" style="background-color: #34495E;"></a></li>
			<li><a class="skin-changer" data-skin="theme-red" style="background-color: #E74C3C;"></a></li>
			<li><a class="skin-changer" data-skin="theme-blue" style="background-color: #2980B9;"></a></li>
			<li><a class="skin-changer" data-skin="theme-whbl" style="background-color: #3498DB;"></a></li>
			<li><a class="skin-changer" data-skin="theme-white" style="background-color: #2ECC71;"></a></li>
			<li><a class="skin-changer" data-skin="theme-amethyst" style="background-color: #9B59B6;"></a></li>
			<li><a class="skin-changer" data-skin="theme-turquoise" style="background-color: #1ABC9C;"></a></li>
		</ul>
	</div>
</div>

<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="js/jquery.nanoscroller.min.js"></script>
<script type="text/javascript" src="js/theme.script.js"></script>
</body>
</html>