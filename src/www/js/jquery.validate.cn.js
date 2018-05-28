///////////////////////////////////////////////////////////////////////////////
//
// 张树林 - 慧佳工作室
//
// Module Name:		jquery.validate.cn.js
// Abstract:		jquery.validate 中文包补充验证函数库
// Version:			1.0
// Date:			2011-04-20
// Author:			woods·zhang
// Email:			hoojar@163.com
// MSN:				hoojar@hotmail.com
// Website:			http://www.hoojar.com/
// Copyright 2001-2011, Hoojar studio All Rights Reserved
//
// 版权 2001-2011，慧佳工作室所有版权保护

//The software for free software, allowing use, copy,
//modify and distribute the software and files. Any
//use of this software must place a copy of all the
//above copyright notice. By the software Huijia studio
//maintenance, if you have any queries please contact us.
//Thank you.
//
//此软件为自由软件，允许使用、拷贝、修改、分发本软件及其文档。
//任何使用此软件的地方都得出现以上版权通告所有副本。此软件由
//慧佳工作室维护，如果您有什么疑问请与我们联系。谢谢使用。
//
///////////////////////////////////////////////////////////////////////////////
jQuery.extend(jQuery.validator.messages, {
	required   :"请选择或填写此项",
	remote     :"请修正该字段",
	email      :"请输入正确格式的电子邮件",
	url        :"请输入合法的网址",
	date       :"请输入合法的日期",
	dateISO    :"请输入合法的日期 (ISO).",
	number     :"请输入合法的数字",
	digits     :"只能输入整数,请重新输入",
	creditcard :"请输入合法的信用卡号",
	equalTo    :"请再次输入相同的值",
	maxlength  :jQuery.validator.format("请输入一个长度最多是 {0} 的字符串"),
	minlength  :jQuery.validator.format("请输入一个长度最少是 {0} 的字符串"),
	rangelength:jQuery.validator.format("请输入一个长度介于 {0} 和 {1} 之间的字符串"),
	range      :jQuery.validator.format("请选择或输入一个介于 {0} 和 {1} 之间的值"),
	max        :jQuery.validator.format("请选择或输入一个最大为 {0} 的值"),
	min        :jQuery.validator.format("请选择或输入一个最小为 {0} 的值")
});

//邮政编码验证
jQuery.validator.addMethod("isZipCode", function(value, element)
{
	var tel = /^[0-9]{6}$/;
	return this.optional(element) || (tel.test(value));
}, "请填写正确的邮政编码");

//校验输入内容是否全为字母(a-Z)
jQuery.validator.addMethod("isLetter", function(value, element)
{
	var ereg = /^[A-Za-z]+$/;
	return this.optional(element) || (ereg.test(value));
}, "请全部填写英文字母");

//校验输入内容是否为小写字母(a-z)
jQuery.validator.addMethod("isLowerCase", function(value, element)
{
	var ereg = /^[a-z]+$/;
	return this.optional(element) || (ereg.test(value));
}, "请全部填写英文小写字母");

//校验输入内容是否为大写字母(A-Z)
jQuery.validator.addMethod("isUpperCase", function(value, element)
{
	var ereg = /^[A-Z]+$/;
	return this.optional(element) || (ereg.test(value));
}, "请全部填写英文大写字母");

//校验输入内容是否为字符模式(数字、字母或下划线组成)
jQuery.validator.addMethod("isChar", function(value, element)
{
	var ereg = /^[A-Z]+$/;
	return this.optional(element) || (ereg.test(value));
}, "请填写的内容需由数字、字母或下划线组成");

//校验输入内容是否为至少包含一个下划线的字符模式(数字、字母和下划线组成,必须有下划线)
jQuery.validator.addMethod("isCharUnderline", function(value, element)
{
	var ereg = /^(\w*)(\_+)(\w*)$/;
	return this.optional(element) || (ereg.test(value));
}, "请填写的内容需由数字、字母和下划线组成,必须有下划线");

//固话，手机号码检查函数
jQuery.validator.addMethod("isPhone", function(value, element)
{
	var ereg = /(^([0\+]\d{2,3})\d{3,4}\-\d{3,8}$)|(^([0\+]\d{2,3})\d{3,4}\d{3,8}$)|(^([0\+]\d{2,3}){0,1}13\d{9}$)|(^\d{3,4}\d{3,8}$)|(^\d{3,4}\-\d{3,8}$)/;
	return this.optional(element) || (ereg.test(value));
}, "请填写正确的电话号码");

//校验输入内容是否为电话号码的格式(<2至5位的数字区号->5至9位的数字号码)
jQuery.validator.addMethod("isTel", function(value, element)
{
	var ereg = /^(\((\d{2,5})\)|\d{2,5})?(\s*)(-?)(\s*)(\d{5,9})$/;
	return this.optional(element) || (ereg.test(value));
}, "请填写正确的电话号码");

//校验输入内容是否为手机号码的格式(前缀可能有一个“+86”,和以13X/15X/18X为开头的11位中国手机号码)
jQuery.validator.addMethod("isMobile", function(value, element)
{
	var ereg = /^(\+86)?1[3,5,8](\d{9})$/;
	return this.optional(element) || (ereg.test(value));
}, "请填写正确的手机号码");

//判断是否为传真号码
jQuery.validator.addMethod("isFax", function(value, element)
{
	var ereg = /(^\d{3,4},\d{7,8}(,\d{1,4})?$)|(^\d{3,4}\-\d{7,8}(\-\d{1,4})?$)|(^\d{3,4}\d{7,11}$)/;
	return this.optional(element) || (ereg.test(value));
}, "请填写正确的传真号码");

//判断是否为有效IP地址
jQuery.validator.addMethod("isIp", function(value, element)
{
	var check = function(d)
	{
		try
		{
			return (d <= 255 && d >= 0);
		}
		catch (e)
		{
			return false;
		}
	}

	var re = value.split('.');
	var result = (re.length == 4) ? (check(re[0]) && check(re[1]) && check(re[2]) && check(re[3])) : false;
	return this.optional(element) || (result);
}, "请填写正确的IP地址");

//判断是否为合法的URL地址
jQuery.validator.addMethod("isUrl", function(value, element)
{
	var strRegex = "^((https|http|ftp|rtsp|mms):\/\/)" + //协议头
		"(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" + //ftp的user@
		"(([0-9]{1,3}\.){3}[0-9]{1,3}" + //IP形式的URL- 199.194.52.184
		"|" + //允许IP和DOMAIN（域名）
		"([0-9a-z_!~*'()-]+\.)*" + //域名 www.
		"([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." + //二级域名
		"[a-z]{2,6})" + //首层域名 .com or .museum
		"(:[0-9]{1,4})?" + //端口 :80
		"((\/?)|" + //a slash isn't required if there is no file name
		"(\/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+\/?)$";
	var ereg = new RegExp(strRegex, "i");
	return this.optional(element) || (ereg.test(value));
}, "请填写正确的URL地址");

//判断是否为时间类型
jQuery.validator.addMethod("isTime", function(value, element)
{
	var ereg = /^(\d{1,2})(:)?(\d{1,2})\2(\d{1,2})$/;
	var a = value.match(ereg);
	if (a == null)
	{
		return false;
	}

	var result = (a[1] > 24 || a[3] > 60 || a[4] > 60) ? false : true;
	return this.optional(element) || (result);
}, "请填写正确的时间");

//判断是否为日期类型
jQuery.validator.addMethod("isDate", function(value, element)
{
	var ereg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/;
	var r = value.match(ereg);
	if (r == null)
	{
		return false;
	}

	var d = new Date(r[1], r[3] - 1, r[4]);
	var result = (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4]);
	return this.optional(element) || (result);
}, "请填写正确的日期");

//判断是否为完整时间类型
jQuery.validator.addMethod("isDateTime", function(value, element)
{
	var ereg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/;
	var r = value.match(ereg);
	if (r == null)
	{
		return false;
	}

	var d = new Date(r[1], r[3] - 1, r[4], r[5], r[6], r[7]);
	var result = (d.getFullYear() == r[1] && (d.getMonth() + 1) == r[3] && d.getDate() == r[4] && d.getHours() == r[5] && d.getMinutes() == r[6] && d.getSeconds() == r[7]);
	return this.optional(element) || (result);
}, "请填写正确的日期与时间");

//校验输入内容是否为身份证的格式(目前只支持中国1代或2代身份证)
jQuery.validator.addMethod("isIdentityCard", function(value, element)
{
	var ereg = /^[1-9](\d{5})(([1-9]\d)|([1,2](\d{3})))(0[1-9]|1[0,2])(0[1-9]|[1,2]\d|3[0,1])(\d{3})([0-9Xx]+)$/;
	return this.optional(element) || (ereg.test(value));
}, "请填写正确身份证编号");

//检验是否由英文与中文组成
jQuery.validator.addMethod("isENCN", function(value, element)
{
	var ereg = /^[\u0391-\uFFE5\w]+$/;
	return this.optional(element) || (ereg.test(value));
}, "请输入由中文，英文字母、数字和下划线组成的数据");

// Accept a value from a file input based on a required mimetype
jQuery.validator.addMethod("accept", function(value, element, param)
{
	var typeParam = typeof param === "string" ? param.replace(/,/g, '|') : "image/*", optionalValue = this.optional(element), i, file;
	if (optionalValue)
	{
		return optionalValue;
	}

	if ($(element).attr("type") === "file")
	{
		typeParam = typeParam.replace("*", ".*");
		if (element.files && element.files.length)
		{
			for (i = 0; i < element.files.length; i++)
			{
				file = element.files[i];
				if (!file.type.match(new RegExp(".?(" + typeParam + ")$", "i")))
				{
					return false;
				}
			}
		}
	}

	return true;
}, jQuery.format("请选择正确的类型文件"));

// Older accept file extension method. Old accept
jQuery.validator.addMethod("extension", function(value, element, param)
{
	param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
}, jQuery.format("请选择正确的扩展名文件"));

//验证提示位置设置
$.validator.setDefaults({
	errorPlacement:function(error, element)
	{
		if (element.is(":radio,:checkbox"))
		{
			error.appendTo(element.parent().next());
		}
		else
		{
			error.appendTo(element.parent());
		}
	}
});