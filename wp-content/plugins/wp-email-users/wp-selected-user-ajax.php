<?php
if (!defined('ABSPATH'))
    exit;
add_action('wp_ajax_weu_selected_users_1', 'callbackfunction_select');
add_action('wp_ajax_weu_selected_users_temp', 'callbackfunction_select2');
add_action('wp_ajax_weu_selected_users_sub', 'callbackfunction_select_sub');
function callbackfunction_select()
    {
    global $wpdb;
    $table_name       = $wpdb->prefix . 'weu_user_notification';
    $table_name_users = $wpdb->prefix . 'users';
    $data             = sanitize_text_field($_POST['data_raw']);
    $data1            = $_POST['data_raw'];
    $myrows           = $wpdb->get_results("select email_value from `" . $table_name . "` where template_id = %s", $data[0]);
    $datas            = unserialize($myrows[0]->email_value);
    $users_ids        = array();
    $users_data       = array();
    if (is_array($datas))
        {
        foreach ($datas as $value)
            {
            $myrows_users = $wpdb->get_results("select ID from `" . $table_name_users . "` where user_email =%s", $value);
            foreach ($myrows_users as $users)
                {
                array_push($users_ids, $users->ID);
                }
            }
        }
    else
        {
        $myrows_users = $wpdb->get_results("select ID from `" . $table_name_users . "` where user_email =%s", $datas);
        foreach ($myrows_users as $users)
            {
            array_push($users_ids, $users->ID);
            }
        }
    echo json_encode($users_data);
    wp_die();
    }
function callbackfunction_select2()
    {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weu_user_notification';
    $data1      = $_POST['data_raw'];
    $user_temp  = $wpdb->get_results("select template_value from `" . $table_name . "` where template_id =" . $data1[0]);
    $temp_Data  = $user_temp[0]->template_value;
    echo $temp_Data;
    wp_die();
    }
function callbackfunction_select_sub()
    {
    $data1 = $_POST['data_raw'];
    switch ($data1[0])
    {
        case 1:
            $subject = get_option('weu_new_user_register');
            break;
        case 2:
            $subject = get_option('weu_new_post_publish');
            break;
        case 3:
            $subject = get_option('weu_new_comment_post');
            break;
        case 4:
            $subject = get_option('weu_password_reset');
            break;
        case 5:
            $subject = get_option('weu_user_role_changed');
            break;
    }
    echo $subject;
    wp_die();
    }