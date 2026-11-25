<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_sanitize_text($value)
{
    return sanitize_text_field($value);
}

function vrp_sanitize_textarea($value)
{
    return sanitize_textarea_field($value);
}

function vrp_register_customizer($wp_customize)
{
    $wp_customize->add_panel('vrp_home_panel', [
        'title' => __('Naslovnica', 'vocne-rakije-petkovic'),
        'priority' => 30,
    ]);

    vrp_customizer_text_section($wp_customize, 'vrp_hero', __('Hero sekcija', 'vocne-rakije-petkovic'), [
        'vrp_hero_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Obiteljska proizvodnja · Šenkovec', 'text'],
        'vrp_hero_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Voće sazrijevano u Međimurju.', 'text'],
        'vrp_hero_content' => [__('Opis', 'vocne-rakije-petkovic'), 'Prirodne voćne rakije i likeri izrađeni od plodova našeg voćnjaka. Svaku seriju pripremamo s pažnjom kako bismo sačuvali prirodan karakter voća i tradiciju Međimurja.', 'textarea'],
        'vrp_hero_primary_label' => [__('Tekst glavnog gumba', 'vocne-rakije-petkovic'), 'Otkrijte ponudu', 'text'],
        'vrp_hero_secondary_label' => [__('Tekst drugog linka', 'vocne-rakije-petkovic'), 'Upoznajte našu priču', 'text'],
        'vrp_hero_points' => [__('Tri točke odvojene znakom |', 'vocne-rakije-petkovic'), 'Provjereno voće|Tradicionalna destilacija|Laboratorijski ispitano', 'textarea'],
        'vrp_hero_stamp_name' => [__('Tekst na oznaci — naziv', 'vocne-rakije-petkovic'), 'Petković', 'text'],
        'vrp_hero_stamp_location' => [__('Tekst na oznaci — lokacija', 'vocne-rakije-petkovic'), 'Međimurje', 'text'],
    ]);

    vrp_customizer_text_section($wp_customize, 'vrp_home_seo', __('SEO naslovnice', 'vocne-rakije-petkovic'), [
        'vrp_home_seo_title' => [__('SEO naslov', 'vocne-rakije-petkovic'), 'Voćne rakije i likeri iz Međimurja | OPG Petković', 'text'],
        'vrp_home_seo_description' => [__('SEO opis', 'vocne-rakije-petkovic'), 'Obiteljski OPG Petković iz Šenkovca proizvodi voćne rakije i likere od plodova vlastitog voćnjaka. Upoznajte naše proizvode i pošaljite upit za narudžbu.', 'textarea'],
    ]);

    $wp_customize->add_section('vrp_slider', [
        'title' => __('Slider naslovnice', 'vocne-rakije-petkovic'),
        'panel' => 'vrp_home_panel',
    ]);
    for ($index = 1; $index <= 4; $index++) {
        $wp_customize->add_setting("vrp_slider_image_{$index}", ['sanitize_callback' => 'absint']);
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, "vrp_slider_image_{$index}", [
            'label' => sprintf(__('Slider slika %d', 'vocne-rakije-petkovic'), $index),
            'section' => 'vrp_slider',
            'mime_type' => 'image',
        ]));
        $wp_customize->add_setting("vrp_slider_caption_{$index}", ['default' => $index === 1 ? 'Svaka boca nosi okus ploda i kraja.' : '', 'sanitize_callback' => 'vrp_sanitize_text']);
        $wp_customize->add_control("vrp_slider_caption_{$index}", [
            'label' => sprintf(__('Opis slike %d', 'vocne-rakije-petkovic'), $index),
            'section' => 'vrp_slider',
            'type' => 'text',
        ]);
    }

    vrp_customizer_text_section($wp_customize, 'vrp_intro', __('Uvodne vrijednosti', 'vocne-rakije-petkovic'), [
        'vrp_intro_title' => [__('Lijevi naslov', 'vocne-rakije-petkovic'), 'Od kruške do čaše. Bez prečaca.', 'text'],
        'vrp_intro_1' => [__('Vrijednost 1 (naslov|opis)', 'vocne-rakije-petkovic'), 'Vlastito voće|Kruške i šljive rastu u našim voćnjacima na oko jednog hektara.', 'textarea'],
        'vrp_intro_2' => [__('Vrijednost 2 (naslov|opis)', 'vocne-rakije-petkovic'), 'Vrijeme zrenja|Destilati odležavaju i razvijaju svoj puni, prepoznatljiv karakter.', 'textarea'],
        'vrp_intro_3' => [__('Vrijednost 3 (naslov|opis)', 'vocne-rakije-petkovic'), 'Male serije|Proizvodnja je obiteljska, osobna i usmjerena na kvalitetu.', 'textarea'],
    ]);

    vrp_customizer_text_section($wp_customize, 'vrp_products', __('Proizvodi na naslovnici', 'vocne-rakije-petkovic'), [
        'vrp_products_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Iz naše ponude', 'text'],
        'vrp_products_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Šest okusa. Jedna tradicija.', 'text'],
        'vrp_products_content' => [__('Opis', 'vocne-rakije-petkovic'), 'Viljamovka, žganica, marelica i tri bogata likera u pakiranju od 700 ml — pronađite okus za svoj stol ili poseban poklon.', 'textarea'],
        'vrp_products_limit' => [__('Broj proizvoda', 'vocne-rakije-petkovic'), '3', 'number'],
        'vrp_products_button' => [__('Tekst gumba', 'vocne-rakije-petkovic'), 'Pogledaj sve proizvode i pakiranja', 'text'],
    ]);

    vrp_customizer_text_section($wp_customize, 'vrp_special', __('Posebna ponuda', 'vocne-rakije-petkovic'), [
        'vrp_special_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Posebna ponuda', 'text'],
        'vrp_special_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Rakije koje već svojom bocom pričaju priču.', 'text'],
        'vrp_special_content' => [__('Opis', 'vocne-rakije-petkovic'), 'TRIS s tri okusa, degustacijske epruvete i viljamovka s kruškom.', 'textarea'],
        'vrp_special_button' => [__('Tekst gumba', 'vocne-rakije-petkovic'), 'Pogledaj cijelu posebnu ponudu', 'text'],
    ]);

    vrp_customizer_text_section($wp_customize, 'vrp_story', __('Naša priča', 'vocne-rakije-petkovic'), [
        'vrp_story_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Naša priča', 'text'],
        'vrp_story_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Mala proizvodnja. Velika pažnja.', 'text'],
        'vrp_story_content' => [__('Opis', 'vocne-rakije-petkovic'), 'U vlastitim voćnjacima u Šenkovcu uzgajamo kruške i šljive, a svaki plod pažljivo odabiremo. Kroz tradicionalnu fermentaciju i destilaciju nastaju rakije u manjim, obiteljskim serijama. Pažljivim radom čuvamo prirodni karakter voća i stvaramo prepoznatljive okuse koji u svakoj boci nose dio našeg kraja.', 'textarea'],
        'vrp_story_stat_1' => [__('Statistika 1 (vrijednost|opis)', 'vocne-rakije-petkovic'), '1 ha|voćnjaka', 'text'],
        'vrp_story_stat_2' => [__('Statistika 2 (vrijednost|opis)', 'vocne-rakije-petkovic'), '5|prepoznatljivih okusa', 'text'],
        'vrp_story_stat_3' => [__('Statistika 3 (vrijednost|opis)', 'vocne-rakije-petkovic'), '100%|obiteljski pristup', 'text'],
        'vrp_story_button' => [__('Tekst gumba', 'vocne-rakije-petkovic'), 'Više o gospodarstvu', 'text'],
    ]);
    $wp_customize->add_setting('vrp_story_image', ['sanitize_callback' => 'absint']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'vrp_story_image', ['label' => __('Fotografija priče', 'vocne-rakije-petkovic'), 'section' => 'vrp_story', 'mime_type' => 'image']));

    vrp_customizer_text_section($wp_customize, 'vrp_gift', __('Istaknuta ponuda', 'vocne-rakije-petkovic'), [
        'vrp_gift_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Novo u ponudi', 'text'],
        'vrp_gift_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Mali pozdrav. Velika prilika.', 'text'],
        'vrp_gift_content' => [__('Opis', 'vocne-rakije-petkovic'), 'Rakije u elegantnim epruvetama od 20 ml idealne su za degustacije, proslave i personalizirane poklone.', 'textarea'],
        'vrp_gift_button' => [__('Tekst gumba', 'vocne-rakije-petkovic'), 'Pošaljite upit', 'text'],
        'vrp_gift_badge' => [__('Oznaka na fotografiji', 'vocne-rakije-petkovic'), '20 ml', 'text'],
    ]);
    $wp_customize->add_setting('vrp_gift_enabled', [
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('vrp_gift_enabled', [
        'label' => __('Prikaži sekciju na naslovnici', 'vocne-rakije-petkovic'),
        'section' => 'vrp_gift',
        'type' => 'checkbox',
    ]);
    $wp_customize->add_setting('vrp_gift_image', ['sanitize_callback' => 'absint']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'vrp_gift_image', ['label' => __('Fotografija ponude', 'vocne-rakije-petkovic'), 'section' => 'vrp_gift', 'mime_type' => 'image']));

    vrp_customizer_text_section($wp_customize, 'vrp_final_cta', __('Završni poziv na kontakt', 'vocne-rakije-petkovic'), [
        'vrp_cta_eyebrow' => [__('Mala oznaka', 'vocne-rakije-petkovic'), 'Šenkovec · Međimurje', 'text'],
        'vrp_cta_title' => [__('Naslov', 'vocne-rakije-petkovic'), 'Želite degustirati ili naručiti?', 'text'],
        'vrp_cta_content' => [__('Opis', 'vocne-rakije-petkovic'), 'Javite nam se i zajedno ćemo pronaći pravo pakiranje za vas.', 'textarea'],
        'vrp_cta_button' => [__('Tekst gumba', 'vocne-rakije-petkovic'), 'Nazovite nas', 'text'],
    ]);

    $wp_customize->add_section('vrp_contact_section', [
        'title' => __('Kontakt podaci', 'vocne-rakije-petkovic'),
        'priority' => 40,
    ]);
    $contact_settings = [
        'vrp_contact_company' => [__('Naziv gospodarstva', 'vocne-rakije-petkovic'), 'OPG Petković Nada'],
        'vrp_contact_address' => [__('Adresa', 'vocne-rakije-petkovic'), "Zrinskih 4a\n40000 Čakovec\nHrvatska"],
        'vrp_contact_phone' => [__('Telefon', 'vocne-rakije-petkovic'), '+385 99 317 3317'],
        'vrp_contact_landline' => [__('Fiksni telefon', 'vocne-rakije-petkovic'), '+385 40 343 314'],
        'vrp_contact_email' => [__('E-mail', 'vocne-rakije-petkovic'), 'info@vocnerakije.hr'],
        'vrp_contact_oib' => [__('OIB', 'vocne-rakije-petkovic'), '42115479386'],
        'vrp_contact_registration' => [__('MBPG', 'vocne-rakije-petkovic'), '50302922'],
        'vrp_map_url' => [__('Poveznica na kartu', 'vocne-rakije-petkovic'), 'https://www.google.com/maps/search/?api=1&query=Zrinskih+4A%2C+Šenkovec%2C+Croatia'],
        'vrp_reference_price_label' => [__('Naziv bazne cijene u cjeniku', 'vocne-rakije-petkovic'), 'Bazna cijena'],
    ];
    foreach ($contact_settings as $id => $values) {
        $wp_customize->add_setting($id, ['default' => $values[1], 'sanitize_callback' => strpos($id, 'address') !== false ? 'vrp_sanitize_textarea' : 'vrp_sanitize_text']);
        $wp_customize->add_control($id, ['label' => $values[0], 'section' => 'vrp_contact_section', 'type' => strpos($id, 'address') !== false ? 'textarea' : 'text']);
    }
    foreach (['vrp_about_page' => __('O nama stranica', 'vocne-rakije-petkovic'), 'vrp_contact_page' => __('Kontakt stranica', 'vocne-rakije-petkovic')] as $id => $label) {
        $wp_customize->add_setting($id, ['sanitize_callback' => 'absint']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'vrp_contact_section', 'type' => 'dropdown-pages']);
    }
}
add_action('customize_register', 'vrp_register_customizer');

function vrp_customizer_text_section($wp_customize, $section_id, $section_title, $settings)
{
    $wp_customize->add_section($section_id, [
        'title' => $section_title,
        'panel' => 'vrp_home_panel',
    ]);
    foreach ($settings as $setting_id => $setting) {
        $wp_customize->add_setting($setting_id, [
            'default' => $setting[1],
            'sanitize_callback' => 'textarea' === $setting[2] ? 'vrp_sanitize_textarea' : 'vrp_sanitize_text',
        ]);
        $wp_customize->add_control($setting_id, [
            'label' => $setting[0],
            'section' => $section_id,
            'type' => $setting[2],
        ]);
    }
}
