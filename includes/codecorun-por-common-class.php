<?php
/**
 * 
 * codecorun_por_common_class
 * @since 1.0.0
 * @author codecorun
 * 
 */
namespace codecorun\por\common;

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

		$path = get_template_directory().'/'.end($plugin_folder).'/'.$other.'templates';
		$child = get_template_directory().'-child/'.end($plugin_folder).'/'.$other.'templates';

		if(is_dir($path.'/'.$file)){
			include $path.'/'.$file;
		}elseif(is_dir($child.'/'.$file)){
			include $child.'/'.$file;
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
					'have_url_param' => __('Have URL Parameter', 'codecorun-product-offer-rules')
				]
			]
		);
	}

	/**
	 * 
	 * get_post_details
	 * @since 1.0.0
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


}