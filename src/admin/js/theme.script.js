//change skip color
var storage,fail,uid;try{uid=new Date;(storage=window.localStorage).setItem(uid,uid);fail=storage.getItem(uid)!=uid;storage.removeItem(uid);fail&&(storage=false);}catch(e){}
if(storage){try{var usedSkin=localStorage.getItem('config-skin');if(usedSkin!=''&&usedSkin!=null){document.body.className=usedSkin;}
else{document.body.className='theme-whbl';}}
catch(e){document.body.className='theme-whbl';}}
else{document.body.className='theme-whbl';}

//show menus
$(function($){setTimeout(function(){$('#content-wrapper > .row').css({opacity:1});},200);$('#sidebar-nav .dropdown-toggle').on('click',function(e){e.preventDefault();var $item=$(this).parent();if(!$item.hasClass('open')){$item.parent().find('.open .submenu').slideUp('fast');$item.parent().find('.open').toggleClass('open');}
$item.toggleClass('open');if($item.hasClass('open')){$item.children('.submenu').slideDown('fast');}
else{$item.children('.submenu').slideUp('fast');}});$('body').on('mouseenter','#page-wrapper.nav-small #sidebar-nav .dropdown-toggle',function(e){var $sidebar=$(this).parents('#sidebar-nav');if($(document).width()>=992){var $item=$(this).parent();$item.addClass('open');$item.children('.submenu').slideDown('fast');}});$('body').on('mouseleave','#page-wrapper.nav-small #sidebar-nav > .nav-pills > li',function(e){var $sidebar=$(this).parents('#sidebar-nav');if($(document).width()>=992){var $item=$(this);if($item.hasClass('open')){$item.find('.open .submenu').slideUp('fast');$item.find('.open').removeClass('open');$item.children('.submenu').slideUp('fast');}
$item.removeClass('open');}});$('#make-small-nav').click(function(e){$('#page-wrapper').toggleClass('nav-small');});$(window).smartresize(function(){if($(document).width()<=991){$('#page-wrapper').removeClass('nav-small');}});$('.mobile-search').click(function(e){e.preventDefault();$('.mobile-search').addClass('active');$('.mobile-search form input.form-control').focus();});$(document).mouseup(function(e){var container=$('.mobile-search');if(!container.is(e.target)&&container.has(e.target).length===0)
{container.removeClass('active');}});$('.fixed-leftmenu #col-left').nanoScroller({alwaysVisible:true,iOSNativeScrolling:false,preventPageScrolling:true,contentClass:'col-left-nano-content'});$("[data-toggle='tooltip']").each(function(index,el){$(el).tooltip({placement:$(this).data("placement")||'top'});});});$.fn.removeClassPrefix=function(prefix){this.each(function(i,el){var classes=el.className.split(" ").filter(function(c){return c.lastIndexOf(prefix,0)!==0;});el.className=classes.join(" ");});return this;};(function($,sr){var debounce=function(func,threshold,execAsap){var timeout;return function debounced(){var obj=this,args=arguments;function delayed(){if(!execAsap)
func.apply(obj,args);timeout=null;};if(timeout)
clearTimeout(timeout);else if(execAsap)
func.apply(obj,args);timeout=setTimeout(delayed,threshold||100);};}
jQuery.fn[sr]=function(fn){return fn?this.bind('resize',debounce(fn)):this.trigger(sr);};})(jQuery,'smartresize');

//change layout
$(function($){var storage,fail,uid;try{uid=new Date;(storage=window.localStorage).setItem(uid,uid);fail=storage.getItem(uid)!=uid;storage.removeItem(uid);fail&&(storage=false);}catch(e){}
if(storage){try{var usedSkin=localStorage.getItem('config-skin');if(usedSkin!=''){$('#skin-colors .skin-changer').removeClass('active');$('#skin-colors .skin-changer[data-skin="'+usedSkin+'"]').addClass('active');}
var fixedHeader=localStorage.getItem('config-fixed-header');if(fixedHeader=='fixed-header'){$('body').addClass(fixedHeader);$('#config-fixed-header').prop('checked',true);}
var fixedFooter=localStorage.getItem('config-fixed-footer');if(fixedFooter=='fixed-footer'){$('body').addClass(fixedFooter);$('#config-fixed-footer').prop('checked',true);}
var boxedLayout=localStorage.getItem('config-boxed-layout');if(boxedLayout=='boxed-layout'){$('body').addClass(boxedLayout);$('#config-boxed-layout').prop('checked',true);}
var rtlLayout=localStorage.getItem('config-rtl-layout');if(rtlLayout=='rtl'){$('body').addClass(rtlLayout);$('#config-rtl-layout').prop('checked',true);}
var fixedLeftmenu=localStorage.getItem('config-fixed-leftmenu');if(fixedLeftmenu=='fixed-leftmenu'){$('body').addClass(fixedLeftmenu);$('#config-fixed-sidebar').prop('checked',true);if($('#page-wrapper').hasClass('nav-small')){$('#page-wrapper').removeClass('nav-small');}
$('.fixed-leftmenu #col-left').nanoScroller({alwaysVisible:true,iOSNativeScrolling:false,preventPageScrolling:true,contentClass:'col-left-nano-content'});}}
catch(e){console.log(e);}}
$('#config-tool-cog').on('click',function(){$('#config-tool').toggleClass('closed');});$('#config-fixed-header').on('change',function(){var fixedHeader='';if($(this).is(':checked')){$('body').addClass('fixed-header');fixedHeader='fixed-header';}
else{$('body').removeClass('fixed-header');if($('#config-fixed-sidebar').is(':checked')){$('#config-fixed-sidebar').prop('checked',false);$('#config-fixed-sidebar').trigger('change');location.reload();}}
writeStorage(storage,'config-fixed-header',fixedHeader);});$('#config-fixed-footer').on('change',function(){var fixedFooter='';if($(this).is(':checked')){$('body').addClass('fixed-footer');fixedFooter='fixed-footer';}
else{$('body').removeClass('fixed-footer');}
writeStorage(storage,'config-fixed-footer',fixedFooter);});$('#config-boxed-layout').on('change',function(){var boxedLayout='';if($(this).is(':checked')){$('body').addClass('boxed-layout');boxedLayout='boxed-layout';}
else{$('body').removeClass('boxed-layout');}
writeStorage(storage,'config-boxed-layout',boxedLayout);});$('#config-rtl-layout').on('change',function(){var rtlLayout='';if($(this).is(':checked')){rtlLayout='rtl';}
else{}
writeStorage(storage,'config-rtl-layout',rtlLayout);location.reload();});$('#config-fixed-sidebar').on('change',function(){var fixedSidebar='';if($(this).is(':checked')){if(!$('#config-fixed-header').is(':checked')){$('#config-fixed-header').prop('checked',true);$('#config-fixed-header').trigger('change');}
if($('#page-wrapper').hasClass('nav-small')){$('#page-wrapper').removeClass('nav-small');}
$('body').addClass('fixed-leftmenu');fixedSidebar='fixed-leftmenu';$('.fixed-leftmenu #col-left').nanoScroller({alwaysVisible:true,iOSNativeScrolling:false,preventPageScrolling:true,contentClass:'col-left-nano-content'});writeStorage(storage,'config-fixed-leftmenu',fixedSidebar);}
else{$('body').removeClass('fixed-leftmenu');writeStorage(storage,'config-fixed-leftmenu',fixedSidebar);location.reload();}});if(!storage){$('#config-fixed-header').prop('checked',false);$('#config-fixed-footer').prop('checked',false);$('#config-fixed-sidebar').prop('checked',false);$('#config-boxed-layout').prop('checked',false);$('#config-rtl-layout').prop('checked',false);}
$('#skin-colors .skin-changer').on('click',function(){$('body').removeClassPrefix('theme-');$('body').addClass($(this).data('skin'));$('#skin-colors .skin-changer').removeClass('active');$(this).addClass('active');writeStorage(storage,'config-skin',$(this).data('skin'));});});function writeStorage(storage,key,value){if(storage){try{localStorage.setItem(key,value);}
catch(e){console.log(e);}}}
$.fn.removeClassPrefix=function(prefix){this.each(function(i,el){var classes=el.className.split(" ").filter(function(c){return c.lastIndexOf(prefix,0)!==0;});el.className=classes.join(" ");});return this;};