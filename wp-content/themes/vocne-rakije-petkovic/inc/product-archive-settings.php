<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_get_product_archive_setting($setting)
{
    $defaults = [
        'vrp_product_archive_title' => 'Okusi koji ostaju u sjećanju.',
        'vrp_product_archive_intro' => 'Naša ponuda okuplja voćne rakije i likere raznolikog karaktera, nastale iz pažljivo odabranog voća i obiteljskog iskustva. Svaka sorta donosi vlastitu aromu i doživljaj — od izraženih voćnih nota do zaokruženih, bogatih okusa. Pregledajte proizvode, upoznajte njihove priče i pronađite okus koji vam najviše odgovara za zajednički stol ili darivanje.',
        'vrp_product_archive_seo_title' => 'Voćne rakije i likeri — naša ponuda | OPG Petković',
        'vrp_product_archive_seo_description' => 'Istražite ponudu voćnih rakija i likera OPG-a Petković iz Međimurja. Upoznajte različite okuse i odaberite proizvod za posebne trenutke.',
    ];

    return get_option($setting, $defaults[$setting] ?? '');
}

function vrp_get_product_category_seo_data($term)
{
    if (!($term instanceof WP_Term) || 'product_category' !== $term->taxonomy) {
        return [];
    }

    $defaults = [
        'rakija' => [
            'title' => 'Voćne rakije iz Međimurja | OPG Petković',
            'description' => 'Upoznajte voćne rakije OPG-a Petković iz Šenkovca. Pregledajte okuse, pakiranja i cijene te nam se javite za dostupnost i narudžbu.',
            'intro' => 'Upoznajte voćne rakije OPG-a Petković iz Šenkovca. Svaka sorta donosi vlastiti karakter ploda, a dostupna pakiranja i cijene možete pregledati na stranicama pojedinih proizvoda.',
        ],
        'liker' => [
            'title' => 'Voćni likeri iz Međimurja | OPG Petković',
            'description' => 'Otkrijte međimurske likere OPG-a Petković, od medice do voćnih i biljnih okusa. Pregledajte pakiranja, cijene i informacije za narudžbu.',
            'intro' => 'Otkrijte likere OPG-a Petković iz Međimurja — od medice s bagremovim medom do voćnih i biljnih okusa. Pregledajte dostupna pakiranja i detalje svakog proizvoda.',
        ],
        'special-offers' => [
            'title' => 'Posebna ponuda rakija i poklona | OPG Petković',
            'description' => 'Istražite posebna pakiranja rakija i likera OPG-a Petković: degustacijske bočice, TRIS i prigodne poklone iz Međimurja.',
            'intro' => 'Ovdje pronađite posebna pakiranja rakija i likera OPG-a Petković — od degustacijskih bočica i TRIS kompleta do prigodnih poklona s međimurskim karakterom.',
        ],
    ];

    $data = $defaults[$term->slug] ?? [
        'title' => sprintf('%s | Voćne rakije i likeri | OPG Petković', $term->name),
        'description' => sprintf('Pregledajte proizvode iz kategorije %s OPG-a Petković iz Međimurja te provjerite dostupna pakiranja i cijene.', $term->name),
        'intro' => sprintf('Pregledajte proizvode iz kategorije %s OPG-a Petković iz Međimurja. Otvorite pojedini proizvod za detalje o dostupnim pakiranjima i cijenama.', $term->name),
    ];

    $term_description = trim(wp_strip_all_tags($term->description));
    if ($term_description) {
        $data['description'] = $term_description;
        $data['intro'] = $term_description;
    }

    return $data;
}

function vrp_register_product_archive_settings()
{
    register_setting('vrp_product_archive_settings', 'vrp_product_archive_title', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('vrp_product_archive_settings', 'vrp_product_archive_intro', [
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    register_setting('vrp_product_archive_settings', 'vrp_product_archive_seo_title', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('vrp_product_archive_settings', 'vrp_product_archive_seo_description', [
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
}
add_action('admin_init', 'vrp_register_product_archive_settings');

function vrp_add_product_archive_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Tekst i SEO stranice proizvoda', 'vocne-rakije-petkovic'),
        __('Tekst i SEO', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-product-archive',
        'vrp_render_product_archive_settings_page'
    );
}
add_action('admin_menu', 'vrp_add_product_archive_settings_page');

function vrp_render_product_archive_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Tekst i SEO stranice proizvoda', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Uredite naslov, uvodni tekst i SEO podatke koji se prikazuju na stranici kataloga proizvoda.', 'vocne-rakije-petkovic'); ?></p>
        <form action="options.php" method="post">
            <?php settings_fields('vrp_product_archive_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="vrp-product-archive-title"><?php esc_html_e('Naslov stranice', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-product-archive-title" name="vrp_product_archive_title" value="<?php echo esc_attr(vrp_get_product_archive_setting('vrp_product_archive_title')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-product-archive-intro"><?php esc_html_e('Uvodni tekst', 'vocne-rakije-petkovic'); ?></label></th>
                    <td>
                        <textarea class="large-text" rows="7" id="vrp-product-archive-intro" name="vrp_product_archive_intro"><?php echo esc_textarea(vrp_get_product_archive_setting('vrp_product_archive_intro')); ?></textarea>
                        <p class="description"><?php esc_html_e('Ovaj se tekst prikazuje ispod naslova na stranici proizvoda.', 'vocne-rakije-petkovic'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-product-archive-seo-title"><?php esc_html_e('SEO naslov', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="large-text" type="text" id="vrp-product-archive-seo-title" name="vrp_product_archive_seo_title" value="<?php echo esc_attr(vrp_get_product_archive_setting('vrp_product_archive_seo_title')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-product-archive-seo-description"><?php esc_html_e('SEO opis', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><textarea class="large-text" rows="3" id="vrp-product-archive-seo-description" name="vrp_product_archive_seo_description"><?php echo esc_textarea(vrp_get_product_archive_setting('vrp_product_archive_seo_description')); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(__('Spremi promjene', 'vocne-rakije-petkovic')); ?>
        </form>
    </div>
    <?php
}
