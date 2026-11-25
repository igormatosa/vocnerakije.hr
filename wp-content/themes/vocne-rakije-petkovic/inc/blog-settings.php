<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_get_blog_archive_settings()
{
    $defaults = [
        'eyebrow' => 'Priče i novosti',
        'title' => 'Život među',
        'title_accent' => 'voćkama.',
        'intro' => 'Voćnjak je mjesto gdje naša priča počinje — među stablima koja kroz godišnja doba traže pažnju, strpljenje i puno rada. Ovdje dijelimo trenutke iz svakodnevice OPG-a Petković: od pripreme tla i sadnje novih voćaka do brige o plodovima, berbe i susreta na manifestacijama. Zavirite u naš rad i novosti iz Međimurja.',
        'seo_title' => 'Iz voćnjaka | Voćne rakije Petković',
        'seo_description' => 'Priče iz voćnjaka OPG-a Petković u Međimurju: sadnja i njega voćaka, berba plodova, svakodnevni rad i novosti s gospodarstva.',
    ];

    $settings = get_option('vrp_blog_archive_settings', []);
    return wp_parse_args(is_array($settings) ? $settings : [], $defaults);
}

function vrp_sanitize_blog_archive_settings($input)
{
    $input = is_array($input) ? $input : [];
    $settings = [];
    foreach (vrp_get_blog_archive_settings() as $key => $default) {
        $value = $input[$key] ?? '';
        $settings[$key] = 'intro' === $key || 'seo_description' === $key
            ? sanitize_textarea_field($value)
            : sanitize_text_field($value);
    }
    return $settings;
}

function vrp_register_blog_archive_settings()
{
    register_setting('vrp_blog_archive_settings_group', 'vrp_blog_archive_settings', [
        'sanitize_callback' => 'vrp_sanitize_blog_archive_settings',
    ]);
}
add_action('admin_init', 'vrp_register_blog_archive_settings');

function vrp_add_blog_archive_settings()
{
    add_submenu_page(
        'edit.php?post_type=page',
        __('Postavke stranice Iz voćnjaka', 'vocne-rakije-petkovic'),
        __('Iz voćnjaka — tekst i SEO', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-blog-settings',
        'vrp_render_blog_archive_settings'
    );
}
add_action('admin_menu', 'vrp_add_blog_archive_settings');

function vrp_render_blog_archive_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = vrp_get_blog_archive_settings();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Postavke stranice Iz voćnjaka', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Blogove dodajte ili uredite u Objave. Za svaku objavu unesite naslov i tekst, postavite istaknutu sliku te po želji uredite izvadak i SEO podatke.', 'vocne-rakije-petkovic'); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url(admin_url('post-new.php')); ?>"><?php esc_html_e('Unesi novu blog objavu', 'vocne-rakije-petkovic'); ?></a> <a class="button" href="<?php echo esc_url(admin_url('edit.php')); ?>"><?php esc_html_e('Uredi postojeće objave', 'vocne-rakije-petkovic'); ?></a></p>
        <form action="options.php" method="post">
            <?php settings_fields('vrp_blog_archive_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="vrp-blog-eyebrow"><?php esc_html_e('Kratki naslov', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-blog-eyebrow" name="vrp_blog_archive_settings[eyebrow]" value="<?php echo esc_attr($settings['eyebrow']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-blog-title"><?php esc_html_e('Naslov — prvi red', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-blog-title" name="vrp_blog_archive_settings[title]" value="<?php echo esc_attr($settings['title']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-blog-title-accent"><?php esc_html_e('Naslov — istaknuti red', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-blog-title-accent" name="vrp_blog_archive_settings[title_accent]" value="<?php echo esc_attr($settings['title_accent']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-blog-intro"><?php esc_html_e('Uvodni tekst', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><textarea class="large-text" rows="4" id="vrp-blog-intro" name="vrp_blog_archive_settings[intro]"><?php echo esc_textarea($settings['intro']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-blog-seo-title"><?php esc_html_e('SEO naslov', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="large-text" type="text" id="vrp-blog-seo-title" name="vrp_blog_archive_settings[seo_title]" value="<?php echo esc_attr($settings['seo_title']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-blog-seo-description"><?php esc_html_e('SEO opis', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><textarea class="large-text" rows="3" id="vrp-blog-seo-description" name="vrp_blog_archive_settings[seo_description]"><?php echo esc_textarea($settings['seo_description']); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(__('Spremi promjene', 'vocne-rakije-petkovic')); ?>
        </form>
    </div>
    <?php
}

function vrp_set_blog_archive_posts_per_page($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_home()) {
        $query->set('posts_per_page', 3);
    }
}
add_action('pre_get_posts', 'vrp_set_blog_archive_posts_per_page');

function vrp_register_blog_seo_meta_box()
{
    add_meta_box(
        'vrp-blog-seo',
        __('SEO podaci bloga', 'vocne-rakije-petkovic'),
        'vrp_render_blog_seo_meta_box',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes_post', 'vrp_register_blog_seo_meta_box');

function vrp_render_blog_seo_meta_box($post)
{
    wp_nonce_field('vrp_save_blog_seo', 'vrp_blog_seo_nonce');
    ?>
    <p><label for="vrp-blog-seo-title"><?php esc_html_e('SEO naslov', 'vocne-rakije-petkovic'); ?></label></p>
    <input class="widefat" type="text" id="vrp-blog-seo-title" name="vrp_blog_seo_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_vrp_blog_seo_title', true)); ?>" />
    <p><label for="vrp-blog-seo-description"><?php esc_html_e('SEO opis', 'vocne-rakije-petkovic'); ?></label></p>
    <textarea class="widefat" rows="3" id="vrp-blog-seo-description" name="vrp_blog_seo_description"><?php echo esc_textarea(get_post_meta($post->ID, '_vrp_blog_seo_description', true)); ?></textarea>
    <?php
}

function vrp_save_blog_seo_meta($post_id)
{
    if (
        !isset($_POST['vrp_blog_seo_nonce'])
        || !wp_verify_nonce(wp_unslash($_POST['vrp_blog_seo_nonce']), 'vrp_save_blog_seo')
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_revision($post_id)
        || 'post' !== get_post_type($post_id)
        || !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    update_post_meta($post_id, '_vrp_blog_seo_title', sanitize_text_field(wp_unslash($_POST['vrp_blog_seo_title'] ?? '')));
    update_post_meta($post_id, '_vrp_blog_seo_description', sanitize_textarea_field(wp_unslash($_POST['vrp_blog_seo_description'] ?? '')));
}
add_action('save_post_post', 'vrp_save_blog_seo_meta');
