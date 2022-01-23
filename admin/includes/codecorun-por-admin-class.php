<?php
/**
 * 
 * codecorun_por_admin_class
 * @since 1.0.0
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
    }

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
			'supports'           => array( 'title', 'excerpt' )
		);
		register_post_type( 'codecorun-por', $args );
    }
}
?>