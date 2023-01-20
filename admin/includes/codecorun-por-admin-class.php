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

		if( is_admin() )
			add_action('admin_enqueue_scripts',[$this, 'assets']);

		add_action('wp_ajax_codecorun_offer_product_options',[$this,'codecorun_offer_product_options']);
		add_action('wp_ajax_codecorun_offer_page_options',[$this,'codecorun_offer_page_options']);
		add_action('wp_ajax_codecorun_offer_post_page_options',[$this, 'codecorun_offer_post_page_options']);
		add_action('save_post_codecorun-por', [$this, 'save_rules']);

		if( !is_plugin_active( CODECORUN_POR_PRO_ID ) ){
			add_action('admin_menu',[$this, 'custom_menu']);
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

			if( !isset( $_GET['post_type'] ) ){
				$abort = true;
			}else{
				if( $_GET['post_type'] != 'codecorun-por' )
					$abort = true;
			}
				
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
			$fallback = get_post_meta( $post_id, 'codecorun-por-fallback', true);

			if( $offers ){
				$offers = $this->prepare_offers_data( $offers, $post_id );
				$offers = json_encode( $offers );
			}else{
				$offers = null;
			}
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_offers', $offers ); 
			
			if( $fallback ){
				$fallback = $this->prepare_offers_data( $fallback, $post_id, 'fallback' );
				$fallback = json_encode( $fallback );
			}else{
				$fallback = null;
			}
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_fallback', $fallback ); 

			if( $rules ){
				$rules = $this->prepare_rules_data( $rules, $post_id );
				$rules = json_encode( $rules );
			}else{
				$rules = null;
			}
			
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_rules', $rules ); 
		}else{
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_offers', null ); 
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_rules', null ); 
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_saved_fallback', null ); 
		}

		wp_enqueue_script('selectWoo');
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style(CODECORUN_POR_PREFIX.'-admin-assets-css', CODECORUN_POR_URL.'admin/assets/admin.css');
		wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_rules', $this->rules()['woo']);

		if( is_plugin_active( CODECORUN_POR_PRO_ID ) ){
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_pro_rules', true );
		}else{
			wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_pro_rules', null );
			if( isset( $_GET['page'] ) ){
				if( $_GET['page'] == 'codecorun-por-custom-menu')
					wp_enqueue_style(CODECORUN_POR_PREFIX.'-admin-market-css', CODECORUN_POR_URL.'admin/assets/market.css');
			}
		}

		//load js translatable labels
		wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_ui_labels', json_encode( [
			'products_to_offer' => __( 'Product(s) to offer', 'codecorun-product-offer-rules' ),
			'no_rules_available' => __( 'No rules available', 'codecorun-product-offer-rules' ),
			'add_rule' => __( 'Add Rule', 'codecorun-product-offer-rules' ),
			'select_rule' => __( 'Select Rule', 'codecorun-product-offer-rules' ),
			'date' => __( 'Date', 'codecorun-product-offer-rules' ),
			'date_tip' => __( 'Display the offer if today\'s date is the date selected', 'codecorun-product-offer-rules' ),
			'condition' => __( 'condition', 'codecorun-product-offer-rules' ),
			'and' => __( 'And', 'codecorun-product-offer-rules' ),
			'or' => __( 'Or', 'codecorun-product-offer-rules' ),
			'remove' => __( 'Remove', 'codecorun-product-offer-rules' ),
			'date_range' => __( 'Date Range', 'codecorun-product-offer-rules' ),
			'date_range_tip' => __( 'Display the offer if the date today is within the date range selected', 'codecorun-product-offer-rules' ),
			'from' => __( 'From', 'codecorun-product-offer-rules' ),
			'to' => __( 'To', 'codecorun-product-offer-rules' ),
			'in_cart_products' => __( 'In cart product(s)', 'codecorun-product-offer-rules' ),
			'in_cart_products_tip' => __( 'Display the offers if the user added one or all selected products in their cart', 'codecorun-product-offer-rules' ),
			'in_product_page' => __( 'In product page', 'codecorun-product-offer-rules' ),
			'in_product_page_tip' => __( 'Display the offer if the user is in the product page', 'codecorun-product-offer-rules' ),
			'select_product' => __( 'Select Product', 'codecorun-product-offer-rules' ),
			'select_products' => __( 'Select Product(s)', 'codecorun-product-offer-rules' ),
			'user_is_logged' => __( 'User is logged in (no field to setup)', 'codecorun-product-offer-rules' ),
			'in_pages' => __( 'In page(s)', 'codecorun-product-offer-rules' ),
			'select_pages' => __( 'Select Page(s)', 'codecorun-product-offer-rules' ),
			'in_pages_tip' => __( 'Display the offer if the user is in the page', 'codecorun-product-offer-rules' ),
			'in_posts' => __( 'In post(s)', 'codecorun-product-offer-rules' ),
			'in_posts_tip' => __( 'Display the offer if the user is in the post', 'codecorun-product-offer-rules' ),
			'select_post' => __( 'Select Post(s)', 'codecorun-product-offer-rules' ),
			'last_viewed_products' => __( 'Last viewed product(s)', 'codecorun-product-offer-rules' ),
			'last_viewed_products_tip' => __( 'Display the offer if the user viewed one or all selected products', 'codecorun-product-offer-rules' ),
			'last_purchased_products' => __( 'Latest purchased product(s)', 'codecorun-product-offer-rules' ),
			'last_purchased_products_tip' => __( 'Display the offer if the user purchased one or all selected products', 'codecorun-product-offer-rules' ),
			'has_url_parameters' => __( 'Has URL parameters', 'codecorun-product-offer-rules' ),
			'has_url_parameters_tip' => __( 'Display the offer if the URL has all the parameters', 'codecorun-product-offer-rules'),
			'key' => __( 'Key', 'codecorun-product-offer-rules' ),
			'value' => __( 'Value', 'codecorun-product-offer-rules' ),
			'add' => __( 'Add', 'codecorun-product-offer-rules')
		] ) );
		
	}

	/**
	 * 
	 * custom_menu
	 * @since 1.0.0
	 * 
	 * 
	 */
	public function custom_menu()
	{
		add_submenu_page(
			'edit.php?post_type=codecorun-por',
			__( 'Full Version', 'codecorun-product-offer-rules' ),
			'<span style="color:#ff008c;">'.esc_html__( 'Full Version', 'codecorun-product-offer-rules' ).'</span>',
			'manage_options',
			'codecorun-por-custom-menu',
			[$this, 'code_menu']
		);
	}

	public function code_menu()
	{
		$this->set_template('full-version',['other' => 'admin']);
	}

	
	/**
	 * 
	 * 
	 * register_post_type
	 * @since 1.0.0
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
	public function table_tabs_content( $column, $post_id )
	{
		$post_id = sanitize_text_field( $post_id );
		switch($column){
			case 0:
				echo '[codecorun-offers id="' . $post_id . '"]';
				break;
			case 1:
				$post_author_id = get_post_field( 'post_author', $post_id );
				$user = get_userdata($post_author_id);
				echo '<a href="user-edit.php?user_id='.esc_attr( $post_author_id ).'" target="_blank">'.esc_html( $user->user_login ).'</a>';
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

		add_meta_box(
            'codecorun-por-meta-offer-fallback',
            __('Fallback offers', 'codecorun-product-offer-rules'),
            [$this,'fallback_html'],
            'codecorun-por',
          	'normal',
            'core'
        );

		add_meta_box(
            'codecorun-por-meta-offer-settings',
            __('Settings', 'codecorun-product-offer-rules'),
            [$this,'setting_html'],
            'codecorun-por',
          	'side',
            'high'
        );


	}

	/**
	 * 
	 * setting_html
	 * @since 1.0.0
	 * 
	 */
	public function setting_html( $post )
	{	
		//check if has data
		$rules = get_transient('codecorun_por_rules_cached-'.$post->ID);
		$offers = get_transient('codecorun_por_offers_cached-'.$post->ID);
		$shortcode = ( $rules && $offers )? '[codecorun-offers id="' . $post->ID . '"]' : '';

		$settings = get_post_meta( $post->ID, 'codecorun_por_settings', true);
		$this->set_template('product-settings',['other' => 'admin', 'shortcode' => $shortcode, 'settings' => $settings ]);
	}


	/**
	 * 
	 * 
	 * fallback_html
	 * @since 1.0.0
	 * 
	 * 
	 */
	public function fallback_html()
	{
		$this->set_template('fallback',['other' => 'admin']);
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

		$offer_settings = [];
		
		if( isset( $_POST['codecorun_setting_offer_title'] ) ){
			$offer_settings['offer_tile'] = sanitize_text_field( $_POST['codecorun_setting_offer_title'] );
		}

		//save settings
		if(	isset( $_POST['codecorun_setting_field_enable_slider'] ) ){
			$settings = [];
			foreach( $_POST['codecorun_setting_field'] as $index => $set ){
				if( !empty( $set ) ){
					$settings[ $index ] = sanitize_text_field( $set );
				}
			}
			$offer_settings[ 'codecorun_por_slider_settings' ] = $settings;
		}else{
			if( isset( $offer_settings[ 'codecorun_por_slider_settings' ] ) ){
				unset( $offer_settings[ 'codecorun_por_slider_settings' ] );
			}
		}

		if( !empty( $offer_settings ) ){
			update_post_meta( $post_id, 'codecorun_por_settings', $offer_settings);
		}else{
			delete_post_meta( $post_id, 'codecorun_por_settings' );
		}


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
		delete_transient('codecorun_por_offers_cached-'.$post_id);


		// save product to fallback
		if( !empty($_POST['codecorun_por_product_fallback']) ){
			$to_offer = array_map( function($data){
				return sanitize_text_field($data);
			}, $_POST['codecorun_por_product_fallback']);
			update_post_meta( $post_id, 'codecorun-por-fallback', $to_offer );
		}else{
			delete_post_meta( $post_id, 'codecorun-por-fallback' );
		}
		//reset cached
		delete_transient('codecorun_por_fallback_cached-'.$post_id);
		

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
                            'key' => sanitize_text_field( $prev_key ),
                            'value' => sanitize_text_field( $rule )
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
			delete_transient('codecorun_por_rules_cached-'.$post_id);
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
	public function prepare_rules_data( $rules = [], $post_id )
	{
		if( empty( $rules ) )
			$rules;

		if( get_transient('codecorun_por_rules_cached-'.$post_id) ){
			return get_transient('codecorun_por_rules_cached-'.$post_id);
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
		
		set_transient('codecorun_por_rules_cached-'.$post_id, $rules, '', 0);

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
	public function prepare_offers_data( $offers, $post_id, $type = 'offers' )
	{
		if( get_transient('codecorun_por_'.$type.'_cached-'.$post_id) ){
			return get_transient('codecorun_por_'.$type.'_cached-'.$post_id);
		}

		$offer_val = $this->get_post_details(
			[
				'post_type' => 'product',
				'ids' => $offers
			]
		);

		set_transient('codecorun_por_'.$type.'_cached-'.$post_id, $offer_val, '', 0);

		return $offer_val;
	}

}
?>