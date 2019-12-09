<?php
/**
 * Plugin Name: WP Email Users
 * Plugin URI:  http://www.techspawn.com
 * Description: WP Email Users send mail to individual user or group of users.
 * Version: 1.6.7
 * Author: techspawn1
 * Author URI: http://www.techspawn.com
 * License: GPL2
 */
/*  Copyright 2016-2017  Techspawn  (email : sales@techspawn.com)

This program is free software; you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation; either version 2 of the License, or

(at your option) any later version.

This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details. 

You should have received a copy of the GNU General Public License

along with this program; if not, write to the Free Software

Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
/**
 * Make sure we don't expose any information if called directly
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I am just a plugin, not much I can do when called directly.';
    exit;
} //!function_exists( 'add_action' )
define('WP_EMAIL_USERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_EMAIL_USERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once('wp-autoresponder-email-settings.php');
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once('wp-email-user-ajax.php');
require_once('wp-email-user-ajax-subscribe.php');
require_once('wp-email-user-template.php');
require_once('wp-email-user-smtp.php');
require_once('wp-selected-user-ajax.php');
require_once('wp-autoresponder-email-configure.php');
require_once('wp-email-widget.php');
require_once('wp-email-shortcode.php');
require_once('wp-smtp-priority-ajax.php');
require_once('wp-cron-function.php');
require_once('wp-email-functions.php');
require_once('wp-email-autorespond-functions.php');
require_once('wp-selected-user-ajax.php');
require_once('wp-email-user-manage-list.php');
require_once('wp-send-mail.php');
require_once('wp-send-email-user-ajax.php');
require_once ABSPATH . WPINC . '/class-phpmailer.php';
require_once ABSPATH . WPINC . '/class-smtp.php';
if (!function_exists('ts_weu_load_enqueue_scripts')) {
    function ts_weu_load_enqueue_scripts()
    {
        wp_enqueue_script('jquery');
    }
} //!function_exists( 'ts_weu_load_enqueue_scripts' )
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links');
function add_action_links($links)
{
    $mylinks = array(
        '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=weu_email_auto_config')) . '">Settings</a>'
    );
    return array_merge($links, $mylinks);
}
add_action('init', 'ts_weu_load_enqueue_scripts');
add_action('wp_ajax_weu_autoresponder_selected_user', 'weu_autoresponder_selected_user');
add_action('wp_ajax_weu_autoresponder_selected_user_role', 'weu_autoresponder_selected_user_role');
if (!function_exists('ts_weu_enqueue_script')) {
    function ts_weu_enqueue_script()
    {
        $actual_link = $_SERVER['REQUEST_URI'];
        if (strpos($actual_link, 'weu_send_email') || strpos($actual_link, 'weu-template') || strpos($actual_link, 'weu-smtp-config') || strpos($actual_link, 'weu_email_setting') || strpos($actual_link, 'weu_email_auto_config') || strpos($actual_link, 'weu-manage-list') || strpos($actual_link, 'weu_custom_role') || strpos($actual_link, 'weu_sent_emails') || strpos($actual_link, 'weu-list-editor&listname')  || strpos($actual_link, 'weu-list-editor&listname') ) {
            
            wp_enqueue_script('wp-email-user-datatable-script', plugins_url('js/jquery.dataTables.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-sweet-alert-script', plugins_url('js/sweetalert.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script', plugins_url('js/email-admin.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_style('wp-email-user-datatable-style', plugins_url('css/jquery.dataTables.min.css', __FILE__));
            wp_enqueue_style('wp-email-user-style', plugins_url('css/style.css', __FILE__));
            wp_enqueue_style('wp-sweet-alert-style', plugins_url('css/sweetalert.css', __FILE__));

           
            wp_enqueue_style('multiselect-bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
            wp_enqueue_script('multiselect-bootstrap_jsselectpicker', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(
                'jquery'
            ));
    
            wp_enqueue_script('multiselect.jsselectpicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.3/js/bootstrap-select.min.js', array(
                'jquery'
            ));
           
            wp_enqueue_style('multiselect-css.selectpicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.3/css/bootstrap-select.min.css');
          
       
        }
    }
}
register_activation_hook(__FILE__, 'weu_setup_activation_data');
register_activation_hook(__FILE__, 'my_activation');

function my_activation()
{
    $crondata = array(
        'cron_job' => "no",
        'cron_time' => ""
    );
    update_option("cron_job_status", $crondata);
    update_option("weu_track_mail",'yes');
}
register_deactivation_hook(__FILE__, 'wp_email_deactivation');
function wp_email_deactivation()
{
    wp_clear_scheduled_hook('WP_mail_event');
    delete_option("cron_all_data");
    delete_option("cron_mail");
    delete_option("cron_mail_send");
    
}
add_action('admin_enqueue_scripts', 'ts_weu_enqueue_script');
add_action('WP_mail_event', 'do_this_hourly');

// enable plugin for custom user start
add_option('enable_plugin_for_other_roles');

$default_role_db = get_option('enable_plugin_for_other_roles');

$get_default_user_role = array(
    'administrator'
);

if ($default_role_db == NULL) {
    
    update_option('enable_plugin_for_other_roles', $get_default_user_role);
    
}
function plugin_update() {
    global $plugin_version;

    if ( get_site_option( 'plugin_version' ) != $plugin_version )
        plugin_updates();
}
add_action( 'plugins_loaded', 'plugin_update' );
function plugin_updates() {
    global $wpdb, $plugin_version;
delete_option('weu_sample_template');
weu_setup_activation_data();

}

// enable plugin for custom user end