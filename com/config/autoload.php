<?php

global $SGPT_AUTOLOAD;
$SGPT_AUTOLOAD = array();

$SGPT_AUTOLOAD['menu_items'] = array(
	array(
		'id' => 'showAll',
		'page_title' => 'All Pricing Tables',
		'menu_title' => 'Pricing Table',
		'capability' => 'manage_options',
		'icon' => 'dashicons-list-view',
		'controller' => 'PricingTable',
		'action' => 'index',
		'submenu_items' => array(
			array(
				'id' => 'showAll',
				'page_title' => 'All Pricing Tables',
				'menu_title' => 'All Pricing Tables',
				'capability' => 'manage_options',
				'controller' => 'PricingTable',
				'action' => 'index',
			),
			array(
				'id' => 'add',
				'page_title' => 'Add/Edit Pricing Table',
				'menu_title' => 'Add Pricing Table',
				'capability' => 'manage_options',
				'controller' => 'PricingTable',
				'action' => 'save',
			),
			array(
				'id' => 'sgPlugins',
				'page_title' => 'Add/Edit Comment',
				'menu_title' => 'More Plugins',
				'capability' => 'manage_options',
				'controller' => 'PricingTable',
				'action' => 'morePlugins',
			)
		),
	),
);

$SGPT_AUTOLOAD['network_admin_menu_items'] = array();

$SGPT_AUTOLOAD['shortcodes'] = array(
	array(
		'shortcode' => 'sgpt_pricing_table',
		'controller' => 'PricingTable',
		'action' => 'sgptShortcode',
	),
);

$SGPT_AUTOLOAD['front_ajax'] = array();

$SGPT_AUTOLOAD['admin_ajax'] = array(
	array(
		'controller' => 'PricingTable',
		'action' => 'ajaxSave',
	),
	array(
		'controller' => 'PricingTable',
		'action' => 'ajaxPreview',
	),
	array(
		'controller' => 'PricingTable',
		'action' => 'ajaxDelete',
	),
	 array(
		'controller' => 'PricingTable',
		'action' => 'ajaxSgptClone',
	),
	 array(
		'controller' => 'PricingTable',
		'action' => 'ajaxCloseBanner',
	),
	  array(
		'controller' => 'PricingTable',
		'action' => 'ajaxSgptImport',
	)
);

$SGPT_AUTOLOAD['admin_post'] = array();

//use wp_ajax_library to include ajax for the frontend
$SGPT_AUTOLOAD['front_scripts'] = array();

//use wp_enqueue_media to enqueue media
$SGPT_AUTOLOAD['admin_scripts'] = array();

$SGPT_AUTOLOAD['front_styles'] = array();

$SGPT_AUTOLOAD['admin_styles'] = array();
