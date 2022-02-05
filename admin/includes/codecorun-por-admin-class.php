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
		wp_register_script( CODECORUN_POR_PREFIX.'-admin-assets-js', CODECORUN_POR_URL.'admin/assets/admin.js', array('jquery') );
		wp_enqueue_script( CODECORUN_POR_PREFIX.'-admin-assets-js' );
		wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
        wp_localize_script( CODECORUN_POR_PREFIX.'-admin-assets-js', 'codecorun_por_nonce', wp_create_nonce('codecorun_por')); 
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
            'codecorun-por-meta-offer',
            __('Setup', 'codecorun-product-offer-rules'),
            [$this,'meta_html'],
            'codecorun-por',
          	'normal',
            'core'
        );


	}

	/**
	 * 
	 * 
	 * 
	 * 
	 * 
	 */
	public function meta_html()
	{
		$this->set_template('setup',['other' => 'admin']);
	}

	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function codecorun_offer_product_options()
	{
		if ( ! wp_verify_nonce( $_GET['nonce'], 'codecorun_por' ) ) {
            //do not echo anything will scare the cat
            exit();
        }
        $search = sanitize_text_field($_GET['search']);
        $args = [
            'posts_per_page' => -1,
            'post_type' => 'product',
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

}
?>