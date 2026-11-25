<?php
$products = new WP_Query([
    'post_type' => 'product',
    'posts_per_page' => 6,
    'meta_key' => '_vrp_packaging_label',
    'meta_value' => '700 ml',
    'orderby' => 'menu_order title',
    'order' => 'ASC',
]);
?>
<section class="section product-preview">
    <div class="container">
        <div class="section-heading split">
            <div><span class="eyebrow"><?php echo esc_html(vrp_get_text_setting('vrp_products_eyebrow')); ?></span><h2><?php echo esc_html(vrp_get_text_setting('vrp_products_title')); ?></h2></div>
            <p><?php echo esc_html(vrp_get_text_setting('vrp_products_content')); ?></p>
        </div>
        <div class="featured-grid">
            <?php if ($products->have_posts()) : while ($products->have_posts()) : $products->the_post(); ?>
                <?php get_template_part('template-parts/product-card'); ?>
            <?php endwhile; wp_reset_postdata(); else : ?>
                <p><?php esc_html_e('Dodajte proizvode u administraciji kako bi se prikazali ovdje.', 'vocne-rakije-petkovic'); ?></p>
            <?php endif; ?>
        </div>
        <div class="center-action"><a class="button button-outline" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_products_button')); ?></a></div>
    </div>
</section>
