<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function grd_enqueue_assets() {
    wp_enqueue_style( 'grd-styles', plugins_url( '../assets/css/style.css', __FILE__ ), array(), '1.1', 'all' );
    wp_enqueue_script( 'grd-scripts', plugins_url( '../assets/js/scripts.js', __FILE__ ), array('jquery'), '1.1', true );
}
add_action( 'wp_enqueue_scripts', 'grd_enqueue_assets' );
?>
