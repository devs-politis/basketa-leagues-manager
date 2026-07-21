<?php
/*
Plugin Name: Basketa Leagues Manager
Description: Manage Basketball Leagues, Ticker & Standings from API Sports
Version: 2.0.0
Author: Efi Kakouni
*/

if (!defined('ABSPATH')) {
    exit;
}

define('BLM_VERSION', '2.0.0');
define('BLM_PATH', plugin_dir_path(__FILE__));
define('BLM_URL', plugin_dir_url(__FILE__));

require_once BLM_PATH . 'includes/class-api.php';
require_once BLM_PATH . 'includes/class-admin.php';
require_once BLM_PATH . 'includes/class-frontend-standings.php';
require_once BLM_PATH . 'includes/class-shortcode.php';

/*
 * Νέα modules
 */
if (file_exists(BLM_PATH . 'includes/class-ticker.php')) {
    require_once BLM_PATH . 'includes/class-ticker.php';
}

if (file_exists(BLM_PATH . 'includes/class-standings.php')) {
    require_once BLM_PATH . 'includes/class-standings.php';
}

if (file_exists(BLM_PATH . 'includes/class-settings.php')) {
    require_once BLM_PATH . 'includes/class-settings.php';
}

/*
 * Core
 */
new BLM_API();
new BLM_Admin();
new BLM_Shortcode();

/*
 * Optional Modules
 */
if (class_exists('BLM_Ticker')) {
    new BLM_Ticker();
}

if (class_exists('BLM_Standings')) {
    new BLM_Standings();
}

if (class_exists('BLM_Settings')) {
    new BLM_Settings();
}