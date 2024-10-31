<?php

global $sgpt;
$sgpt->includeController('Controller');
$sgpt->includeModel('PricingTable');
$sgpt->includeModel('PtFeature');
$sgpt->includeModel('PtPlan');

class SGPT_SetupController extends SGPT_Controller
{
	public static function activate()
	{
		add_option(SG_REVIEW_BANNER,SG_REVIEW_BANNER);
		SGPT_PricingTableModel::create();
		SGPT_PtPlanModel::create();
		SGPT_PtFeatureModel::create();
		if (is_multisite()) {
			$sites = wp_get_sites();
			foreach($sites as $site) {
				SGPT_PricingTableModel::create();
				SGPT_PtPlanModel::create();
				SGPT_PtFeatureModel::create();
			}
		}
	}

	public static function deactivate()
	{

	}

	public static function uninstall()
	{
		SGPT_PtFeatureModel::drop();
		SGPT_PtPlanModel::drop();
		SGPT_PricingTableModel::drop();
		if (is_multisite()) {
			$sites = wp_get_sites();
			foreach($sites as $site) {
				SGPT_PtFeatureModel::drop();
				SGPT_PtPlanModel::drop();
				SGPT_PricingTableModel::drop();
			}
		}
	}

	public static function createBlog()
	{

	}

}
