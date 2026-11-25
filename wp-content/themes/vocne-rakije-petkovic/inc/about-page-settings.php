<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_get_about_page_settings()
{
    $defaults = [
        'hero_eyebrow' => 'Obiteljsko gospodarstvo Petković',
        'hero_title' => 'Od zemlje.',
        'hero_title_accent' => 'Do dobre čaše.',
        'hero_intro' => 'U Šenkovcu, u manjim serijama i tradicionalnim postupkom, stvaramo voćne rakije i likere od plodova našeg kraja.',
        'orchard_eyebrow' => 'Naša zemlja, naše voće',
        'orchard_title' => 'Priroda određuje ritam — mi brinemo o detaljima.',
        'orchard_paragraphs' => "Na obiteljskom gospodarstvu uzgajamo kruške sorte žuta viljamovka te šljive bistrice, čačanske rodne, ljepotice, rane i valjevke. U nasadu od približno jednog hektara njegujemo voće i pratimo njegov prirodni ritam.\n\nPlodove beremo kada su zreli i pripremamo ih za preradu bez kemijskih zaštitnih sredstava. Svaka sorta traži svoj pristup, a pažljiv odabir voća prvi je korak do prepoznatljivog okusa.\n\nFermentaciju, tradicionalnu destilaciju i odležavanje pratimo osobno. Rakije i likeri nastaju u manjim serijama — za uživanje, druženje i darivanje, a ne za masovnu proizvodnju.",
        'orchard_image_id' => 0,
        'orchard_image_alt' => 'Voćnjak obiteljskog gospodarstva Petković',
        'process_eyebrow' => 'Naš postupak',
        'process_title' => 'Četiri koraka, bez prečaca.',
        'process_1_title' => 'Berba',
        'process_1_description' => 'Zrelo voće biramo pažljivo i ručno, prateći prirodni ritam voćnjaka.',
        'process_2_title' => 'Priprema',
        'process_2_description' => 'Plodove pripremamo za kontroliranu fermentaciju, uz pažnju prema sorti i njezinu karakteru.',
        'process_3_title' => 'Destilacija',
        'process_3_description' => 'Tradicionalnim postupkom čuvamo miris i punoću voća iz kojeg nastaje destilat.',
        'process_4_title' => 'Odležavanje',
        'process_4_description' => 'Destilati odležavaju u staklenim, inox i drvenim posudama prije punjenja.',
        'quality_eyebrow' => 'Sigurnost i kvaliteta',
        'quality_title' => 'Provjerena kvaliteta svake serije.',
        'quality_items' => "Proizvode laboratorijski ispituje BIOINSTITUT u Čakovcu.\nGospodarstvo je upisano u Upisnik poljoprivrednih gospodarstava.\nUpisani smo u Upisnik destilatera Ministarstva poljoprivrede.\nProizvodnja je evidentirana kod Carinarnice Varaždin.",
        'seo_title' => 'O nama | Obiteljsko gospodarstvo Petković',
        'seo_description' => 'Upoznajte obiteljsko gospodarstvo Petković iz Šenkovca: naš voćnjak, tradicionalni postupak izrade te voćne rakije i likere u manjim serijama.',
    ];

    $settings = get_option('vrp_about_page_settings', []);
    return wp_parse_args(is_array($settings) ? $settings : [], $defaults);
}

function vrp_get_about_page_setting($key)
{
    $settings = vrp_get_about_page_settings();
    return $settings[$key] ?? '';
}

function vrp_sanitize_about_page_settings($input)
{
    $input = is_array($input) ? $input : [];
    $defaults = vrp_get_about_page_settings();
    $textarea_fields = ['hero_intro', 'orchard_paragraphs', 'process_1_description', 'process_2_description', 'process_3_description', 'process_4_description', 'quality_items', 'seo_description'];
    $settings = [];

    foreach ($defaults as $key => $default) {
        $value = $input[$key] ?? '';
        if ('orchard_image_id' === $key) {
            $image_id = absint($value);
            $settings[$key] = $image_id && wp_attachment_is_image($image_id) ? $image_id : 0;
        } elseif (in_array($key, $textarea_fields, true)) {
            $settings[$key] = sanitize_textarea_field($value);
        } else {
            $settings[$key] = sanitize_text_field($value);
        }
    }

    return $settings;
}

function vrp_register_about_page_settings()
{
    register_setting('vrp_about_page_settings_group', 'vrp_about_page_settings', [
        'sanitize_callback' => 'vrp_sanitize_about_page_settings',
    ]);
}
add_action('admin_init', 'vrp_register_about_page_settings');

function vrp_add_about_page_settings()
{
    add_submenu_page(
        'edit.php?post_type=page',
        __('Uređivanje stranice O nama', 'vocne-rakije-petkovic'),
        __('O nama — tekst i SEO', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-about-settings',
        'vrp_render_about_page_settings'
    );
}
add_action('admin_menu', 'vrp_add_about_page_settings');

function vrp_enqueue_about_page_media($hook)
{
    if ('page_page_vrp-about-settings' === $hook) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'vrp_enqueue_about_page_media');

function vrp_ensure_about_page_exists()
{
    $about_page_id = absint(get_theme_mod('vrp_about_page'));
    if ($about_page_id && 'publish' === get_post_status($about_page_id)) {
        return;
    }

    $about_page = get_page_by_path('o-nama', OBJECT, 'page');
    if (!$about_page) {
        $about_page_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => __('O nama', 'vocne-rakije-petkovic'),
            'post_name' => 'o-nama',
            'post_content' => '',
        ], true);
        if (is_wp_error($about_page_id)) {
            error_log('Voćne rakije Petković: O nama page could not be created: ' . $about_page_id->get_error_message());
            return;
        }
    } else {
        $about_page_id = $about_page->ID;
    }

    set_theme_mod('vrp_about_page', absint($about_page_id));
}
add_action('init', 'vrp_ensure_about_page_exists', 20);

function vrp_render_about_text_field($key, $label, $description = '', $textarea = false, $rows = 4)
{
    $settings = vrp_get_about_page_settings();
    $value = $settings[$key] ?? '';
    ?>
    <tr>
        <th scope="row"><label for="vrp-about-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
        <td>
            <?php if ($textarea) : ?>
                <textarea class="large-text" rows="<?php echo esc_attr($rows); ?>" id="vrp-about-<?php echo esc_attr($key); ?>" name="vrp_about_page_settings[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($value); ?></textarea>
            <?php else : ?>
                <input class="large-text" type="text" id="vrp-about-<?php echo esc_attr($key); ?>" name="vrp_about_page_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" />
            <?php endif; ?>
            <?php if ($description) : ?><p class="description"><?php echo esc_html($description); ?></p><?php endif; ?>
        </td>
    </tr>
    <?php
}

function vrp_render_about_page_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = vrp_get_about_page_settings();
    $image_id = absint($settings['orchard_image_id']);
    $preview = $image_id
        ? wp_get_attachment_image($image_id, 'medium')
        : '<img src="' . esc_url(vrp_asset_url('orchard.jpg')) . '" alt="" />';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Uređivanje stranice O nama', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Uredite tekstove, fotografiju voćnjaka i SEO podatke koji se prikazuju na stranici O nama.', 'vocne-rakije-petkovic'); ?></p>
        <form action="options.php" method="post">
            <?php settings_fields('vrp_about_page_settings_group'); ?>
            <h2><?php esc_html_e('Uvod', 'vocne-rakije-petkovic'); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                vrp_render_about_text_field('hero_eyebrow', __('Kratki naslov iznad glavnog naslova', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('hero_title', __('Glavni naslov — prvi red', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('hero_title_accent', __('Glavni naslov — istaknuti red', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('hero_intro', __('Uvodni tekst', 'vocne-rakije-petkovic'), '', true, 3);
                ?>
            </table>

            <h2><?php esc_html_e('Voćnjak i gospodarstvo', 'vocne-rakije-petkovic'); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                vrp_render_about_text_field('orchard_eyebrow', __('Kratki naslov', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('orchard_title', __('Naslov odjeljka', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('orchard_paragraphs', __('Tekst odjeljka', 'vocne-rakije-petkovic'), __('Odvojite odlomke praznim retkom.', 'vocne-rakije-petkovic'), true, 10);
                ?>
                <tr>
                    <th scope="row"><label for="vrp-about-orchard-image-alt"><?php esc_html_e('Opis fotografije (alt)', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="large-text" type="text" id="vrp-about-orchard-image-alt" name="vrp_about_page_settings[orchard_image_alt]" value="<?php echo esc_attr($settings['orchard_image_alt']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Fotografija voćnjaka', 'vocne-rakije-petkovic'); ?></th>
                    <td>
                        <input type="hidden" id="vrp-about-orchard-image-id" name="vrp_about_page_settings[orchard_image_id]" value="<?php echo esc_attr($image_id); ?>" />
                        <div id="vrp-about-orchard-image-preview" style="max-width:480px;margin-bottom:12px"><?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                        <button type="button" class="button" id="vrp-about-select-image"><?php esc_html_e('Odaberi fotografiju', 'vocne-rakije-petkovic'); ?></button>
                        <button type="button" class="button" id="vrp-about-remove-image"<?php echo $image_id ? '' : ' hidden'; ?>><?php esc_html_e('Vrati zadanu fotografiju', 'vocne-rakije-petkovic'); ?></button>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e('Postupak izrade', 'vocne-rakije-petkovic'); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                vrp_render_about_text_field('process_eyebrow', __('Kratki naslov', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('process_title', __('Naslov odjeljka', 'vocne-rakije-petkovic'));
                for ($step = 1; $step <= 4; $step++) {
                    vrp_render_about_text_field('process_' . $step . '_title', sprintf(__('Korak %d — naslov', 'vocne-rakije-petkovic'), $step));
                    vrp_render_about_text_field('process_' . $step . '_description', sprintf(__('Korak %d — opis', 'vocne-rakije-petkovic'), $step), '', true, 2);
                }
                ?>
            </table>

            <h2><?php esc_html_e('Kvaliteta', 'vocne-rakije-petkovic'); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                vrp_render_about_text_field('quality_eyebrow', __('Kratki naslov', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('quality_title', __('Naslov odjeljka', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('quality_items', __('Popis provjera i upisa', 'vocne-rakije-petkovic'), __('Unesite jednu stavku po retku.', 'vocne-rakije-petkovic'), true, 6);
                ?>
            </table>

            <h2><?php esc_html_e('SEO', 'vocne-rakije-petkovic'); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                vrp_render_about_text_field('seo_title', __('SEO naslov', 'vocne-rakije-petkovic'));
                vrp_render_about_text_field('seo_description', __('SEO opis', 'vocne-rakije-petkovic'), '', true, 3);
                ?>
            </table>
            <?php submit_button(__('Spremi promjene', 'vocne-rakije-petkovic')); ?>
        </form>
    </div>
    <script>
        jQuery(function ($) {
            let frame;
            $('#vrp-about-select-image').on('click', function (event) {
                event.preventDefault();
                if (!frame) {
                    frame = wp.media({
                        title: '<?php echo esc_js(__('Odaberite fotografiju voćnjaka', 'vocne-rakije-petkovic')); ?>',
                        button: { text: '<?php echo esc_js(__('Koristi fotografiju', 'vocne-rakije-petkovic')); ?>' },
                        library: { type: 'image' },
                        multiple: false
                    });
                    frame.on('select', function () {
                        const image = frame.state().get('selection').first().toJSON();
                        const imageUrl = image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url;
                        $('#vrp-about-orchard-image-id').val(image.id);
                        $('#vrp-about-orchard-image-preview').empty().append($('<img>', { src: imageUrl, alt: image.alt || '', style: 'max-width:100%;height:auto' }));
                        $('#vrp-about-remove-image').prop('hidden', false);
                    });
                }
                frame.open();
            });
            $('#vrp-about-remove-image').on('click', function (event) {
                event.preventDefault();
                $('#vrp-about-orchard-image-id').val('0');
                $('#vrp-about-orchard-image-preview').html('<?php echo esc_js('<img src="' . esc_url(vrp_asset_url('orchard.jpg')) . '" alt="" />'); ?>');
                $(this).prop('hidden', true);
            });
        });
    </script>
    <?php
}
