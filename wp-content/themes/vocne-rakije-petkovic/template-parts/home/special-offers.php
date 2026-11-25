<?php
$offer_codes = ['60', '200', '101'];
$offer_ids = [];
foreach ($offer_codes as $offer_code) {
    $offer = get_posts([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_vrp_product_id',
        'meta_value' => $offer_code,
    ]);
    if ($offer) {
        $offer_ids[] = (int) $offer[0];
    }
}
$offers = new WP_Query([
    'post_type' => 'product',
    'posts_per_page' => 3,
    'post__in' => $offer_ids ?: [0],
    'orderby' => 'post__in',
]);
?>
<section class="special-offer-section">
    <div class="container">
        <div class="section-heading split">
            <div><span class="eyebrow light"><?php echo esc_html(vrp_get_text_setting('vrp_special_eyebrow')); ?></span><h2><?php echo esc_html(vrp_get_text_setting('vrp_special_title')); ?></h2></div>
            <p><?php echo esc_html(vrp_get_text_setting('vrp_special_content')); ?></p>
        </div>
        <?php if ($offers->have_posts()) : ?>
            <div class="special-offer-grid">
                <?php while ($offers->have_posts()) : $offers->the_post(); ?>
                    <a class="special-offer-card" href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) the_post_thumbnail('large'); ?>
                        <div>
                            <span><?php echo esc_html(get_post_meta(get_the_ID(), '_vrp_product_subtitle', true)); ?></span>
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                            <strong class="special-more"><?php esc_html_e('Saznaj više', 'vocne-rakije-petkovic'); ?> →</strong>
                        </div>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
        <?php $special_category = get_term_by('slug', 'special-offers', 'product_category'); ?>
        <div class="center-action"><a class="button button-light" href="<?php echo esc_url($special_category ? get_term_link($special_category) : get_post_type_archive_link('product')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_special_button')); ?></a></div>
    </div>
</section>
