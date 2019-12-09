<?php
$splitted = explode( "wp-content", dirname( __FILE__ ) );
$result   = $splitted[ 0 ];
require_once( $result . 'wp-load.php' );
function track_email( )
{
    global $wpdb;
    $image_id              = $_GET[ 'image_id' ];
    $table_name_sent_email = $wpdb->prefix . 'weu_sent_email';
    $myrows                = $wpdb->get_results( $wpdb->prepare( "SELECT image_id, weu_seen, weu_seen_count FROM $table_name_sent_email WHERE `image_id` =%d", $image_id ) );
    $rowcount              = $wpdb->num_rows;
    if ( $rowcount != 0 ) {
        $seen_count = $myrows[ 0 ]->weu_seen_count;
        $seen_count = $seen_count + 1;
        $myrows1    = $wpdb->query( $wpdb->prepare( "UPDATE `" . $table_name_sent_email . "` SET weu_seen = %d , weu_seen_count=%d where image_id =%d", 1, $seen_count, $image_id ) );
    }
}
track_email();