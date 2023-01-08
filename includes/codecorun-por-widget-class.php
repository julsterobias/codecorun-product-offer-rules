<?php
/**
 * 
 * codecorun_por_admin_widget
 * class to register cusotm widget
 * @since 1.0.0
 * @author codecorun
 * 
 * 
 */
class codecorun_por_admin_widget extends \WP_Widget {
 
	function __construct() {
	    parent::__construct(
            'codecorun_widget', __('Codecorun Offers', 'codecorun-product-offer-rules'), 
	        array( 'description' => __( 'Widget for codecorun product offer rules', 'codecorun-product-offer-rules' ), )
        );
	}

	public function widget( $args, $instance ) {

        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];

        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
        
        // This is where you run the code and display the output
        //echo __( 'Hello, World!', 'wpb_widget_domain' );
        echo $args['after_widget'];
	}

	public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = '';
        }
        ?>
            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'codecorun-product-offer-rules' ); ?></label>
                <input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php esc_html_e( $title ); ?>" />
            </p>

            <?php $offers = $this->get_offers_rules(); ?>
            <p>
                <label>
                    <?php if( $offers ): ?>
                    <select id="<?php esc_attr_e( $this->get_field_id( 'codecorun_offers' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'codecorun_offers' ) ); ?>">
                        <?php foreach( $offers as $offer ): ?>
                        <option value="<?php esc_attr_e($offer['id']); ?>"><?php esc_html_e($offer['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                        <center>No offers available</center>
                    <?php endif; ?>
                </label>
            </p>
	    <?php
	}
	 
	public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
	}

    /**
     * 
     * get_offers_rules
     * @since 1.0.1
     * @param
     * @return []
     * 
     */
    public function get_offers_rules()
    {
        $args = [
            'numberposts' => -1,
            'post_type' => 'codecorun-por',
            'post_status' => 'publish'
        ];
        $offers = get_posts( $args );
        if( !empty( $offers ) ){
            $data = [];
            foreach( $offers as $offer ){
                $data[] = [
                    'id' => $offer->ID,
                    'text' => $offer->ID.' - '.$offer->post_title
                ];
            }
            return $data;
        }else{
            return;
        }
    }

} 

add_action( 'widgets_init', 'codecorun_por_widget');
function codecorun_por_widget()
{
    register_widget( 'codecorun_por_admin_widget' );
}
?>