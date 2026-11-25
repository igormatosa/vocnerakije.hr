<?php get_header(); ?>
<main>
    <section class="page-hero">
        <div class="container">
            <?php
            $is_category_archive = is_tax('product_category');
            $category_term = $is_category_archive ? get_queried_object() : null;
            $category_data = $is_category_archive ? vrp_get_product_category_seo_data($category_term) : [];
            $archive_title = $is_category_archive && $category_term instanceof WP_Term
                ? $category_term->name
                : vrp_get_product_archive_setting('vrp_product_archive_title');
            $archive_intro = $is_category_archive
                ? ($category_data['intro'] ?? '')
                : vrp_get_product_archive_setting('vrp_product_archive_intro');
            ?>
            <div class="breadcrumbs">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Početna', 'vocne-rakije-petkovic'); ?></a>
                / <a href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php esc_html_e('Proizvodi', 'vocne-rakije-petkovic'); ?></a>
                <?php if ($is_category_archive && $category_term instanceof WP_Term) : ?> / <?php echo esc_html($category_term->name); ?><?php endif; ?>
            </div>
            <span class="eyebrow"><?php echo $is_category_archive ? esc_html__('Kategorija proizvoda', 'vocne-rakije-petkovic') : esc_html__('Naša ponuda', 'vocne-rakije-petkovic'); ?></span>
            <h1><?php echo esc_html($archive_title); ?></h1>
            <div class="lead"><?php echo wpautop(esc_html($archive_intro)); ?></div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <?php
            $categories = get_terms(['taxonomy' => 'product_category', 'hide_empty' => false, 'orderby' => 'term_order', 'order' => 'ASC']);
            $initial_filter = is_tax('product_category') ? get_queried_object()->slug : 'varieties';
            $catalog_query = new WP_Query([
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'tax_query' => $is_category_archive ? [[
                    'taxonomy' => 'product_category',
                    'field' => 'term_id',
                    'terms' => $category_term->term_id,
                ]] : [],
                'meta_query' => [
                    [
                        'key' => '_vrp_is_product_family',
                        'value' => '1',
                    ],
                ],
                'orderby' => 'menu_order title',
                'order' => 'ASC',
            ]);
            if ($categories && !is_wp_error($categories) && $is_category_archive) :
            ?>
                <nav class="filter-row" aria-label="<?php esc_attr_e('Kategorije proizvoda', 'vocne-rakije-petkovic'); ?>">
                    <a class="filter-button" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php esc_html_e('Sve sorte', 'vocne-rakije-petkovic'); ?></a>
                    <?php foreach ($categories as $category) :
                        $category_link = get_term_link($category);
                        if (is_wp_error($category_link)) {
                            continue;
                        }
                        ?>
                        <a class="filter-button<?php echo $category->term_id === $category_term->term_id ? ' active' : ''; ?>" href="<?php echo esc_url($category_link); ?>"<?php echo $category->term_id === $category_term->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html($category->name); ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php elseif ($categories && !is_wp_error($categories)) : ?>
                <nav class="filter-row" aria-label="<?php esc_attr_e('Kategorije proizvoda', 'vocne-rakije-petkovic'); ?>" data-product-filters data-initial-filter="<?php echo esc_attr($initial_filter); ?>">
                    <button class="filter-button<?php echo 'varieties' === $initial_filter ? ' active' : ''; ?>" type="button" data-product-filter="varieties" aria-pressed="<?php echo 'varieties' === $initial_filter ? 'true' : 'false'; ?>"><?php esc_html_e('Sve sorte', 'vocne-rakije-petkovic'); ?></button>
                    <?php foreach ($categories as $category) : ?>
                        <button class="filter-button<?php echo $category->slug === $initial_filter ? ' active' : ''; ?>" type="button" data-product-filter="<?php echo esc_attr($category->slug); ?>" aria-pressed="<?php echo $category->slug === $initial_filter ? 'true' : 'false'; ?>"><?php echo esc_html($category->name); ?></button>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <div class="pricebook-note">
                <div class="pricebook-intro">
                    <span><?php esc_html_e('Cjenik proizvoda', 'vocne-rakije-petkovic'); ?></span>
                    <strong><?php esc_html_e('Otkrijte našu ponudu rakija i likera.', 'vocne-rakije-petkovic'); ?></strong>
                    <p><?php esc_html_e('Za detalje i dostupna pakiranja odaberite „Saznaj više”.', 'vocne-rakije-petkovic'); ?></p>
                </div>
                <div class="pricebook-actions">
                    <?php if (is_file(vrp_price_list_current_path())) : ?>
                        <a class="pricebook-link pricebook-link-primary" href="<?php echo esc_url(home_url('/cjenik/roba.csv')); ?>"><?php esc_html_e('Preuzmi važeći cjenik (CSV)', 'vocne-rakije-petkovic'); ?></a>
                    <?php endif; ?>
                    <a class="pricebook-link" href="<?php echo esc_url(home_url('/cjenik/arhiva/')); ?>"><?php esc_html_e('Arhiva cjenika', 'vocne-rakije-petkovic'); ?></a>
                </div>
            </div>
            <div class="catalog-grid">
                <?php if ($catalog_query->have_posts()) : while ($catalog_query->have_posts()) : $catalog_query->the_post(); ?>
                    <?php get_template_part('template-parts/product-card', null, ['initial_filter' => $initial_filter]); ?>
                <?php endwhile; else : ?>
                    <p><?php esc_html_e('Trenutno nema proizvoda u ovoj kategoriji.', 'vocne-rakije-petkovic'); ?></p>
                <?php endif; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
