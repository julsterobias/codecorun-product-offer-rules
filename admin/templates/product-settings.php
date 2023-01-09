<div>
    <p>
        <label><?php _e('Shortcode', 'codecorun-product-offer-rules'); ?>
            <input type="text" class="widefat" readonly value="<?php esc_html_e($params['shortcode'], 'codecorun-product-offer-rules') ?>">
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="codecorun_setting_field_enable_slider" class="codecorun_por_enable_carousel" value="Y" <?php echo ( !empty( $params['settings'] ) )? esc_attr('checked') : null; ?>> <?php _e('Enable Carousel', 'codecorun-product-offer-rules'); ?>
        </label>
    </p>
    <div class="codecorun_por_slider_setting_con codecorun_por_conditional <?php echo ( !empty( $params['settings'] ) )? esc_attr('active') : null; ?>">
        <p>
            <label><?php _e('Number of post', 'codecorun-product-offer-rules'); ?>
                <input type="number" class="widefat" name="codecorun_setting_field[codecorun_por_slider_post_number]" value="<?php echo ( !empty( $params['settings']['codecorun_por_slider_post_number'] ) )? esc_attr($params['settings']['codecorun_por_slider_post_number']) : null; ?>" placeholder="0">
            </label>
        </p>
        <p>
            <label><?php _e('Delay', 'codecorun-product-offer-rules'); ?>
                <input type="number" class="widefat" name="codecorun_setting_field[codecorun_por_slider_delay]" value="<?php echo ( !empty( $params['settings']['codecorun_por_slider_delay'] ) )? esc_attr($params['settings']['codecorun_por_slider_delay']) : null; ?>" placeholder="Milliseconds">
            </label>
        </p>
        <?php 
            $animate_type = ( !empty( $params['settings']['codecorun_por_slider_delay'] ) )? $params['settings']['codecorun_por_slider_animation'] : null;
        ?>
        <p>
            <label><?php _e('Animation', 'codecorun-product-offer-rules'); ?><br/>
                <select name="codecorun_setting_field[codecorun_por_slider_animation]" class="codecorun_por_slider_animation">
                    <option value="slide" <?php echo ( $animate_type == 'slide' )? 'selected' : null; ?>><?php esc_html_e('Slide', 'codecorun-product-offer-rules'); ?></option>
                    <option value="fade" <?php echo ( $animate_type == 'fade' )? 'selected' : null; ?>><?php esc_html_e('Fade', 'codecorun-product-offer-rules'); ?></option>
                </select>
            </label>
        </p>
    </div>
</div>