<?php
// Heading
$_['heading_title'] = '国家设置';

// Text
$_['text_success'] = '成功提示： 您已成功更新国家设置！';

// Column
$_['column_name']       = '国家名称';
$_['column_iso_code_2'] = 'ISO 代码 (2)';
$_['column_iso_code_3'] = 'ISO 代码 (3)';
$_['column_action']     = '管理';

// Entry
$_['entry_status']            = '国家状态：';
$_['entry_name']              = '国家名称：';
$_['entry_iso_code_2']        = 'ISO 代码 (2)：';
$_['entry_iso_code_3']        = 'ISO 代码 (3)：';
$_['entry_address_format']    = '地址格式：<span class="help">
名 = {firstname}<br />
姓 = {lastname}<br />
电话 = {telephone}<br />
公司 = {company}<br />
详细地址 = {address_1}<br />
附加地址 = {address_2}<br />
城市 = {city}<br />
邮编 = {postcode}<br />
国家地区 = {zone}<br />
地区代码 = {zone_code}<br />
国家 = {country}</span>';
$_['entry_postcode_required'] = '邮编必填：';
$_['entry_status']            = '状态：';

// Error
$_['error_permission']       = '系统提示： 抱歉，您没有权限新增或修改国家设置！';
$_['error_name']             = '系统提示： 国家名称必须在2至128个字符之间！';
$_['error_default']          = '系统提示： 该国家不能删除，因为被设为系统默认国家！';
$_['error_store']            = '系统提示： 该国家不能删除，因为被绑定到 %s 系统！';
$_['error_address']          = '系统提示： 该国家不能删除，因为被绑定到 %s 地址簿记录！';
$_['error_affiliate']        = '系统提示： 该国家不能删除，因为被绑定到 %s 加盟会员！';
$_['error_zone']             = '系统提示： 该国家不能删除，因为被绑定到 %s 区域！';
$_['error_zone_to_geo_zone'] = '系统提示： 该国家不能删除，因为被绑定到 %s 区域群组！';
?>