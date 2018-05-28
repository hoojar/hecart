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

function print_r(theObj)
{
	var retStr = '';
	if (typeof theObj == 'object')
	{
		retStr += '<div style="font-size:15px;">';
		for (var p in theObj)
		{
			if (typeof theObj[p] == 'object')
			{
				retStr += '<div><b>[' + p + '] => ' + typeof(theObj) + '</b></div>';
				retStr += '<div style="padding-left:25px;">' + print_r(theObj[p]) + '</div>';
			}
			else
			{
				retStr += '<div>[' + p + '] => <b>' + theObj[p] + '</b></div>';
			}
		}
		retStr += '</div>';
	}
	return retStr;
}

function tipBox(tip, tit, cExec)
{
	tit = tit || 'HECart Notification';
	cExec = cExec || function () {};
	$.fn.tboxy({title: tit, value: tip, time: 6000, closeExec: cExec});
}

jQuery.cookie = function (name, value, options)
{
	if (typeof value != 'undefined') //name and value given, set cookie
	{
		options = options || {};
		if (value === null)
		{
			value = '';
			options.expires = -1;
		}

		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toGMTString))
		{
			var date;
			if (typeof options.expires == 'number')
			{
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			}
			else
			{
				date = options.expires;
			}
			expires = '; expires=' + date.toGMTString();
		}

		var path = options.path ? '; path=' + (options.path) : '';
		var domain = options.domain ? '; domain=' + (options.domain) : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	}
	else //only name given, get cookie
	{
		var cookieValue = null;
		if (document.cookie && document.cookie != '')
		{
			var cookies = document.cookie.split(';');
			for (var i = 0; i < cookies.length; i++)
			{
				var cookie = jQuery.trim(cookies[i]);
				if (cookie.substring(0, name.length + 1) == (name + '='))
				{
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		return cookieValue;
	}
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

function loadJsCss(filename)
{
	var ref, strFile = filename.toLowerCase();
	if (strFile.indexOf('.js') != -1)
	{
		ref = document.createElement('script');
		ref.setAttribute('type', 'text/javascript');
		ref.setAttribute('src', filename);
	}
	else if (strFile.indexOf('.css'))
	{
		ref = document.createElement('link');
		ref.setAttribute('rel', 'stylesheet');
		ref.setAttribute('type', 'text/css');
		ref.setAttribute('href', filename);
	}

	if (typeof ref != 'undefined')
	{
		document.getElementsByTagName('head')[0].appendChild(ref);
	}
}

function pwdSetting(obj)
{
	$(obj).find(':password').each(function ()
	{
		if ($(this).val())
		{
			$(this).val($.md5($(this).val()));
		}
	});
}

function pwdChecking(obj)
{
	$(obj).find(':password').each(function ()
	{
		if ($(this).val())
		{
			var salt = $(this).attr('salt');
			var pwd = $.md5($(this).val());
			$(this).val($.md5($.md5($.md5(pwd.substring(0, 9)) + pwd) + salt));
		}
	});
}

$(document).ready(function ()
{
	$(".tboxy").tboxy({title: document.title});

	/* Show User */
	if ($.cookie('cust_id'))
	{
		$('#user-logged').show();
		$('#user-logged > a:first-child').html($.cookie('cust_email'));
	}
	else
	{
		$('#user-welcome').show();
	}

	/* Show Cart */
	var cart_info = $.cookie('cartTotal');
	if (cart_info)
	{
		$("#cart-total").html(String(cart_info).replace(/\+/g, ' '));
	}

	/* Search */
	$('#header input[name="search"]').on('keydown', function (e)
	{
		if (e.keyCode == 13)
		{
			searchFilter();
		}
	});

	/* Ajax Load Cart */
	ajaxLoadCart();
	$('#cart').on('mouseleave', function () {$(this).removeClass('active');});

	/* Some tips fade out show */
	$('.success img, .warning img, .attention img, .information img').on('click', function ()
	{
		$(this).parent().fadeOut('slow', function () {$(this).remove();});
	});
});