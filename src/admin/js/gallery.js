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
(function($)
{
	$.fn.slideGallery = function(options)
	{
		var settings =
		{
			width			: 480,		//slide宽度
			height			: 480,		//slide高度
			zIndex			: 0,		//z-index层级
			autoplay		: 0,		//多少毫秒后就自动播放放一个
			navCurrent		: 'sld',	//导航定位class样式
			navOpacity		: 1,		//导航条透明度
			navShowTitle	: false,	//在slide-nav处是否显示标题
			navShowText		: true		//在slide-nav处是否显示说明
		};
		if (options) {$.extend(settings, options);}

		this.each(function()
		{
			var slideImg = $('.slide-a a', this);
			var slideTxt = $('.slide-text', this);
			var slideNav = $('.slide-nav', this);
			var index = 0, slideTimer = null;
			var totalIndex = slideImg.size();//统计有多少张图片

			$(this).css('width',	settings.width);//slide宽度
			$(this).css('height',	settings.height);//slide高度
			$(this).css('z-index',	settings.zIndex);//z-index层级

			slideImg.each(function(i)
			{
				var tip = settings.navShowTitle ? $(this).attr('title') : (i + 1);
				slideNav.append('<a href="javascript:void()">' + tip + '</a>');
			});

			var slideHref = $('.slide-nav a', this);
			slideNav.animate({opacity: settings.navOpacity}, 0);
			if (settings.navShowText) {slideTxt.show();} else {slideTxt.hide();}

			//滑动展示图片
			var slideShowImg = function(i)
			{
				if (settings.navShowText) {slideTxt.text(slideImg.eq(i).find('img').attr('alt'));}
				slideImg.eq(i).stop(true, true).fadeIn(600).siblings('a').hide();
				slideHref.eq(i).addClass(settings.navCurrent).siblings('a.'+settings.navCurrent).removeClass(settings.navCurrent);
			}

			//滑动超链接点击
			slideHref.mouseover(function()
			{
				index = slideHref.index(this);
				slideShowImg(index);
				return false;
			});

			slideShowImg(index); index++;//将图片定位到第一张

			//定时开始自动播放
			var autoplay =
			{
				timeoutId : null,
				performProcessing : function()
				{
					slideShowImg(index); index++;
					if (index == totalIndex) {index = 0;}
				},
				process : function()
				{
					clearInterval(autoplay.timeoutId);
					autoplay.timeoutId = setInterval(function()
					{
						autoplay.performProcessing()
					}, settings.autoplay);
				},
				dispose : function()
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
example for: $('#swipeGallery').swipeGallery({autoHeight: false});
<ul id="swipeGallery">
	<li><img src="_img/slide4.jpg" alt="" title=""/></li>
	<li><img src="_img/slide5.jpg" alt="" title=""/></li>
</ul>
 */
(function($)
{
	$.fn.swipeGallery = function(options)
	{
		var settings =
		{
			delay		: 300,				//动画加延时毫秒
			width		: 480,				//图片广告宽度
			height		: 800,				//图片广告高度
			tolerance	: 0.25,				//容忍差数
			autoplay	: 0,				//多少毫秒后就自动播放放一个
			autoHeight	: false,			//是否自动设置高度
			classname	: 'swipe-gallery',	//图片class的名称
			background	: 'transparent none repeat scroll 0% 0%'
		}
		if (options) {$.extend(settings, options);}

		var mousedown	= false;
		var mouseX		= 0;
		var imgLength	= 0;
		var imgCurrent	= 0;
		var xdiff		= 0;
		var boxHeight	= 0;
		var boxWidth	= 0;

		function doResizeImage(listElement)
		{
			$(listElement).css('height', boxHeight);
			$(listElement).css('width', boxWidth);
			var img = $(listElement).find('img');
		}

		function init(obj, parent, imgHandler)
		{
			if (settings.autoSize)
			{
				boxHeight = $(window).height();
				boxWidth = $(window).width();
			}
			else
			{
				boxHeight = parseInt(settings.height);
				boxWidth = parseInt(settings.width);
			}

			imgLength = 0;
			obj.find('li').each(function()
			{
				doResizeImage(this);
				imgLength++;
			});

			parent.css('height', boxHeight);
			parent.css('width',  boxWidth);

			imgHandler.css('width', boxWidth);
			imgHandler.css('height', boxHeight);
			imgHandler.css('left', parent.position().left);
			imgHandler.css('top', parent.position().bottom);
			obj.css('width', imgLength * boxWidth);
		}

		return this.each(function()
		{
			var _this = $(this);
			_this.wrap('<div class="' + settings.classname + '"/>');
			var parent = _this.parent();
			parent.css('background-color', settings.background);
			parent.prepend('<div class="image-handler"/>');

			var imgHandler = _this.parent().find('.image-handler');
			init(_this, parent, imgHandler);
			if (settings.autoSize) {$(window).resize(function(){init(_this, parent, imgHandler);});}

			imgHandler.mousedown(function(event)
			{
				if (!this.mousedown)
				{
					this.mousedown = true;
					this.mouseX = event.pageX;
				}
				return false;
			});

			imgHandler.mousemove(function(event)
			{
				if (this.mousedown)
				{
					xdiff = event.pageX - this.mouseX;
					_this.css('left', -imgCurrent * boxWidth + xdiff);
				}
				return false;
			});

			imgHandler.mouseup(function(event)
			{
				this.mousedown = false;
				if (!xdiff) {return false;}

				var fullWidth = parseInt(settings.width);
				var halfWidth = fullWidth / 2;
				if (-xdiff > halfWidth - fullWidth * settings.tolerance)
				{
					imgCurrent++;
					imgCurrent = imgCurrent >= imgLength ? imgLength-1 : imgCurrent;
					_this.animate({left: -imgCurrent * boxWidth}, settings.delay);
				}
				else if (xdiff > halfWidth - fullWidth * settings.tolerance)
				{
					imgCurrent--;
					imgCurrent = imgCurrent < 0 ? 0 : imgCurrent;
					_this.animate({left: -imgCurrent * boxWidth}, settings.delay);
				}
				else
				{
					_this.animate({left: -imgCurrent * boxWidth}, settings.delay);
				}
				xdiff = 0;
				return false;
			});

			imgHandler.mouseleave(function(event){imgHandler.mouseup();});

			//定时开始自动播放
			var autoplay =
			{
				timeoutId : null,
				performProcessing : function()
				{
					imgCurrent++; if (imgCurrent >= imgLength) {imgCurrent = 0;}
					_this.animate({left: -imgCurrent * boxWidth}, settings.delay);
				},
				process : function()
				{
					clearInterval(autoplay.timeoutId);
					autoplay.timeoutId = setInterval(function()
					{
						autoplay.performProcessing()
					}, settings.autoplay);
				},
				dispose : function()
				{
					clearInterval(autoplay.timeoutId);
				}
			};
			if (settings.autoplay > 1)
			{
				autoplay.process();
				imgHandler.hover(autoplay.dispose, autoplay.process);//鼠标移动到图片上则暂停自动播放
			}
		});
	};
})(jQuery);

/*
example for: $("#prd-slide").slideLayer({effect : 'both',autoplay : 5000});$("#prd-slide img[rel=lazy-load]").scrollLoading();
<div class="t-slide t-index-slide" id="prd-slide" title="导航指示在下侧">
	<div class="wrap" style="width:365px;height:365px;border-bottom:1px solid #C4C4C4;">
		<ul class="slide">
			<li><a href="/"><img src="/img/1.jpg"></a></li>
			<li><a href="/url/"><img src="/img/2.jpg"></a></li>
			<li><a href="/"><img rel="lazy-load" src="/images/load.gif" data-src="/img/3.jpg"></a></li>
		</ul>
	</div>
	<div class="pgn pagination" style="width:365px;"><a class="prev pg-btn">&lt;</a><a class="next pg-btn">&gt;</a></div>
</div>

<div class="t-slide t-offer-pic-slide" id="prd-slide" title="导航指示在左右侧">
	<div class="pagination"><a class="prev pg-btn">&lt;</a><a class="next pg-btn">&gt;</a></div>
	<div class="wrap" style="width:365px;height:365px;">
		<ul class="slide">
			<li><a href="/"><img src="/img/1.jpg"></a></li>
			<li><a href="/url/"><img src="/img/2.jpg"></a></li>
			<li><a href="/"><img rel="lazy-load" src="/images/load.gif" data-src="/img/3.jpg"></a></li>
		</ul>
	</div>
	<div class="pgn pagination"></div>
</div>
 */
(function($)
{
	$.fn.slideLayer = function(options)
	{
		var settings =
		{
			direction	: 'X',		//移动方向ＸＹ
			slideEl		: '.slide',	//移动对象元素
			childEl		: 'li',		//移动对象子元素
			wrapEl		: '.wrap',	//包套元素
			effect		: 'scroll',	//移动操作效果{slide,scroll,both}
			current		: 1,		//当前移动到第几个了
			cycle		: 1,		//是否循环移动
			autoplay	: 0,		//多少毫秒后就自动播放放一个
			width		: 365,		//移动元素宽度
			height		: 365		//移动元素高度
		};
		if (options) {$.extend(settings, options);}

		return $(this).each(function(i, it)
		{
			var sl		= $(it);
			var wrap	= sl.find(settings.wrapEl);
			var slide	= sl.find(settings.slideEl);
			var touch	= slide[0];
			var child	= slide.find(settings.childEl);
			var total	= Math.ceil(slide.find(settings.childEl).length);
			var cur		= settings.current;

			wrap.css({width:settings.width, height:settings.height});
			var wrapWidth	= wrap.width();
			var wrapHeight	= wrap.height();
			slide.css('width', total * wrapWidth);
			trigs(); if (total < 2) {return false;}
			var isTouch = ('ontouchstart' in document.documentElement);

			switch (settings.effect)
			{
				case 'slide':
					pgn();
					break;
				case 'scroll':
					scroll();
					break;
				case 'both':
					pgn();
					scroll();
					break;
			}

			function trigs()
			{
				var str = '<div class="trigs"><ul>';
				for (var n = 0; n < total; n++) {str += '<li class="' + (n == 0 ? 'cur' : '') + '">n</li>';}
				str += '</ul></div>';

				sl.find(".pgn").append(str).css('width', settings.width);
				var txt = '\u7b2c<span style="color:#e10000">' + cur + '</span>/' + total + '\u9875';
				sl.find(".pg-num").html(txt);
				sl.find(".comment .img-text").text(sl.find(" .slide li img").eq(cur - 1).attr("img-text") || "");
			}

			function pgn()
			{
				sl.find(".pagination .prev").click(function(e)
				{
					if (settings.cycle == 1)
					{
						cycprev();
					}
					else
					{
						if (cur != 1) {cycprev();}
					}
				});

				sl.find(".pagination .next").click(function(e)
				{
					if (settings.cycle == 1)
					{
						cycnext();
					}
					else
					{
						if (cur != total) {cycnext();}
					}
				})
			}

			function scroll()
			{
				if (isTouch)
				{
					slide.touchWipe({wipeLeft:function(){cycnext();}, wipeRight:function(){cycprev();}});
				}
				else
				{
					slide.dragWipe({wipeLeft:function(){cycnext();}, wipeRight:function(){cycprev();}});
				}
			}

			function updateTrigs()
			{
				sl.find(".trigs li").eq(cur - 1).addClass("cur").siblings().removeClass("cur");
				sl.find(".pg-num span").text(cur);
				sl.find(".comment .img-text").text(sl.find(" .slide li img").eq(cur - 1).attr("img-text") || "");
			}

			function prev()
			{
				if (settings.direction == "X")
				{
					slide.animate({left : -(wrapWidth * (cur - 2))}, settings.delay, function()
					{
						slide.css("left", -(wrapWidth * (cur - 1)));
						child.eq(total - 1).css("left", 0);
					});
				}
				else
				{
					slide.animate({top : -(wrapHeight * (cur - 2))}, settings.delay);
				}
				cur == 1 ? cur = total : cur--;
				updateTrigs();
			}

			function next()
			{
				if (settings.direction == "X")
				{
					slide.animate({left : -(wrapWidth * cur)}, settings.delay, function()
					{
						slide.css("left", -(wrapWidth * (cur - 1)));
						child.eq(0).css("left", 0);
					});
				}
				else
				{
					slide.animate({top : -(wrapHeight * cur)}, settings.delay);
				}
				cur == total ? cur = 1 : cur++;
				updateTrigs();
			}

			var cycprev = function()
			{
				if (settings.autoplay > 1) {autoplay.process();}
				if (settings.cycle == 1)
				{
					if (cur != 1)
					{
						prev();
						return false;
					}
					else
					{
						prev();
						child.eq(total - 1).css("left", -(wrapWidth * total));
						child.eq(0).css("left", 0);
						return false;
					}
				}
				else
				{
					if (cur != 1) {prev();return false;}
				}
			};

			var cycnext = function()
			{
				if (settings.autoplay > 1) {autoplay.process();}
				if (settings.cycle == 1)
				{
					if (cur != total) {
						next();
						return false;
					}
					else
					{
						next();
						child.eq(0).css("left", wrapWidth * total);
						child.find(settings.childEl).css("left", 0);
						return false;
					}
				}
				else
				{
					if (cur != total)
					{
						next();
						return false;
					}
				}
			};

			var autoplay =
			{
				timeoutId : null,
				performProcessing : function() {cycnext();},
				process : function()
				{
					clearInterval(autoplay.timeoutId);
					autoplay.timeoutId = setInterval(function()
					{
						autoplay.performProcessing()
					}, settings.autoplay);
				},
				dispose : function()
				{
					clearInterval(autoplay.timeoutId);
					return;
				}
			};
			if (settings.autoplay > 1) {autoplay.process();}
		});
	};

	$.fn.scrollLoading = function(options)
	{
		var settings = {attr:"data-src"};
		var params = $.extend({}, settings, options || {});
		params.cache = [];

		$(this).each(function()
		{
			var node = this.nodeName.toLowerCase(), url = $(this).attr(params["attr"]);
			if (!url) {return;}
			var data = {obj : $(this), tag : node, url : url};
			params.cache.push(data);
		});

		//动态显示数据
		var loading = function()
		{
			var st = $(window).scrollTop(), sth = st + $(window).height();
			$.each(params.cache, function(i, data)
			{
				var o = data.obj, tag = data.tag, url = data.url;
				if (o)
				{
					post = o.position().top;
					if (post < 10){post = o.offset().top;}
					posb = post + o.height();
					if ((post > st && post < sth) || (posb > st && posb < sth))
					{
						if (tag === "img")
						{
							o.attr("src", url);
							o.removeClass("imgunloaded");
							o.addClass("imgloaded");
						}
						else if (tag == "iframe")
						{
							o.attr("src", url);
						}
						else if (tag == "span")
						{
							eval(url);
						}
						else
						{
							o.load(url);
						}
						data.obj = null;
					}
				}
			});
			return false;
		};
		loading();
		$(window).bind("scroll", loading);
	};
})(jQuery);

/*
example for: $('.flip').flip().show();
<ul class="flip">
	<li><a href="/"><img src="/img/1.jpg"></a></li>
	<li><a href="/url/"><img src="/img/2.jpg"></a></li>
</ul>
*/
(function(window, document, location, $)
{
	var CSS_PREFIX = 'flip-',
		NEXT = 'next',
		PREVIOUS = 'previous',
		ANIMATE = 'animate',
		inverse = {next: PREVIOUS,previous: NEXT};

	function animate(from, to, type)
	{
		var animationClassNames = inverse[type] + ' ' + ANIMATE;
		// Stage the next page
		to.removeClass(animationClassNames).addClass(type);

		// Exit the current page
		from.addClass(animationClassNames);

		// Pause so that the staging has time to complete
		setTimeout(function()
		{
			to.addClass(ANIMATE).removeClass(type);
		}, 0);// Enter the next page
	}

	$.fn.flip = function(options)
	{
		var settings =
		{
			pages		: null,	//CSS selector for pages
			navigation	: true,	//enable button navigation
			touch		: true,	//enable touch navigation
			orientation	:'horizontal', //flip orientation (horizontal|vertical)
			text		:
			{
				previous: '&#9664;',//previous button text
				next	: '&#9654;'	//next button text
			}
		};

		// Extend default options with configured options
		options = $.extend({}, settings, options);

		// Configure each instance
		return this.each(function()
		{
			// Find pages by selector if specified or use children
			var element = $(this),
			pages = settings.pages ? element.find(settings.pages) : element.children(),
			navigation,
			currentPage = pages.filter(':first');
			element.addClass('flip ' + CSS_PREFIX + settings.orientation);

			// Add page class to pages
			pages.addClass(CSS_PREFIX + 'page').not(currentPage).addClass(NEXT);
			function handleNavigation(event)
			{
				var type = event.type,
				index = currentPage.index(),
				lastIndex = pages.size() - 1,
				nextIndex,
				offset,
				dimension,
				nextPage;

				if (type === 'click' && event.currentTarget === this)
				{
					if ($(event.target).is('a')) {return;}
					if (settings.orientation === 'horizontal')
					{
						offset = ~~(event.pageX - currentPage.offset().left);
						dimension = currentPage.width();
					}
					else
					{
						offset = ~~(event.pageY - currentPage.offset().top);
						dimension = currentPage.height();
					}
					type = (offset > dimension / 2) ? NEXT : PREVIOUS;
				}

				if (type === NEXT)
				{
					nextIndex = index < lastIndex ? index + 1 : 0;
				}
				else
				{
					nextIndex = index > 0 ? index - 1 : lastIndex;
				}

				if (nextIndex !== index)
				{
					nextPage = pages.eq(nextIndex);
					animate(currentPage, nextPage, type);
					currentPage = nextPage;
				}
			}

			// Add navigation
			if (settings.navigation)
			{
				navigation = $('<div class="' + CSS_PREFIX + 'buttons"/>');
				$([PREVIOUS, NEXT]).each(function(n, direction)
				{
					var button = $('<button class="' + CSS_PREFIX + 'button ' + CSS_PREFIX + 'button-' + direction + '"/>')
					.html(settings.text[direction])
					.hover(function(event)
					{
						button.toggleClass('hover', event.type === 'mouseenter');
					}).bind('click', function(event)
					{
						button.trigger(direction);
						if (window.Touch){button.removeClass('hover');}
					}).appendTo(navigation);

					$(window).bind('hover', function()
					{
						if (event.type === 'mouseover')
						{
							button.show();
						}
						else
						{
							button.hide();
						}
					});
				});

				navigation.appendTo(element);
				navigation.bind(NEXT + ' ' + PREVIOUS, handleNavigation);
			}

			pages.bind('click', handleNavigation);//Bind events
		});

	};

	var touchstartX, touchstartY;
	try
	{
		window.addEventListener('touchstart', function(event)
		{
			var touches = event.targetTouches,
			touch = touches[0];
			touchstartX = touch.pageX;
			touchstartY = touch.pageY;
		}, false);
	
		window.addEventListener('touchmove', function(event)
		{
			var touches = event.targetTouches,
			touch = touches[0];
			touchendX = touch.pageX,
			touchendY = touch.pageY;
			if (touches.length !== 1){return;}
			if (touchendX > touchstartX)
			{
				pages.trigger('next');
			}
			else if (touchendX < touchstartX)
			{
				pages.trigger('previous');
			}
		}, false);
	}catch(e){}
})(this, document, location, $);

/*
example for: $.fn.tboxy({title:'sys tip', value:'loading error', time:5000});$("a[rel=tboxy]").tboxy();
*/
(function($)
{
	$.fn.tboxyCount = 0;	//展示框总数
	$.fn.tboxyIndex = 100;	//展示框层数
	$.fn.tboxy = function(options)
	{
		var settings =
		{
			id			: 0,			//展示框编号0为系统自动分配
			title		: 'tboxy',		//标题文本，若不想显示title请通过CSS设置其display为none
			opacity		: 1,			//展示框的透明度
			modal		: true,			//是否是模态展示框
			center		: true,			//是否居中
			fixed		: true,			//是否跟随页面滚动
			top			: 0,			//展示框自定义顶部位置为0代表自动分配
			left		: 0,			//展示框自定义左侧位置为0代表自动分配
			width		: 'auto',		//窗口宽度
			height		: 'auto',		//窗口高度
			time		: 0,			//自动关闭时间，为0表示不会自动关闭
			value		: '',			//初始化的内容
			icon		: '',			//提示图标
			draggable	: true,			//是否移动
			bgColor		: '',			//展示框的背景颜色
			closeText	: '[X]',		//关闭按钮文字，若不想显示关闭按钮请通过CSS设置其display为none
			closeExec	: function(){},	//关闭按钮完成后需要执行的函数
			afterShow	: function(){}	//展示完成后需要执行的函数
		};

		var boxyDiv 	= null;//窗口层
		var overDiv 	= null;//遮盖层
		var timeId		= null;//自动关闭计时器
		if (options) {$.extend(settings, options);}
		settings.id		= (settings.id != 0) ? settings.id : $.fn.tboxyCount++;

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
					switch(c.type.toLowerCase())
					{
						case 'id':
							div.html($('#' + c.val).html());
							break;
						case 'obj':
							div.html($(c.val).html());
							break;
						case 'img':
							div.html('loading...');
							$('<img />').load(function(){div.empty().append($(this)); reposition();}).attr('src',c.val);
							break;
						case 'url':
							div.html('loading...');
							$.ajax(
							{
								url:c.val,
								success:function(html){div.html(html); reposition();},
								error:function(xml,textStatus,error){div.html('loading error');}
							});
							break;
						case 'iframe':
							div.append($('<iframe style="width:100%;height:'+settings.height+';" border="0" frameborder="0" src="' + c.val + '" />'));
							break;
						case 'text':
						default:
							div.html(c.val);
							break;
					}
				}
			}

			bindEvent();//给展示框绑定事件
			if (settings.modal) {$('body').append(overDiv); overDiv.show();}
			$('body').append(boxyDiv); reposition();//显示展示框内容并重新定位
			boxyDiv.show();
		}

		//关闭展示框
		function closeTboxy()
		{
			clearTimeout(timeId);
			boxyDiv.fadeOut('fast', function(){$(this).remove();});
			if (settings.modal) {$('#tboxy-overlay' + settings.id).fadeOut('fast', function(){$(this).remove();});}
			settings.closeExec();//关闭按钮完成后需要执行的函数
		}

		//重新定位展示框位置
		function reposition()
		{
			if (settings.center)
			{
				var top	= ($(window).height()- boxyDiv.height())/ 2;
				var left= ($(window).width() - boxyDiv.width()) / 2;
				if (settings.fixed)
				{
					boxyDiv.css({top:top, left:left});
				}
				else
				{
					boxyDiv.css({top:top + $(document).scrollTop(), left:left + $(document).scrollLeft()});
				}
			}
			else
			{
				var top	= (settings.top	!= 0) ? settings.top	: ($(window).height()- boxyDiv.height())/ 2;
				var left= (settings.left != 0) ? settings.left: ($(window).width() - boxyDiv.width()) / 2;
				boxyDiv.css({top:top, left:left});
			}
			settings.afterShow();//显示展示框内容并执行展示后的函数
		}

		//给展示框绑定事件
		function bindEvent()
		{
			overDiv.attr('id', 'tboxy-overlay' + (++settings.id)).css({'height':$(document).height(), 'z-index':++$.fn.tboxyIndex});
			boxyDiv.css({'width':settings.width,'height':settings.height,'background':settings.bgColor,'z-index':++$.fn.tboxyIndex,'opacity':settings.opacity,'position':(settings.fixed ? 'fixed' : 'absolute')});
			boxyDiv.find('.close').bind('click', closeTboxy);
			overDiv.bind('click', closeTboxy);

			//以下代码处理框体是否可以移动
			var mouse = {x:0, y:0};
			function moveBoxy(event)
			{
				var e = window.event || event;
				var top = parseInt(boxyDiv.css('top')) + (e.clientY - mouse.y);
				if (top <= 0) {top = 0;} else if (top + boxyDiv.height() >= $(window).height()) {top = $(window).height() - boxyDiv.height();}

				var left = parseInt(boxyDiv.css('left')) + (e.clientX - mouse.x);
				if (left <= 0) {left = 0;} else if (left + boxyDiv.width() >= $(window).width()) {left = $(window).width() - boxyDiv.width();}
				boxyDiv.css({top:top, left:left});
				mouse.x = e.clientX; mouse.y = e.clientY;
			};

			boxyDiv.find('.title-bar').mousedown(function(event)
			{
				if(!settings.draggable) {return;}
				var e = window.event || event;
				mouse.x = e.clientX; mouse.y = e.clientY;
				$(document).bind('mousemove',moveBoxy);
			});
			$(document).mouseup(function(event){$(document).unbind('mousemove', moveBoxy);});
			$(window).resize(function(){reposition();});
		}

		//根据选择器A元素来展示其href地址
		this.each(function()
		{
			var obj = $(this);
			$(this).click(function()
			{
				switch (obj[0].tagName.toLowerCase())
				{
					case 'a':
						var url = obj.attr('href');
						setContent((url != '') ? {type:'url', val:url} : {type:'obj', val:this});
						break;
					case 'img':
						setContent({type:'img', val:obj.attr('src')});
						break;
					case 'iframe':
						setContent({type:'iframe', val:obj.attr('src')});
						break;
					default:
						setContent({type:'obj', val:this});
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

		if (settings.value != '') {setContent(settings.value);}//设置窗口内容
		if (settings.time != 0) {timeId = setTimeout(closeTboxy, settings.time);}//设置到时自动关闭
	};
})(jQuery);