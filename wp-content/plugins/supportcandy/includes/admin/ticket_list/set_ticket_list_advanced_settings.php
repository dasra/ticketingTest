<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user,$wpscfunction;
if (!($current_user->ID && $current_user->has_cap('manage_options'))) {
	exit;
}
$wpsc_close_ticket_status = get_option('wpsc_close_ticket_status');
$wpsc_close_ticket_group= isset($_POST) && isset($_POST['wpsc_close_ticket_group']) && is_array($_POST['wpsc_close_ticket_group']) ? $wpscfunction->sanitize_array($_POST['wpsc_close_ticket_group']) : array($wpsc_close_ticket_status);
update_option('wpsc_close_ticket_group',$wpsc_close_ticket_group);

echo '{ "sucess_status":"1","messege":"'.__('Settings saved.','supportcandy').'" }';
?>