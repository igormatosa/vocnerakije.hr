<?php get_header(); ?>
<main>
    <?php
    while (have_posts()) :
        the_post();
        $product_id = get_the_ID();
        $packaging = vrp_get_product_packaging($product_id);
        $gallery_ids = vrp_get_product_gallery($product_id);
        $subtitle = get_post_meta($product_id, '_vrp_product_subtitle', true);
        $default_index = count($gallery_ids) > 4 ? 4 : 0;
        $gallery_slides = [];
        foreach ($gallery_ids as $index => $image_id) {
            $variant_id = absint($packaging[$index]['variant_product_id'] ?? 0);
            if (!$variant_id) {
                $variant_id = wp_get_post_parent_id($image_id);
            }
            $variant = $variant_id ? get_post($variant_id) : null;
            $gallery_slides[] = [
                'image_id' => $image_id,
                'label' => $packaging[$index]['label'] ?? sprintf(__('Slika %d', 'vocne-rakije-petkovic'), $index + 1),
                'title' => $variant && $variant_id !== $product_id ? get_the_title($variant) : '',
                'description' => $variant ? wp_strip_all_tags($variant->post_content) : '',
            ];
        }
        $default_slide = $gallery_slides[$default_index] ?? [];
        ?>
        <section class="product-hero">
            <div class="container product-detail-grid">
                <div class="detail-gallery product-gallery">
                    <button class="gallery-main" type="button" aria-label="<?php esc_attr_e('Povećaj fotografiju proizvoda', 'vocne-rakije-petkovic'); ?>">
                        <?php
                        if ($gallery_ids) {
                            echo wp_get_attachment_image($gallery_ids[$default_index], 'large', false, ['alt' => get_post_meta($gallery_ids[$default_index], '_wp_attachment_image_alt', true) ?: get_the_title()]);
                        } elseif (has_post_thumbnail()) {
                            the_post_thumbnail('large');
                        }
                        ?>
                        <span class="gallery-zoom"><?php esc_html_e('Povećaj', 'vocne-rakije-petkovic'); ?></span>
                    </button>
                    <?php if ($subtitle) : ?><span class="gallery-fruit"><?php echo esc_html($subtitle); ?></span><?php endif; ?>
                    <?php if (count($gallery_ids) > 1) : ?>
                        <div class="gallery-thumbnails" aria-label="<?php esc_attr_e('Fotografije pakiranja', 'vocne-rakije-petkovic'); ?>">
                            <?php foreach ($gallery_slides as $index => $slide) :
                                $image_id = $slide['image_id'];
                                $full = wp_get_attachment_image_url($image_id, 'full');
                                $thumb_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title();
                                $image_label = $slide['label'];
                                ?>
                                <button type="button" class="<?php echo $index === $default_index ? 'active' : ''; ?>" data-gallery-index="<?php echo esc_attr($index); ?>" data-full-src="<?php echo esc_url($full); ?>" data-alt="<?php echo esc_attr($thumb_alt); ?>" data-slide-title="<?php echo esc_attr($slide['title']); ?>" data-slide-description="<?php echo esc_attr($slide['description']); ?>" aria-label="<?php echo esc_attr__('Prikaži ', 'vocne-rakije-petkovic') . esc_attr($image_label); ?>" aria-current="<?php echo $index === $default_index ? 'true' : 'false'; ?>">
                                    <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                                    <span><?php echo esc_html($image_label); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($default_slide['description']) : ?>
                            <div class="gallery-slide-copy" aria-live="polite">
                                <strong class="gallery-slide-title"><?php echo esc_html($default_slide['title'] ?: $default_slide['label']); ?></strong>
                                <p class="gallery-slide-description"><?php echo esc_html($default_slide['description']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="detail-copy">
                    <div class="product-intro">
                        <a class="product-return-link" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>">
                            <span aria-hidden="true">←</span>
                            <?php esc_html_e('Svi proizvodi', 'vocne-rakije-petkovic'); ?>
                        </a>
                        <span class="eyebrow"><?php $terms = get_the_terms($product_id, 'product_category'); echo esc_html($terms && !is_wp_error($terms) ? $terms[0]->name : __('Proizvod', 'vocne-rakije-petkovic')); ?></span>
                        <h1><?php the_title(); ?></h1>
                        <div class="lead"><?php the_content(); ?></div>
                    </div>
                    <div class="product-purchase">
                    <?php if ($packaging) : ?>
                        <div class="size-heading"><strong><?php esc_html_e('Pakiranja i cijene', 'vocne-rakije-petkovic'); ?></strong><span><?php esc_html_e('Narudžbe telefonom ili e-mailom', 'vocne-rakije-petkovic'); ?></span></div>
                        <?php
                        $reference_date = $packaging[0]['reference_price_date'] ?? get_post_meta($product_id, '_vrp_reference_price_date', true);
                        $reference_label = $reference_date
                            ? sprintf(__('Bazna cijena %s.', 'vocne-rakije-petkovic'), wp_date('d.m.Y', strtotime($reference_date)))
                            : get_theme_mod('vrp_reference_price_label', __('Bazna cijena', 'vocne-rakije-petkovic'));
                        ?>
                        <div class="price-column-labels"><span><?php esc_html_e('Pakiranje', 'vocne-rakije-petkovic'); ?></span><span><?php esc_html_e('Trenutna cijena', 'vocne-rakije-petkovic'); ?></span><span><?php echo esc_html($reference_label); ?></span></div>
                        <div class="size-list">
                            <?php foreach ($packaging as $row) : ?>
                                <div class="size-price-row"><span><?php echo esc_html($row['label']); ?></span><strong><?php echo esc_html(vrp_format_price($row['current_price'])); ?></strong><strong><?php echo esc_html(vrp_format_price($row['reference_price'])); ?></strong></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="button button-dark full-button" href="<?php echo esc_url(vrp_get_page_link('vrp_contact_page')); ?>?product=<?php echo rawurlencode(get_the_title()); ?>#inquiry"><?php esc_html_e('Zatražite ponudu', 'vocne-rakije-petkovic'); ?></a>
                    <p class="detail-note"><?php esc_html_e('Cijene su informativne. Za dostupnost i dogovor dostave kontaktirajte nas.', 'vocne-rakije-petkovic'); ?></p>
                    </div>
                </div>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
