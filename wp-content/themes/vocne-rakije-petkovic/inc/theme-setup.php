<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_register_setup_page()
{
    add_theme_page(
        __('Postavljanje teme', 'vocne-rakije-petkovic'),
        __('Postavljanje teme', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-theme-setup',
        'vrp_render_setup_page'
    );
}
add_action('admin_menu', 'vrp_register_setup_page');

function vrp_render_setup_page()
{
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Postavljanje teme Voćne rakije Petković', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Ovaj alat može izraditi početne proizvode, kategorije, stranice, menije i fotografije prema pripremljenom dizajnu. Postojeći proizvodi i stranice s istim poveznicama neće se prepisati.', 'vocne-rakije-petkovic'); ?></p>
        <?php if (isset($_GET['vrp_setup'])) : ?><div class="notice notice-success"><p><?php esc_html_e('Početni sadržaj je uspješno izrađen. Provjerite proizvode, stranice i Izgled → Prilagodi.', 'vocne-rakije-petkovic'); ?></p></div><?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('vrp_import_starter_content', 'vrp_setup_nonce'); ?>
            <input type="hidden" name="action" value="vrp_import_starter_content" />
            <?php submit_button(__('Izradi početni sadržaj', 'vocne-rakije-petkovic'), 'primary', 'submit', false); ?>
        </form>
        <h2><?php esc_html_e('Nakon uvoza', 'vocne-rakije-petkovic'); ?></h2>
        <ol>
            <li><?php esc_html_e('U Izgledu → Prilagodi uredite tekstove naslovnice, logotip, kontakt podatke i slike slidera.', 'vocne-rakije-petkovic'); ?></li>
            <li><?php esc_html_e('U Proizvodima uredite naziv, opis, kategoriju, pakiranja, cijene i galeriju.', 'vocne-rakije-petkovic'); ?></li>
            <li><?php esc_html_e('Na Stranicama uredite O nama, Kontakt i Iz voćnjaka sadržaj.', 'vocne-rakije-petkovic'); ?></li>
            <li><?php esc_html_e('Otvorite Postavke → Trajne poveznice i kliknite Spremi promjene.', 'vocne-rakije-petkovic'); ?></li>
        </ol>
    </div>
    <?php
}

function vrp_import_starter_content()
{
    if (!current_user_can('manage_options') || !isset($_POST['vrp_setup_nonce']) || !wp_verify_nonce(wp_unslash($_POST['vrp_setup_nonce']), 'vrp_import_starter_content')) {
        wp_die(__('Nemate dopuštenje za ovu radnju.', 'vocne-rakije-petkovic'));
    }

    $category_ids = [];
    $categories = [
        'rakija' => 'Rakija',
        'liker' => 'Liker',
        'special-offers' => 'Posebna ponuda',
    ];
    foreach ($categories as $slug => $name) {
        $term = term_exists($slug, 'product_category');
        if (!$term) {
            $term = wp_insert_term($name, 'product_category', ['slug' => $slug]);
        }
        if (!is_wp_error($term)) {
            $category_ids[$slug] = (int) (is_array($term) ? $term['term_id'] : $term);
        }
    }

    $products = [
        ['slug' => 'viljamovka', 'name' => 'Međimurska Viljamovka', 'category' => 'rakija', 'subtitle' => 'Kruška Williams', 'description' => 'Aromatična voćna rakija od kruške sorte žuta viljamovka.', 'image' => 'pear-brandy', 'prices' => [['50 ml', '4', '3'], ['100 ml', '8', '7'], ['200 ml', '10', '8'], ['500 ml', '15', '13'], ['700 ml', '20', '18'], ['1 l', '26', '24']]],
        ['slug' => 'zganica', 'name' => 'Međimurska Žganica', 'category' => 'rakija', 'subtitle' => 'Autohtona šljiva', 'description' => 'Tradicionalna međimurska šljivovica od ručno branih plodova.', 'image' => 'plum-brandy', 'prices' => [['50 ml', '4', '3'], ['100 ml', '8', '7'], ['200 ml', '10', '8'], ['500 ml', '13', '11'], ['700 ml', '18', '16'], ['1 l', '21', '19']]],
        ['slug' => 'medica', 'name' => 'Međimurska Medica', 'category' => 'liker', 'subtitle' => 'Med i viljamovka', 'description' => 'Spoj međimurskog bagremovog meda i naše viljamovke.', 'image' => 'honey-liqueur', 'prices' => [['50 ml', '4', '3'], ['100 ml', '8', '7'], ['200 ml', '10', '8'], ['500 ml', '15', '13'], ['700 ml', '20', '18'], ['1 l', '26', '23']]],
        ['slug' => 'orehovec', 'name' => 'Međimurski Orehovec', 'category' => 'liker', 'subtitle' => 'Zeleni orah', 'description' => 'Gorki liker od zelenog oraha i aromatskih biljaka.', 'image' => 'walnut-liqueur', 'prices' => [['50 ml', '4', '3'], ['100 ml', '8', '7'], ['200 ml', '10', '8'], ['500 ml', '13', '11'], ['700 ml', '18', '16'], ['1 l', '21', '19']]],
        ['slug' => 'visnjevec', 'name' => 'Međimurski Višnjevec', 'category' => 'liker', 'subtitle' => 'Višnja i aronija', 'description' => 'Slatki voćni liker s višnjama i aronijom.', 'image' => 'cherry-liqueur', 'prices' => [['50 ml', '4', '3'], ['100 ml', '8', '7'], ['200 ml', '10', '8'], ['500 ml', '13', '11'], ['700 ml', '18', '16'], ['1 l', '21', '19']]],
        ['slug' => 'tris', 'name' => 'TRIS — tri okusa', 'category' => 'special-offers', 'subtitle' => '3 × 200 ml', 'description' => 'Kombinacija žganice, višnjevca i orehovca u TRIS boci.', 'featured' => 'triple-set.jpg', 'prices' => [['3 × 200 ml', '25', '23']]],
        ['slug' => 'epruvete', 'name' => 'Rakija u epruvetama', 'category' => 'special-offers', 'subtitle' => '20 ml', 'description' => 'Degustacijsko pakiranje za proslave i personalizirane poklone.', 'featured' => 'test-tubes.jpg', 'prices' => [['20 ml', 'Po upitu', 'Po upitu']]],
        ['slug' => 'viljamovka-s-1-plodom', 'name' => 'Viljamovka s 1 plodom', 'category' => 'special-offers', 'subtitle' => '500 ml · 40%', 'description' => 'Viljamovka s jednom kruškom zarobljenom u boci.', 'featured' => 'pear-brandy-one-fruit.jpg', 'prices' => [['500 ml', '35', '30']]],
        ['slug' => 'viljamovka-s-2-ploda', 'name' => 'Viljamovka s 2 ploda', 'category' => 'special-offers', 'subtitle' => '500 ml · 40%', 'description' => 'Viljamovka s dvije kruške zarobljene u boci.', 'featured' => 'pear-brandy-two-fruits.jpg', 'prices' => [['500 ml', '40', '35']]],
        ['slug' => 'viljamovka-s-3-ploda', 'name' => 'Viljamovka s 3 ploda', 'category' => 'special-offers', 'subtitle' => '500 ml · 40%', 'description' => 'Viljamovka s tri kruške zarobljene u boci.', 'featured' => 'pear-brandy-three-fruits.jpg', 'prices' => [['500 ml', '45', '40']]],
    ];

    foreach ($products as $product) {
        $existing = get_page_by_path($product['slug'], OBJECT, 'product');
        if (!$existing) {
            $product_id = wp_insert_post([
                'post_type' => 'product',
                'post_status' => 'publish',
                'post_name' => $product['slug'],
                'post_title' => $product['name'],
                'post_excerpt' => $product['description'],
                'post_content' => $product['description'],
            ]);
        } else {
            $product_id = $existing->ID;
        }
        if (!$product_id || is_wp_error($product_id)) {
            continue;
        }
        wp_set_object_terms($product_id, [$category_ids[$product['category']]], 'product_category');
        update_post_meta($product_id, '_vrp_product_subtitle', $product['subtitle']);
        update_post_meta($product_id, '_vrp_special_offer', 'special-offers' === $product['category'] ? '1' : '');
        $packaging = [];
        foreach ($product['prices'] as $price) {
            $packaging[] = ['label' => $price[0], 'current_price' => $price[1], 'reference_price' => $price[2]];
        }
        update_post_meta($product_id, '_vrp_packaging', $packaging);

        $gallery_ids = [];
        if (isset($product['image'])) {
            for ($index = 1; $index <= 6; $index++) {
                $source = get_template_directory() . '/assets/images/gallery/' . $product['image'] . '-' . $index . '.jpg';
                $attachment_id = vrp_import_theme_attachment($source, $product['name'] . ' ' . $index);
                if ($attachment_id) {
                    $gallery_ids[] = $attachment_id;
                }
            }
        } elseif (isset($product['featured'])) {
            $attachment_id = vrp_import_theme_attachment(get_template_directory() . '/assets/images/' . $product['featured'], $product['name']);
            if ($attachment_id) {
                $gallery_ids[] = $attachment_id;
            }
        }
        if ($gallery_ids) {
            update_post_meta($product_id, '_vrp_gallery', implode(',', $gallery_ids));
            set_post_thumbnail($product_id, $gallery_ids[isset($product['image']) ? 4 : 0]);
        }
    }

    $page_ids = vrp_create_starter_pages();
    update_option('show_on_front', 'page');
    update_option('page_on_front', $page_ids['home']);
    update_option('page_for_posts', $page_ids['blog']);
    set_theme_mod('vrp_about_page', $page_ids['about']);
    set_theme_mod('vrp_contact_page', $page_ids['contact']);
    vrp_create_starter_menu($page_ids);
    flush_rewrite_rules();

    wp_safe_redirect(admin_url('themes.php?page=vrp-theme-setup&vrp_setup=1'));
    exit;
}
add_action('admin_post_vrp_import_starter_content', 'vrp_import_starter_content');

function vrp_import_theme_attachment($source_path, $title)
{
    if (!file_exists($source_path)) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $source_path = wp_normalize_path($source_path);
    $existing = get_posts([
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_vrp_source_path',
        'meta_value' => $source_path,
    ]);
    if ($existing) {
        return (int) $existing[0];
    }

    $file_data = file_get_contents($source_path);
    $upload = wp_upload_bits(basename($source_path), null, $file_data);
    if (!empty($upload['error'])) {
        return 0;
    }
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => wp_check_filetype($upload['file'])['type'],
        'post_title' => sanitize_file_name($title),
        'post_content' => '',
        'post_status' => 'inherit',
    ], $upload['file']);
    if (!$attachment_id || is_wp_error($attachment_id)) {
        return 0;
    }
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
    update_post_meta($attachment_id, '_vrp_source_path', $source_path);
    return $attachment_id;
}

function vrp_migrate_special_offer_families()
{
    if ('1' === get_option('vrp_special_offer_families_migrated')) {
        return;
    }

    $find_product = function ($slugs) {
        foreach ($slugs as $slug) {
            $product = get_page_by_path($slug, OBJECT, 'product');
            if ($product instanceof WP_Post) {
                return $product;
            }
        }

        return null;
    };

    $tris = $find_product(['60-tris-3-x-200ml', 'tris']);
    $tris_small = $find_product(['601-tris-mali-3-x-100ml']);
    $test_tubes = $find_product(['200-epruveta-20-ml', 'epruvete']);
    $fruit_variants = [
        [
            'slugs' => ['101-viljamovka-05-l-s-plodom', 'viljamovka-s-1-plodom'],
            'label' => '0,5 l · 1 plod',
            'image' => 'pear-brandy-one-fruit.jpg',
        ],
        [
            'slugs' => ['102-viljamovka-05-l-s-2-ploda', 'viljamovka-s-2-ploda'],
            'label' => '0,5 l · 2 ploda',
            'image' => 'pear-brandy-two-fruits.jpg',
        ],
        [
            'slugs' => ['103-viljamovka-05-l-s-3-ploda', 'viljamovka-s-3-ploda'],
            'label' => '0,5 l · 3 ploda',
            'image' => 'pear-brandy-three-fruits.jpg',
        ],
    ];

    foreach ($fruit_variants as &$variant) {
        $variant['post'] = $find_product($variant['slugs']);
    }
    unset($variant);

    $fruit_variants = array_values(array_filter($fruit_variants, function ($variant) {
        return $variant['post'] instanceof WP_Post;
    }));

    $special_term = get_term_by('slug', 'special-offers', 'product_category');
    if (!$tris || !$test_tubes || 3 !== count($fruit_variants) || !$special_term) {
        return;
    }

    $fruit_family = $find_product(['viljamovka-s-vocem']);
    if (!$fruit_family) {
        $fruit_family_id = wp_insert_post([
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_name' => 'viljamovka-s-vocem',
            'post_title' => 'Viljamovka s voćem',
            'post_excerpt' => 'Posebna Viljamovka s jednom, dvije ili tri kruške uzgojene u boci.',
            'post_content' => 'Odaberite posebnu Viljamovku s jednom, dvije ili tri kruške koje su prirodno rasle u boci. Svaka varijanta spaja aromatični destilat i jedinstven izgled ploda.',
        ], true);
        if (is_wp_error($fruit_family_id) || !$fruit_family_id) {
            return;
        }
        $fruit_family = get_post($fruit_family_id);
    }

    if (!$fruit_family instanceof WP_Post) {
        return;
    }

    foreach ($fruit_variants as $variant) {
        $image_id = vrp_import_theme_attachment(
            get_template_directory() . '/assets/images/' . $variant['image'],
            get_the_title($variant['post'])
        );
        if (!$image_id) {
            continue;
        }

        wp_update_post([
            'ID' => $image_id,
            'post_parent' => $variant['post']->ID,
        ]);
        update_post_meta($image_id, '_wp_attachment_image_alt', get_the_title($variant['post']));
        set_post_thumbnail($variant['post']->ID, $image_id);
        update_post_meta($variant['post']->ID, '_vrp_gallery', (string) $image_id);
    }

    $configure_family = function ($parent_id, $title, $subtitle, $variants, $description = '') use ($special_term) {
        $parent = get_post($parent_id);
        if (!$parent instanceof WP_Post) {
            return false;
        }

        $family_slug = $parent->post_name;
        $rows = [];
        foreach ($variants as $variant) {
            $variant_post = $variant['post'];
            $source_rows = vrp_get_product_packaging($variant_post->ID);
            $row = $source_rows[0] ?? [
                'current_price' => get_post_meta($variant_post->ID, '_vrp_current_price', true),
                'reference_price' => get_post_meta($variant_post->ID, '_vrp_reference_price', true),
                'reference_price_date' => get_post_meta($variant_post->ID, '_vrp_reference_price_date', true),
            ];
            $row['label'] = $variant['label'];
            $row['variant_product_id'] = (int) $variant_post->ID;
            $rows[] = $row;
        }

        if (!$rows) {
            return false;
        }

        $post_fields = [
            'ID' => (int) $parent_id,
            'post_name' => $family_slug,
            'post_title' => $title,
        ];
        if ($description) {
            $post_fields['post_content'] = $description;
            $post_fields['post_excerpt'] = wp_strip_all_tags($description);
        }
        $updated_post = wp_update_post($post_fields, true);
        if (is_wp_error($updated_post)) {
            return false;
        }

        update_post_meta($parent_id, '_vrp_is_product_family', '1');
        update_post_meta($parent_id, '_vrp_product_family', $family_slug);
        update_post_meta($parent_id, '_vrp_product_subtitle', $subtitle);
        update_post_meta($parent_id, '_vrp_special_offer', '1');
        update_post_meta($parent_id, '_vrp_packaging', $rows);
        update_post_meta($parent_id, '_vrp_packaging_label', $rows[0]['label']);
        update_post_meta($parent_id, '_vrp_current_price', $rows[0]['current_price'] ?? '');
        update_post_meta($parent_id, '_vrp_reference_price', $rows[0]['reference_price'] ?? '');
        update_post_meta($parent_id, '_vrp_reference_price_date', $rows[0]['reference_price_date'] ?? '');
        update_post_meta($parent_id, '_vrp_seo_title', $title . ' | OPG Petković');
        update_post_meta($parent_id, '_vrp_seo_description', wp_trim_words(wp_strip_all_tags($description ?: $parent->post_content), 28, ''));

        $product_ids = array_unique(array_merge(
            [(int) $parent_id],
            array_map(function ($variant) {
                return (int) $variant['post']->ID;
            }, $variants)
        ));
        foreach ($product_ids as $product_id) {
            if ($product_id !== (int) $parent_id) {
                update_post_meta($product_id, '_vrp_product_family', $family_slug);
                delete_post_meta($product_id, '_vrp_is_product_family');
            }
            update_post_meta($product_id, '_vrp_special_offer', '1');
            $terms = wp_set_object_terms($product_id, [(int) $special_term->term_id], 'product_category');
            if (is_wp_error($terms)) {
                return false;
            }
        }

        $first_variant_id = (int) $variants[0]['post']->ID;
        $image_id = get_post_thumbnail_id($first_variant_id);
        if ($image_id) {
            set_post_thumbnail($parent_id, $image_id);
        }

        return true;
    };

    $tris_variants = [[
        'post' => $tris,
        'label' => '3 × 200 ml',
    ]];
    if ($tris_small instanceof WP_Post) {
        $tris_variants[] = [
            'post' => $tris_small,
            'label' => '3 × 100 ml',
        ];
    }

    $configured = $configure_family(
        $tris->ID,
        'TRIS — tri okusa',
        '3 × 200 ml i 3 × 100 ml',
        $tris_variants,
        'TRIS poklon-boca okuplja tri odabrana okusa. Dostupna su standardna i manja pakiranja, a kombinaciju rakija i likera možete prilagoditi prema želji.'
    );
    $configured = $configure_family(
        $test_tubes->ID,
        'Rakija u epruvetama',
        '20 ml',
        [[
            'post' => $test_tubes,
            'label' => '20 ml',
        ]]
    ) && $configured;
    $configured = $configure_family(
        $fruit_family->ID,
        'Viljamovka s voćem',
        '500 ml · 1, 2 ili 3 ploda',
        array_map(function ($variant) {
            return [
                'post' => $variant['post'],
                'label' => $variant['label'],
            ];
        }, $fruit_variants)
    ) && $configured;

    if ($configured) {
        update_option('vrp_special_offer_families_migrated', '1');
    }
}
add_action('init', 'vrp_migrate_special_offer_families', 20);

function vrp_create_starter_pages()
{
    $pages = [
        'home' => ['Početna', 'home', ''],
        'about' => ['O nama', 'o-nama', '<h2>Od zemlje. Do dobre čaše.</h2><p>Obiteljsko gospodarsko poljoprivredno gospodarstvo iz Šenkovca proizvodi prirodne voćne rakije i likere tradicionalnim postupkom.</p>'],
        'blog' => ['Iz voćnjaka', 'blog', ''],
        'contact' => ['Kontakt', 'kontakt', '<p>Za narudžbu, dostupnost, degustaciju ili posjet gospodarstvu javite nam se telefonom ili e-mailom.</p>'],
    ];
    $page_ids = [];
    foreach ($pages as $key => $page) {
        $existing = get_page_by_path($page[1]);
        if ($existing) {
            $page_ids[$key] = $existing->ID;
            continue;
        }
        $page_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $page[0],
            'post_name' => $page[1],
            'post_content' => $page[2],
        ]);
        $page_ids[$key] = $page_id;
    }
    if (!empty($page_ids['contact'])) {
        update_post_meta($page_ids['contact'], '_wp_page_template', 'page-contact.php');
    }
    return $page_ids;
}

function vrp_create_starter_menu($page_ids)
{
    $menu_name = 'Primary Navigation';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) {
        return;
    }
    $items = [
        'Početna' => home_url('/'),
        'Proizvodi' => get_post_type_archive_link('product'),
        'O nama' => get_permalink($page_ids['about']),
        'Iz voćnjaka' => get_permalink($page_ids['blog']),
        'Kontakt' => get_permalink($page_ids['contact']),
    ];
    foreach ($items as $label => $url) {
        $existing = false;
        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            if ($item->title === $label) {
                $existing = true;
                break;
            }
        }
        if (!$existing) {
            wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-type' => 'custom']);
        }
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    $locations['footer'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
