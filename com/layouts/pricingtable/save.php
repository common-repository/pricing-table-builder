<?php
	global $sgpt;
	$sgpt->includeStyle('page/styles/jquery-ui-dialog');
	$sgpt->includeScript('core/scripts/jquery-ui-dialog');
	$featuresTextareaPlaceholder = __('Each feature in a separate line.', 'sgpt');
	$sgptCount = PRICING_TABLES_TO_SHOW;
if (isset($sgptDataArray) && !empty($sgptDataArray)) {
	$sgptCount = count($sgptDataArray);}
?>
<div class="wrap sgpt-wrapper">
	<?php require_once plugin_dir_path(__FILE__) . '../free-banner.php'; ?>
	<form class="sgpt-js-form">
		<div class="sgpt-top-bar">
			<h1 class="add-edit-title">
			<?php echo ($sgptTableId != 0) ? _e('Edit Pricing Table', 'sgpt') : _e('Add New Pricing Table', 'sgpt');?>
				<a href="javascript:void(0)"
				   class="sgpt-js-update button-primary sgpt-pull-right"><?php _e('Save changes', 'sgpt'); ?></a>
				<a href="javascript:void(0)"
				   class="loading sgpt-js-preview button-primary sgpt-pull-right"><i style='display:none'><img style='vertical-align:sub;' src='<?php echo $sgpt->app_url.'/assets/page/img/ajax-loader.gif';?>'></i> <?php _e('Preview', 'sgpt'); ?></a>
			</h1>
			<input class="sgpt-text-input sgpt-title-input" value="<?php echo @$sgptDataArray[0]['title']; ?>"
				   type="text" autofocus name="sgpt-title" placeholder="<?php _e('Enter title here', 'sgpt'); ?>">
			<div class="sgpt-theme-box">
				<strong><?php _e('Theme: ', 'sgpt'); ?></strong><span id="sgpt-theme-name"><?php echo isset($sgptDataArray[0]['theme'])?$sgptDataArray[0]['theme']:'bej_blue'; ?></span>
				<input  class="sgpt-theme-selector button-small button" type="button" value="<?php _e('Select theme')?>"/>
			</div>
		</div>
		<input type="hidden" name="sgpt-id" value="<?php echo @$_GET['id']; ?>">
		<input type="hidden" name="sgpt-theme" value="<?php echo isset($sgptDataArray[0]['theme'])?$sgptDataArray[0]['theme']:'bej_blue'; ?>">
		<div class="sgpt-main-container">
		<?php if (SGPT_PRO_VERSION == 1) :?>
			<?php require_once('SgptProOptions.php');?>

			<?php else :?>

				<div style='position: relative;'>
					<div class="version"><input type="button" class="upgrade-button" value="Upgrade to PRO version" onclick="window.open('<?php echo SGPT_PRO_URL;?>')"></div>
					<h3 id="options-toggle-button">Options <a href="javascript:void(0)" class="reset-options button-small button"> Reset to default</a></h3>
				<div class="options-wrapper">
					<div id='current-text-options-wrapper'>
						<div id="options">
							<div class="options-title"><b>Background color</b></div>
							<div class="single-option"><span class='single-option-title'>Plan name : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="single-option"><span class='single-option-title'>Content : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="single-option"><span class='single-option-title'>Features : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="single-option"><span class='single-option-title'>Button : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
						</div>
					</div>
					<div id='current-options-wrapper'>

						<div id="options-text">
							<div class="text-options-title"><b>Text color</b></div>
							<div class="text-single-option"><span class="text-single-option-title">Font : </span>
								<span class="span-input">
									<div id="selectFonts" class="bfh-selectbox bfh-googlefonts">
										<span class="selectbox-caret">
											<select class="fontSelectbox" name="fontSelectbox">
												<option value="">Select Your custom font</option>
											</select>
										</span>
										
									</div>
									<span id="drop"></span>
									
									<input class="fontSelectbox" type="hidden">
								</span>
								<div class="clear"></div>
							</div>
							<div class="text-single-option"><span class="text-single-option-title">Name : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title">Features : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title">Button : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
						</div>
					</div>

					<div id="current-plan-shadow-options-wrapper">
						<div id="options-plan-shadow">
							<label for="plan-shadow-checkbox">
								<div class="text-options-title"><b>Plan shadow effect </b> <input id="plan-shadow-checkbox" type="checkbox"></div>
							</label>
							<div class="text-single-option"><span class="text-single-option-title">Color : </span>
								<span class="span-input"><input class="colorPicker" /></span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title"><i class="required-asterisk"> * </i>To Left / Right (- / +) : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text" /> - px</span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title"><i class="required-asterisk"> * </i>To Top / Bottom (- / +) : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text" /> - px</span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title">Blur effect : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text"/> - px</span>
							</div>
						</div>
					</div>

					<div id="current-text-shadow-options-wrapper">
						<div id="options-text-shadow">
							<label for="text-shadow-checkbox">
								<div class="text-options-title"><b>Text shadow effect </b> <input id="text-shadow-checkbox" value="true" type="checkbox"></div>
							</label>
							<div class="text-single-option"><span class="text-single-option-title">Color : </span>
								<span class="span-input"><input type="text" class="colorPicker" /></span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title"><i class="required-asterisk"> * </i>To Left / Right (- / +) : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text"/> - px</span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title"><i class="required-asterisk"> * </i>To Top / Bottom (- / +) : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text"/> - px</span>
							</div>
							<div class="text-single-option"><span class="text-single-option-title">Blur effect : </span>
								<span class="span-input" style="padding-right: 6px"><input class="shadow-directions" type="text"/> - px</span>
							</div>
						</div>
					</div>
				</div>

				</div>

		<?php endif;?>


			<div class="sgpt-container-top-bar">
				<a class="button-primary js-sgpt-clone-column sgpt-pull-right">
					<span class="sgpt-dashicon dashicons dashicons-plus-alt"></span>
					<?php _e('New Column', 'sgpt'); ?>
				</a>
				<div class="clear"></div>
			</div>
			<div class="sgpt-sub-container">
			<div class="sgpt-container">
				<div class="sgpt-tables-container">
					<?php for ($i = 0; $i < $sgptCount; $i++): ?>
						<div id='sgpt-<?php echo($i + 1); ?>' class="one-column ui-state-highlighted col-<?=$i+1?>">
						<!-- Input descriptions -->
						<ul class="sgpt-description">
							<li><strong><?php _e('Plan name', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Pricing', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Pricing plan', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Plan features', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Button text', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Button URL', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Shortcode', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Ribbon', 'sgpt'); ?></strong></li>
							<li><strong><?php _e('Highlighted', 'sgpt'); ?></strong></li>
						</ul>
						<!-- End of Input descriptions -->

						<!-- Pricing Table Inputs -->
						<ul class="sgpt-column">
							<li class="sortable-mouse">
								<input id="plan-name" type="text" name="plan-name[]"
									   value="<?php echo @$sgptDataArray[$i]['plan-name']; ?>"
									   placeholder="<?php _e('Pricing plan name'); ?>" class="sgpt-text-input">
								<a class="button-primary sgpt-remove-icon-button"
								   onclick="SGPT.remove('sgpt-<?php echo($i + 1); ?>')"><span
										class="dashicons dashicons-no"></span></a>
							</li>
							<li>
								<input class="sgpt-text-input" type="text" name="plan-price[]"
									   placeholder="<?php _e('Price of current plan'); ?>"
									   value="<?php echo @$sgptDataArray[$i]['plan-price']; ?>">
							</li>
							<li>
								<input class="sgpt-text-input" type="text" name="pricing-plan[]"
									   value="<?php echo @$sgptDataArray[$i]['pricing-plan']; ?>"
									   placeholder="<?php _e('Per month'); ?>">
							</li>
							<li>
								<textarea class="sgpt-textarea-input" name="pricing-features[]"
										  placeholder="<?php echo $featuresTextareaPlaceholder; ?>"><?php echo @$sgptDataArray[$i]['features']; ?></textarea>
							</li>
							<li>
								<textarea class="sgpt-text-input sgpt-button-text-input" name="button-text[]"
									   placeholder="<?php _e('Buy/Login'); ?>"><?php echo strval(@$sgptDataArray[$i]['button-text'])?></textarea>
							</li>
							<li>
								<input class="sgpt-text-input" type="text" name="button-url[]"
									   value="<?php echo @$sgptDataArray[$i]['button-url']; ?>"
									   placeholder="<?php _e('http://example.com'); ?>">
							</li>
							<li>
								<textarea class="sgpt-text-input sgpt-shortcode-text-input" type="text" name="button-shortcode[]"
									   placeholder="<?php _e('[your_shortcode]'); ?>"><?php echo strval(@$sgptDataArray[$i]['button-shortcode'])?></textarea>
							</li>
							<li>
								<input class="sgpt-text-input" type="text" name="badge-text[]"
									   value="<?php echo @$sgptDataArray[$i]['badge-text']; ?>"
									   placeholder="<?php _e('New'); ?>">
							</li>
							<li>
								<input class="sgpt-radio-input" type="radio"
									   name="highlight-plan[]" <?php echo !empty($sgptDataArray[$i]['highlight-plan']) ? 'checked' : '' ?> value="<?php echo $i;?>">
							</li>
							<input class="sgpt-fake-id" type="hidden" name="fake-id[]" value="<?php echo $i; ?>">
						</ul>
						</div>
					<?php endfor; ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
	</div>
</div>

<div id="sgpt-themes" title="Select theme" style="display:none">
	<?php
	global $SGPT_AVAILABLE_THEMES;
	arsort($SGPT_AVAILABLE_THEMES);
	foreach($SGPT_AVAILABLE_THEMES as $key=>$val):
		$isChecked = ($key == @$sgptDataArray[0]['theme'])?'checked':'';
		$proHtml = '<div class="sgpt-ribbon-wrapper" style="position:relative;display:block;"><div class="sgpt-ribbon"><div><a target="_blank" href="'.SGPT_PRO_URL.'">PRO</a></div></div></div>';
		if($val==1) $proHtml='';
	?>
	<label class="sgpt-themes-label">
		<?php if($val):?>
		<input type="radio" class="sgpt-radio" name="sgpt-theme-radio" value="<?php echo $key?>" <?php echo $isChecked;?>>
		<?php endif?>
		<?php echo $proHtml; ?>
		<img  width="200px" src="<?php echo $sgpt->app_url.'assets/page/img/'.$key.'.jpg'; ?>">
	</label>
	<?php endforeach; ?>
</div>
<input type="hidden" id="sgpt-plugin-url" value="<?php echo $sgpt->app_url; ?>">