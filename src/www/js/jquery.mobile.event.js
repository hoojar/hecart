/*
 * touchWipe 触摸滑动上下左右
 * dragWipe	 拖放滑动上下左右
 * example for: $("#touch").touchWipe({wipeLeft:function(){doLeft();}, wipeRight:function(){doRight();}});
 * example for: $("#drage").dragWipe({wipeLeft:function(){doLeft();}, wipeRight:function(){doRight();}});
 */
(function($)
{
	$.fn.touchWipe = function(configs)
	{
		var config =
		{
			minMoveX	: 20,				//最小X轴移动像数
			minMoveY	: 35,				//最小Y轴数像数
			wipeLeft	: function() {},	//向左擦拭时执行的函数
			wipeRight	: function() {},	//向右擦拭时执行的函数
			wipeUp		: function() {},	//向上擦拭时执行的函数
			wipeDown	: function() {},	//向下擦拭时执行的函数
			selfMove	: false,			//自己是否移动
			preventDefaultEvents : false	//是否预防默认事件
		};
		if (configs) {$.extend(config, configs);}

		this.each(function()
		{
			var startX, startY, sxy;
			var tobj = $(this), tpos;
			if ('ontouchstart' in document.documentElement)
			{
				this.addEventListener('touchstart', onTouchStart, false);
			}

			function onTouchEnd(e)
			{
				var x = e.changedTouches[0].pageX;
				var y = e.changedTouches[0].pageY;
				var dx = startX - x;
				var dy = startY - y;
				if (Math.abs(dx) >= config.minMoveX && Math.abs(dy) < config.minMoveY)
				{
					if (dx > 0)
					{
						config.wipeLeft();
					}
					else
					{
						config.wipeRight();
					}
				}
				else if (Math.abs(dy) >= config.minMoveY)
				{
					if (dy > 0)
					{
						config.wipeDown();
					}
					else
					{
						config.wipeUp();
					}
				}
				this.removeEventListener('touchend', onTouchEnd);
				this.removeEventListener('touchmove', onTouchMove);
			}

			function onTouchMove(e)
			{
				if (config.preventDefaultEvents) {e.preventDefault();}

				if (config.selfMove)
				{
					var mxy = getClient(e);
					var left = (mxy[0] - sxy[0]) + tpos[0];
					var top =  (mxy[1] - sxy[1]) + tpos[1];
					if (left<= 0) {left	= 0;} else if (left + tobj.width()	>= $(window).width()) {left = $(window).width()	- tobj.width();}
					if (top	<= 0) {top	= 0;} else if (top	+ tobj.height() >= $(window).height()){top	= $(window).height()- tobj.height();}
					tobj.css({left:left, top:top});
				}
			}

			function onTouchStart(e)
			{
				startX	= e.changedTouches[0].pageX;
				startY	= e.changedTouches[0].pageY;
				sxy		= getClient(e);
				tpos	= [tobj.position().left, tobj.position().top];
				this.addEventListener('touchmove', onTouchMove, false);
				this.addEventListener('touchend', onTouchEnd, false);
				return false;
			}

			function getClient(e)
			{
				var coors = [0, 0];
				coors[0] = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
				coors[1] = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
				return coors;
			}
		});
		return this;
	};

	$.fn.dragWipe = function(options)
	{
		var config =
		{
			direction	: "X",				//擦拭方向X轴与Y轴
			minMoveX	: 20,				//最小X轴移动像数
			minMoveY	: 20,				//最小Y轴数像数
			wipeLeft	: function() {},	//向左擦拭时执行的函数
			wipeRight	: function() {},	//向右擦拭时执行的函数
			wipeUp		: function() {},	//向上擦拭时执行的函数
			wipeDown	: function() {},	//向下擦拭时执行的函数
			selfMove	: false,			//自己是否移动
			preventDefaultEvents : false	//是否预防默认事件
		}
		if (options) {$.extend(config, options);}

		this.each(function()
		{
			var tobj = $(this); tobj[0].onmousedown = tobj[0].ontouchstart = startDrag;

			function startDrag(e)
			{
				var startXY 		= getClient(e);
				var tpos			= [tobj.position().left, tobj.position().top];
				tobj[0].ontouchmove	= tobj[0].onmousemove	= moveDrag;
				tobj[0].ontouchend	= document.onmouseup= endDrag;
				if (config.preventDefaultEvents) {e.preventDefault();} return false;

				function moveDrag(e)
				{
					if (config.preventDefaultEvents) {e.preventDefault();}

					var cxy	= getClient(e);
					var left= (cxy[0] - startXY[0]) + tpos[0];
					var top	= (cxy[1] - startXY[1]) + tpos[1];
					if (config.selfMove)
					{
						if (left<= 0) {left	= 0;} else if (left + tobj.width()	>= $(window).width()) {left = $(window).width()	- tobj.width();}
						if (top	<= 0) {top	= 0;} else if (top	+ tobj.height() >= $(window).height()){top	= $(window).height()- tobj.height();}
						tobj.css({left:left, top:top});
					}
					else
					{
						(config.direction == "X") ? tobj.css('left', left) : tobj.css('top', top);
					}
				}

				function endDrag(e)
				{
					var cxy = getClient(e);
					var dx = startXY[0] - cxy[0];
					var dy = startXY[1] - cxy[1];
					if (Math.abs(dx) >= config.minMoveX && Math.abs(dy) < 20)
					{
						if (dx > 0)
						{
							config.wipeLeft();
						}
						else
						{
							config.wipeRight();
						}
					}
					else if (Math.abs(dy) >= config.minMoveY)
					{
						if (dy > 0)
						{
							config.wipeDown();
						}
						else
						{
							config.wipeUp();
						}
					}
					tobj[0].ontouchmove = tobj[0].ontouchend = tobj[0].onmousemove = document.onmouseup = null;
				}
			}

			function getClient(e)
			{
				var coors = [0, 0];
				coors[0] = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
				coors[1] = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
				return coors;
			}
		});
		return this;
	};
})(jQuery);