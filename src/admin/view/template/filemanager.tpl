<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link type="text/css" href="/js/css/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/ajaxupload.js"></script>
<script type="text/javascript" src="/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/jquery.tree.min.js"></script>
<script type="text/javascript" src="/js/jquery.cookie.js"></script>
<script type="text/javascript" src="/js/jquery.tree.cookie.js"></script>
<script type="text/javascript" src="/js/jquery.lazyload.min.js"></script>
<style type="text/css">
body{padding:0;margin:0;background:#F7F7F7;font-family:Verdana, Arial, Helvetica, sans-serif;font-size:11px;}
img{border:0;}
#container{padding:0px 10px 7px 10px;height:340px;}
#menu{clear:both;height:29px;margin-bottom:3px;border-bottom:1px solid #EEEEEE;}
#column-left{background:#FFF;border:1px solid #CCC;float:left;width:19%;height:440px;overflow:auto;}
#column-right{background:#FFF;border:1px solid #CCC;float:right;width:80%;height:440px;overflow:auto;text-align:center;}
#column-right div{text-align:left;padding:5px;}
#column-right a{display:inline-block;text-align:center;border:1px solid #EEEEEE;cursor:pointer;margin:5px;padding:5px;width:130px;}
#column-right a.selected{border:1px solid #7DA2CE;background:#EBF4FD;}
#column-right input{display:none;}
#dialog{display:none;padding-top:25px;height:auto !important;}
#dialog p{font-size:13px;line-height:25px;}
.watermark{line-height:25px;margin-bottom:3px;clear:both;}
.button{display:block;float:left;border:1px solid transparent;padding:6px 5px 6px 25px;margin-right:5px;background-position:5px 6px;background-repeat:no-repeat;cursor:pointer;}
.button:hover{background-color:#EEEEEE;border:1px solid #DBDEE1;border-radius:5px;}
.thumb{padding:5px;width:105px;height:105px;background:#F7F7F7;border:1px solid #CCCCCC;cursor:pointer;cursor:move;position:relative;}
@media screen and (max-width:640px){#column-left{clear:both;float:none;width:100%;height:auto;min-height:60px;}#column-right{clear:both;float:none;width:100%}#mobile-op{display:block !important;}}
</style>
</head>
<body>

<div id="container">
	<div id="menu">
		<a id="create" class="button" style="background-image: url('/view/image/filemanager/folder.png');"><?php echo $button_folder; ?></a>
		<a id="delete" class="button" style="background-image: url('/view/image/filemanager/edit-delete.png');"><?php echo $button_delete; ?></a>
		<a id="move" class="button" style="background-image: url('/view/image/filemanager/edit-cut.png');"><?php echo $button_move; ?></a>
		<a id="copy" class="button" style="background-image: url('/view/image/filemanager/edit-copy.png');"><?php echo $button_copy; ?></a>
		<a id="rename" class="button" style="background-image: url('/view/image/filemanager/edit-rename.png');"><?php echo $button_rename; ?></a>
		<a id="upload" class="button" style="background-image: url('/view/image/filemanager/upload.png');"><?php echo $button_upload; ?></a>
		<a id="refresh" class="button" style="background-image: url('/view/image/filemanager/refresh.png');"><?php echo $button_refresh; ?></a>
	</div>

	<div class="watermark">
		<b><?php echo $wmark_title; ?></b>
		<label for="wtm0"><input type="radio" value="" id="wtm0" name="watermark" checked/><?php echo $wmark_none; ?></label>
		<label for="wtm1"><input type="radio" value="center" id="wtm1" name="watermark" /><?php echo $wmark_center; ?></label>
		<label for="wtm2"><input type="radio" value="topleft" id="wtm2" name="watermark" /><?php echo $wmark_topleft; ?></label>
		<label for="wtm3"><input type="radio" value="topright" id="wtm3" name="watermark" /><?php echo $wmark_topright; ?></label>
		<label for="wtm4"><input type="radio" value="bottomleft" id="wtm4" name="watermark" /><?php echo $wmark_bottomleft; ?></label>
		<label for="wtm5"><input type="radio" value="bottomright" id="wtm5" name="watermark" /><?php echo $wmark_bottomright; ?></label>
	</div>

	<div id="column-left"></div>
	<div id="column-right"></div>

	<div id="mobile-op" style="display:none;text-align:center;margin-top:6px;">
		<input type="button" value="OK" style="font-size:15px;background:#003a88;border-radius:10px;color:#fff;width:95%;padding:8px;" onclick="$('#column-right a.selected').dblclick()" />
	</div>
</div>

<script type="text/javascript"><!--
$(document).ready(function()
{
	$('#column-left').tree({
		plugins : {cookie: {}},
		data    : {
			type : 'json',
			async: true,
			opts : {method: 'post',	url: '/common/filemanager/directory'}
		},
		selected: 'top',
		ui      : {theme_name: 'classic', animation : 400},
		types   : {
			'default': {
				clickable     : true,
				creatable     : false,
				renameable    : false,
				deletable     : false,
				draggable     : false,
				max_children  : -1,
				max_depth     : -1,
				valid_children: 'all'
			}
		},
		callback: {
			beforedata: function (NODE, TREE_OBJ)
			{
				if (NODE == false)
				{
					TREE_OBJ.settings.data.opts.static = [
						{
							data      : 'image',
							attributes: {'id': 'top', 'directory': ''},
							state     : 'closed'
						}
					];
					return {'directory': ''}
				}
				else
				{
					TREE_OBJ.settings.data.opts.static = false;
					return {'directory': $(NODE).attr('directory')}
				}
			},
			onselect: function (NODE, TREE_OBJ)
			{
				$.ajax({
					url     : '/common/filemanager/files',
					type    : 'post',
					data    : 'directory=' + encodeURIComponent($(NODE).attr('directory')),
					dataType: 'json',
					success : function (json)
					{
						var html = '<div>';
						if (json)
						{
							for (i = 0; i < json.length; i++)
							{
								html += '<a file="' + json[i]['file'] + '"><img src="<?php echo $no_image; ?>" class="lazy" data-original="' + json[i]['url'] + '" /><br />';
								html += ((json[i]['filename'].length > 15) ? (json[i]['filename'].substr(0, 15) + '..') : json[i]['filename']) + '<br />' + json[i]['size'] + '</a>';
							}
						}
						html += '</div>';
						$('#column-right').html(html);
						$("#column-right .lazy").lazyload({container: "#column-right"});

						$('#column-right a').on('click', function ()
						{
							if ($(this).attr('class') == 'selected')
							{
								$(this).removeAttr('class');
							}
							else
							{
								$('#column-right a').removeAttr('class');
								$(this).attr('class', 'selected');
							}
						});

						$('#column-right a').on('dblclick', function()
						{
							<?php if ($fckeditor) { ?>
							window.opener.CKEDITOR.tools.callFunction(<?php echo $fckeditor; ?>, '<?php echo $directory; ?>data/' + $(this).attr('file'));
							self.close();
							<?php } else { ?>
							parent.$('#<?php echo $field; ?>').val('data/' + $(this).attr('file'));
							parent.$('#<?php echo $field; ?>').attr('src', $(this).find('img').attr('src'));
							parent.$('.touch-boxy-overlay').click();
							<?php } ?>
						});
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			},
			onopen: function (TREE_OBJ)
			{
				var tr = $('#column-left li#top li[directory]');
				tr.each(function (index, domEle)
				{
					var dd = $(domEle).attr('directory');
					dd = dd.replace(/\//g, '').replace(/\s/g, '');
					$(domEle).attr('id', dd);
				});

				var myTree = $.tree.reference('#column-left');
				myTree.select_branch('#' + $.cookie('selected'));
			}
		}
	});

	$('#create').on('click', function ()
	{
		var tree = $.tree.focused();
		if (tree.selected)
		{
			$('#dialog').remove();
			var html = '<div id="dialog">';
			html += '<?php echo $entry_folder; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
			html += '</div>';
			$('#column-right').prepend(html);

			$('#dialog').dialog({title: '<?php echo $button_folder; ?>', resizable: false});
			$('#dialog input[type=\'button\']').on('click', function ()
			{
				$.ajax({
					url     : '/common/filemanager/create',
					type    : 'post',
					data    : 'directory=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							tree.refresh(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		}
		else
		{
			alert('<?php echo $error_directory; ?>');
		}
	});

	$('#delete').on('click', function ()
	{
		var path = $('#column-right a.selected').attr('file');
		if (path == undefined)
		{
			$('#dialog').remove();
			var html = '<div id="dialog">';
			html += '<p><strong  style="color: red;">WARNING:</strong> You are trying to delete a folder.<br />';
			html += 'All files and folder under it will be deleted. <strong  style="color: red;">Confirm?</strong></p>';
			html += '</div>';
			$('#column-right').prepend(html);

			$("#dialog").dialog({
				resizable: false,
				height   : 165,
				width    : 380,
				modal    : true,
				title    : 'Folder deletion',
				buttons  : {
					"Delete folder": function ()
					{
						var tree = $.tree.focused();
						if (tree.selected)
						{
							$.ajax({
								url     : '/common/filemanager/delete',
								type    : 'post',
								data    : 'path=' + encodeURIComponent($(tree.selected).attr('directory')),
								dataType: 'json',
								success : function (json)
								{
									if (json.success)
									{
										tree.select_branch(tree.parent(tree.selected));
										tree.refresh(tree.selected);
									}
									else
									{
										alert(json.error);
									}
								},
								error   : function (xhr, ajaxOptions, thrownError)
								{
									alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
						}
						else
						{
							alert('<?php echo $error_select; ?>');
						}
						$(this).dialog("close");
					},
					Cancel         : function ()
					{
						$(this).dialog("close");
					}
				}
			});
		}
		else if (path)
		{
			$.ajax({
				url     : '/common/filemanager/delete',
				type    : 'post',
				data    : 'path=' + encodeURIComponent(path),
				dataType: 'json',
				success : function (json)
				{
					if (json.success)
					{
						var tree = $.tree.focused();
						tree.select_branch(tree.selected);
					}
					else
					{
						alert(json.error);
					}
				},
				error   : function (xhr, ajaxOptions, thrownError)
				{
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
		else
		{
			var tree = $.tree.focused();
		}
	});

	$('#move').on('click', function ()
	{
		$('#dialog').remove();
		var html = '<div id="dialog">';
		html += '<?php echo $entry_move; ?> <select name="to"></select> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';
		$('#column-right').prepend(html);

		$('#dialog').dialog({title: '<?php echo $button_move; ?>', resizable: false});
		$('#dialog select[name=\'to\']').load('/common/filemanager/folders');
		$('#dialog input[type=\'button\']').on('click', function ()
		{
			var path = $('#column-right a.selected').attr('file');
			if (path)
			{
				$.ajax({
					url     : '/common/filemanager/move',
					type    : 'post',
					data    : 'from=' + encodeURIComponent(path) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							var tree = $.tree.focused();
							tree.select_branch(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
			else
			{
				var tree = $.tree.focused();
				$.ajax({
					url     : '/common/filemanager/move',
					type    : 'post',
					data    : 'from=' + encodeURIComponent($(tree.selected).attr('directory')) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							tree.select_branch('#top');
							tree.refresh(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

	$('#copy').on('click', function ()
	{
		$('#dialog').remove();
		var html = '<div id="dialog">';
		html += '<?php echo $entry_copy; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';
		$('#column-right').prepend(html);

		$('#dialog').dialog({title: '<?php echo $button_copy; ?>', resizable: false});
		$('#dialog select[name=\'to\']').load('/common/filemanager/folders');
		$('#dialog input[type=\'button\']').on('click', function ()
		{
			var path = $('#column-right a.selected').attr('file');
			if (path)
			{
				$.ajax({
					url     : '/common/filemanager/copy',
					type    : 'post',
					data    : 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							var tree = $.tree.focused();
							tree.select_branch(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
			else
			{
				var tree = $.tree.focused();
				$.ajax({
					url     : '/common/filemanager/copy',
					type    : 'post',
					data    : 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							tree.select_branch(tree.parent(tree.selected));
							tree.refresh(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

	$('#rename').on('click', function ()
	{
		$('#dialog').remove();
		var html = '<div id="dialog">';
		html += '<?php echo $entry_rename; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';
		$('#column-right').prepend(html);

		$('#dialog').dialog({title: '<?php echo $button_rename; ?>', resizable: false});
		$('#dialog input[type=\'button\']').on('click', function ()
		{
			var path = $('#column-right a.selected').attr('file');
			if (path)
			{
				$.ajax({
					url     : '/common/filemanager/rename',
					type    : 'post',
					data    : 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							var tree = $.tree.focused();
							tree.select_branch(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
			else
			{
				var tree = $.tree.focused();
				$.ajax({
					url     : '/common/filemanager/rename',
					type    : 'post',
					data    : 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success : function (json)
					{
						if (json.success)
						{
							$('#dialog').remove();
							tree.select_branch(tree.parent(tree.selected));
							tree.refresh(tree.selected);
						}
						else
						{
							alert(json.error);
						}
					},
					error   : function (xhr, ajaxOptions, thrownError)
					{
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

	new AjaxUpload('#upload', {
		action      : '/common/filemanager/upload',
		name        : 'image[]',
		autoSubmit  : false,
		responseType: 'json',
		onChange    : function (file, extension)
		{
			var tree = $.tree.focused();
			if (tree.selected)
			{
				this.setData({'directory': $(tree.selected).attr('directory'), 'watermark': $("input:radio[name='watermark']:checked").val()});
			}
			else
			{
				this.setData({'directory': '', 'watermark': $("input:radio[name='watermark']:checked").val()});
			}
			this.submit();
		},
		onSubmit    : function (file, extension)
		{
			$('#upload').append('<img src="/view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
		},
		onComplete  : function (file, json)
		{
			if (json.success)
			{
				var tree = $.tree.focused();
				tree.select_branch(tree.selected);
			}
			else
			{
				alert(json.error);
			}
			$('.loading').remove();
		}
	});

	$('#refresh').on('click', function ()
	{
		var tree = $.tree.focused();
		tree.refresh(tree.selected);
	});
});
//-->
</script>
</body>
</html>