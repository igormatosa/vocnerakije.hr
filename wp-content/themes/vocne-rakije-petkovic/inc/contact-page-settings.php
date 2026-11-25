<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_get_contact_page_settings()
{
    $defaults = [
        'hero_eyebrow' => 'Razgovarajmo',
        'hero_title' => 'Dobrodošli',
        'hero_title_accent' => 'u Šenkovec.',
        'hero_intro' => 'Za narudžbu, dostupnost, degustaciju ili posjet gospodarstvu — javite nam se telefonom ili e-mailom.',
        'details_eyebrow' => 'Podaci gospodarstva',
        'form_eyebrow' => 'Pošaljite upit',
        'form_title' => 'Kako vam možemo pomoći?',
        'privacy_note' => 'Podatke ne pohranjujemo na stranici; koristimo ih samo za odgovor na vaš upit.',
        'faq_eyebrow' => 'Najčešća pitanja',
        'faq_title' => 'Dobro je znati.',
        'faq_1_question' => 'Kako mogu naručiti proizvode?',
        'faq_1_answer' => 'Pošaljite nam upit putem obrasca, e-mailom ili telefonom. Navedite proizvode i pakiranja koja vas zanimaju pa ćemo vam odgovoriti o dostupnosti i načinu preuzimanja ili dostave.',
        'faq_2_question' => 'Postoje li poklon-pakiranja?',
        'faq_2_answer' => 'U ponudi imamo posebna pakiranja i proizvode prikladne za darivanje. Javite nam se kako bismo zajedno provjerili trenutačnu ponudu.',
        'faq_3_question' => 'Mogu li doći na gospodarstvo?',
        'faq_3_answer' => 'Možete, uz prethodni dogovor. Nazovite nas ili pošaljite upit kako bismo dogovorili termin posjeta.',
        'seo_title' => 'Kontakt | Voćne rakije Petković',
        'seo_description' => 'Kontaktirajte OPG Petković u Šenkovcu za informacije o voćnim rakijama, likerima, dostupnosti proizvoda i posjetu gospodarstvu.',
    ];

    $settings = get_option('vrp_contact_page_settings', []);
    $settings = is_array($settings) ? $settings : [];

    if (!array_key_exists('hero_intro', $settings)) {
        $contact_page_id = absint(get_theme_mod('vrp_contact_page'));
        if (!$contact_page_id) {
            $contact_page = get_page_by_path('kontakt', OBJECT, 'page');
            $contact_page_id = $contact_page ? $contact_page->ID : 0;
        }
        $existing_intro = $contact_page_id ? get_post_field('post_excerpt', $contact_page_id) : '';
        if ($existing_intro) {
            $defaults['hero_intro'] = $existing_intro;
        }
    }

    return wp_parse_args($settings, $defaults);
}

function vrp_get_contact_page_setting($key)
{
    $settings = vrp_get_contact_page_settings();
    return $settings[$key] ?? '';
}

function vrp_is_contact_page()
{
    $contact_page_id = absint(get_theme_mod('vrp_contact_page'));
    return is_page('kontakt') || ($contact_page_id && is_page($contact_page_id));
}

function vrp_sanitize_contact_page_settings($input)
{
    $input = is_array($input) ? $input : [];
    $settings = [];
    $textarea_fields = [
        'hero_intro',
        'privacy_note',
        'faq_1_answer',
        'faq_2_answer',
        'faq_3_answer',
        'seo_description',
    ];

    foreach (vrp_get_contact_page_settings() as $key => $default) {
        $value = $input[$key] ?? '';
        $settings[$key] = in_array($key, $textarea_fields, true)
            ? sanitize_textarea_field($value)
            : sanitize_text_field($value);
    }

    return $settings;
}

function vrp_register_contact_page_settings()
{
    register_setting('vrp_contact_page_settings_group', 'vrp_contact_page_settings', [
        'sanitize_callback' => 'vrp_sanitize_contact_page_settings',
    ]);
}
add_action('admin_init', 'vrp_register_contact_page_settings');

function vrp_add_contact_page_settings()
{
    add_submenu_page(
        'edit.php?post_type=page',
        __('Postavke stranice Kontakt', 'vocne-rakije-petkovic'),
        __('Kontakt — tekst i SEO', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-contact-settings',
        'vrp_render_contact_page_settings'
    );
}
add_action('admin_menu', 'vrp_add_contact_page_settings');

function vrp_render_contact_page_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = vrp_get_contact_page_settings();
    $groups = [
        __('Uvod', 'vocne-rakije-petkovic') => [
            'hero_eyebrow' => __('Kratki naslov', 'vocne-rakije-petkovic'),
            'hero_title' => __('Naslov — prvi red', 'vocne-rakije-petkovic'),
            'hero_title_accent' => __('Naslov — istaknuti red', 'vocne-rakije-petkovic'),
            'hero_intro' => __('Uvodni tekst', 'vocne-rakije-petkovic'),
        ],
        __('Kontakt i obrazac', 'vocne-rakije-petkovic') => [
            'details_eyebrow' => __('Naslov podataka gospodarstva', 'vocne-rakije-petkovic'),
            'form_eyebrow' => __('Kratki naslov obrasca', 'vocne-rakije-petkovic'),
            'form_title' => __('Naslov obrasca', 'vocne-rakije-petkovic'),
            'privacy_note' => __('Napomena o privatnosti', 'vocne-rakije-petkovic'),
        ],
        __('Česta pitanja', 'vocne-rakije-petkovic') => [
            'faq_eyebrow' => __('Kratki naslov', 'vocne-rakije-petkovic'),
            'faq_title' => __('Naslov odjeljka', 'vocne-rakije-petkovic'),
            'faq_1_question' => __('Pitanje 1', 'vocne-rakije-petkovic'),
            'faq_1_answer' => __('Odgovor 1', 'vocne-rakije-petkovic'),
            'faq_2_question' => __('Pitanje 2', 'vocne-rakije-petkovic'),
            'faq_2_answer' => __('Odgovor 2', 'vocne-rakije-petkovic'),
            'faq_3_question' => __('Pitanje 3', 'vocne-rakije-petkovic'),
            'faq_3_answer' => __('Odgovor 3', 'vocne-rakije-petkovic'),
        ],
        __('SEO', 'vocne-rakije-petkovic') => [
            'seo_title' => __('SEO naslov', 'vocne-rakije-petkovic'),
            'seo_description' => __('SEO opis', 'vocne-rakije-petkovic'),
        ],
    ];
    $textarea_fields = ['hero_intro', 'privacy_note', 'faq_1_answer', 'faq_2_answer', 'faq_3_answer', 'seo_description'];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Postavke stranice Kontakt', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Ovdje možete urediti tekstove stranice i SEO podatke. Naziv gospodarstva, adresu, telefone, e-mail i poveznicu na kartu uredite u Izgled → Prilagodi → Kontakt podaci.', 'vocne-rakije-petkovic'); ?></p>
        <form action="options.php" method="post">
            <?php settings_fields('vrp_contact_page_settings_group'); ?>
            <?php foreach ($groups as $group_title => $fields) : ?>
                <h2><?php echo esc_html($group_title); ?></h2>
                <table class="form-table" role="presentation">
                    <?php foreach ($fields as $key => $label) : ?>
                        <?php $field_id = 'vrp-contact-' . str_replace('_', '-', $key); ?>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($label); ?></label></th>
                            <td>
                                <?php if (in_array($key, $textarea_fields, true)) : ?>
                                    <textarea class="large-text" rows="3" id="<?php echo esc_attr($field_id); ?>" name="vrp_contact_page_settings[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($settings[$key]); ?></textarea>
                                <?php else : ?>
                                    <input class="large-text" type="text" id="<?php echo esc_attr($field_id); ?>" name="vrp_contact_page_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($settings[$key]); ?>" />
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
            <?php submit_button(__('Spremi promjene', 'vocne-rakije-petkovic')); ?>
        </form>
    </div>
    <?php
}
