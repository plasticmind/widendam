<?php
    /*
     Plugin Name: Widen DAM Library
     Plugin URI: http://widen.com
     Description: Add Assets from your Widen DAM system into WordPress posts, pages, and the Media Library.  To get started, configure the connection to your Collective site in 'Settings|Widen DAM Settings', then search for Assets in 'Media|Widen DAM Library'.
     Version: 1.0.0
     Author: Widen Enterprises
     Author URI: http://widen.com
     */

require_once('widen_ajax.php');
require_once('widen_restapi.php');
include_once( ABSPATH . WPINC. '/class-http.php' );

function widen_admin()
{
    include('widen_admin.php');
}

function widen_media()
{
    include('widen_media.php');
}

function widen_admin_actions()
{
    add_options_page("Widen DAM Settings", "Widen DAM Settings", 1, "DAMSettings", "widen_admin");
    add_media_page("Widen DAM Library", "Widen DAM Library", 1, "DAMLibrary", "widen_media");
}

function widen_upload_media_menu($tabs)
{
    wp_enqueue_script('media-upload');
    $tabs['widen_upload']='Insert from Widen DAM';
    return $tabs;
}

function widen_upload_menu_handle()
{
    return wp_iframe('widen_media');
}

add_action('admin_menu', 'widen_admin_actions');

add_filter('media_upload_tabs', 'widen_upload_media_menu');

add_action('media_upload_widen_upload', 'widen_upload_menu_handle');

?>
