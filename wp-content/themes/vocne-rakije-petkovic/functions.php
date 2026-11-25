<?php
/**
 * Theme setup and shared helpers.
 */

if (!defined('ABSPATH')) {
    exit;
}

function vrp_theme_setup()
{
    load_theme_textdomain('vocne-rakije-petkovic', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    register_nav_menus([
        'primary' => __('Primary navigation', 'vocne-rakije-petkovic'),
        'footer' => __('Footer navigation', 'vocne-rakije-petkovic'),
    ]);
}
add_action('after_setup_theme', 'vrp_theme_setup');

function vrp_enqueue_assets()
{
    wp_enqueue_style('vrp-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap', [], null);
    wp_enqueue_style('vrp-style', get_stylesheet_uri(), [], '1.0.29');
    wp_enqueue_script('vrp-script', get_template_directory_uri() . '/assets/script.js', [], '1.0.4', true);
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'vrp_enqueue_assets');

function vrp_has_seo_plugin()
{
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION') || defined('SEOPRESS_VERSION');
}

function vrp_filter_product_document_title($title_parts)
{
    if (vrp_has_seo_plugin()) {
        return $title_parts;
    }

    if (is_front_page()) {
        $seo_title = get_theme_mod('vrp_home_seo_title', 'Voćne rakije i likeri iz Međimurja | OPG Petković');
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (is_page('o-nama')) {
        $seo_title = vrp_get_about_page_setting('seo_title');
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (vrp_is_contact_page()) {
        $seo_title = vrp_get_contact_page_setting('seo_title');
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (is_home()) {
        $seo_title = vrp_get_blog_archive_settings()['seo_title'];
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (is_singular('post')) {
        $seo_title = get_post_meta(get_queried_object_id(), '_vrp_blog_seo_title', true);
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (is_tax('product_category')) {
        $category_data = vrp_get_product_category_seo_data(get_queried_object());
        if (!empty($category_data['title'])) {
            $title_parts['title'] = $category_data['title'];
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (is_post_type_archive('product')) {
        $seo_title = vrp_get_product_archive_setting('vrp_product_archive_seo_title');
        if ($seo_title) {
            $title_parts['title'] = $seo_title;
            $title_parts['site'] = '';
        }
        return $title_parts;
    }

    if (!is_singular('product')) {
        return $title_parts;
    }

    $product_id = get_queried_object_id();
    if ('1' !== get_post_meta($product_id, '_vrp_is_product_family', true)) {
        return $title_parts;
    }

    $seo_title = get_post_meta($product_id, '_vrp_seo_title', true);
    if ($seo_title) {
        $title_parts['title'] = $seo_title;
        $title_parts['site'] = '';
    }
    return $title_parts;
}
add_filter('document_title_parts', 'vrp_filter_product_document_title');

function vrp_get_current_seo_description()
{
    if (vrp_has_seo_plugin()) {
        return '';
    }

    if (is_front_page()) {
        $description = get_theme_mod(
            'vrp_home_seo_description',
            'Obiteljski OPG Petković iz Šenkovca proizvodi voćne rakije i likere od plodova vlastitog voćnjaka. Upoznajte naše proizvode i pošaljite upit za narudžbu.'
        );
    } elseif (vrp_is_contact_page()) {
        $description = vrp_get_contact_page_setting('seo_description');
    } elseif (is_page('o-nama')) {
        $description = vrp_get_about_page_setting('seo_description');
    } elseif (is_home()) {
        $description = vrp_get_blog_archive_settings()['seo_description'];
    } elseif (is_tax('product_category')) {
        $category_data = vrp_get_product_category_seo_data(get_queried_object());
        $description = $category_data['description'] ?? '';
    } elseif (is_singular('post')) {
        $post_id = get_queried_object_id();
        $description = get_post_meta($post_id, '_vrp_blog_seo_description', true);
        if (!$description) {
            $description = get_the_excerpt($post_id);
        }
    } elseif (is_post_type_archive('product')) {
        $description = vrp_get_product_archive_setting('vrp_product_archive_seo_description');
    } elseif (is_singular()) {
        $post_id = get_queried_object_id();
        $description = get_the_excerpt($post_id);
        if (!$description) {
            $post = get_post($post_id);
            if ($post) {
                $description = wp_trim_words(
                    wp_strip_all_tags(strip_shortcodes($post->post_content)),
                    30,
                    '…'
                );
            }
        }
        if (is_singular('product') && '1' === get_post_meta($post_id, '_vrp_is_product_family', true)) {
            $description = get_post_meta($post_id, '_vrp_seo_description', true) ?: $description;
        }
    } else {
        return '';
    }

    $description = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $description)));
    return $description;
}

function vrp_output_seo_description()
{
    $description = vrp_get_current_seo_description();
    if ($description) {
        printf("<meta name=\"description\" content=\"%s\" />\n", esc_attr($description));
    }
}
add_action('wp_head', 'vrp_output_seo_description', 1);

function vrp_filter_product_variant_canonical($canonical_url, $post)
{
    if (vrp_has_seo_plugin() || !($post instanceof WP_Post) || 'product' !== $post->post_type) {
        return $canonical_url;
    }

    $family_slug = get_post_meta($post->ID, '_vrp_product_family', true);
    if (!$family_slug || '1' === get_post_meta($post->ID, '_vrp_is_product_family', true)) {
        return $canonical_url;
    }

    $family = get_page_by_path($family_slug, OBJECT, 'product');
    return $family ? get_permalink($family->ID) : $canonical_url;
}
add_filter('get_canonical_url', 'vrp_filter_product_variant_canonical', 10, 2);

function vrp_get_current_canonical_url()
{
    if (is_singular()) {
        $post = get_queried_object();
        return $post instanceof WP_Post ? (wp_get_canonical_url($post) ?: get_permalink($post)) : '';
    }

    if (is_front_page()) {
        return home_url('/');
    }

    if (is_tax('product_category')) {
        $term = get_queried_object();
        $term_link = $term instanceof WP_Term ? get_term_link($term) : '';
        return is_wp_error($term_link) ? '' : $term_link;
    }

    if (is_post_type_archive('product')) {
        return get_post_type_archive_link('product') ?: '';
    }

    if (is_home() || is_archive()) {
        return get_pagenum_link(max(1, absint(get_query_var('paged'))));
    }

    return '';
}

function vrp_output_archive_canonical()
{
    if (vrp_has_seo_plugin() || is_singular()) {
        return;
    }

    $canonical_url = vrp_get_current_canonical_url();
    if ($canonical_url) {
        printf("<link rel=\"canonical\" href=\"%s\" />\n", esc_url($canonical_url));
    }
}
add_action('wp_head', 'vrp_output_archive_canonical', 3);

function vrp_get_social_image()
{
    $image_id = is_singular() ? get_post_thumbnail_id(get_queried_object_id()) : 0;
    if (!$image_id && is_front_page()) {
        $image_id = absint(get_theme_mod('vrp_slider_image_1'));
    }
    if (!$image_id) {
        $image_id = absint(get_theme_mod('custom_logo'));
    }
    if (!$image_id) {
        $image_id = absint(get_option('site_icon'));
    }

    if (!$image_id) {
        return [
            'url' => vrp_asset_url('logo.png'),
            'alt' => get_bloginfo('name'),
        ];
    }

    $image = wp_get_attachment_image_src($image_id, 'full');
    if (!$image) {
        return [];
    }

    $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    return [
        'url' => $image[0],
        'alt' => $alt ?: get_bloginfo('name'),
    ];
}

function vrp_output_social_metadata()
{
    if (vrp_has_seo_plugin() || is_404() || is_search()) {
        return;
    }

    $canonical_url = vrp_get_current_canonical_url();
    $description = vrp_get_current_seo_description();
    if (!$canonical_url) {
        return;
    }

    $title = wp_get_document_title();
    $image = vrp_get_social_image();
    $type = is_singular('post') ? 'article' : 'website';
    ?>
    <meta property="og:locale" content="<?php echo esc_attr(str_replace('_', '-', get_locale())); ?>" />
    <meta property="og:type" content="<?php echo esc_attr($type); ?>" />
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
    <meta property="og:title" content="<?php echo esc_attr($title); ?>" />
    <?php if ($description) : ?><meta property="og:description" content="<?php echo esc_attr($description); ?>" /><?php endif; ?>
    <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>" />
    <?php if (!empty($image['url'])) : ?>
        <meta property="og:image" content="<?php echo esc_url($image['url']); ?>" />
        <meta property="og:image:alt" content="<?php echo esc_attr($image['alt']); ?>" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:image" content="<?php echo esc_url($image['url']); ?>" />
    <?php else : ?>
        <meta name="twitter:card" content="summary" />
    <?php endif; ?>
    <meta name="twitter:title" content="<?php echo esc_attr($title); ?>" />
    <?php if ($description) : ?><meta name="twitter:description" content="<?php echo esc_attr($description); ?>" /><?php endif; ?>
    <?php
}
add_action('wp_head', 'vrp_output_social_metadata', 4);

function vrp_output_business_schema()
{
    if (vrp_has_seo_plugin()) {
        return;
    }

    $site_url = home_url('/');
    $company = vrp_get_text_setting('vrp_contact_company', 'OPG Petković Nada');
    $phone = vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317');
    $email = vrp_get_text_setting('vrp_contact_email', 'info@vocnerakije.hr');
    $address_text = vrp_get_text_setting('vrp_contact_address', "Zrinskih 4a, Šenkovec\n40000 Čakovec, Hrvatska");
    $address_lines = preg_split('/\r\n|\r|\n/', trim($address_text));
    $street_address = trim($address_lines[0] ?? '');
    $locality = '';
    $postal_code = '';

    if (strpos($street_address, ',') !== false) {
        list($street_address, $locality) = array_map('trim', explode(',', $street_address, 2));
    }
    if (isset($address_lines[1]) && preg_match('/^(\d{4,5})\s+([^,]+)/u', trim($address_lines[1]), $address_match)) {
        $postal_code = $address_match[1];
        if (!$locality) {
            $locality = trim($address_match[2]);
        }
    }

    $social_image = vrp_get_social_image();
    $logo_id = absint(get_theme_mod('custom_logo'));
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    $business = [
        '@type' => 'LocalBusiness',
        '@id' => trailingslashit($site_url) . '#business',
        'name' => $company,
        'url' => $site_url,
        'areaServed' => 'Međimurje',
    ];

    if (!empty($social_image['url'])) {
        $business['image'] = $social_image['url'];
    }
    if ($logo_url) {
        $business['logo'] = $logo_url;
    }
    if ($phone) {
        $business['telephone'] = $phone;
        $business['contactPoint'] = [
            '@type' => 'ContactPoint',
            'telephone' => $phone,
            'contactType' => 'customer service',
            'availableLanguage' => 'Croatian',
        ];
    }
    if ($email) {
        $business['email'] = $email;
        if (isset($business['contactPoint'])) {
            $business['contactPoint']['email'] = $email;
        }
    }
    if ($street_address) {
        $address = [
            '@type' => 'PostalAddress',
            'streetAddress' => $street_address,
            'addressCountry' => 'HR',
        ];
        if ($locality) {
            $address['addressLocality'] = $locality;
        }
        if ($postal_code) {
            $address['postalCode'] = $postal_code;
        }
        $business['address'] = $address;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => trailingslashit($site_url) . '#website',
                'url' => $site_url,
                'name' => get_bloginfo('name'),
                'inLanguage' => get_locale(),
                'publisher' => ['@id' => trailingslashit($site_url) . '#business'],
            ],
            $business,
        ],
    ];
    $json = wp_json_encode(
        $schema,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );

    if ($json) {
        printf("<script type=\"application/ld+json\">%s</script>\n", $json);
    }
}
add_action('wp_head', 'vrp_output_business_schema', 5);

function vrp_filter_product_sitemap_query_args($args, $post_type)
{
    $post_type_name = $post_type instanceof WP_Post_Type ? $post_type->name : $post_type;
    if ('product' !== $post_type_name) {
        return $args;
    }

    $family_filter = [
        'relation' => 'OR',
        ['key' => '_vrp_is_product_family', 'value' => '1'],
        ['key' => '_vrp_product_family', 'compare' => 'NOT EXISTS'],
        ['key' => '_vrp_product_family', 'value' => '', 'compare' => '='],
    ];
    if (!empty($args['meta_query'])) {
        $args['meta_query'] = [
            'relation' => 'AND',
            $args['meta_query'],
            $family_filter,
        ];
    } else {
        $args['meta_query'] = $family_filter;
    }

    return $args;
}
add_filter('wp_sitemaps_posts_query_args', 'vrp_filter_product_sitemap_query_args', 10, 2);

function vrp_register_sitemap_rewrite_rule()
{
    add_rewrite_rule('^(wp-)?sitemap\.xml$', 'index.php?vrp_sitemap=1', 'top');
}
add_action('init', 'vrp_register_sitemap_rewrite_rule', 100);

function vrp_register_sitemap_query_var($query_vars)
{
    $query_vars[] = 'vrp_sitemap';
    return $query_vars;
}
add_filter('query_vars', 'vrp_register_sitemap_query_var');

function vrp_flush_sitemap_rewrite_rules_once()
{
    if (get_option('vrp_sitemap_rewrite_flushed')) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('vrp_sitemap_rewrite_flushed', '1');
}
add_action('init', 'vrp_flush_sitemap_rewrite_rules_once', 101);

function vrp_get_public_sitemap_url($url)
{
    $url_path = wp_parse_url($url, PHP_URL_PATH);
    if (!$url_path) {
        return '';
    }

    $site_path = untrailingslashit((string) wp_parse_url(home_url('/'), PHP_URL_PATH));
    if (
        $site_path
        && ($url_path === $site_path || 0 === strpos($url_path, $site_path . '/'))
    ) {
        $url_path = substr($url_path, strlen($site_path));
    }

    $public_url = 'https://vocnerakije.hr/' . ltrim($url_path, '/');
    $query = wp_parse_url($url, PHP_URL_QUERY);
    if ($query) {
        $public_url .= '?' . $query;
    }

    return $public_url;
}

function vrp_get_sitemap_urls()
{
    $urls = [];
    $add_url = function ($url, $last_modified = '') use (&$urls) {
        $url = vrp_get_public_sitemap_url($url);
        if (!$url) {
            return;
        }

        if (!isset($urls[$url])) {
            $urls[$url] = ['lastmod' => $last_modified];
        } elseif (!$urls[$url]['lastmod'] && $last_modified) {
            $urls[$url]['lastmod'] = $last_modified;
        }
    };

    $add_url(home_url('/'));

    $content = get_posts([
        'post_type' => ['page', 'post', 'product'],
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'modified',
        'order' => 'DESC',
    ]);

    foreach ($content as $post) {
        if ($post->post_password) {
            continue;
        }

        if ('product' === $post->post_type) {
            $family_slug = get_post_meta($post->ID, '_vrp_product_family', true);
            $is_family = '1' === get_post_meta($post->ID, '_vrp_is_product_family', true);
            if ($family_slug && !$is_family) {
                continue;
            }
        }

        $add_url(get_permalink($post), get_post_modified_time('c', true, $post));
    }

    $product_archive_url = get_post_type_archive_link('product');
    if ($product_archive_url) {
        $add_url($product_archive_url);
    }

    ksort($urls, SORT_STRING);
    return $urls;
}

function vrp_output_sitemap()
{
    $is_sitemap_request = '1' === get_query_var('vrp_sitemap');
    if (!$is_sitemap_request && isset($_SERVER['REQUEST_URI'])) {
        $request_path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
        $is_sitemap_request = (bool) preg_match('~(?:^|/)(?:wp-)?sitemap\.xml$~', (string) $request_path);
    }

    if (!$is_sitemap_request) {
        return;
    }

    status_header(200);
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex, follow');

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (vrp_get_sitemap_urls() as $url => $entry) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
        if ($entry['lastmod']) {
            echo '    <lastmod>' . esc_html($entry['lastmod']) . "</lastmod>\n";
        }
        echo "  </url>\n";
    }
    echo "</urlset>\n";
    exit;
}
add_action('template_redirect', 'vrp_output_sitemap', 0);

function vrp_asset_url($path)
{
    return trailingslashit(get_template_directory_uri()) . 'assets/images/' . ltrim($path, '/');
}

function vrp_add_favicon_fallback()
{
    if (has_site_icon()) {
        return;
    }

    printf(
        "<link rel=\"icon\" href=\"%s\" type=\"image/png\" sizes=\"512x512\" />\n",
        esc_url(vrp_asset_url('favicon.png') . '?v=3')
    );
}
add_action('wp_head', 'vrp_add_favicon_fallback', 2);

function vrp_get_text_setting($setting, $fallback = '')
{
    $defaults = [
        'vrp_hero_eyebrow' => 'Obiteljska proizvodnja · Šenkovec',
        'vrp_hero_title' => 'Voće sazrijevano u Međimurju.',
        'vrp_hero_content' => 'Prirodne voćne rakije i likeri izrađeni od plodova našeg voćnjaka. Svaku seriju pripremamo s pažnjom kako bismo sačuvali prirodan karakter voća i tradiciju Međimurja.',
        'vrp_hero_primary_label' => 'Otkrijte ponudu',
        'vrp_hero_secondary_label' => 'Upoznajte našu priču',
        'vrp_hero_points' => 'Provjereno voće|Tradicionalna destilacija|Laboratorijski ispitano',
        'vrp_hero_stamp_name' => 'Petković',
        'vrp_hero_stamp_location' => 'Međimurje',
        'vrp_slider_caption_1' => 'Svaka boca nosi okus ploda i kraja.',
        'vrp_slider_caption_2' => '',
        'vrp_slider_caption_3' => '',
        'vrp_slider_caption_4' => '',
        'vrp_intro_title' => 'Od kruške do čaše. Bez prečaca.',
        'vrp_intro_1' => 'Vlastito voće|Kruške i šljive rastu u našim voćnjacima na oko jednog hektara.',
        'vrp_intro_2' => 'Vrijeme zrenja|Destilati odležavaju i razvijaju svoj puni, prepoznatljiv karakter.',
        'vrp_intro_3' => 'Male serije|Proizvodnja je obiteljska, osobna i usmjerena na kvalitetu.',
        'vrp_products_eyebrow' => 'Iz naše ponude',
        'vrp_products_title' => 'Šest okusa. Jedna tradicija.',
        'vrp_products_content' => 'Viljamovka, žganica, marelica i tri bogata likera u pakiranju od 700 ml — pronađite okus za svoj stol ili poseban poklon.',
        'vrp_products_limit' => '3',
        'vrp_products_button' => 'Pogledaj sve proizvode i pakiranja',
        'vrp_special_eyebrow' => 'Posebna ponuda',
        'vrp_special_title' => 'Rakije koje već svojom bocom pričaju priču.',
        'vrp_special_content' => 'TRIS s tri okusa, degustacijske epruvete i viljamovka s kruškom.',
        'vrp_special_button' => 'Pogledaj cijelu posebnu ponudu',
        'vrp_story_eyebrow' => 'Naša priča',
        'vrp_story_title' => 'Mala proizvodnja. Velika pažnja.',
        'vrp_story_content' => 'U vlastitim voćnjacima u Šenkovcu uzgajamo kruške i šljive, a svaki plod pažljivo odabiremo. Kroz tradicionalnu fermentaciju i destilaciju nastaju rakije u manjim, obiteljskim serijama. Pažljivim radom čuvamo prirodni karakter voća i stvaramo prepoznatljive okuse koji u svakoj boci nose dio našeg kraja.',
        'vrp_story_stat_1' => '1 ha|voćnjaka',
        'vrp_story_stat_2' => '5|prepoznatljivih okusa',
        'vrp_story_stat_3' => '100%|obiteljski pristup',
        'vrp_story_button' => 'Više o gospodarstvu',
        'vrp_gift_eyebrow' => 'Novo u ponudi',
        'vrp_gift_title' => 'Mali pozdrav. Velika prilika.',
        'vrp_gift_content' => 'Rakije u elegantnim epruvetama od 20 ml idealne su za degustacije, proslave i personalizirane poklone.',
        'vrp_gift_button' => 'Pošaljite upit',
        'vrp_gift_badge' => '20 ml',
        'vrp_cta_eyebrow' => 'Šenkovec · Međimurje',
        'vrp_cta_title' => 'Želite degustirati ili naručiti?',
        'vrp_cta_content' => 'Javite nam se i zajedno ćemo pronaći pravo pakiranje za vas.',
        'vrp_cta_button' => 'Nazovite nas',
    ];

    return get_theme_mod($setting, array_key_exists($setting, $defaults) ? $defaults[$setting] : $fallback);
}

function vrp_get_page_link($page_id_setting)
{
    $page_id = absint(get_theme_mod($page_id_setting));
    return $page_id ? get_permalink($page_id) : '#';
}

function vrp_primary_menu_fallback()
{
    $products_url = get_post_type_archive_link('product');
    $posts_page_id = absint(get_option('page_for_posts'));
    $posts_url = $posts_page_id ? get_permalink($posts_page_id) : home_url('/blog/');
    $about_page_id = absint(get_theme_mod('vrp_about_page'));
    $contact_page_id = absint(get_theme_mod('vrp_contact_page'));
    $links = [
        [
            'url' => home_url('/'),
            'label' => __('Početna', 'vocne-rakije-petkovic'),
            'active' => is_front_page(),
        ],
        [
            'url' => $products_url ?: home_url('/proizvodi/'),
            'label' => __('Proizvodi', 'vocne-rakije-petkovic'),
            'active' => is_post_type_archive('product') || is_singular('product') || is_tax('product_category'),
        ],
        [
            'url' => vrp_get_page_link('vrp_about_page'),
            'label' => __('O nama', 'vocne-rakije-petkovic'),
            'active' => $about_page_id && is_page($about_page_id),
        ],
        [
            'url' => $posts_url,
            'label' => __('Iz voćnjaka', 'vocne-rakije-petkovic'),
            'active' => is_home() || is_singular('post') || is_category() || is_tag() || is_author() || is_date(),
        ],
        [
            'url' => vrp_get_page_link('vrp_contact_page'),
            'label' => __('Kontakt', 'vocne-rakije-petkovic'),
            'active' => $contact_page_id && is_page($contact_page_id),
        ],
    ];

    foreach ($links as $link) {
        $attributes = $link['active'] ? ' class="active" aria-current="page"' : '';
        printf('<a href="%s"%s>%s</a>', esc_url($link['url']), $attributes, esc_html($link['label']));
    }

    $order_phone = preg_replace('/[^+0-9]/', '', vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317'));
    if (!$order_phone) {
        $order_phone = '+385993173317';
    }

    $order_phone_label = vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317');
    echo '<details class="nav-order-menu">';
    echo '<summary class="nav-order">' . esc_html__('Naruči', 'vocne-rakije-petkovic') . '</summary>';
    echo '<div class="nav-order-options">';
    echo '<a href="tel:' . esc_attr($order_phone) . '">' . esc_html__('Nazovi:', 'vocne-rakije-petkovic') . ' ' . esc_html($order_phone_label) . '</a>';
    echo '<a href="' . esc_url(vrp_get_page_link('vrp_contact_page')) . '#inquiry">' . esc_html__('Pošalji upit putem obrasca', 'vocne-rakije-petkovic') . '</a>';
    echo '</div>';
    echo '</details>';
}

function vrp_format_price($price)
{
    return '€' === substr(trim((string) $price), -1) ? trim((string) $price) : trim((string) $price) . ' €';
}

function vrp_get_product_packaging($product_id)
{
    $rows = get_post_meta($product_id, '_vrp_packaging', true);
    return is_array($rows) ? $rows : [];
}

function vrp_get_product_gallery($product_id)
{
    if ('1' === get_post_meta($product_id, '_vrp_is_product_family', true)) {
        $family_slug = get_post_field('post_name', $product_id);
        $variants = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_vrp_product_family',
            'meta_value' => $family_slug,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ]);
        $variant_gallery = [];
        foreach (vrp_get_product_packaging($product_id) as $row) {
            $variant_product_id = absint($row['variant_product_id'] ?? 0);
            if ($variant_product_id) {
                foreach ($variants as $variant) {
                    if ((int) $variant->ID !== $variant_product_id) {
                        continue;
                    }

                    $image_id = get_post_thumbnail_id($variant->ID);
                    if ($image_id) {
                        $variant_gallery[] = $image_id;
                    }
                    break;
                }
                continue;
            }

            $label = $row['label'] ?? '';
            if (!$label) {
                continue;
            }
            $is_dark = false !== stripos($label, 'TAMNA');
            $variant_label = preg_replace('/\s*(?:-|–)\s*TAMNA$/iu', '', $label);
            foreach ($variants as $variant) {
                $variant_title = get_the_title($variant);
                $variant_is_dark = false !== stripos($variant_title, 'TAMNA');
                if (strcasecmp(get_post_meta($variant->ID, '_vrp_packaging_label', true), $variant_label) !== 0 || $is_dark !== $variant_is_dark) {
                    continue;
                }
                $image_id = get_post_thumbnail_id($variant->ID);
                if ($image_id) {
                    if ($is_dark && false === stripos(get_the_title($image_id) . ' ' . get_post_meta($image_id, '_wp_attachment_image_alt', true), 'TAMNA')) {
                        break;
                    }
                    $variant_gallery[] = $image_id;
                }
                break;
            }
        }
        if ($variant_gallery) {
            return array_values(array_unique($variant_gallery));
        }
    }

    $gallery = get_post_meta($product_id, '_vrp_gallery', true);
    $gallery_ids = $gallery ? array_filter(array_map('absint', explode(',', $gallery))) : [];
    if (empty($gallery_ids) && has_post_thumbnail($product_id)) {
        $gallery_ids[] = get_post_thumbnail_id($product_id);
    }
    return array_values(array_unique($gallery_ids));
}

function vrp_get_product_family_url($product_id)
{
    $family_slug = get_post_meta($product_id, '_vrp_product_family', true);
    if (!$family_slug) {
        return get_permalink($product_id);
    }

    $family = get_page_by_path($family_slug, OBJECT, 'product');
    return $family ? get_permalink($family) : get_permalink($product_id);
}

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/contact-form.php';
require_once get_template_directory() . '/inc/theme-setup.php';
require_once get_template_directory() . '/inc/product-archive-settings.php';
require_once get_template_directory() . '/inc/about-page-settings.php';
require_once get_template_directory() . '/inc/contact-page-settings.php';
require_once get_template_directory() . '/inc/blog-settings.php';
require_once get_template_directory() . '/inc/product-price-list.php';

function vrp_register_admin_assets($hook)
{
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    if ('product' !== get_post_type()) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style('vrp-admin', get_template_directory_uri() . '/assets/admin.css', [], '1.0.0');
    wp_enqueue_script('vrp-admin', get_template_directory_uri() . '/assets/admin.js', ['jquery'], '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'vrp_register_admin_assets');
