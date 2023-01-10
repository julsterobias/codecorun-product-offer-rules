<?php
/**
 * 
 * codecorun_por_main_class
 * @since 1.0.0
 * @author codecorun
 * 
 */
namespace codecorun\por\main;
use codecorun\por\common\codecorun_por_common_class;

class codecorun_por_main_class extends codecorun_por_common_class
{
    private static $instance = null;

    /**
     * 
     * factory instance method
     * @since 1.0.0
     * @return class
     * 
     */
    public static function factory()
    {
        if(!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->load_assets();
        add_shortcode( 'codecorun-offers', [$this, 'offers'] );
    }

    /**
     * 
     * load_assets
     * @since 1.0.0
     * @param
     * 
     */
    public function load_assets()
    {
        wp_enqueue_style(CODECORUN_POR_PREFIX.'-admin-assets-css', CODECORUN_POR_URL.'assets/public.css');
    }


    /**
     * 
     * 
     * offers
     * @since 1.0.0
     * @param
     * @return
     * 
     * 
     */
    public function offers( $attr )
    {

        if( !isset( $attr['id'] ) ){
            error_log( __('Codecorun Error: No offer ID is found', 'codecorun-product-offer-rules') );
            return;
        }
        
        $offers = get_transient('codecorun_por_offers_cached-'.$attr['id']);
        $settings = get_post_meta( $attr['id'], 'codecorun_por_settings', true);

        $style = ( isset($attr['style']) )? $attr['style'] : null;
        
        if( !empty( $offers ) ){
            ob_start();
                $this->set_template('offer', ['offers' => $offers, 'settings' => $settings, 'style' => $style ] );
            return ob_get_clean();
        }else{
            error_log( __('Codecorun Error: No offer is found', 'codecorun-product-offer-rules') );
            return;
        }
        
    }

    
}
?>