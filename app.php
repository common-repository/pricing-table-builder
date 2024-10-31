<?php
/*
Plugin Name: Pricing Table Builder
Plugin URI: http://huge-it.com/wordpress-pricing-table-builder/
Description: Pricing Table Builder will allow you to create awesome responsive pricing tables for your site/blog.
Version: 1.2.0
Author: Huge IT
Author URI: http://huge-it.com/
Text Domain: sgpt
License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('WPINC')) {
    die;
}

require_once(dirname(__FILE__).'/com/config/bootstrap.php');
require_once(dirname(__FILE__).'/com/core/SGPT.php');

global $sgpt;

$sgpt = new SGPT();
$sgpt->app_path = realpath(dirname(__FILE__)).'/';
$sgpt->app_url = plugin_dir_url(__FILE__);
$sgpt->run();
