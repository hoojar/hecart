<?php echo $page_header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
		<div class="content">
			<table class="form">
				<tr>
				  <td><span class="required">*</span> <?php echo $entry_firstname; ?></td>
				  <td><input type="text" name="firstname" value="<?php echo $firstname; ?>" />
					<?php if ($error_firstname) { ?>
					<span class="error"><?php echo $error_firstname; ?></span>
					<?php } ?></td>
				</tr>
				<tr>
				  <td><span class="required">*</span> <?php echo $entry_lastname; ?></td>
				  <td><input type="text" name="lastname" value="<?php echo $lastname; ?>" />
					<?php if ($error_lastname) { ?>
					<span class="error"><?php echo $error_lastname; ?></span>
					<?php } ?></td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_telephone; ?></td>
					<td><input type="text" name="telephone" value="<?php echo $telephone; ?>" />
						<?php if ($error_telephone) { ?>
						<span class="error"><?php echo $error_telephone; ?></span>
						<?php } ?></td>
				</tr>
				<?php if ($tax_id_display) { ?>
				<tr>
					<td><?php echo $entry_tax_id; ?></td>
					<td><input type="text" name="tax_id" value="<?php echo $tax_id; ?>" />
						<?php if ($error_tax_id) { ?>
						<span class="error"><?php echo $error_tax_id; ?></span>
						<?php } ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_country; ?></td>
					<td><select name="country_id">
							<option value=""><?php echo $text_select; ?></option>
							<?php foreach ($countries as $country) { ?>
							<?php if ($country['country_id'] == $country_id) { ?>
							<option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
						<?php if ($error_country) { ?>
						<span class="error"><?php echo $error_country; ?></span>
						<?php } ?></td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_zone; ?></td>
					<td><select name="zone_id"></select>
						<?php if ($error_zone) { ?>
						<span class="error"><?php echo $error_zone; ?></span>
						<?php } ?></td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_city; ?></td>
					<td><span id="city" data="<?php echo $city; ?>"><input type="text" name="city" value="<?php echo $city; ?>" /></span>
						<?php if ($error_city) { ?>
						<span class="error"><?php echo $error_city; ?></span>
						<?php } ?></td>
				</tr>
				<tr>
					<td><span class="required">*</span> <?php echo $entry_address_1; ?></td>
					<td><input type="text" name="address_1" value="<?php echo $address_1; ?>" />
						<?php if ($error_address_1) { ?>
						<span class="error"><?php echo $error_address_1; ?></span>
						<?php } ?></td>
				</tr>
				<tr>
					<td><?php echo $entry_address_2; ?></td>
					<td><input type="text" name="address_2" value="<?php echo $address_2; ?>" /></td>
				</tr>
				<tr>
					<td><?php echo $entry_company; ?></td>
					<td><input type="text" name="company" value="<?php echo $company; ?>" /></td>
				</tr>
				<?php if ($company_id_display) { ?>
				<tr>
					<td><?php echo $entry_company_id; ?></td>
					<td><input type="text" name="company_id" value="<?php echo $company_id; ?>" />
						<?php if ($error_company_id) { ?>
						<span class="error"><?php echo $error_company_id; ?></span>
						<?php } ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td><span id="postcode-required" class="required">*</span> <?php echo $entry_postcode; ?></td>
					<td><input type="text" name="postcode" value="<?php echo $postcode; ?>" />
						<?php if ($error_postcode) { ?>
						<span class="error"><?php echo $error_postcode; ?></span>
						<?php } ?></td>
				</tr>
				<tr>
					<td><?php echo $entry_default; ?></td>
					<td><?php if ($default) { ?>
						<input type="radio" name="default" value="1" checked="checked" />
						<?php echo $text_yes; ?>
						<input type="radio" name="default" value="0" />
						<?php echo $text_no; ?>
						<?php } else { ?>
						<input type="radio" name="default" value="1" />
						<?php echo $text_yes; ?>
						<input type="radio" name="default" value="0" checked="checked" />
						<?php echo $text_no; ?>
						<?php } ?></td>
				</tr>
			</table>
		</div>
		<div class="buttons">
			<div class="left"><a href="<?php echo $back; ?>" class="button"><?php echo $button_back; ?></a></div>
			<div class="right"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></div>
		</div>
	</form>
	</div>
<script type="text/javascript"><!--
var $zone_id = '<?php echo $zone_id; ?>';
var $text_none = '<?php echo $text_none; ?>';
var $text_select = '<?php echo $text_select; ?>';
//--></script>
<script type="text/javascript" src="/js/address.js"></script>
<?php echo $page_footer; ?>