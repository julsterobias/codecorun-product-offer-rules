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
        if( !is_admin() )
            $this->load_assets();

        add_shortcode( 'codecorun-offers', [$this, 'offers'] );
        add_action( 'template_redirect', [$this, 'last_view'] );
        add_action( 'woocommerce_payment_complete', [$this, 'clear_purchased'], 10, 2);
    }

    /**
     * 
     * clear_purchased
     * 
     */
    public function clear_purchased( $order_id, $order )
    {
        $user_id = get_post_meta($order_id, '_customer_user', true);
        if( isset( $user_id ) ){
            //reset user's cached purchased IDs
            wp_cache_get('codecorun_cached_purchased-'.$user_id);
        }
    }

    /**
     * 
     * 
     * last_view
     * @since 1.0.0
     * @param
     * @return
     * 
     */
    public function last_view()
    {
        global $post;
        if( is_single() && get_post_type( $post->ID ) == 'product' ){

           if( !isset( $_COOKIE[ 'codecorun_recent_prod_viewed' ] ) ){
                //set for 1 day
                $viewed = implode( [$post->ID] );
                setcookie( 'codecorun_recent_prod_viewed', $viewed, time()+86400, '/' );
           }else{
                $viewed = explode(',', $_COOKIE['codecorun_recent_prod_viewed'] );
                $viewed[] = $post->ID;
                $viewed = array_unique( $viewed );
                //update cookie
                setcookie( 'codecorun_recent_prod_viewed', implode(',', $viewed ), time()+86400, '/' );
           }
        }
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
        wp_enqueue_style(CODECORUN_POR_PREFIX.'-public-assets-css', CODECORUN_POR_URL.'assets/public.css');
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


        //check the rules
        $rules = get_transient('codecorun_por_rules_cached-'.$attr['id']);
        $result = $this->check_rules( $rules );

        //print_r( $result );
        
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


    /**
     * 
     * 
     * check_rules
     * 
     * 
     */
    public function check_rules( $rules = [] )
    {
        if( empty( $rules ) )
            return;

        $cond_value = [];

        foreach( $rules  as $index => $rule):
            $type = explode('-', $index);
            switch( $type[0] )
            {
                case 'date':
                case 'date_range':
                    $cond_value[] = $this->date( [
                        'type' => $type[0],
                        'date' => $rule
                    ] );
                break;
                case 'codecorun_dy_field_in_cart_products':
                    $cond_value[] = $this->in_cart_products( $rule );
                    break;
                case 'codecorun_dy_field_in_product_page':
                    $cond_value[] = $this->in_product_page( $rule );
                    break;
                case 'is_logged_in':
                    $cond_value[] = $this->is_logged_in();
                    break;
                case 'codecorun_dy_field_in_page':
                case 'codecorun_dy_field_in_post':
                    $cond_value[] = $this->in_page_post( $rule );
                    break;
                case 'codecorun_dy_field_last_views':
                    $cond_value[] = $this->last_viewed( $rule );
                    break;
                case 'codecorun_dy_field_had_purchased':
                    $cond_value[] = $this->had_purchased( $rule );
                    break;
                case 'have_url_param':
                    $cond_value[] = $this->have_url( $rule );
                    break;

            }
        endforeach;

        return $cond_value;
        
    }

    /**
     * 
     * 
     * date
     * @since 1.0.0
     * 
     * 
     */
    public function date( $args = [] )
    {   
        if( empty( $args ) )
            return;

        $locatime = explode(' ',current_time( 'mysql' ));

        if( $args['type'] == 'date' ){
            $today = strtotime( $locatime[0] );
            $date = strtotime( date( $args['date'] ) );
            $diff = $date - $today;
            return ( $diff == 0 )? 1 : 0;
        }else{
            $today = strtotime( $locatime[0] );
            $date1 = strtotime( date( $args['date']['from'] ) );
            $date2 = strtotime( date( $args['date']['to'] ) );
            $diff1 = $date1 - $today;
            $diff2 = $date2 - $today;
            return ( $diff1 <= 0 && $diff2 >= 0 )? 1 : 0;
        }
    }

    /**
     * 
     * 
     * in_cart_products
     * @since 1.0.0
     * 
     * 
     */
    public function in_cart_products( $rules )
    {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        $in_cart = 0;

        foreach($items as $values) { 
            $values['data']->get_id();
            foreach( $rules as $rule ){
                if( $rule['id'] == $values['data']->get_id() ){
                    $in_cart++;
                }
            }
        } 

        return ( $in_cart == count( $rules ) )? 1 : 0;
    }


    /**
     * 
     * 
     * in_product_page
     * @since 1.0.0
     * 
     * 
     */
    public function in_product_page( $rules )
    {
        global $post;
        return ( $rules[0]['id'] == $post->ID )? 1 : 0;
        
    }

    /**
     * 
     * 
     * is_logged_in
     * @since 1.0.0
     * 
     * 
     */
    public function is_logged_in()
    {
        return ( is_user_logged_in() )?  1 : 0;
    }

    /**
     * 
     * 
     * in_page_post
     * @since 1.0.0
     * 
     * 
     */
    public function in_page_post( $rules )
    {
        global $post;

        foreach( $rules as $rule ){
            if( $post->ID == $rule['id'] ){
                return 1;
            }
        }
        return 0;
    }

    /**
     * 
     * 
     * last_viewed
     * @since 1.0.0
     * 
     * 
     */
    public function last_viewed( $rules )
    {
        $last_cookie = ( isset( $_COOKIE['codecorun_recent_prod_viewed'] ) )? $_COOKIE['codecorun_recent_prod_viewed'] : null;
        if( $last_cookie ){
            $last_cookie = explode(',', $last_cookie);
            foreach( $rules as $rule ){
                if( in_array( $rule['id'], $last_cookie ) ){
                    return 1;
                }
            }
        }else{
            return 0;
        }
    }

    /**
     * 
     * 
     * had_purchased
     * @since 1.0.0
     * 
     * 
     */
    public function had_purchased( $rules )
    {
        $res = $this->get_purchased_by_user();

        foreach( $rules as $rule ){
            if( in_array( $rule['id'], $res) ){
                return 1;
                break;
            }
        }
        return 0;

    }

    /**
     * 
     * 
     * have_url
     * @since 1.0.0
     * 
     * 
     */
    public function have_url( $rules )
    {
        $params = $_GET;
        if( !empty( $params ) ){
            
            $it_has = 0;
            foreach( $params as $index => $param ){
                foreach( $rules as $rule ){
                    $param_ = sanitize_text_field( $param );
                    if( $rule['key'] == $index && $rule['value'] == $param_ ){
                        $it_has++;
                    }
                }
            }

            if( count( $rules ) == $it_has )
                return 1;
            else
                return 0;

        }else{
            return 0;
        }

    }



    
}
?>