/*
 example for: $("#slideGallery").slideGallery();
 <div class="slide-gallery" id="slideGallery">
 <div class="slide-a">
 <a href="http://www.hoojar.com/" title="慧佳" target="_blank"><img alt="慧佳网" src="http://www.hoojar.com/images/banner.gif"></a>
 <a href="http://www.hoojar.com/" title="广告1" target="_blank"><img alt="广告1" src="http://www.hoojar.com/images/1.jpg"></a>
 </div>
 <div class="slide-text"></div><div class="slide-nav"></div><div class="slide-over"></div>
 </div>
 */
(function ($)
{
	$.fn.slideGallery = function (options)
	{
		var settings =
		{
			width       : 480,		//slide宽度
			height      : 480,		//slide高度
			zIndex      : 0,		//z-index层级
			autoplay    : 0,		//多少毫秒后就自动播放放一个
			navCurrent  : 'sld',	//导航定位class样式
			navOpacity  : 1,		//导航条透明度
			navShowTitle: false,	//在slide-nav处是否显示标题
			navShowText : true		//在slide-nav处是否显示说明
		};
		if (options)
		{
			$.extend(settings, options);
		}

		return this.each(function ()
		{
			var slideImg = $('.slide-a a', this);
			var slideTxt = $('.slide-text', this);
			var slideNav = $('.slide-nav', this);
			var index = 0, slideTimer = null;
			var totalIndex = slideImg.size();//统计有多少张图片

			$(this).css('width', settings.width);//slide宽度
			$(this).css('height', settings.height);//slide高度
			$(this).css('z-index', settings.zIndex);//z-index层级

			slideImg.each(function (i)
			{
				var tip = settings.navShowTitle ? $(this).attr('title') : (i + 1);
				slideNav.append('<a href="javascript:void()">' + tip + '</a>');
			});

			var slideHref = $('.slide-nav a', this);
			slideNav.animate({opacity: settings.navOpacity}, 0);
			(settings.navShowText) ? slideTxt.show() : slideTxt.hide();

			//滑动展示图片
			var slideShowImg = function (i)
			{
				if (settings.navShowText)
				{
					slideTxt.text(slideImg.eq(i).find('img').attr('alt'));
				}
				slideImg.eq(i).stop(true, true).fadeIn(600).siblings('a').hide();
				slideHref.eq(i).addClass(settings.navCurrent).siblings('a.' + settings.navCurrent).removeClass(settings.navCurrent);
			}

			//滑动超链接点击
			slideHref.mouseover(function ()
			{
				index = slideHref.index(this);
				slideShowImg(index);
				return false;
			});

			slideShowImg(index);
			index++;//将图片定位到第一张

			//手机端滑动处理
			try
			{
				$(this).swiperight(function ()
				{
					index++;
					index = (index >= totalIndex) ? 0 : index;
					slideShowImg(index);
				});

				$(this).swipeleft(function ()
				{
					index--;
					index = (index <= -1) ? totalIndex : index;
					slideShowImg(index);
				});
			}
			catch (e)
			{
			}

			//定时开始自动播放
			var autoplay =
			{
				timeoutId        : null,
				performProcessing: function ()
				{
					slideShowImg(index);
					index++;
					if (index >= totalIndex)
					{
						index = 0;
					}
				},
				process          : function ()
				{
					clearInterval(autoplay.timeoutId);
					autoplay.timeoutId = setInterval(function ()
					{
						autoplay.performProcessing()
					}, settings.autoplay);
				},
				dispose          : function ()
				{
					clearInterval(autoplay.timeoutId);
				}
			};
			if (settings.autoplay > 1)
			{
				autoplay.process();
				slideImg.hover(autoplay.dispose, autoplay.process);//鼠标移动到图片上则暂停自动播放
			}
		});
	};
})(jQuery);

/*
 example for: $.fn.tboxy({title:'sys tip', value:'loading error', time:5000});$("a[rel=tboxy]").tboxy();
 */
(function ($)
{
	$.fn.tboxyCount = 0;	//展示框总数
	$.fn.tboxyIndex = 100;	//展示框层数
	$.fn.tboxy = function (options)
	{
		var settings =
		{
			id       : 0,			//展示框编号0为系统自动分配
			title    : 'tboxy',		//标题文本，若不想显示title请通过CSS设置其display为none
			opacity  : 1,			//展示框的透明度
			modal    : true,		//是否是模态展示框
			center   : true,		//是否居中
			fixed    : true,		//是否跟随页面滚动
			top      : 0,			//展示框自定义顶部位置为0代表自动分配
			left     : 0,			//展示框自定义左侧位置为0代表自动分配
			width    : 'auto',		//窗口宽度
			height   : 'auto',		//窗口高度
			time     : 0,			//自动关闭时间，为0表示不会自动关闭
			value    : '',			//初始化的内容
			icon     : '',			//提示图标
			draggable: true,			//是否移动
			bgColor  : '',			//展示框的背景颜色
			closeText: '[X]',		//关闭按钮文字，若不想显示关闭按钮请通过CSS设置其display为none
			closeExec: function () {},	//关闭按钮完成后需要执行的函数
			afterShow: function () {}	//展示完成后需要执行的函数
		};

		var boxyDiv = null;//窗口层
		var overDiv = null;//遮盖层
		var timeId = null;//自动关闭计时器
		if (options)
		{
			$.extend(settings, options);
		}
		settings.id = (settings.id != 0) ? settings.id : $.fn.tboxyCount++;

		//设置内容参数c为混合变量
		function setContent(c)
		{
			var div = boxyDiv.find('.content');
			if ('object' != typeof(c))
			{
				div.html(c);//直接设置HTML内容
			}
			else//根据类型加载不同的数据
			{
				if (!jQuery.isPlainObject(c))
				{
					div.append(c);//此处c为DOM结点可以追加到div中
				}
				else
				{
					switch (c.type.toLowerCase())
					{
					case 'id':
						div.html($('#' + c.val).html());
						break;
					case 'obj':
						div.html($(c.val).html());
						break;
					case 'img':
						div.html('loading...');
						$('<img />').load(function ()
						{
							div.empty().append($(this));
							reposition();
						}).attr('src', c.val);
						break;
					case 'url':
						div.html('loading...');
						$.ajax(
							{
								url    : c.val,
								success: function (html)
								{
									div.html(html);
									reposition();
								},
								error  : function (xml, textStatus, error) {div.html('loading error');}
							});
						break;
					case 'iframe':
						div.append($('<iframe style="width:100%;height:' + settings.height + ';" border="0" frameborder="0" src="' + c.val + '" />'));
						break;
					case 'text':
					default:
						div.html(c.val);
						break;
					}
				}
			}

			bindEvent();//给展示框绑定事件
			if (settings.modal)
			{
				$('body').append(overDiv);
				overDiv.show();
			}
			$('body').append(boxyDiv);
			reposition();//显示展示框内容并重新定位
			boxyDiv.show();
		}

		//关闭展示框
		function closeTboxy()
		{
			clearTimeout(timeId);
			boxyDiv.fadeOut('fast', function () {$(this).remove();});
			if (settings.modal)
			{
				$('#tboxy-overlay' + settings.id).fadeOut('fast', function () {$(this).remove();});
			}
			settings.closeExec();//关闭按钮完成后需要执行的函数
		}

		//重新定位展示框位置
		function reposition()
		{
			if (settings.center)
			{
				var top = ($(window).height() - boxyDiv.height()) / 2;
				var left = ($(window).width() - boxyDiv.width()) / 2;
				if (!settings.fixed || boxyDiv.css('position') == 'absolute')
				{
					top = top + $(document).scrollTop();
					left = left + $(document).scrollLeft();
				}
			}
			else
			{
				var top = (settings.top != 0) ? settings.top : ($(window).height() - boxyDiv.height()) / 2;
				var left = (settings.left != 0) ? settings.left : ($(window).width() - boxyDiv.width()) / 2;
			}

			top = (top <= 0) ? 0 : top;
			left = (left <= 0) ? 0 : left;
			boxyDiv.css({top: top, left: left});
			settings.afterShow();//显示展示框内容并执行展示后的函数
		}

		//给展示框绑定事件
		function bindEvent()
		{
			overDiv.attr('id', 'tboxy-overlay' + (++settings.id)).css({'height': $(document).height(), 'z-index': ++$.fn.tboxyIndex});
			boxyDiv.css({'width': settings.width, 'height': settings.height, 'background': settings.bgColor, 'z-index': ++$.fn.tboxyIndex, 'opacity': settings.opacity, 'position': (settings.fixed ? 'fixed' : 'absolute')});
			boxyDiv.find('.close').bind('click', closeTboxy);
			overDiv.bind('click', closeTboxy);

			//以下代码处理框体是否可以移动
			var mouse = {x: 0, y: 0};

			function moveBoxy(event)
			{
				var e = window.event || event;
				var top = parseInt(boxyDiv.css('top')) + (e.clientY - mouse.y);
				if (top <= 0)
				{
					top = 0;
				}
				else if (top + boxyDiv.height() >= $(window).height())
				{
					top = $(window).height() - boxyDiv.height();
				}

				var left = parseInt(boxyDiv.css('left')) + (e.clientX - mouse.x);
				if (left <= 0)
				{
					left = 0;
				}
				else if (left + boxyDiv.width() >= $(window).width())
				{
					left = $(window).width() - boxyDiv.width();
				}

				boxyDiv.css({top: top, left: left});
				mouse.x = e.clientX;
				mouse.y = e.clientY;
			};

			boxyDiv.find('.title-bar').mousedown(function (event)
			{
				if (!settings.draggable)
				{
					return;
				}
				var e = window.event || event;
				mouse.x = e.clientX;
				mouse.y = e.clientY;
				$(document).bind('mousemove', moveBoxy);
			});

			$(document).mouseup(function (event) {$(document).unbind('mousemove', moveBoxy);});
			$(window).resize(function () {reposition();});
		}

		//根据选择器A元素来展示其href地址
		this.each(function ()
		{
			var obj = $(this);
			$(this).click(function ()
			{
				switch (obj[0].tagName.toLowerCase())
				{
				case 'a':
					var url = obj.attr('href');
					setContent((url != '') ? {type: 'url', val: url} : {type: 'obj', val: this});
					break;
				case 'img':
					setContent({type: 'img', val: obj.attr('src')});
					break;
				case 'iframe':
					setContent({type: 'iframe', val: obj.attr('src')});
					break;
				default:
					setContent({type: 'obj', val: this});
				}
				return false;
			});
		});

		//初始化展示框
		this.init()
		{
			overDiv = $('<div class="touch-boxy-overlay"></div>');
			var titleBarHtml = (settings.title == '') ? '' : '<div class="title-bar"><span class="title">' + settings.title + '</span><a class="close">' + settings.closeText + '</a></div>';
			boxyDiv = $('<div class="touch-boxy">' + titleBarHtml + '<div class="content ' + settings.icon + '"></div></div>');
		}

		if (settings.value != '')
		{
			setContent(settings.value);//设置窗口内容
		}

		if (settings.time != 0)
		{
			timeId = setTimeout(closeTboxy, settings.time);//设置到时自动关闭
		}
	};
})(jQuery);