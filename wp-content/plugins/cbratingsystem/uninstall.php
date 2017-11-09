<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   rating
 * @author    codeboxr <info@codeboxr.com>
 * @license   GPL-2.0+
 * @link      http://codeboxr.com
 * @copyright 2014 codeboxr
 */

// If uninstall not called from WordPress, then exit
require_once(plugin_dir_path(__FILE__) . "data.php");
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!current_user_can('activate_plugins')) {
    return;
}

check_admin_referer('bulk-plugins');

$checkuninstall = intval(get_option('cbratingsystem_deleteonuninstall'));
if ($checkuninstall == 1) {

    CBRatingSystemData::delete_tables();
    CBRatingSystemData::delete_options();
    CBRatingSystemData::delete_metakeys();
}

