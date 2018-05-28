var $china_country_id = 44;

function loadCounty($country_id, $zone_id, $slt_country_id, $slt_zone_id, $slt_city_id, $slt_post_code)
{
	if (!$country_id) {return false;}
	$.ajax({
		url: '/common/area/country?country_id=' + $country_id,
		dataType: 'json',
		beforeSend: function()
		{
			$('select[name=\'' + $slt_country_id + '\']').after('<span class="wait">&nbsp;<img src="view/default/image/loading.gif" alt="" /></span>');
		},
		complete: function()
		{
			$('.wait').remove();
		},
		success: function(json)
		{
			var slt_city_obj = $slt_city_id.replace(/\[|\]/g, '');
			if ($country_id == $china_country_id)
			{
				$('select[name=\'' + $slt_country_id + '\']').parents().find('#' + slt_city_obj).html('<select name="' + $slt_city_id + '" class="large-field"></select>');
				$('select[name=\'' + $slt_zone_id + '\']').unbind('change');
				$('select[name=\'' + $slt_zone_id + '\']').bind('change', function(){loadCity(this.value, $slt_zone_id, $slt_city_id)});
			}
			else
			{
				$city_val = $('#' + slt_city_obj).attr('data') || '';
				$('select[name=\'' + $slt_country_id + '\']').parents().find('#' + slt_city_obj).html('<input type="text" name="' + $slt_city_id + '" value="' + $city_val + '" class="large-field" />');
			}

			if (json['postcode_required'] == '1')
			{
				$('#' + $slt_post_code).show();
			}
			else
			{
				$('#' + $slt_post_code).hide();
			}

			html = '<option value="">' + $text_select + '</option>';
			if (json['zone'] != '')
			{
				for (i = 0; i < json['zone'].length; i++)
				{
					html += '<option value="' + json['zone'][i]['zone_id'] + '"';
					if (json['zone'][i]['zone_id'] == $zone_id) {html += ' selected="selected"';}
					html += '>' + json['zone'][i]['name'] + '</option>';
				}
			}
			else
			{
				html += '<option value="0" selected="selected">' + $text_none + '</option>';
			}

			$('select[name=\'' + $slt_zone_id + '\']').html(html);
			if ($country_id == $china_country_id) {$('select[name=\'' + $slt_zone_id + '\']').trigger('change');}
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			tipBox(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function loadCity($zone_id, $slt_zone_id, $slt_city_id)
{
	if (!$zone_id) {return false;}
	$.ajax({
		url: '/common/area/zone?zone_id=' + $zone_id,
		dataType: 'json',
		beforeSend: function()
		{
			$('select[name=\'' + $slt_zone_id + '\']').after('<span class="wait">&nbsp;<img src="view/default/image/loading.gif" alt="" /></span>');
		},
		complete: function()
		{
			$('.wait').remove();
		},
		success: function(json)
		{
			html = '<option value="">' + $text_select + '</option>';
			if (json != '')
			{
				var $city_data = $('#' + $slt_city_id).attr('data');
				for (i = 0; i < json.length; i++)
				{
					html += '<option value="' + json[i]['name'] + '"';
					if (json[i]['name'] == $city_data) {html += ' selected="selected"';}
					html += '>' + json[i]['name'] + '</option>';
				}
			}
			else
			{
				html += '<option value="0" selected="selected">' + $text_none + '</option>';
			}
			$('select[name=\'' + $slt_city_id + '\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			tipBox(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

if ($('select[name=\'country_id\']').val() != 'undefined')
{
	$('select[name=\'country_id\']').on('change', function(){loadCounty(this.value, $zone_id, 'country_id', 'zone_id', 'city', 'postcode-required')});
	$('select[name=\'country_id\']').trigger('change');
}