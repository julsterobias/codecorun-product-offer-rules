<?php 
if( isset( $params['offers'] ) ):
?>
<div class="codecorun-por-content">
    <?php if( isset( $params['settings']['offer_tile'] ) ): ?>
        <h3><?php echo esc_attr( $params['settings']['offer_tile'] ); ?></h3>
    <?php endif; ?>
    <ul class="codecorun-por-list codecorun-por-style-<?php echo ( isset( $params['style'] ) )? esc_attr( $params['style'] ) : null; ?>">
        <?php foreach( $params['offers'] as $offers ): ?>
        <li>
            <div>
            <?php if( !empty( $offers['image'] ) ): ?>
                <a href=""><img src="<?php echo esc_attr( $offers['image'] ); ?>" title="<?php echo esc_attr( $offers['title'] ); ?>"></a>
                <a href=""><span class="codecorun-por-title codecorun-por-attrs"> <?php echo esc_attr( $offers['title'] ); ?> </span></a>
                <span class="codecorun-por-price codecorun-por-attrs"> <?php echo $offers['price']; ?> </span>
                <a href="" class="button">Add to cart</a>
            <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php 
endif;
?>