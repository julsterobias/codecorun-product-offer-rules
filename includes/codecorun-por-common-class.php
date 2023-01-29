<?php
/**
 * 
 * codecorun_por_common_class
 * @since 1.0.0
 * @author codecorun
 * 
 */
namespace codecorun\por\common;

defined( 'ABSPATH' ) or die( 'No access area' );

class codecorun_por_common_class
{
    /**
	 * 
	 * set_template
	 * @since 1.0
	 * @param string, array
	 * @return file
	 * 
	 */
    public function set_template($file, $params = [])
    {
        if(!$file)
			return;

		if(strpos($file,'.php') === false)
			$file = $file.'.php';

		$other = null;
		if(isset($params['other'])){
			$other = $params['other'].'/';
		}

		//get plugin folder name without manually assigning the folder name
		$plugin_folder = explode('/',CODECORUN_POR_URL);
		$plugin_folder = array_filter($plugin_folder);

		$path = get_stylesheet_directory().'/'.end($plugin_folder).'/'.$other.'templates';		

		if(file_exists($path.'/'.$file)){
			include $path.'/'.$file;
		}else{
			if(isset($params['other'])){
				$other = $params['other'];
			}
			include CODECORUN_POR_PATH.$other.'/templates/'.$file;
		}


    }

    /**
	 * 
	 * rules
	 * @since 1.0
	 * @param
	 * @return array
	 * 
	 */

	public static function rules()
	{
		return apply_filters(
			'cpor-rules',
			[
				'woo' => [
					'date' => __('Date','codecorun-product-offer-rules'),
					'date_range' => __('Date range','codecorun-product-offer-rules'),
					'in_cart_products' => __('In cart products','codecorun-product-offer-rules'),
					'in_product_page' => __('In product page','codecorun-product-offer-rules'),
                    'is_logged_in' => __('Is user logged in','codecorun-product-offer-rules'),
                    'in_page' => __('In pages','codecorun-product-offer-rules'),
                    'in_post' => __('In post','codecorun-product-offer-rules'),
                    'last_views' => __('Last product views','codecorun-product-offer-rules'),
                    'had_purchased' => __('Last purchased product(s)','codecorun-product-offer-rules'),
					'have_url_param' => __('Have URL Parameter', 'codecorun-product-offer-rules'),
					'user_have_meta' => __('User have meta', 'codecorun-product-offer-rules')
				]
			]
		);
	}

	/**
	 * 
	 * get_post_details
	 * @since 1.0.0
	 * @param array
	 * @return array
	 * 
	 */
	public function get_post_details( $data = [] )
	{
		if( empty($data) )
			return;

		$args = [
			'numberposts' => -1,
			'post_type' => ( isset($data['post_type']) )? $data['post_type'] : 'post',
			'post__in' => $data['ids'],
			'post_status' => 'publish'
		];

		$posts = get_posts( $args );
		if( !empty( $posts ) ){
			$offer_val = [];
			foreach($posts as $offer){
				
				//get other details
				if( $data['post_type'] == 'product' ){
					$product = wc_get_product( $offer->ID );
				}
				
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $offer->ID ), 'medium' );

				$offer_d = [
					'id' => $offer->ID,
					'title' => $offer->post_title,
					'image' => ( isset( $image[0] ) )? $image[0] : null
				];

				if( $data['post_type'] == 'product' ){
					$offer_d['price'] = $product->get_price_html();
				}

				$offer_val[] = $offer_d;

			}
			return $offer_val;
		}
		return;
	}	


	/**
	 * 
	 * get_purchased_by_user
	 * @since 1.0.0
	 * @param
	 * @return
	 * 
	 */
	public function get_purchased_by_user()
	{
		
		$current_user = wp_get_current_user();
		
		if( 0 == $current_user->ID ) 
			return;

		$cached_ids = wp_cache_get('codecorun_cached_purchased-'.$current_user->ID);

		if( $cached_ids ){
			return $cached_ids;
		}else{

			$args = array(
				'numberposts' => -1,
				'meta_key' => '_customer_user',
				'meta_value' => $current_user->ID,
				'post_type' => wc_get_order_types(),
				'post_status' => array_keys( wc_get_is_paid_statuses() ),
			);
	 
			$customer_orders = get_posts( $args);
	
			$product_ids = [];
			if( !empty( $customer_orders ) ){	
				foreach ( $customer_orders as $customer_order ) {
					$order = wc_get_order( $customer_order->ID );
					$items = $order->get_items();
					foreach ( $items as $item ) {
						$product_id = $item->get_product_id();
						$product_ids[] = $product_id;
					}
				}
			}
			wp_cache_set('codecorun_cached_purchased-'.$current_user->ID, $product_ids);
			return $product_ids;

		}
		
	}


}