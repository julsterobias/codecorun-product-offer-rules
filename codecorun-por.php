<?php
/** 
 * 
 * Plugin Name: Codecorun - Product Offer Rules
 * Plugin URI: https://codecorun.com/plugins/product-offer-rules/
 * Description: A woocommerce extension that will allow you to offer or upsell products according to rules
 * Author:      Codecorun
 * Plugin Type: Extension
 * Author URI: https://codecorun.com
 * Version: 1.0.0
 * Text Domain: codecorun-product-offer-rules
 * 
 * 
*/

defined( 'ABSPATH' ) or die( 'No access area' );
define('CODECORUN_POR_PATH', plugin_dir_path( __FILE__ ));
define('CODECORUN_POR_URL', plugin_dir_url( __FILE__ ));
define('CODECORUN_POR_PREFIX','codecorun_por');
define('CODECORUN_POR_VERSION','1.0.0');


add_action( 'init', 'codecorun_por_load_textdomain' );
function codecorun_por_load_textdomain() {
	load_plugin_textdomain( 'codecorun-product-offer-rules', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

function codecorun_por_install(){
	if(class_exists('WooCommerce'))
		return;		

	echo '<h3>'.__('Plugin failed to install', 'codecorun-product-offer-rules').'</h3>';
    @trigger_error(__('This plugin requires WooCommerce installation', 'codecorun-product-offer-rules'), E_USER_ERROR);
}
register_activation_hook( __FILE__, 'codecorun_por_install' );

//autoload classes
spl_autoload_register(function ($class) {

	if(strpos($class,CODECORUN_POR_PREFIX) !== false){
		$class = preg_replace('/\\\\/', '{'.CODECORUN_POR_PREFIX.'}', $class);
        $fullclass = $class;
		$class = explode('{'.CODECORUN_POR_PREFIX.'}', $class);
		if(!empty(end($class))){
			$filename = str_replace("_", "-", end($class));
            $admin = (strpos($fullclass,'admin') !== false)? 'admin/' : null;
			include $admin.'includes/'.$filename.'.php';
		}
	}

});


add_action('plugins_loaded','codecorun_por_init');
function codecorun_por_init(){
	
	if(current_user_can('administrator')){
		//load admin class
		new codecorun\por\admin\codecorun_por_admin_class();
	}
	
	//load global class
	new codecorun\por\main\codecorun_por_main_class();
	
}

?>