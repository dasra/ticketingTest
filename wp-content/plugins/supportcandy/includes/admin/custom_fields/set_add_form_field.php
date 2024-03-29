<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb, $wpscfunction;
if (!($current_user->ID && $current_user->has_cap('manage_options'))) {exit;}

$field_label = isset($_POST) && isset($_POST['field_label']) ? sanitize_text_field($_POST['field_label']) : '';
if (!$field_label) {exit;}

$extra_info = isset($_POST) && isset($_POST['extra_info']) ? sanitize_text_field($_POST['extra_info']) : '';

$personal_info = isset($_POST) && isset($_POST['personal_info']) ? intval($_POST['personal_info']) : '1';

$field_type = isset($_POST) && isset($_POST['field_type']) ? intval($_POST['field_type']) : '';
if (!$field_type) {exit;}

$wpsc_tf_placeholder_text = isset($_POST) && isset($_POST['wpsc_tf_placeholder_text']) ? sanitize_text_field($_POST['wpsc_tf_placeholder_text']) : '';

$field_options = isset($_POST) && isset($_POST['field_options']) ? explode("\n",  $_POST['field_options']) : array();
$field_types = $wpscfunction->get_custom_field_types();
if ($field_types[$field_type]['has_options']==1 && !$field_options) {exit;}

foreach ($field_options as $key => $value) {
	$field_options[$key] = trim(sanitize_text_field($value));
}

$html_content = isset($_POST) && isset($_POST['html_content']) ? wp_kses_post(htmlspecialchars_decode($_POST['html_content'], ENT_QUOTES)) : '';

$required = isset($_POST) && isset($_POST['required']) ? intval($_POST['required']) : '1';

if($field_type == '22'){
	$required = 0;
}

$width = isset($_POST) && isset($_POST['width']) ? sanitize_text_field($_POST['width']) : '1';
$visibility = isset($_POST) && isset($_POST['visibility']) ? $_POST['visibility'] : array();
$wpsc_limit = isset($_POST) && isset($_POST['limit']) && $_POST['limit'] ? sanitize_text_field($_POST['limit']) : '0';

foreach ($visibility as $key => $value) {
	$visibility[$key] = sanitize_text_field($value);
}
$field_label = stripslashes($field_label);

$term = wp_insert_term( $field_label, 'wpsc_ticket_custom_fields' );
if (!is_wp_error($term) && isset($term['term_id'])) {
  $load_order = $wpdb->get_var("select max(meta_value) as load_order from {$wpdb->prefix}termmeta WHERE meta_key='wpsc_tf_load_order'");
  add_term_meta ($term['term_id'], 'wpsc_tf_label', $field_label);
  add_term_meta ($term['term_id'], 'wpsc_tf_extra_info', $extra_info);
	add_term_meta ($term['term_id'], 'agentonly', '0');
	add_term_meta ($term['term_id'], 'wpsc_tf_status', '1');
	add_term_meta ($term['term_id'], 'wpsc_tf_type', $field_type);
	add_term_meta ($term['term_id'], 'wpsc_tf_placeholder_text', $wpsc_tf_placeholder_text);
	add_term_meta ($term['term_id'], 'wpsc_tf_options', $field_options);
	add_term_meta ($term['term_id'], 'wpsc_tf_required', $required);
	add_term_meta ($term['term_id'], 'wpsc_tf_width', $width);
	add_term_meta ($term['term_id'], 'wpsc_tf_visibility', $visibility);
	add_term_meta ($term['term_id'], 'wpsc_tf_personal_info', $personal_info);
	add_term_meta ($term['term_id'], 'wpsc_tf_load_order', ++$load_order);
	add_term_meta ($term['term_id'], 'wpsc_tf_limit', $wpsc_limit);
	add_term_meta ($term['term_id'], 'wpsc_html_content', $html_content);
	if ($field_types[$field_type]['allow_ticket_list']) {
		add_term_meta ($term['term_id'], 'wpsc_allow_ticket_list', '1');
		add_term_meta ($term['term_id'], 'wpsc_customer_ticket_list_status', '0');
		add_term_meta ($term['term_id'], 'wpsc_agent_ticket_list_status', '0');
	} else {
		add_term_meta ($term['term_id'], 'wpsc_allow_ticket_list', '0');
	}
	
	if ($field_types[$field_type]['allow_ticket_filter']) {
		add_term_meta ($term['term_id'], 'wpsc_allow_ticket_filter', '1');
		add_term_meta ($term['term_id'], 'wpsc_ticket_filter_type', $field_types[$field_type]['ticket_filter_type']);
		add_term_meta ($term['term_id'], 'wpsc_customer_ticket_filter_status', '0');
		add_term_meta ($term['term_id'], 'wpsc_agent_ticket_filter_status', '0');
	} else {
		add_term_meta ($term['term_id'], 'wpsc_allow_ticket_filter', '0');
	}
	
	if ($field_types[$field_type]['allow_orderby']) {
		add_term_meta ($term['term_id'], 'wpsc_allow_orderby', '1');
	} else {
		add_term_meta ($term['term_id'], 'wpsc_allow_orderby', '0');
	}
		
	$check_str = $wpscfunction->check_str_is_non_english($field_label);

	if(!$check_str){
		wp_update_term($term['term_id'],'wpsc_ticket_custom_fields',array('slug' => 'cust_'.$term['term_id']));
	}
	
	do_action('wpsc_set_add_form_field',$term['term_id']);
	
	/**
 * Update translation option for custom field label in order to support WPML
 */
$custom_fields_localize = get_option('wpsc_custom_fields_localize');
if (!$custom_fields_localize) {
   $custom_fields_localize = array();
}
$custom_fields_localize['custom_fields_' . $term['term_id']] = $field_label;
update_option('wpsc_custom_fields_localize', $custom_fields_localize);

$custom_fields_extra_info = get_option('wpsc_custom_fields_extra_info');
if (!$custom_fields_extra_info) {
   $custom_fields_extra_info = array();
}
$custom_fields_extra_info['custom_fields_extra_info_' . $term['term_id']] = $extra_info;
update_option('wpsc_custom_fields_extra_info', $custom_fields_extra_info);	

	echo '{ "sucess_status":"1","messege":"'.__('Field added successfully.','supportcandy').'" }';
} else {
	echo '{ "sucess_status":"0","messege":"'.__('An error occured while creating custom field.','supportcandy').'" }';
}
