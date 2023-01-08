<?php
/**
 * 
 * codecorun_por_admin_class
 * @since 1.0.0
 * @author codecorun
 * 
 */
namespace codecorun\por\admin;
use codecorun\por\common\codecorun_por_common_class;

defined( 'ABSPATH' ) or die( 'No access area' );

class codecorun_por_admin_class extends codecorun_por_common_class
{
    private static $instance = null;

    /**
     * 
     * factory instance method
     * @since 1.0.0
     * static
     * 
     */
    public static function factory()
    {
        if(!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 
     * __constructor
     * @since 1.0.0
     * 
     * 
     */
    public function __construct()
    {
        add_action('init',[$this, 'register_post_type']);
		add_filter('manage_codecorun-por_posts_columns' , [$this, 'table_tabs']);
		add_action('manage_codecorun-por_posts_custom_column', [$this,'table_tabs_content'], 10, 2 );
		add_action('add_meta_boxes',[$this,'meta_box']);
		add_action('admin_enqueue_scripts',[$this, 'assets']);
		add_action('wp_ajax_codecorun_offer_product_options',[$this,'codecorun_offer_product_options']);
		add_action('wp_ajax_codecorun_offer_page_options',[$this,'codecorun_offer_page_options']);
		add_action('wp_ajax_codecorun_offer_post_page_options',[$this, 'codecorun_offer_post_page_options']);
		add_action('save_post_codecorun-por', [$this, 'save_rules']);

		if(isset($_GET['por_debug'])){
			print_r(get_post_meta($_GET['por_debug'],'codecorun-por-rules',true));
			echo '===============<br/>';
			print_r(get_post_meta($_GET['por_debug'],'codecorun-por-offers',true));
			die();
		}
    }
	/**
	 * 
	 * 
	 * 
	 * 
	 * 
	 */
	public function assets()
	{
		global $pagenow;
		$abort = false;

		//load assets in pages related to plugin only
		if( $pagenow == 'post.php' ){

			if( !isset( $_GET['post'] ) && !isset( $_GET['action'] ) )
				$abort = true;
				
			if( get_post_type($_GET['post']) != 'codecorun-por' )
				$abort = true;

		}elseif ( $pagenow == 'post-new.php' ){

			if( !isset( $_GET['post_type'] ) )
				$abort = true;

			if( $_GET['post_type'] != 'codecorun-por' )
				$abort = true;

		}

		if($abort)
			return;

		wp_register_script( CODECORUN_POR_PREFIX.'-admin-assets-js', CODECORUN_POR_URL.'admin/assets/admin.js', array('jquery') );
		wp_enqueue_script( CODECORUN_POR_PREFIX.'-admin-assets-js' );
		wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
        wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_nonce', wp_create_nonce('codecorun_por')); 

		if( isset( $_GET['post'] ) ){

			$post_id = sanitize_text_field($_GET['post']);
			$offers = get_post_meta( $post_id, 'codecorun-por-offers', true );
			$rules = get_post_meta( $post_id, 'codecorun-por-rules', true );
			if( $offers ){
				$offers = $this->prepare_offers_data( $offers );
				$offers = json_encode( $offers );
			}else{
				$offers = null;
			}
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_offers', $offers ); 

			if( $rules ){
				$rules = $this->prepare_rules_data( $rules );

				if( isset( $_GET['debug']) ){
					print_r($rules);
					die();
				}
				

				$rules = json_encode( $rules );
			}else{
				$rules = null;
			}
			
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_rules', $rules ); 
		}else{
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_offers', null ); 
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_rules', null ); 
		}

		wp_enqueue_script('selectWoo');
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style(CODECORUN_POR_PREFIX.'-admin-assets-css', CODECORUN_POR_URL.'admin/assets/admin.css');
		wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_rules', $this->rules()['woo']);
	}

	/**
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 */
    public function register_post_type()
    {
        $labels = array(
			'name'               => _x( 'Product Offer Rules', 'Product Offer Rules', 'codecorun-product-offer-rules' ),
			'singular_name'      => _x( 'Product Offer Rule', 'Product Offer Rule', 'codecorun-product-offer-rules' ),
			'menu_name'          => _x( 'Product Offer Rules', 'codecorun-por', 'codecorun-product-offer-rules' ),
			'name_admin_bar'     => _x( 'Product Offer Rule', 'add new rule', 'codecorun-product-offer-rules' ),
			'add_new'            => _x( 'Add New', 'Product Offer Rule', 'codecorun-product-offer-rules' ),
			'add_new_item'       => __( 'Add New Product Offer Rule', 'codecorun-product-offer-rules' ),
			'new_item'           => __( 'New Product Offer Rule', 'codecorun-product-offer-rules' ),
			'edit_item'          => __( 'Edit Product Offer Rule', 'codecorun-product-offer-rules' ),
			'view_item'          => __( 'View Product Offer Rules', 'codecorun-product-offer-rules' ),
			'all_items'          => __( 'All Rules', 'codecorun-product-offer-rules' ),
			'search_items'       => __( 'Search Product Offer Rules', 'codecorun-product-offer-rules' ),
			'parent_item_colon'  => __( 'Parent Product Offer Rule:', 'codecorun-product-offer-rules' ),
			'not_found'          => __( 'No Product Offer Rules found.', 'codecorun-product-offer-rules' ),
			'not_found_in_trash' => __( 'No Product Offer Rules found in Trash.', 'codecorun-product-offer-rules' )
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Description.', 'codecorun-product-offer-rules' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => array( 'slug' => 'codecorun-por' ),
			'menu_icon'			 => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNS4yLjMsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAxNTAgMTUwIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxNTAgMTUwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPg0KCS5zdDB7ZmlsbDojRkZGRkZGO30NCjwvc3R5bGU+DQo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTMwLjIsMTIwLjRsMy4yLTcyLjVIMTE0QzExNCwzLDc1LDQuNSw3NSw0LjVTMzYsMywzNiw0Ny45SDE2LjZsMy4yLDcyLjVsLTUuNCwyMkg3NWg2MC43TDEzMC4yLDEyMC40eg0KCSBNNDguMiwxMjMuNkw2NSw2Mi44aDEwLjlsLTE2LjgsNjAuOEg0OC4yeiBNODMuMSwxMjMuNkg3Mi4yTDg5LDYyLjhoMTAuOUw4My4xLDEyMy42eiBNNzQuOCw0Ny40SDQ4LjJjMC0zMi40LDI2LjctMzEuOCwyNi43LTMxLjgNCglzMjYuNy0wLjUsMjYuNywzMS44SDc0Ljh6Ii8+DQo8L3N2Zz4NCg==',
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'supports'           => array( 'title' )
		);
		register_post_type( 'codecorun-por', $args );
    }

	/**
	 * 
	 * table_tabs
	 * @since 1.0.0
	 * @param array
	 * @return array
	 * 
	 */
	public function table_tabs($columns)
	{	
		array_splice( $columns, 2, 0, [__('Shortcode','codecorun-product-offer-rules')] );
		array_splice( $columns, 3, 0, [__('Author','codecorun-product-offer-rules')] );
		array_splice( $columns, 4, 0, [__('Expiry','codecorun-product-offer-rules')] );
		array_splice( $columns, 5, 0, [__('Status','codecorun-product-offer-rules')] );
		return $columns;
	}

	/**
	 * 
	 * table_tabs_content
	 * @since 1.0.0
	 * @param mixed, int
	 * @return
	 * 
	 */
	public function table_tabs_content($column, $post_id)
	{
		switch($column){
			case 0:
				break;
			case 1:
				$post_author_id = get_post_field( 'post_author', $post_id );
				$user = get_userdata($post_author_id);
				echo esc_attr($user->display_name);
				break;
			case 2:
				break;
			case 3:
				break;
		}
	}

	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function meta_box()
	{

		add_meta_box(
            'codecorun-por-meta-offer-products',
            __('Products', 'codecorun-product-offer-rules'),
            [$this,'meta_product_html'],
            'codecorun-por',
          	'normal',
            'core'
        );

		add_meta_box(
            'codecorun-por-meta-offer',
            __('Rules', 'codecorun-product-offer-rules'),
            [$this,'meta_html'],
            'codecorun-por',
          	'normal',
            'core'
        );


	}


	/**
	 * 
	 * 
	 * meta_product_html
	 * 
	 */
	public function meta_product_html()
	{
		$this->set_template('product-setup',['other' => 'admin']);
	}

	/**
	 * 
	 * 
	 * meta_html
	 * 
	 * 
	 */
	public function meta_html( $post )
	{
		$this->set_template('setup',['other' => 'admin']);
	}


	/**
	 * 
	 * codecorun_offer_post_page_options
	 * @since 1.0.0
	 * @param
	 * @return
	 * 
	 * 
	 */
	public function codecorun_offer_post_page_options()
	{
		if ( ! wp_verify_nonce( $_GET['nonce'], 'codecorun_por' ) ) {
            //do not echo anything will scare the cat
            exit();
        }
        $search = sanitize_text_field($_GET['search']);
		$type = sanitize_text_field($_GET['post_type']);
		$type = (isset($type))? $type : 'post';
        $args = [
            'posts_per_page' => -1,
            'post_type' => $type,
            's' => $search,
            'post_status' => 'publish'
        ];

        $args = apply_filters('cpor_search_args', $args);

        $results = get_posts($args);
        $res = [];
        if($results){
            foreach($results as $result){
                $res[] = [
                    'id' => $result->ID,
                    'text' => $result->post_title
                ];
            }
        }
        
        echo json_encode($res);
        exit;
	}

	/**
	 * 
	 * save_rules
	 * @since 1.0.0
	 * @param int
	 * 
	 * 
	 */
	public function save_rules( $post_id ){

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;


        $offer_rules = (isset($_POST['codecorun_por_field']))? $_POST['codecorun_por_field'] : null;

		// save product to offers
		if( !empty($_POST['codecorun_por_product_offers']) ){
			$to_offer = array_map( function($data){
				return sanitize_text_field($data);
			}, $_POST['codecorun_por_product_offers']);
			update_post_meta( $post_id, 'codecorun-por-offers', $to_offer );
		}else{
			delete_post_meta( $post_id, 'codecorun-por-offers' );
		}
		//reset cached
		delete_transient('codecorun_por_offers_cached');

        if(!$offer_rules){
            delete_post_meta($post_id,'codecorun-por-rules');
			delete_transient('codecorun_por_rules_cached');
            return;
        }
            

        //let's sanitize
        foreach($offer_rules as $index => $rules){
            if(!is_array($rules)){
                $offer_rules[$index] = sanitize_text_field($rules);
            }else{
                //reloop
                //check index and do something for meta & params
                foreach($rules as $i => $rule){
                    $offer_rules[$index][$i] = sanitize_text_field($rule);
                }
            }
        }

        //reloop and check for index to reformat their data structure
        foreach($offer_rules as $index => $rules){
            $what_index = explode('-', $index);
            if($what_index[0] == 'have_metas' || $what_index[0] == 'have_url_param'){
                //do reformatting
                $prev_key = null;
                $new_format_ = [];
                foreach($rules as $i => $rule){
                    if($i % 2 == 0){
                        $prev_key = $rule;
                    }else{
                        $meta_param = [
                            'key' => $prev_key,
                            'value' => $rule
                        ];
                        $new_format_[] = $meta_param;
                        $meta_param = null;
                        $prev_key = null;
                    }
                }
                $offer_rules[$index] =  $new_format_;
            } 
        }

        //save to post meta
        if(!empty($offer_rules)){
            update_post_meta($post_id,'codecorun-por-rules',$offer_rules);
			//reset cache
			delete_transient('codecorun_por_rules_cached');
        }
	}


	/**
	 * 
	 * 
	 * prepare_rules_data
	 * @since 1.0.0
	 * @param array
	 * @return array
	 * 
	 * 
	 */
	public function prepare_rules_data( $rules = [] )
	{
		if( empty( $rules ) )
			$rules;

		if( get_transient('codecorun_por_rules_cached') ){
			return get_transient('codecorun_por_rules_cached');
		}

		$all_ids = [];

		foreach( $rules as $index => $rule ){
			$new_index = explode( '-', $index );
			switch( $new_index[0] ){
				case 'codecorun_dy_field_in_cart_products':
				case 'codecorun_dy_field_had_purchased':
				case 'codecorun_dy_field_last_views':
				case 'codecorun_dy_field_had_purchased':
				case 'codecorun_dy_field_in_product_page':
					if( is_array($rule) ){
						foreach( $rule as $r ){
							$all_ids[] = $r;
						}
					}else{
						$all_ids[] = $rule;
					}
				break;
				case 'codecorun_dy_field_in_page':
					if( is_array($rule) ){
						foreach( $rule as $r ){
							$all_ids[] = $r;
						}
					}else{
						$all_ids[] = $rule;
					}
				break;
				case 'codecorun_dy_field_in_post':
					if( is_array($rule) ){
						foreach( $rule as $r ){
							$all_ids[] = $r;
						}
					}else{
						$all_ids[] = $rule;
					}
				break;
			}					
		}

		$post_types = ['product', 'post', 'page'];
		$all_ids = array_unique( $all_ids );
		$all_result = $this->get_post_details(
			[
				'post_type' => $post_types,
				'ids' => $all_ids
			]
		);

		//redistribute the result to their respected data
		foreach( $rules as $index => $rule ){

			$new_index = explode( '-', $index );
			$is_modified = false;
			$new_data = [];
			switch( $new_index[0] ){
				case 'codecorun_dy_field_in_cart_products':
				case 'codecorun_dy_field_had_purchased':
				case 'codecorun_dy_field_last_views':
				case 'codecorun_dy_field_had_purchased':
				case 'codecorun_dy_field_in_product_page':
					$new_data = $this->reassign_data( $rule, $all_result );
					$is_modified = true;
				break;
				case 'codecorun_dy_field_in_page':
					$new_data = $this->reassign_data( $rule, $all_result );
					$is_modified = true;
				break;
				case 'codecorun_dy_field_in_post':
					$new_data = $this->reassign_data( $rule, $all_result );
					$is_modified = true;
				break;
			}	
			
			if( $is_modified ){
				$rules[ $index ] = $new_data;
			}
		}
		
		set_transient('codecorun_por_rules_cached', $rules, '', 0);

		return $rules;

	}

	/**
	 * 
	 * 
	 * reassign_data
	 * @since 1.0.0
	 * @param array - meta result
	 * @param array - queried result
	 * @param string - post type
	 * @return array
	 * 
	 */
	public function reassign_data( $rule, $result )
	{
		$new_data = [];
		if( is_array($rule) ){
			foreach( $rule as $r ){
				foreach( $result as $res ){
					if( $r == $res['id'] ){
						$new_data[] = [
							'id' => $res['id'],
							'title' => $res['title']
						];
					}
				}
			}
		}else{
			foreach( $result as $res ){
				if( $rule == $res['id'] ){
					$new_data[] = [
						'id' => $res['id'],
						'title' => $res['title']
					];
				}
			}
		}

		return $new_data;
	}

	/**
	 * 
	 * 
	 * prepare_offers_data
	 * @since 1.0.0
	 * @param array
	 * @return array
	 * 
	 * 
	 */
	public function prepare_offers_data( $offers )
	{
		if( get_transient('codecorun_por_offers_cached') ){
			return get_transient('codecorun_por_offers_cached');
		}

		$offer_val = $this->get_post_details(
			[
				'post_type' => 'product',
				'ids' => $offers
			]
		);

		set_transient('codecorun_por_offers_cached', $offer_val, '', 0);

		return $offer_val;
	}

}
?>