<?php
/*
Plugin Name: Myposeo
Plugin URI:   https://developer.wordpress.org/plugins/myposeo/
Description:  Myposeo.com dashboard integration into WordPress
Version:      0.7.1
Author:       Myposeo
Author URI:   https://www.myposeo.com
Text Domain:  myposeo
Domain Path:  /languages/
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


define( 'MYPOSEO_URL', plugin_dir_url ( __FILE__ ) );
define( 'MYPOSEO_DIR', plugin_dir_path( __FILE__ ) );

// Activation, uninstall
register_activation_hook( __FILE__, 'Myposeo_Install' );
register_deactivation_hook ( __FILE__, 'Myposeo_Uninstall' );


// Load translations
load_plugin_textdomain ( 'myposeo', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );


function myposeo_register_menus(){
        add_menu_page( 'Myposeo', 'Myposeo', '', 'menumyposeo', '', MYPOSEO_URL.'assets/images/logo.svg', 58 );
        add_submenu_page( 'menumyposeo', __('Settings','myposeo'), __('Settings','myposeo'), 'manage_options', 'myposeo_settings', 'myposeo_settings');
        add_submenu_page( 'menumyposeo', __('SEO Dashboard','myposeo'), __('SEO Dashboard','myposeo'), 'manage_options', 'myposeo_dashboard', 'myposeo_dashboard');
}
add_action( 'admin_menu', 'myposeo_register_menus' );

function myposeo_enqueue_scripts() {
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-datatables',MYPOSEO_URL.'assets/js/jquery.dataTables.js',array('jquery'),false,true);
    wp_enqueue_script( 'jquery-datatables-yadcf',MYPOSEO_URL.'assets/js/jquery.dataTables.yadcf.js',array('jquery-datatables'),false,true);

    wp_enqueue_style( 'jquery-ui', MYPOSEO_URL.'assets/css/jquery-ui.min.css' );
    wp_enqueue_style( 'jquery-ui', MYPOSEO_URL.'assets/css/jquery-ui.theme.min.css' );
    wp_enqueue_style( 'jquery', MYPOSEO_URL.'assets/css/jquery.styles.css' );
    wp_enqueue_style( 'jquery-datatables',MYPOSEO_URL.'assets/css/jquery.dataTables.css' );  
    wp_enqueue_style( 'jquery-datatables-yadcf',MYPOSEO_URL.'assets/css/jquery.dataTables.yadcf.css' );  
}
add_action( 'admin_enqueue_scripts', 'myposeo_enqueue_scripts' );

function myposeo_init() {
    register_post_type('labs', [
        'label' => 'Labs',
        'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M1591 1448q56 89 21.5 152.5t-140.5 63.5h-1152q-106 0-140.5-63.5t21.5-152.5l503-793v-399h-64q-26 0-45-19t-19-45 19-45 45-19h512q26 0 45 19t19 45-19 45-45 19h-64v399zm-779-725l-272 429h712l-272-429-20-31v-436h-128v436z"/></svg>')
     ]);
}
add_action('init', 'myposeo_init');


function myposeo_settings(){
	include("pages/admin/connection.php");
}
function myposeo_dashboard(){
	include("pages/admin/dashboard.php");
}
?>
