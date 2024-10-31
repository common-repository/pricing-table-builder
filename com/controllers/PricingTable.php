<?php

global $sgpt;
$sgpt->includeController('Controller');
$sgpt->includeView('Admin');
$sgpt->includeView('PricingTable');
$sgpt->includeModel('PricingTable');
$sgpt->includeModel('PtFeature');
$sgpt->includeModel('PtPlan');

class SGPT_PricingTableController extends SGPT_Controller
{
	public function index()
	{
		global $sgpt;
		$pricingTables = array();
		$sgpt->includeStyle('page/styles/save');
		$sgpt->includeScript('page/scripts/save');
		$sgpt->includeScript('core/scripts/main');
		$sgpt->includeScript('core/scripts/sgptRequestHandler');
		$table = new SGPT_PricingTableTableView();
		$pricingTables = SGPT_PricingTableModel::finder()->findAll();
		$createNewUrl = $sgpt->adminUrl('PricingTable/save');
		$export = 'admin-post.php?action=sgptExport';

		SGPT_AdminView::render('pricingtable/index', array(
			'createNewUrl' => $createNewUrl,
			'table' => $table,
			'export'=>$export,
			'pricingTables'=>$pricingTables
		));
	}

	public function sgptShortcode($atts, $content)
	{
		global $sgpt;

		$attributes = shortcode_atts(array(
			'id' => '1',
		), $atts);
		$sgptId = (int)$attributes['id'];
		$sgptTbl = SGPT_PricingTableModel::finder()->findByPk($sgptId);
		if(!$sgptTbl){
			return;
		}
		$options = $sgptTbl->getOptions();
		$options = json_decode($options,true);
		$theme = $sgptTbl->getTheme();
		$sgptPlans = SGPT_PtPlanModel::finder()->findAll('pt_id = %d', $sgptTbl->getId());
		foreach ($sgptPlans as $sgptPlan) {
			$tmpArray = SGPT_PtPlanModel::toArray($sgptPlan);
			if ($options) {
				$tmpArray['options'] = $options;
			}
			$sgptPlanId = $sgptPlan->getId();
			$features = SGPT_PtFeatureModel::finder()->findAll('plan_id = %d', $sgptPlanId);
			$featuresArray = SGPT_PtFeatureModel::featuresToArray($features);
			$tmpArray['pricing-feature'] = $featuresArray;
			$sgptDataArray[] = $tmpArray;
			
		}

		$sgpt->includeStyle('core/styles/css/'.$theme);
		$html = $this->createPricingTableHtml($sgptDataArray, $theme);
		return $html;
	}

	public function morePlugins()
	{
		global $sgpt;
		$sgpt->includeStyle('page/styles/save');
		$sgpt->includeStyle('page/styles/sg-box-cols');
		$sgpt->includeScript('page/scripts/save');
		$sgpt->includeScript('core/scripts/main');
		$sgpt->includeScript('core/scripts/sgptRequestHandler');
		SGPT_AdminView::render('pricingtable/morePlugins');
	}

	public function ajaxSgptImport()
	{
		global $sgpt;
		global $wpdb;
		$sgpt->includeScript('page/scripts/save');
		$mainSgpt = new SGPT_PricingTableModel();
		$sgptFeature = new SGPT_PtFeatureModel();
		$sgptPlan = new SGPT_PtPlanModel();
		$mainTableName = $mainSgpt::TABLE;
		$mainFeature = $sgptFeature::TABLE;
		$mainPlan = $sgptPlan::TABLE;

		$url = $_POST['attachmentUrl'];
		$contents = unserialize(base64_decode(file_get_contents($url)));
		foreach ($contents as $singlePT) {
			$sqlPt = $wpdb->prepare("INSERT INTO ".$wpdb->prefix.'sgpt_'.$mainTableName." (name, type, theme, options) VALUES (%s, %s, %s, %s)", $singlePT['name'], $singlePT['type'], $singlePT['theme'], $singlePT['options']);
			$resPt = $wpdb->query($sqlPt);
			$id = $wpdb->insert_id;
			foreach ($singlePT['plans'] as $singlePlan) {
				$sqlPlan = $wpdb->prepare("INSERT INTO ".$wpdb->prefix.'sgpt_'.$mainPlan." (pt_id, name, price, price_timeframe, button_url, button_text, button_shortcode, highlighted_plan, badge_text) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", $id, $singlePlan['name'], $singlePlan['price'], $singlePlan['price_timeframe'], $singlePlan['button_url'], $singlePlan['button_text'], $singlePlan['button_shortcode'], $singlePlan['highlighted_plan'], $singlePlan['badge_text']);
				$resPlan = $wpdb->query($sqlPlan);
				$planId = $wpdb->insert_id;

				foreach ($singlePlan['features'] as $singleFeature) {
					$sqlFeature = $wpdb->prepare("INSERT INTO ".$wpdb->prefix.'sgpt_'.$mainFeature." (plan_id, name) VALUES (%s, %s)", $planId, $singleFeature['name']);
					$resFeature = $wpdb->query($sqlFeature);
				}
			}
		}
		exit();
	}

	public function ajaxPreview()
	{
		global $sgpt;
		$dataArray = array();
		$theme = @sanitize_text_field($_POST['sgpt-theme']);
		$colorOprions = array();
		foreach ($_POST['plan-name'] as $key => $val) {
			$dataArray[$key]['plan-name'] = $val;
			$dataArray[$key]['badge-text'] = $_POST['badge-text'][$key];
			$dataArray[$key]['plan-price'] = $_POST['plan-price'][$key];
			$dataArray[$key]['pricing-plan'] = $_POST['pricing-plan'][$key];
			$dataArray[$key]['button-url'] = $_POST['button-url'][$key];
			$dataArray[$key]['button-text'] = $_POST['button-text'][$key];
			$dataArray[$key]['button-shortcode'] = stripslashes($_POST['button-shortcode'][$key]);
			$dataArray[$key]['pricing-feature'] = explode(PHP_EOL, $_POST['pricing-features'][$key]);
			if (isset($_POST['highlight-plan'][0])) {
				$dataArray[$key]['highlight-plan'] = $_POST['fake-id'][$key] == $_POST['highlight-plan'][0]; //Boolean value true if highlighted
			}
		}
		$html = $this->createPricingTableHtml($dataArray, $theme);
		echo $html;
		exit();
	}

	public function ajaxSave()
	{
		global $wpdb;
		if (count($_POST)) {
			//Flag for insert or update
			$isUpdate = false;
			$extendedOptions = false;
			$optionsArr = array();

			$title = @sanitize_text_field($_POST['sgpt-title']);
			$theme = @sanitize_text_field($_POST['sgpt-theme']);
			$planNameColor = $_POST['planNameColor'];
			$bgColor = $_POST['bgColor'];
			$featureColor = $_POST['featureColor'];
			$buttonColor = $_POST['buttonColor'];
			$textNameColor = $_POST['textNameColor'];
			$textFeatureColor = $_POST['textFeatureColor'];
			$textButtonColor = $_POST['textButtonColor'];
			$font = $_POST['fontSelectbox'];

			$planShadowOnCheckbox = isset($_POST['planShadowOn']);
			$textShadowOnCheckbox = isset($_POST['textShadowOn']);

			$planShadowOn = '';
			$textShadowOn = '';

			$planShadowColor = '';
			$planLeftRight = '';
			$planTopBottom = '';
			$planBlur = '';

			$textShadowColor = '';
			$textLeftRight = '';
			$textTopBottom = '';
			$textBlur = '';

			if ($planShadowOnCheckbox) {
				$planShadowOn = 1;
				$planShadowColor = $_POST['planShadowColor'];
				$planLeftRight = $_POST['planLeftRight'];
				$planTopBottom = $_POST['planTopBottom'];
				$planBlur = $_POST['planBlur'];				
			}

			if ($textShadowOnCheckbox) {
				$textShadowOn = 1;
				$textShadowColor = $_POST['textShadowColor'];
				$textLeftRight = $_POST['textLeftRight'];
				$textTopBottom = $_POST['textTopBottom'];
				$textBlur = $_POST['textBlur'];
			}

			$optionsArr = array(
				'planNameColor' => $planNameColor,
				'bgColor' => $bgColor,
				'featureColor' => $featureColor,
				'buttonColor' => $buttonColor,
				'textNameColor' => $textNameColor,
				'textFeatureColor' => $textFeatureColor,
				'textButtonColor' => $textButtonColor,
				'font' => $font,
				'planShadowOn' => $planShadowOn,
				'textShadowOn' => $textShadowOn,
				'textShadowColor' => $textShadowColor,
				'planShadowColor' => $planShadowColor,
				'textLeftRight' => $textLeftRight,
				'planLeftRight' => $planLeftRight,
				'textTopBottom' => $textTopBottom,
				'planTopBottom' => $planTopBottom,
				'textBlur' => $textBlur,
				'planBlur' => $planBlur
			);
			$extendedOptions = json_decode($extendedOptions,true);
			$type = 0;
			//Save pricing table
			$pricingTable = new SGPT_PricingTableModel();
			if (isset($_POST['sgpt-id'])) {
				$isUpdate = true;
				$sgptId = (int)$_POST['sgpt-id'];
				$pricingTable = SGPT_PricingTableModel::finder()->findByPk($sgptId);
				if (!$pricingTable) {
					$pricingTable = new SGPT_PricingTableModel();
					$isUpdate = false;
				}
			}
			$extendedOptions = $optionsArr;

			$pricingTable->setName($title);
			$pricingTable->setTheme($theme);
			$pricingTable->setType($type);
			$pricingTable->setOptions(json_encode($optionsArr));
			$res = $pricingTable->save();
			$lastPtId = $wpdb->insert_id;

			if ($isUpdate) {
				$lastPtId = $sgptId;
				SGPT_PtPlanModel::finder()->deleteAll('pt_id = %d', $lastPtId);
			}
			foreach ($_POST['plan-name'] as $key => $val) {
				//Save Pricing Table plan
				$sgptPlan = new SGPT_PtPlanModel();
				$sgptPlan->setPt_id($lastPtId);
				$sgptPlan->setName(sanitize_text_field($val));
				$sgptPlan->setPrice(sanitize_text_field($_POST['plan-price'][$key]));
				$sgptPlan->setPrice_timeframe(sanitize_text_field($_POST['pricing-plan'][$key]));
				$sgptPlan->setButton_url(sanitize_text_field($_POST['button-url'][$key]));
				$sgptPlan->setButton_text(sanitize_text_field(stripslashes($_POST['button-text'][$key])));
				$sgptPlan->setButton_shortcode(sanitize_text_field(stripslashes($_POST['button-shortcode'][$key])));
				$isHighlighted = @$dataArray[$key]['highlight-plan'] = $_POST['fake-id'][$key] == $_POST['highlight-plan'][0]; //Boolean value true if highlighted
				$sgptPlan->setHighlighted_plan($isHighlighted);
				$sgptPlan->setBadge_text(sanitize_text_field($_POST['badge-text'][$key]));
				$res = $sgptPlan->save();
				$lastPlanId = $wpdb->insert_id;
				//Save plan feature
				$sgptFeatures = array_filter(explode(PHP_EOL, $_POST['pricing-features'][$key]));
				foreach ($sgptFeatures as $feature) {
					if(empty($feature)) continue;
					$sgptFeature = new SGPT_PtFeatureModel();
					$sgptFeature->setName(sanitize_text_field($feature));
					$sgptFeature->setPlan_id($lastPlanId);
					$res = $sgptFeature->save();
				}
			}
		}
		echo $lastPtId;
		exit();
	}

	//Add pricing table page
	public function save()
	{
		global $wpdb;
		global $sgpt;
		$sgpt->includeStyle('page/styles/save');
		$sgpt->includeScript('page/scripts/save');
		if (SGPT_PRO_VERSION) {
			$sgpt->includeStyle('page/styles/bootstrap-formhelpers.min');
			$sgpt->includeScript('page/scripts/bootstrap-formhelpers.min');
		}
		$sgpt->includeScript('core/scripts/main');
		$sgpt->includeScript('core/scripts/sgptRequestHandler');
		$sgptId = 0;
		$sgptDataArray = array();

		isset($_GET['id']) ? $sgptId = (int)$_GET['id'] : 0;
		//If edit
		if ($sgptId) {
			$sgptDataArray = array();
			$sgptTbl = SGPT_PricingTableModel::finder()->findByPk($sgptId);
			if (!$sgptTbl) {
				$sgptTbl = new SGPT_PricingTableModel();
				//die('Error, pricing table not found');
			}
			$sgptTitle = $sgptTbl->getName();
			$sgptTheme = $sgptTbl->getTheme();
			$sgptOptions = $sgptTbl->getOptions();
			$sgptOptions = json_decode($sgptOptions,true);

			$sgptPlans = SGPT_PtPlanModel::finder()->findAll('pt_id = %d', $sgptTbl->getId());
			foreach ($sgptPlans as $sgptPlan) {
				$tmpArray = SGPT_PtPlanModel::toArray($sgptPlan);
				$sgptPlanId = $sgptPlan->getId();

				$features = SGPT_PtFeatureModel::finder()->findAll('plan_id = %d', $sgptPlanId);
				$featureName = SGPT_PtFeatureModel::getFeaturesNameAsString($features);
				$tmpArray['features'] = $featureName;
				$tmpArray['title'] = $sgptTitle;
				$tmpArray['theme'] = $sgptTheme;
				$tmpArray['options'] = $sgptOptions;

				$sgptDataArray[] = $tmpArray;
			}

		}
		add_thickbox();
		//WordPress Popup
		SGPT_AdminView::render('pricingtable/save', array(
			'sgptDataArray' => $sgptDataArray,
			'sgptTableId' => $sgptId
		));
	}

	public function ajaxDelete()
	{
		global $sgpt;
		$id = (int)$_POST['id'];
		SGPT_PricingTableModel::finder()->deleteByPk($id);
		SGPT_PtPlanModel::finder()->deleteAll('pt_id = %d', $id);
		SGPT_PtFeatureModel::finder()->deleteAll('pt_id = %d', $id);
		exit();
	}

	public function ajaxCloseBanner()
	{
		delete_option(SG_REVIEW_BANNER);
		exit();
	}

	public function ajaxSgptClone()
	{
		global $sgpt;
		global $wpdb;
		$id = (int)$_POST['id'];
		$currentPt = SGPT_PricingTableModel::finder()->findByPk($id);
		$currentPtPlans = SGPT_PtPlanModel::finder()->findAll('pt_id = %d', $id);

		$name = $currentPt->getName();
		$type = $currentPt->getType();
		$theme = $currentPt->getTheme();
		$options = $currentPt->getOptions();

		$newClonedPt = new SGPT_PricingTableModel();
		$newClonedPt->setName($name);
		$newClonedPt->setType($type);
		$newClonedPt->setTheme($theme);
		$newClonedPt->setOptions($options);
		$newClonedPt->save();

		$clonePtId = $wpdb->insert_id;

		foreach ($currentPtPlans as $plan) {
			$planId[] = $plan->getId();
			$planName[] = $plan->getName();
			$planPrice[] = $plan->getPrice();
			$planPriceTimeframe[] = $plan->getPrice_timeframe();
			$planButtonUrl[] = $plan->getButton_url();
			$planButtonText[] = $plan->getButton_text();
			$planButtonShortcode[] = $plan->getButton_shortcode();
			$planHighlightedPlan[] = $plan->getHighlighted_plan();
			$planBadgeText[] = $plan->getBadge_text();
		}

		for ($i = 0;$i<count($currentPtPlans);$i++) {
			$newClonedPtPlan = new SGPT_PtPlanModel();
			$newClonedPtPlan->setPt_id($clonePtId);
			$newClonedPtPlan->setName($planName[$i]);
			$newClonedPtPlan->setPrice($planPrice[$i]);
			$newClonedPtPlan->setPrice_timeframe($planPriceTimeframe[$i]);
			$newClonedPtPlan->setButton_url($planButtonUrl[$i]);
			$newClonedPtPlan->setButton_text($planButtonText[$i]);
			$newClonedPtPlan->setButton_shortcode($planButtonShortcode[$i]);
			$newClonedPtPlan->setHighlighted_plan($planHighlightedPlan[$i]);
			$newClonedPtPlan->setBadge_text($planBadgeText[$i]);
			$newClonedPtPlan->save();
			$newClonedPtPlanIds[] = $wpdb->insert_id;
		}

		$currentPtFeatures = array();
		for ($i = 0;$i<count($planId);$i++) {
			$currentPtFeatures[] = SGPT_PtFeatureModel::finder()->findAll('plan_id = %d', $planId[$i]);
			if (!$currentPtFeatures) {
				return;
			}
		}

		for ($i = 0;$i<count($currentPtFeatures);$i++) {
			for ($j = 0;$j<count($currentPtFeatures[$i]);$j++) {
				$newClonedPtFeature = new SGPT_PtFeatureModel();
				$newClonedPtFeature->setPlan_id($newClonedPtPlanIds[$i]);
				$newClonedPtFeature->setName($currentPtFeatures[$i][$j]->getName());
				$newClonedPtFeature->save();
			}
		}
		echo $clonePtId;
		exit();

	}

	private function createPricingTableHtml($plans, $theme)
	{
		global $sgpt;
		global $post;
		$sgpt->includeStyle('page/styles/save');
		if (SGPT_PRO_VERSION) {
			$sgpt->includeStyle('page/styles/bootstrap-formhelpers.min');
			$sgpt->includeScript('page/scripts/bootstrap-formhelpers.min');
		}
		$sgpt->includeScript('core/scripts/supportFunctions');
		$themeCss = 'sns_'.$theme;
		$html = '';
		$sgptOptions = array();

		foreach ($plans as $plan) {
			if (!isset($plan['highlight-plan'])) {
				$plan['highlight-plan'] = 0;
			}
			if (!is_admin()) {

				$sgtable = SGPT_PricingTableModel::finder()->findByPk($plan['pt_id']);
				$options = $sgtable->getOptions();
				$sgptOptions = json_decode($options,true);

				$sgptFeatures = $plan['pricing-feature'];
				$featuresHtml = '<ul>';
				$mainStyles = '';
				$planShadowStyle = '';
				$textShadowStyle = '';
				
				foreach ($sgptFeatures as $feature) {
					$mainStyles = '';
					if ($sgptOptions['featureColor']) {
						$mainStyles .= 'background-color:'.$sgptOptions['featureColor'].';';
					}
					if ($sgptOptions['textFeatureColor']) {
						$mainStyles .= 'color:'.$sgptOptions['textFeatureColor'].';';
					}
					if ($sgptOptions['font']) {
						$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$featuresHtml .= '<li class="my-content-css"'.$mainStyles.'><span>'.$feature.'</span></li>';
				}
				$featuresHtml .= '</ul>';
				///////////////button shortcode///////////////
				if ($this->hasContentShortcode(@$plan['button-shortcode'])) {
					$buttonShortcode = do_shortcode(stripslashes($plan['button-shortcode']));
				}
			    else {
			    	$buttonShortcode = '';
			    }
			    /////////////////////////////////
				$highlightedHtml = '';
				if ($plan['highlight-plan']) {
					$highlightedHtml = 'highlighted_'.$theme.'_plan';

					$mainStyles = '';
					if ($sgptOptions['font']) {
						$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
					}
					if ($sgptOptions['planShadowOn']) {

						if ($sgptOptions['planLeftRight'] && $sgptOptions['planTopBottom']) {
							$planShadowStyle .= 'box-shadow:'.$sgptOptions['planLeftRight'].'px '.$sgptOptions['planTopBottom'].'px ';
							
							if ($sgptOptions['planBlur']) {
								$planShadowStyle .= $sgptOptions['planBlur'].'px ';
							}
							if ($sgptOptions['planShadowColor']) {
								$planShadowStyle .= $sgptOptions['planShadowColor'];
							}
						}
						$mainStyles .= $planShadowStyle.';border-radius:5px !important;';

					}
					if ($sgptOptions['textShadowOn']) {

						if ($sgptOptions['textLeftRight'] && $sgptOptions['textTopBottom']) {
							$textShadowStyle .= 'text-shadow:'.$sgptOptions['textLeftRight'].'px '.$sgptOptions['textTopBottom'].'px ';
							
							if ($sgptOptions['textBlur']) {
								$textShadowStyle .= $sgptOptions['textBlur'].'px ';
							}
							if ($sgptOptions['textShadowColor']) {
								$textShadowStyle .= $sgptOptions['textShadowColor'];
							}
						}
						$mainStyles .= $textShadowStyle.';';

					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$html .= '<div'.$mainStyles.' class="sgpt-user-style sgpt-plan-count sgpt-highlight-transform '.$themeCss.'_whole" id="'.$highlightedHtml.'">';

					$mainStyles = '';
					if ($sgptOptions['planNameColor']) {
						$mainStyles .= 'background-color:'.$sgptOptions['planNameColor'].';';
					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$html .= '<div'.$mainStyles.' class="'.$themeCss.'_type">';

				}
				else {
					$mainStyles = '';
					if ($sgptOptions['font']) {
						$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
					}
					if ($sgptOptions['planShadowOn']) {

						if ($sgptOptions['planLeftRight'] && $sgptOptions['planTopBottom']) {
							$planShadowStyle .= 'box-shadow:'.$sgptOptions['planLeftRight'].'px '.$sgptOptions['planTopBottom'].'px ';
							
							if ($sgptOptions['planBlur']) {
								$planShadowStyle .= $sgptOptions['planBlur'].'px ';
							}
							if ($sgptOptions['planShadowColor']) {
								$planShadowStyle .= $sgptOptions['planShadowColor'];
							}
						}
						$mainStyles .= $planShadowStyle.';border-radius:5px !important;';

					}
					if ($sgptOptions['textShadowOn']) {

						if ($sgptOptions['textLeftRight'] && $sgptOptions['textTopBottom']) {
							$textShadowStyle .= 'text-shadow:'.$sgptOptions['textLeftRight'].'px  '.$sgptOptions['textTopBottom'].'px ';
							
							if ($sgptOptions['textBlur']) {
								$textShadowStyle .= $sgptOptions['textBlur'].'px ';
							}
							if ($sgptOptions['textShadowColor']) {
								$textShadowStyle .= $sgptOptions['textShadowColor'];
							}
						}
						$mainStyles .= $textShadowStyle.';';

					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$html .= '<div'.$mainStyles.' class="sgpt-plan-count '.$themeCss.'_whole" id="'.$highlightedHtml.'">';
					
					$mainStyles = '';
					if ($sgptOptions['font']) {
						$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
					}
					if ($sgptOptions['planNameColor']) {
						$mainStyles .= 'background-color:'.$sgptOptions['planNameColor'].';';
					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$html .= '<div'.$mainStyles.' class="sgpt-user-style '.$themeCss.'_type">';

				}
				$html .= '<input type="hidden" class="sgpt-current-font" value="'.$sgptOptions['font'].'">';
				if (!empty($plan['badge-text'])) {
					$html .= '<p class="snsBanner0">
								<span>'.$plan['badge-text'].'</span>
							</p>';
				}

				$mainStyles = '';
				if ($sgptOptions['font']) {
					$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
				}
				if ($sgptOptions['textNameColor']) {
					$mainStyles .= 'color:'.$sgptOptions['textNameColor'].';';
				}
				if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

				$html .= '<div'.$mainStyles.' class="sgpt-user-style '.$themeCss.'_type_text my-plan-name-css">'.$plan['plan-name'].'&nbsp;</div>
							</div>';
					$mainStyles = '';
					if ($sgptOptions['font']) {
						$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
					}
					if ($sgptOptions['bgColor']) {
						$mainStyles .= 'background-color:'.$sgptOptions['bgColor'].';';
					}
					if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

					$html .= '<div'.$mainStyles.' class="'.$themeCss.'_plan">
								<div class="sgpt-user-style '.$themeCss.'_header">
									<div class="'.$themeCss.'_details">';

							$mainStyles = '';
							if ($sgptOptions['font']) {
								$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
							}
							if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

							$html .= '<div'.$mainStyles.' class="'.$themeCss.'_header_price">'.$plan['plan-price'].'</div>
								<div'.$mainStyles.' class="'.$themeCss.'_header_timeframe">'.$plan['pricing-plan'].'</div>
								</div>
								</div>
								<div class="sgpt-user-style '.$themeCss.'_content">'.$featuresHtml.'</div>';

								$mainStyles = '';
								if ($sgptOptions['font']) {
									$mainStyles .= 'font-family:'.$sgptOptions['font'].';';
								}
								if ($sgptOptions['buttonColor']) {
									$mainStyles .= 'background-color:'.$sgptOptions['buttonColor'].';';
								}
								if ($sgptOptions['textButtonColor']) {
									$mainStyles .= 'color:'.$sgptOptions['textButtonColor'].';';
								}
								if ($mainStyles) $mainStyles = ' style="'.$mainStyles.'"';

								$html .= '<div class="sgpt-user-style '.$themeCss.'_price">';
								
								if (!@$plan['button-url'] && !@$plan['button-text']) {
									$html .= '';
								}
								if ($this->hasContentShortcode($plan['button-text'])) {
									$html .= do_shortcode($plan['button-text']);
								}
								else {
									$html .= '<a'.$mainStyles.' href="'.$plan['button-url'].'" class="'.$themeCss.'_bottom">'.$plan['button-text'].'</a>';
								}

								$html .= '</div>
								<div class="sgpt-user-style">'.$buttonShortcode.'</div>
							</div>
						</div>';

			}
			else {
				//Plan features
				$sgptFeatures = $plan['pricing-feature'];
				$featuresHtml = '<ul>';
				foreach ($sgptFeatures as $feature) {
					$featuresHtml .= '<li class="my-content-css"><span>'.$feature.'</span></li>';
				}
				$featuresHtml .= '</ul>';

				$highlightedHtml = '';

				if ($plan['highlight-plan']) {
					$highlightedHtml = 'highlighted_'.$theme.'_plan';

					$html .= '<div class="sgpt-user-style sgpt-plan-count sgpt-highlight-transform '.$themeCss.'_whole" id="'.$highlightedHtml.'">
							<div class="'.$themeCss.'_type">';
				}
				else {
					$html .= '<div style="margin-top:10px" class="sgpt-plan-count '.$themeCss.'_whole" id="'.$highlightedHtml.'">
							<div class="sgpt-user-style '.$themeCss.'_type">';
				}

				if (!empty($plan['badge-text'])) {
					$html .= '<p class="snsBanner0">
								<span>'.$plan['badge-text'].'</span>
							</p>';
				}

				$html .= '<div class="sgpt-user-style '.$themeCss.'_type_text my-plan-name-css">'.$plan['plan-name'].'&nbsp;</div>
							</div>
							<div class="'.$themeCss.'_plan">
								<div class="sgpt-user-style '.$themeCss.'_header">
									<div class="'.$themeCss.'_details">
									<div class="'.$themeCss.'_header_price">'.$plan['plan-price'].'</div>
									<div class="'.$themeCss.'_header_timeframe">'.$plan['pricing-plan'].'</div>
									</div>
									</div>
								<div class="sgpt-user-style '.$themeCss.'_content">
									'.$featuresHtml.'
								</div>
								<div class="sgpt-user-style '.$themeCss.'_price">';
								if (!@$plan['button-url'] && !@$plan['button-text']) {
									$html .= '';
								}
								if ($this->hasContentShortcode(@$plan['button-text'])) {
									$html .= do_shortcode(@$plan['button-text']);
								}
								else {
									$html .= '<a style="min-width:150px" href="'.$plan['button-url'].'" class="'.$themeCss.'_bottom">'.$plan['button-text'].'</a>';
								}
							    if ($this->hasContentShortcode(@$plan['button-shortcode']))
							    {
							        $buttonShortcode .= do_shortcode(stripslashes($plan['button-shortcode']));
							    }
							    else {
							    	$buttonShortcode = '';
							    }
								$html .= '</div>
								<div class="sgpt-user-style">'.$buttonShortcode.'</div>
							</div>
						</div>';
			}
		}
		if (SGPT_PRO_VERSION) {
			return '<script type="text/javascript" src="'.$sgpt->app_url.'assets/page/scripts/bootstrap-formhelpers.min.js"></script>
			<div class="sgpt-wrapper">'.$html.'</div><script type="text/javascript" src="'.$sgpt->app_url.'assets/page/scripts/save.js"></script>';
		}
		return '<div class="sgpt-wrapper">'.$html.'</div><script type="text/javascript" src="'.$sgpt->app_url.'assets/page/scripts/save.js"></script>';
	}

	public function hasContentShortcode($content) {

		global $shortcode_tags;

		preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
		$tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

		/* If tagnames is empty it's mean content does not have shortcode */
		if (empty($tagnames)) {
		   return false;
		}
		return true;

   }
}