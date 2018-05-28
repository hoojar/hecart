<!DOCTYPE html>
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<?php if ($description) { ?><meta name="description" content="<?php echo $description; ?>" /><?php } ?>
<?php if ($keywords) { ?><meta name="keywords" content="<?php echo $keywords; ?>" /><?php } ?>
<?php foreach ($links as $link) { ?><link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" /><?php } ?>
<link rel="stylesheet" type="text/css" href="/view/style.css" />
<link rel="stylesheet" type="text/css" href="/view/autofit_admin_mobile.css" />
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="/js/css/gallery.css" />
<link type="text/css" href="/js/css/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/gallery.js"></script>
<script type="text/javascript" src="/js/jquery.freezeheader.js"></script>
<?php foreach ($scripts as $script) { ?><script type="text/javascript" src="<?php echo $script; ?>"></script><?php } ?>
<!--[if lt IE 9]>
<script src="/js/html5shiv.js"></script>
<script src="/js/respond.min.js"></script>
<![endif]-->
</head>
<body>
<?php if ($logged) { ?>
<link rel="stylesheet" type="text/css" href="/view/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/view/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="/view/nanoscroller.css" />
<link rel="stylesheet" type="text/css" href="/view/theme_styles.css" />
<div id="theme-wrapper">
	<header class="navbar" id="header-navbar">
		<div class="container">
			<a href="/" id="logo" class="navbar-brand"><img src="/view/image/logo.png" class="normal-logo logo-white" /></a>
			<div class="clearfix">
				<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
					<span class="sr-only">Toggle navigation</span><span class="fa fa-bars"></span>
				</button>
				<div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
					<ul class="nav navbar-nav pull-left">
						<li><a class="btn" id="make-small-nav"><i class="fa fa-bars"></i></a></li>
					</ul>
				</div>
				<div class="nav-no-collapse pull-right" id="header-nav">
					<ul class="nav navbar-nav pull-right">
						<li class="dropdown profile-dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<!--img src="/view/image/uicon.png"-->
								<span class="hidden-xs"><?php echo $logged; ?></span><?php if (!empty($stores)) { ?><b class="caret"></b><?php } ?>
							</a>
							<?php if (!empty($stores)) { ?>
							<ul class="dropdown-menu">
								<?php foreach ($stores as $stores) { ?>
								<li><a href="<?php echo $stores['href']; ?>" target="_blank"><?php echo $stores['name']; ?></a></li>
								<?php } ?>
							</ul>
							<?php } ?>
						</li>
						<li class="hidden-xxs"><a class="btn" href="<?php echo $user_profile; ?>"><?php echo $text_user_profile; ?> &nbsp;<i class="fa fa-user"></i></a></li>
						<li class="hidden-xxs"><a class="btn" href="<?php echo $logout; ?>"><?php echo $text_logout; ?> &nbsp;<i class="fa fa-power-off"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
	</header>

	<div id="page-wrapper" class="container">
		<div class="row">
			<div id="nav-col">
				<section id="col-left" class="col-left-nano">
					<div id="col-left-inner" class="col-left-nano-content">
						<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
							<ul class="nav nav-pills nav-stacked" id="menus">
								<li id="dashboard"><a href="/<?php echo $home; ?>"><i class="fa fa-dashboard"></i><span><?php echo $text_dashboard; ?></span></a></li>
								<?php foreach ($menus as $mk => $mv) { ?>
								<li id="<?php echo $mk; ?>">
									<a href="#" class="dropdown-toggle">
										<i class="fa fa-<?php echo $mk; ?>"></i>
										<span><?php echo ${"text_{$mk}"}; ?></span>
										<i class="fa fa-chevron-circle-right drop-icon"></i>
									</a>
									<ul class="submenu">
									<?php foreach ($mv as $mk1 => $mv1) { ?>
										<?php if (!is_array($mv1)) { ?>
										<?php if ($this->user->hasPermission('access', ${$mv1})) {  ?>
										<li><a href="/<?php echo ${$mv1}; ?>"><?php echo ${"text_{$mv1}"}; ?></a></li>
										<?php } ?>
										<?php } else { ?>
										<li><a href="javascript:void()" class="dropdown-toggle"><?php echo ${"text_{$mk1}"}; ?><i class="fa fa-chevron-circle-right drop-icon"></i></a>
											<ul class="submenu">
											<?php foreach ($mv1 as $mk2 => $mv2) { ?>
												<?php if (!is_array($mv2)) { ?>
												<?php if ($this->user->hasPermission('access', ${$mv2})) {  ?>
												<li><a href="/<?php echo ${$mv2}; ?>"><?php echo ${"text_{$mv2}"}; ?></a></li>
												<?php } ?>
												<?php } else { ?>
												<li><a href="javascript:void()" class="dropdown-toggle"><?php echo ${"text_{$mk2}"}; ?><i class="fa fa-chevron-circle-right drop-icon"></i></a>
													<ul class="submenu">
													<?php foreach ($mv2 as $mk3 => $mv3) { ?>
														<?php if ($this->user->hasPermission('access', ${$mv3})) {  ?>
														<li><a href="/<?php echo ${$mv3}; ?>"><?php echo ${"text_{$mv3}"}; ?></a></li>
														<?php } ?>
													<?php } ?>
													</ul>
												</li>
												<?php } ?>
											<?php } ?>
											</ul>
										</li>
										<?php } ?>
									<?php } ?>
									</ul>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
				</section>
			</div>
<script type="text/javascript">
function getURLVar(key)
{
	var value = [];
	var query = String(document.location).split('?');
	if (query[1])
	{
		var part = query[1].split('&');
		for (i = 0; i < part.length; i++)
		{
			var data = part[i].split('=');
			if (data[0] && data[1])
			{
				value[data[0]] = data[1];
			}
		}
	}
	return (typeof value[key] != 'undefined') ? value[key] : '';
}

// Hide no children menu
$('#menus li a.dropdown-toggle').each(function (index)
{
	if ($(this).next('ul').children('li').size() == 0)
	{
		$(this).parent('li').css('display', 'none');
	}
});

[<?php foreach ($menus as $mk => $mv){ echo "'#{$mk}',";} ?>].map(function (obj)
{
	if ($(obj + ' ul li:not(:has(a.dropdown-toggle))').size() == 0)
	{
		$(obj).css('display', 'none');
	}
});

// Navigation Selected
var route = getURLVar('route');
if (!route)
{
	var part = String(document.location).split('?');
	route = String(part[0]).replace($('base').attr('href'), '');
}
if (!route)
{
	$('#menus #dashboard').addClass('open active');
}
else
{
	var part = route.split('/'), url = part[0];
	if (part[1]) {url += '/' + part[1];}
	$("#menus a[href='/" + url + "']").addClass('active').parents('li[id]').addClass('open active');
}

$(document).ready(function ()
{
	// Confirm Delete
	$('#form').submit(function ()
	{
		if ($(this).attr('action').indexOf('delete', 1) != -1)
		{
			if ($(this).find(':checked').length <= 0)
			{
				alert('<?php echo $text_nochecked; ?>');
				return false;
			}
			if (!confirm('<?php echo $text_confirm; ?>')) {return false;}
		}
	});

	// Confirm Uninstall
	$('a').click(function ()
	{
		if ($(this).attr('href') != null && $(this).attr('href').indexOf('uninstall', 1) != -1)
		{
			if (!confirm('<?php echo $text_confirm; ?>')) {return false;}
		}
	});

	// Operation Button Scroll
	$(window).scroll(function ()
	{
		if ($(window).scrollTop() > 140)
		{
			$("#ctrl-div").addClass('fixed');
		}
		else
		{
			$("#ctrl-div").removeClass('fixed');
		}

		if ($(document).height() > $(window).height())
		{
			$('#content-wrapper').css('min-height', $(document).height() - 51);
		}
	});

	$('#content-wrapper').css('min-height', $(window).height() - 51);
	$(window).resize(function() {$('#content-wrapper').css('min-height', $(window).height() - 51);});
	$(".list").freezeHeader();

	/**
	 * 机构文字拼音搜索
	 */
	$("input[name='org_name']").focus(function ()
	{
		$('#org_div').animate({scrollTop: 200}, 500);
		$(this).siblings('div').css({left: $(this).position().left, width: $(this).innerWidth()}).show();
	});

	$("input[name='org_name']").blur(function ()
	{
		setTimeout(function ()
		{
			$("input[name='org_name']").siblings('div').hide();
		}, 150);
	});

	$("input[name='org_name']").on('input', function ()
	{
		var org = $(this).val().toUpperCase();
		$(this).siblings('div').find('ul').children('li').each(function ()
		{
			if ($(this).attr('spell').indexOf(org) > -1 || $(this).text().indexOf(org) > -1)
			{
				$(this).show();
			}
			else
			{
				$(this).hide();
			}
		});
		var $first_li = $(this).siblings('div').find('ul').children('li').filter(':visible').first();
		$first_li.siblings().removeClass('org_li_select');
		$first_li.parent().find('span').remove();
		$first_li.addClass('org_li_select');
		$first_li.append('<span class="glyphicon glyphicon-ok"></span>');
		$("input[name='org_id']").val($first_li.attr('orgId'));

		if (org == '')
		{
			$("input[name='org_id']").val('*');
			$first_li.removeClass('org_li_select');
			$first_li.find('span').remove();
		}
	});

	$('div.content').on('keypress', function (event)
	{
		if (event.keyCode == 13)
		{
			filter();
		}
	});
});

function tipBox(tip, tit, icn, cExec)
{
	icn = icn || 'error';
	tit = tit || 'HECart Notification';
	cExec = cExec || function () {};
	if (String(tip).indexOf('<') == -1)
	{
		tip = '<p style="padding:10px;">' + tip + '</p>';
	}
	$.fn.tboxy({title: tit, value: tip, icon: icn, time: 6000, closeExec: cExec});
}

$.fn.easyTabs = function ()
{
	var selector = this;
	this.each(function ()
	{
		var obj = $(this);
		$(obj.attr('href')).hide();
		$(obj).click(function ()
		{
			$(selector).removeClass('selected');
			$(selector).each(function (i, element)
			{
				$($(element).attr('href')).hide();
			});

			$(this).addClass('selected');
			$($(this).attr('href')).fadeIn();
			return false;
		});
	});

	$(this).show();
	$(this).first().click();
};

function orgSelect(obj, org_id, org_name)
{
	$("input[name='org_id']").val(org_id);
	$("input[name='org_name']").val(org_name);
	$("input[name='org_name']").siblings('div').hide();

	$(obj).siblings().removeClass('org_li_select');
	$(obj).parent().find('span').remove();

	$(obj).addClass('org_li_select');
	$(obj).append('<span class="glyphicon glyphicon-ok"></span>');
}

function filter()
{
	var url = '/user/org?';
	var filter_org_id = $('input[name=\'org_id\']').val();
	if (filter_org_id != '*')
	{
		url += '&filter_org_id=' + encodeURIComponent(filter_org_id);
	}

	location = url;
}
</script>
<div id="content-wrapper">
<?php } ?>
