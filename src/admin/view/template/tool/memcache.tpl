<?php echo $page_header; ?>
<style type="text/css">
h1.memcache{background:rgb(153,153,204);margin:0;padding:0.5em 1em 0.5em 1em;}
h1.memcache a:hover{text-decoration:none;color:rgb(90,90,90);}
h1.memcache span.logo{background:rgb(119,123,180);color:black;border-right: solid black 1px;border-bottom: solid black 1px;font-style:italic;font-size:1em;padding-left:1.2em;padding-right:1.2em;text-align:right;display:block;width:130px;}
h1.memcache span.logo span.name{color:white;font-size:0.7em;padding:0 12px;0 2em;}
h1.memcache span.nameinfo{color:white;display:inline;font-size:0.4em;}
h1.memcache div.copy{color:black;font-size:0.4em;position:absolute;right:1em;}
hr.memcache{background:white;border-bottom:solid rgb(102,102,153) 1px;border-style:none;border-top:solid rgb(102,102,153) 10px;height:12px;margin:0;margin-top:1px;padding:0;}

table td{white-space:nowrap;}
table td:first-child{font-weight:bold;}
div.info{margin-bottom:10px;}
div.info h2{background:#DBDBDB;color:black;font-size:1em;margin:0;padding:0.1em 1em 0.1em 1em;}
div.info table{border:solid #DBDBDB 1px;border-spacing:0;width:100%;}
div.info table td h3{color:black;font-size:15px;}
div.graph{margin-bottom:1em;}
div.graph h2{background:#DBDBDB;color:black;font-size:1em;margin:0;padding:0.1em 1em 0.1em 1em;}

div.sorting{margin:1.5em 0em 1.5em 0em;}
.center{text-align:center;}
.aright{position:absolute;right:1em;}
.right{text-align:right;}
.ok{color:rgb(0,200,0);font-weight:bold;}
.failed{color:rgb(200,0,0);font-weight:bold;}
span.box{border: black solid 1px;border-right:solid black 2px;border-bottom:solid black 2px;padding:0 0.5em 0 0.5em;margin-right:0em;}
span.green{background:#60F060;padding:0 0.5em 0 0.5em;}
span.red{background:#D06030;padding:0 0.5em 0 0.5em;}
div.authneeded{background:rgb(238,238,238);border:solid #DBDBDB 1px;color:rgb(200,0,0);font-size:1.2em;font-weight:bold;padding:2em;text-align:center;}
</style>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/setting.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div"><?php if ($mpermission) {echo $menu;} ?></div>
		</div>
		<div class="content">
			<?php echo $content; ?>
		</div>
	</div>
</div>
<?php echo $page_footer; ?>