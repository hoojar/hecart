//增加校验的相关函数
var validator =
{
	// 正则匹配
	test           : function (s, p)
	{
		s = s.nodeType == 1 ? s.value : s;
		return new RegExp(p).test(s);
	},
	//校验输入内容是否全为数字(0-9)
	isNumber       : function (s)
	{
		return this.test(s, /\D/);
	},
	//浮点小数
	isDecimal      : function (s)
	{
		return this.test(s, /^-?\d+(\.\d+)?$/g);
	},
	//整数或负数
	isInteger      : function (s)
	{
		return this.test(s, /^[-+]?\d*$/);
	},
	//校验输入内容是否为空(空字符串或只包含空格的字符串)
	isEmpty        : function (s)
	{
		return !jQuery.isEmptyObject(s);
	},
	//校验输入内容是否全为字母(a-Z)
	isLetter       : function (v)
	{
		return this.test(s, /^[A-Za-z]+$/);
	},
	//校验输入内容是否为小写字母(a-z)
	isLowerCase    : function (s)
	{
		return this.test(s, /^[a-z]+$/);
	},
	//校验输入内容是否为大写字母(A-Z)
	isUpperCase    : function (s)
	{
		return this.test(s, /^[A-Z]+$/);
	},
	//校验输入内容是否为字符模式(数字、字母或下划线组成)
	isChar         : function (s)
	{
		return this.test(s, /^\w+$/);
	},
	//校验输入内容是否为至少包含一个下划线的字符模式(数字、字母和下划线组成,必须有下划线)
	isCharUnderline: function (s)
	{
		return this.test(s, /^(\w*)(\_+)(\w*)$/);
	},
	//固话，手机号码检查函数，合法返回true,反之,返回false
	isPhone        : function (s)
	{
		return this.test(s, /(^([0\+]\d{2,3})\d{3,4}\-\d{3,8}$)|(^([0\+]\d{2,3})\d{3,4}\d{3,8}$)|(^([0\+]\d{2,3}){0,1}13\d{9}$)|(^\d{3,4}\d{3,8}$)|(^\d{3,4}\-\d{3,8}$)/);
	},
	//校验输入内容是否为电话号码的格式(<2至5位的数字区号->5至9位的数字号码)
	isTel          : function (s)
	{
		return this.test(s, /^(\((\d{2,5})\)|\d{2,5})?(\s*)(-?)(\s*)(\d{5,9})$/);
	},
	//校验输入内容是否为手机号码的格式(前缀可能有一个“+86”,和以13X/15X/18X为开头的11位中国手机号码)
	isMobile       : function (s)
	{
		return this.test(s, /^(\+86)?1[3,4,5,7,8](\d{9})$/);
	},
	//判断是否为传真号码
	isFax          : function (s)
	{
		return this.test(s, /(^\d{3,4},\d{7,8}(,\d{1,4})?$)|(^\d{3,4}\-\d{7,8}(\-\d{1,4})?$)|(^\d{3,4}\d{7,11}$)/);

	},
	//判断是否为有效IP地址
	isIp           : function (s)
	{
		return this.test(s, /((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/);
	},
	//判断是否为合法的URL地址
	isUrl          : function (s)
	{
		var strRegex = "^((https|http|ftp|rtsp|mms):\/\/)"//协议头
			+ "(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?"//ftp的user@
			+ "(([0-9]{1,3}\.){3}[0-9]{1,3}"//IP形式的URL- 199.194.52.184
			+ "|"//允许IP和DOMAIN（域名）
			+ "([0-9a-z_!~*'()-]+\.)*"//域名 www.
			+ "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\."//二级域名
			+ "[a-z]{2,6})"//首层域名 .com or .museum
			+ "(:[0-9]{1,4})?"//端口 :80
			+ "((\/?)|"//a slash isn't required if there is no file name
			+ "(\/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+\/?)$";
		var ereg = new RegExp(strRegex, "i");
		return (ereg.test(s)) ? true : false;
	},
	//判断是否是有效的EMAIL地址
	isEmail        : function (s)
	{
		return this.test(s, /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/);
	},
	//判断是否为时间字符串
	isTime         : function (v)
	{
		var a = v.match(/^(\d{1,2})(:)?(\d{1,2})\2(\d{1,2})$/);
		if (a == null)
		{
			return false;
		}
		return (a[1] > 24 || a[3] > 60 || a[4] > 60) ? false : true
	},
	//是否为日期（YYYY-MM-DD）类型字符串
	isDate         : function (v)
	{
		var r = v.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
		if (r == null)
		{
			return false;
		}
		var d = new Date(r[1], r[3] - 1, r[4]);
		return (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4]);
	},
	//判断是否为时间日期类型
	isDateTime     : function (v)
	{
		var ereg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/;
		var r = v.match(ereg);
		if (r == null)
		{
			return false;
		}
		var d = new Date(r[1], r[3] - 1, r[4], r[5], r[6], r[7]);
		return (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4] && d.getHours() == r[5] && d.getMinutes() == r[6] && d.getSeconds() == r[7]);
	},
	//校验是否为身份证
	isIdentityCard : function (s)
	{
		//15位数身份证正则表达式
		var arg1 = /^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/;
		//18位数身份证正则表达式
		var arg2 = /^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[A-Z])$/;
		return (s.match(arg1) == null && s.match(arg2) == null) ? false : true;
	}
};

/************************************身份证号码的验证*************************************/
/**
 * 身份证15位编码规则：dddddd yymmdd xx p
 * dddddd：地区码
 * yymmdd: 出生年月日
 * xx: 顺序类编码，无法确定
 * p: 性别，奇数为男，偶数为女
 * <p />
 * 身份证18位编码规则：dddddd yyyymmdd xxx y
 * dddddd：地区码
 * yyyymmdd: 出生年月日
 * xxx:顺序类编码，无法确定，奇数为男，偶数为女
 * y: 校验码，该位数值可通过前17位计算获得
 * <p />
 * 18位号码加权因子为(从右到左) Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2,1 ]
 * 验证位 Y = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ]
 * 校验位计算公式：Y_P = mod( ∑(Ai×Wi),11 )
 * i为身份证号码从右往左数的 2...18 位; Y_P为脚丫校验码所在校验码数组位置
 *
 */
var Wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];// 加权因子
var ValideCode = [1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2];// 身份证验证位值.10代表X
function IdCardValidate(idCard)
{
	idCard = trim(idCard.replace(/ /g, ""));
	if (idCard.length == 15)
	{
		return isValidityBrithBy15IdCard(idCard);
	}
	else if (idCard.length == 18)
	{
		var a_idCard = idCard.split("");// 得到身份证数组
		return (isValidityBrithBy18IdCard(idCard) && isTrueValidateCodeBy18IdCard(a_idCard)) ? true : false;
	}
	else
	{
		return false;
	}
}

/**
 * 判断身份证号码为18位时最后的验证位是否正确
 * @param a_idCard 身份证号码数组
 * @return
 */
function isTrueValidateCodeBy18IdCard(a_idCard)
{
	var sum = 0; // 声明加权求和变量
	if (a_idCard[17].toLowerCase() == 'x')
	{
		a_idCard[17] = 10;// 将最后位为x的验证码替换为10方便后续操作
	}
	for (var i = 0; i < 17; i++)
	{
		sum += Wi[i] * a_idCard[i];// 加权求和
	}
	valCodePosition = sum % 11;// 得到验证码所位置
	return (a_idCard[17] == ValideCode[valCodePosition]) ? true : false;
}

/**
 * 通过身份证判断是男是女
 * @param idCard 15/18位身份证号码
 * @return 'female'-女、'male'-男
 */
function maleOrFemalByIdCard(idCard)
{
	idCard = trim(idCard.replace(/ /g, ""));// 对身份证号码做处理。包括字符间有空格。
	if (idCard.length == 15)
	{
		return (idCard.substring(14, 15) % 2 == 0) ? 'female' : 'male';
	}
	else if (idCard.length == 18)
	{
		return (idCard.substring(14, 17) % 2 == 0) ? 'female' : 'male';
	}
	else
	{
		return null;
	}
}

/**
 * 验证18位数身份证号码中的生日是否是有效生日
 * @param idCard 18位书身份证字符串
 * @return
 */
function isValidityBrithBy18IdCard(idCard18)
{
	var year = idCard18.substring(6, 10);
	var month = idCard18.substring(10, 12);
	var day = idCard18.substring(12, 14);
	var temp_date = new Date(year, parseFloat(month) - 1, parseFloat(day));
	// 这里用getFullYear()获取年份，避免千年虫问题
	if (temp_date.getFullYear() != parseFloat(year) ||
		temp_date.getMonth() != parseFloat(month) - 1 ||
		temp_date.getDate() != parseFloat(day))
	{
		return false;
	}
	else
	{
		return true;
	}
}

/**
 * 验证15位数身份证号码中的生日是否是有效生日
 * @param idCard15 15位书身份证字符串
 * @return
 */
function isValidityBrithBy15IdCard(idCard15)
{
	var year = idCard15.substring(6, 8);
	var month = idCard15.substring(8, 10);
	var day = idCard15.substring(10, 12);
	var temp_date = new Date(year, parseFloat(month) - 1, parseFloat(day));
	// 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法
	if (temp_date.getYear() != parseFloat(year) ||
		temp_date.getMonth() != parseFloat(month) - 1 ||
		temp_date.getDate() != parseFloat(day))
	{
		return false;
	}
	else
	{
		return true;
	}
}

/************************************移动设备的验证*************************************/

//判断是否移动设备
function isMobileDevice()
{
	if (typeof this._isMobile === 'boolean')
	{
		return this._isMobile;
	}
	var screenWidth = this.getScreenWidth();
	var fixViewPortsExperiment = rendererModel.runningExperiments.FixViewport || rendererModel.runningExperiments.fixviewport;
	var fixViewPortsExperimentRunning = fixViewPortsExperiment && (fixViewPortsExperiment.toLowerCase() === "new");
	if (!fixViewPortsExperiment)
	{
		if (!this.isAppleMobileDevice())
		{
			screenWidth = screenWidth / window.devicePixelRatio;
		}
	}
	var isMobileScreenSize = screenWidth < 600;
	var isMobileUserAgent = false;
	this._isMobile = isMobileScreenSize && this.isTouchScreen();
	return this._isMobile;
}

//判断是否移动设备访问
function isMobileUserAgent()
{
	return (/iphone|ipod|android.*mobile|windows.*phone|blackberry.*mobile/i.test(window.navigator.userAgent.toLowerCase()));
}

//判断是否苹果移动设备访问
function isAppleMobileDevice()
{
	return (/iphone|ipod|ipad|Macintosh/i.test(navigator.userAgent.toLowerCase()));
}

//判断是否安卓移动设备访问
function isAndroidMobileDevice()
{
	return (/android/i.test(navigator.userAgent.toLowerCase()));
}

//判断是否Touch屏幕
function isTouchScreen()
{
	return (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch);
}