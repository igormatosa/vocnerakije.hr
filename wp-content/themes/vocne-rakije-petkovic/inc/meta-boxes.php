<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_register_product_meta_boxes()
{
    add_meta_box(
        'vrp-product-form',
        __('Podaci proizvoda', 'vocne-rakije-petkovic'),
        'vrp_render_product_form',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'vrp_register_product_meta_boxes');

function vrp_render_product_form($post)
{
    wp_nonce_field('vrp_save_product', 'vrp_product_nonce');

    $product_id = get_post_meta($post->ID, '_vrp_product_id', true);
    $packaging_rows = vrp_get_product_packaging($post->ID);
    $packaging_row = $packaging_rows[0] ?? [];
    $packaging = get_post_meta($post->ID, '_vrp_packaging_label', true);
    $current_price = get_post_meta($post->ID, '_vrp_current_price', true);
    $reference_price = get_post_meta($post->ID, '_vrp_reference_price', true);
    $reference_date = get_post_meta($post->ID, '_vrp_reference_price_date', true);
    $selected_image = get_post_thumbnail_id($post->ID);
    $is_product_family = '1' === get_post_meta($post->ID, '_vrp_is_product_family', true);
    $seo_title = get_post_meta($post->ID, '_vrp_seo_title', true);
    $seo_description = get_post_meta($post->ID, '_vrp_seo_description', true);
    $selected_terms = wp_get_object_terms($post->ID, 'product_category', ['fields' => 'ids']);
    $selected_category = !is_wp_error($selected_terms) && $selected_terms ? (int) $selected_terms[0] : 0;
    $categories = get_terms([
        'taxonomy' => 'product_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);

    if ('' === $packaging) {
        $packaging = $packaging_row['label'] ?? '';
    }
    if ('' === $current_price) {
        $current_price = $packaging_row['current_price'] ?? '';
    }
    if ('' === $reference_price) {
        $reference_price = $packaging_row['reference_price'] ?? '';
    }
    if ('' === $reference_date) {
        $family_slug = get_post_meta($post->ID, '_vrp_product_family', true);
        $reference_date = 'marelica' === $family_slug ? '2026-11-13' : '2025-05-02';
    }
    ?>
    <div class="vrp-product-form">
        <div class="vrp-form-field">
            <label for="vrp-product-id"><?php esc_html_e('ID proizvoda', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="text" id="vrp-product-id" name="vrp_product_id" value="<?php echo esc_attr($product_id); ?>" placeholder="<?php esc_attr_e('npr. VR-001', 'vocne-rakije-petkovic'); ?>" required />
            <p class="description"><?php esc_html_e('Unesite internu šifru ili identifikator proizvoda.', 'vocne-rakije-petkovic'); ?></p>
        </div>
        <div class="vrp-form-field">
            <label for="vrp-product-name"><?php esc_html_e('Naziv', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="text" id="vrp-product-name" name="vrp_product_name" value="<?php echo esc_attr($post->post_title); ?>" required />
        </div>
        <div class="vrp-form-field vrp-form-field-wide">
            <label for="vrp-product-description"><?php echo $is_product_family ? esc_html__('Opći opis sorte ili proizvoda', 'vocne-rakije-petkovic') : esc_html__('Opis pakiranja / boce', 'vocne-rakije-petkovic'); ?></label>
            <textarea class="widefat" id="vrp-product-description" name="vrp_product_description" rows="6"><?php echo esc_textarea($post->post_content); ?></textarea>
            <?php if (!$is_product_family) : ?>
                <p class="description"><?php esc_html_e('Ovaj opis prikazuje se uz fotografiju pakiranja na stranici sorte.', 'vocne-rakije-petkovic'); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($is_product_family) : ?>
            <div class="vrp-form-field">
                <label for="vrp-seo-title"><?php esc_html_e('SEO naslov', 'vocne-rakije-petkovic'); ?></label>
                <input class="widefat" type="text" id="vrp-seo-title" name="vrp_seo_title" value="<?php echo esc_attr($seo_title); ?>" />
                <p class="description"><?php esc_html_e('Naslov koji se prikazuje u rezultatima tražilica.', 'vocne-rakije-petkovic'); ?></p>
            </div>
            <div class="vrp-form-field vrp-form-field-wide">
                <label for="vrp-seo-description"><?php esc_html_e('SEO opis', 'vocne-rakije-petkovic'); ?></label>
                <textarea class="widefat" id="vrp-seo-description" name="vrp_seo_description" rows="3"><?php echo esc_textarea($seo_description); ?></textarea>
                <p class="description"><?php esc_html_e('Kratak sažetak stranice koji tražilice mogu prikazati u rezultatima.', 'vocne-rakije-petkovic'); ?></p>
            </div>
        <?php endif; ?>
        <div class="vrp-form-field">
            <label for="vrp-current-price"><?php esc_html_e('Trenutna cijena (€)', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="text" id="vrp-current-price" name="vrp_current_price" value="<?php echo esc_attr($current_price); ?>" inputmode="decimal" placeholder="20,00" />
        </div>
        <div class="vrp-form-field">
            <label for="vrp-reference-price"><?php esc_html_e('Bazna cijena (€)', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="text" id="vrp-reference-price" name="vrp_reference_price" value="<?php echo esc_attr($reference_price); ?>" inputmode="decimal" placeholder="18,00" />
        </div>
        <div class="vrp-form-field">
            <label for="vrp-reference-date"><?php esc_html_e('Datum bazne cijene', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="date" id="vrp-reference-date" name="vrp_reference_price_date" value="<?php echo esc_attr($reference_date); ?>" />
        </div>
        <div class="vrp-form-field">
            <label for="vrp-packaging"><?php esc_html_e('Pakiranje', 'vocne-rakije-petkovic'); ?></label>
            <input class="widefat" type="text" id="vrp-packaging" name="vrp_packaging_label" value="<?php echo esc_attr($packaging); ?>" placeholder="0,7 l" />
        </div>
        <div class="vrp-form-field vrp-form-field-wide">
            <label for="vrp-product-category"><?php esc_html_e('Kategorija', 'vocne-rakije-petkovic'); ?></label>
            <select class="widefat" id="vrp-product-category" name="vrp_product_category" required>
                <option value=""><?php esc_html_e('Odaberite kategoriju', 'vocne-rakije-petkovic'); ?></option>
                <?php if (!is_wp_error($categories)) : foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, (int) $category->term_id); ?>><?php echo esc_html($category->name); ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div class="vrp-form-field vrp-form-field-wide">
            <label><?php esc_html_e('Slika proizvoda', 'vocne-rakije-petkovic'); ?></label>
            <input type="hidden" id="vrp-product-image-id" name="vrp_product_image_id" value="<?php echo esc_attr($selected_image); ?>" />
            <div id="vrp-product-image-preview" class="vrp-product-image-preview">
                <?php if ($selected_image) : echo wp_get_attachment_image($selected_image, 'medium'); endif; ?>
            </div>
            <button type="button" class="button" id="vrp-select-product-image"><?php esc_html_e('Odaberi sliku', 'vocne-rakije-petkovic'); ?></button>
            <button type="button" class="button vrp-remove-product-image" id="vrp-remove-product-image"<?php echo $selected_image ? '' : ' hidden'; ?>><?php esc_html_e('Ukloni sliku', 'vocne-rakije-petkovic'); ?></button>
        </div>
    </div>
    <?php
}

function vrp_save_product_meta($post_id)
{
    if (!isset($_POST['vrp_product_nonce']) || !wp_verify_nonce(wp_unslash($_POST['vrp_product_nonce']), 'vrp_save_product')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $name = sanitize_text_field(wp_unslash($_POST['vrp_product_name'] ?? ''));
    $description = wp_kses_post(wp_unslash($_POST['vrp_product_description'] ?? ''));
    $packaging = sanitize_text_field(wp_unslash($_POST['vrp_packaging_label'] ?? ''));
    $current_price = sanitize_text_field(wp_unslash($_POST['vrp_current_price'] ?? ''));
    $reference_price = sanitize_text_field(wp_unslash($_POST['vrp_reference_price'] ?? ''));
    $reference_date = sanitize_text_field(wp_unslash($_POST['vrp_reference_price_date'] ?? ''));
    $parsed_reference_date = DateTimeImmutable::createFromFormat('Y-m-d', $reference_date);
    $reference_date = $parsed_reference_date && $parsed_reference_date->format('Y-m-d') === $reference_date ? $reference_date : '';
    $image_id = absint($_POST['vrp_product_image_id'] ?? 0);
    $category_id = absint($_POST['vrp_product_category'] ?? 0);
    $is_product_family = '1' === get_post_meta($post_id, '_vrp_is_product_family', true);
    $existing_packaging_label = get_post_meta($post_id, '_vrp_packaging_label', true);

    if ($is_product_family) {
        update_post_meta($post_id, '_vrp_seo_title', sanitize_text_field(wp_unslash($_POST['vrp_seo_title'] ?? '')));
        update_post_meta($post_id, '_vrp_seo_description', sanitize_textarea_field(wp_unslash($_POST['vrp_seo_description'] ?? '')));
    }

    update_post_meta($post_id, '_vrp_product_id', sanitize_text_field(wp_unslash($_POST['vrp_product_id'] ?? '')));
    update_post_meta($post_id, '_vrp_packaging_label', $packaging);
    update_post_meta($post_id, '_vrp_current_price', $current_price);
    update_post_meta($post_id, '_vrp_reference_price', $reference_price);
    update_post_meta($post_id, '_vrp_reference_price_date', $reference_date);
    $packaging_row = [
        'label' => $packaging,
        'current_price' => $current_price,
        'reference_price' => $reference_price,
        'reference_price_date' => $reference_date,
    ];
    $existing_packaging = vrp_get_product_packaging($post_id);
    if ($is_product_family && $existing_packaging) {
        $updated = false;
        foreach ($existing_packaging as &$row) {
            $variant_product_id = absint($row['variant_product_id'] ?? 0);
            $is_parent_variant = $variant_product_id && $variant_product_id === (int) $post_id;
            $is_original_row = !$variant_product_id && ($row['label'] ?? '') === $existing_packaging_label;
            if (!$is_parent_variant && !$is_original_row) {
                continue;
            }

            $row = array_merge($row, $packaging_row);
            $updated = true;
            break;
        }
        unset($row);

        if (!$updated) {
            $existing_packaging[0] = array_merge($existing_packaging[0], $packaging_row);
        }
        update_post_meta($post_id, '_vrp_packaging', $existing_packaging);
    } else {
        update_post_meta($post_id, '_vrp_packaging', [$packaging_row]);
    }

    if ($image_id) {
        set_post_thumbnail($post_id, $image_id);
        update_post_meta($post_id, '_vrp_gallery', (string) $image_id);
    } else {
        delete_post_thumbnail($post_id);
        delete_post_meta($post_id, '_vrp_gallery');
    }

    $category = $category_id ? get_term($category_id, 'product_category') : null;
    $valid_category = $category && !is_wp_error($category) ? $category : null;
    $valid_category_id = $valid_category ? (int) $valid_category->term_id : 0;
    $special_offer = $valid_category && 'special-offers' === $valid_category->slug;
    wp_set_object_terms($post_id, $valid_category_id ? [$valid_category_id] : [], 'product_category');
    update_post_meta($post_id, '_vrp_special_offer', $special_offer ? '1' : '');

    remove_action('save_post_product', 'vrp_save_product_meta');
    wp_update_post([
        'ID' => $post_id,
        'post_title' => $name,
        'post_excerpt' => wp_strip_all_tags($description),
        'post_content' => $description,
    ]);
    add_action('save_post_product', 'vrp_save_product_meta');
}
add_action('save_post_product', 'vrp_save_product_meta');
