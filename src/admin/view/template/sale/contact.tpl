<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<div class="box">
		<div class="heading">
			<h1><img src="/view/image/mail.png" /> <?php echo $heading_title; ?></h1>
			<div class="buttons" id="ctrl-div">
				<?php if ($mpermission) { ?><a id="button-send" onclick="send('/sale/contact/send');" class="button"><?php echo $button_send; ?></a><?php } ?>
				<a onclick="location = '<?php echo $cancel; ?>';" class="button btn-yellow"><?php echo $button_cancel; ?></a>
			</div>
		</div>
		<div class="content">
				<table id="mail" class="form">
					<tr>
						<td><?php echo $entry_store; ?></td>
						<td><select name="store_id">
								<option value="0"><?php echo $text_default; ?></option>
								<?php foreach ($stores as $store) { ?>
								<option value="<?php echo $store['store_id']; ?>"><?php echo $store['name']; ?></option>
								<?php } ?>
							</select></td>
					</tr>
					<tr>
						<td><?php echo $entry_to; ?></td>
						<td><select name="to">
								<option value="newsletter"><?php echo $text_newsletter; ?></option>
								<option value="customer_all"><?php echo $text_customer_all; ?></option>
								<option value="customer_group"><?php echo $text_customer_group; ?></option>
								<option value="customer"><?php echo $text_customer; ?></option>
								<option value="affiliate_all"><?php echo $text_affiliate_all; ?></option>
								<option value="affiliate"><?php echo $text_affiliate; ?></option>
								<option value="product"><?php echo $text_product; ?></option>
							</select></td>
					</tr>
					<tbody id="to-customer-group" class="to">
						<tr>
							<td><?php echo $entry_customer_group; ?></td>
							<td><select name="customer_group_id">
									<?php foreach ($customer_groups as $customer_group) { ?>
									<option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
									<?php } ?>
								</select></td>
						</tr>
					</tbody>
					<tbody id="to-customer" class="to">
						<tr>
							<td><?php echo $entry_customer; ?></td>
							<td><input type="text" name="customers" value="" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><div id="customer" class="scrollbox"></div></td>
						</tr>
					</tbody>
					<tbody id="to-affiliate" class="to">
						<tr>
							<td><?php echo $entry_affiliate; ?></td>
							<td><input type="text" name="affiliates" value="" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><div id="affiliate" class="scrollbox"></div></td>
						</tr>
					</tbody>
					<tbody id="to-product" class="to">
						<tr>
							<td><?php echo $entry_product; ?></td>
							<td><input type="text" name="products" value="" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><div id="product" class="scrollbox"></div></td>
						</tr>
					</tbody>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_subject; ?></td>
						<td><input type="text" name="subject" value="" /></td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_message; ?></td>
						<td><textarea name="message"></textarea></td>
					</tr>
				</table>
		</div>
	</div>
</div>
<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript"><!--
$('textarea[name=\'message\']').ckeditor({
	filebrowserBrowseUrl: '/common/filemanager',
	filebrowserImageBrowseUrl: '/common/filemanager',
	filebrowserFlashBrowseUrl: '/common/filemanager',
	filebrowserUploadUrl: '/common/filemanager',
	filebrowserImageUploadUrl: '/common/filemanager',
	filebrowserFlashUploadUrl: '/common/filemanager'
});
//--></script>
<script type="text/javascript"><!--
$('select[name=\'to\']').bind('change', function() {
	$('#mail .to').hide();

	$('#mail #to-' + $(this).val().replace('_', '-')).show();
});

$('select[name=\'to\']').trigger('change');
//--></script>
<script type="text/javascript"><!--
$.widget("custom.catcomplete", $.ui.autocomplete, {
	_renderMenu: function (ul, items)
	{
		var that = this, currentCategory = "";
		$.each(items, function (index, item)
		{
			if (item.category != currentCategory)
			{
				ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
				currentCategory = item.category;
			}
			that._renderItemData(ul, item);
		});
	}
});

$('input[name=\'customers\']').catcomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: '/sale/customer/autocomplete?search=' +	encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						category: item.customer_group,
						label: item.name,
						value: item.customer_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('#customer' + ui.item.value).remove();
		$('#customer').append('<div id="customer' + ui.item.value + '">' + ui.item.label + '<img src="/view/image/delete.png" /><input type="hidden" name="customer[]" value="' + ui.item.value + '" /></div>');

		$('#customer div:odd').attr('class', 'odd');
		$('#customer div:even').attr('class', 'even');
		return false;
	},
	focus: function(event, ui) {
				return false;
	 	}
});

$('#customer div img').on('click', function() {
	$(this).parent().remove();

	$('#customer div:odd').attr('class', 'odd');
	$('#customer div:even').attr('class', 'even');
});
//--></script>
<script type="text/javascript"><!--
$('input[name=\'affiliates\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: '/sale/affiliate/autocomplete?search=' +	encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.affiliate_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('#affiliate' + ui.item.value).remove();
		$('#affiliate').append('<div id="affiliate' + ui.item.value + '">' + ui.item.label + '<img src="/view/image/delete.png" /><input type="hidden" name="affiliate[]" value="' + ui.item.value + '" /></div>');

		$('#affiliate div:odd').attr('class', 'odd');
		$('#affiliate div:even').attr('class', 'even');
		return false;
	},
	focus: function(event, ui) {
				return false;
	 	}
});

$('#affiliate div img').on('click', function() {
	$(this).parent().remove();

	$('#affiliate div:odd').attr('class', 'odd');
	$('#affiliate div:even').attr('class', 'even');
});

$('input[name=\'products\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: '/catalog/product/autocomplete?search=' +	encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	},
	select: function(event, ui) {
		$('#product' + ui.item.value).remove();
		$('#product').append('<div id="product' + ui.item.value + '">' + ui.item.label + '<img src="/view/image/delete.png" /><input type="hidden" name="product[]" value="' + ui.item.value + '" /></div>');

		$('#product div:odd').attr('class', 'odd');
		$('#product div:even').attr('class', 'even');
		return false;
	},
	focus: function(event, ui) {
				return false;
	 	}
});

$('#product div img').on('click', function() {
	$(this).parent().remove();

	$('#product div:odd').attr('class', 'odd');
	$('#product div:even').attr('class', 'even');
});

function send(url) {
	$('textarea[name=\'message\']').html($('textarea[name=\'message\']').val());

	$.ajax({
		url: url,
		type: 'post',
		data: $('select, input, textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-send').attr('disabled', true);
			$('#button-send').before('<span class="wait"><img src="/view/image/loading.gif" />&nbsp;</span>');
		},
		complete: function() {
			$('#button-send').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.success, .warning, .error').remove();
			if (json['error']) {
				if (json['error']['warning']) {
					$('.box').before('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
					$('.warning').fadeIn('slow');
				}
				if (json['error']['subject']) {
					$('input[name=\'subject\']').after('<span class="error">' + json['error']['subject'] + '</span>');
				}
				if (json['error']['message']) {
					$('textarea[name=\'message\']').parent().append('<span class="error">' + json['error']['message'] + '</span>');
				}
			}
			if (json['next']) {
				if (json['success']) {
					$('.box').before('<div class="success">' + json['success'] + '</div>');
					send(json['next']);
				}
			} else {
				if (json['success']) {
					$('.box').before('<div class="success" style="display: none;">' + json['success'] + '</div>');
					$('.success').fadeIn('slow');
				}
			}
		}
	});
}
//--></script>
<?php echo $page_footer; ?>