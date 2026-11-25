<?php
$product_id = get_the_ID();
$product_url = vrp_get_product_family_url($product_id);
$terms = get_the_terms($product_id, 'product_category');
$category_slugs = $terms && !is_wp_error($terms) ? wp_list_pluck($terms, 'slug') : [];
$initial_filter = isset($args['initial_filter']) ? $args['initial_filter'] : '';
$is_hidden = 'varieties' === $initial_filter
    ? in_array('special-offers', $category_slugs, true)
    : ($initial_filter && !in_array($initial_filter, $category_slugs, true));
?>
<article <?php post_class('product-card'); ?> data-product-categories="<?php echo esc_attr(implode(' ', $category_slugs)); ?>"<?php echo $is_hidden ? ' hidden' : ''; ?>>
    <a class="product-image" href="<?php echo esc_url($product_url); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <?php if ($subtitle = get_post_meta($product_id, '_vrp_product_subtitle', true)) : ?><span class="product-fruit"><?php echo esc_html($subtitle); ?></span><?php endif; ?>
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('large'); ?>
        <?php else : ?>
            <img src="<?php echo esc_url(vrp_asset_url('logo.png')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
        <?php endif; ?>
        <span class="product-arrow">↗</span>
    </a>
    <div class="product-info">
        <span class="product-category"><?php echo esc_html($terms && !is_wp_error($terms) ? $terms[0]->name : __('Proizvod', 'vocne-rakije-petkovic')); ?></span>
        <h3><a href="<?php echo esc_url($product_url); ?>"><?php the_title(); ?></a></h3>
        <p><?php echo esc_html(get_the_excerpt()); ?></p>
        <div class="product-bottom"><a href="<?php echo esc_url($product_url); ?>"><?php esc_html_e('Saznaj više', 'vocne-rakije-petkovic'); ?></a></div>
    </div>
</article>
