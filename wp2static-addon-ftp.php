<?php

/**
 * Plugin Name:       WP2Static Add-on: FTP
 * Plugin URI:        https://wp2static.com
 * Description:       FTP as a deployment option for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-ftp
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_FTP_PATH', plugin_dir_path( __FILE__ ) );

require WP2STATIC_FTP_PATH . 'vendor/autoload.php';

// @codingStandardsIgnoreStart
$ajax_action = isset( $_POST['ajax_action'] ) ? $_POST['ajax_action'] : '';
// @codingStandardsIgnoreEnd

if ( $ajax_action == 'test_ftp' ) {
    $ftp = new WP2Static\FTP();

    $ftp->test_ftp();

    wp_die();
    return null;
} elseif ( $ajax_action == 'ftp_prepare_export' ) {
    $ftp = new WP2Static\FTP();

    $ftp->bootstrap();
    $ftp->prepareDeploy();

    wp_die();
    return null;
} elseif ( $ajax_action == 'ftp_transfer_files' ) {
    $ftp = new WP2Static\FTP();

    $ftp->bootstrap();
    $ftp->upload_files();

    wp_die();
    return null;
}

define( 'PLUGIN_NAME_VERSION', '0.1' );

function run_wp2static_addon_ftp() {
    $plugin = new WP2Static\FTPAddon();
    $plugin->run();

}

run_wp2static_addon_ftp();
