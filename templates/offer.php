<?php 
if( isset( $params['offers'] ) ):
?>
<div class="codecorun-por-content">
    <?php if( isset( $params['settings']['offer_tile'] ) ): ?>
        <h3><?php echo esc_attr( $params['settings']['offer_tile'] ); ?></h3>
    <?php endif; 
        $slider_class = '';
        $slider_attr = '';
        if( isset($params['settings']['codecorun_por_slider_settings']) ){
            $slider_class = 'codecorun-por-slick';
            $slider_class_id = 'codecorun-por-slick-'.esc_attr( $params['id'] );
            $data_per_slide = ( isset($params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_post_number']) )? 'data-slide-post-per-page='.esc_attr( $params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_post_number'] ) : null;
            $data_delay = ( isset($params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_delay']) )? 'data-slide-delay='.esc_attr( $params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_delay'] ) : null;
            $data_animation = ( isset($params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_animation']) )? 'data-slide-animation='.esc_attr( $params['settings']['codecorun_por_slider_settings']['codecorun_por_slider_animation'] ) : null;
            $data_id = ( isset( $params['id'] ) )? 'data-slide-id='.esc_attr( $params['id'] ) : null;

            $slider_attr = $data_per_slide.' '.$data_delay.' '.$data_animation.' '.$data_id;
            $slider_class .= ' '.$slider_class_id;
        }
    ?>
    <ul class="codecorun-por-list codecorun-por-style-<?php echo ( isset( $params['style'] ) )? esc_attr( $params['style'] ) : null; ?> <?php esc_attr_e( $slider_class ); ?>" <?php echo esc_attr( $slider_attr ); ?>>
        <?php foreach( $params['offers'] as $offer ):  ?>
        <li>
            <div>
            <?php if( !empty( $offer['image'] ) ): ?>
                <a href="<?php echo esc_attr( get_the_permalink( $offer['id'] ) ); ?>"><img src="<?php echo esc_attr( $offer['image'] ); ?>" title="<?php echo esc_attr( $offer['title'] ); ?>"></a>
                <a href="<?php echo esc_attr( get_the_permalink( $offer['id'] ) ); ?>"><span class="codecorun-por-title codecorun-por-attrs"> <?php echo esc_html( $offer['title'] ); ?> </span></a>
                <span class="codecorun-por-price codecorun-por-attrs"><?php echo html_entity_decode( esc_html( $offer['price'] ) ); ?></span>
                <a href="<?php echo wc_get_cart_url(); ?>?add-to-cart=<?php echo esc_attr( $offer['id'] ); ?>" target="_blank" class="button"><?php esc_html_e('Add to cart', 'codecorun-product-offer-rules'); ?></a>
            <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php 
endif;
?>