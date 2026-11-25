<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_price_list_archive_path()
{
    $uploads = wp_upload_dir();
    return trailingslashit($uploads['basedir']) . 'cjenik/arhiva';
}

function vrp_price_list_current_path()
{
    $uploads = wp_upload_dir();
    return trailingslashit($uploads['basedir']) . 'cjenik/current.csv';
}

function vrp_price_list_archive_files()
{
    $path = vrp_price_list_archive_path();
    if (!is_dir($path)) {
        return [];
    }

    $files = glob(trailingslashit($path) . '*.csv');
    if (!$files) {
        return [];
    }

    usort($files, function ($first, $second) {
        return filemtime($second) <=> filemtime($first);
    });

    return $files;
}

function vrp_price_list_archive_file_by_name($filename)
{
    $filename = (string) $filename;
    if ('' === $filename || $filename !== basename($filename) || 'csv' !== strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
        return false;
    }

    foreach (vrp_price_list_archive_files() as $file) {
        if ($filename === basename($file)) {
            return $file;
        }
    }

    return false;
}

function vrp_price_list_archive_delete_after($file)
{
    if (preg_match('/_(\d{8})_(\d{4})\.csv$/', basename($file), $matches)) {
        $published = DateTimeImmutable::createFromFormat('!Ymd_Hi', $matches[1] . '_' . $matches[2], wp_timezone());
        if ($published && $published->format('Ymd_Hi') === $matches[1] . '_' . $matches[2]) {
            return $published->getTimestamp() + (15 * DAY_IN_SECONDS);
        }
    }

    return filemtime($file) + (15 * DAY_IN_SECONDS);
}

function vrp_price_list_invalid_archives()
{
    $invalid_archives = get_option('vrp_price_list_invalid_archives', []);
    return is_array($invalid_archives) ? $invalid_archives : [];
}

function vrp_price_list_latest_valid_archive()
{
    $invalid_archives = vrp_price_list_invalid_archives();
    foreach (vrp_price_list_archive_files() as $file) {
        if (!isset($invalid_archives[basename($file)])) {
            return $file;
        }
    }

    return false;
}

function vrp_price_list_scope()
{
    $scope = get_option('vrp_price_list_scope', 'all');
    return in_array($scope, ['all', 'location'], true) ? $scope : 'all';
}

function vrp_price_list_oib()
{
    return get_option('vrp_price_list_oib', get_theme_mod('vrp_contact_oib', '42115479386'));
}

function vrp_sanitize_price_list_scope($scope)
{
    return in_array($scope, ['all', 'location'], true) ? $scope : 'all';
}

function vrp_sanitize_price_list_oib($oib)
{
    return preg_replace('/\D+/', '', (string) $oib);
}

function vrp_register_price_list_settings()
{
    register_setting('vrp_price_list_settings', 'vrp_price_list_oib', [
        'sanitize_callback' => 'vrp_sanitize_price_list_oib',
    ]);
    register_setting('vrp_price_list_settings', 'vrp_price_list_scope', [
        'sanitize_callback' => 'vrp_sanitize_price_list_scope',
    ]);
    register_setting('vrp_price_list_settings', 'vrp_price_list_outlet_code', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('vrp_price_list_settings', 'vrp_price_list_outlet_address', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
}
add_action('admin_init', 'vrp_register_price_list_settings');

function vrp_add_price_list_admin_page()
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Uvoz i arhiva cjenika', 'vocne-rakije-petkovic'),
        __('Cjenik i arhiva', 'vocne-rakije-petkovic'),
        'manage_options',
        'vrp-price-list',
        'vrp_render_price_list_admin_page'
    );
}
add_action('admin_menu', 'vrp_add_price_list_admin_page');

function vrp_price_list_admin_notice()
{
    if (!isset($_GET['page']) || 'vrp-price-list' !== sanitize_key(wp_unslash($_GET['page']))) {
        return;
    }

    $notice_key = 'vrp_price_list_notice_' . get_current_user_id();
    $notice = get_transient($notice_key);
    if (!$notice || !is_array($notice)) {
        return;
    }
    delete_transient($notice_key);
    ?>
    <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible">
        <p><?php echo esc_html($notice['message']); ?></p>
        <?php if (!empty($notice['details'])) : ?>
            <ul><?php foreach ($notice['details'] as $detail) : ?><li><?php echo esc_html($detail); ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
    </div>
    <?php
}
add_action('admin_notices', 'vrp_price_list_admin_notice');

function vrp_render_price_list_admin_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $uploads = wp_upload_dir();
    $current_file = vrp_price_list_current_path();
    $current_name = get_option('vrp_price_list_current_filename', '');
    $archive_files = vrp_price_list_archive_files();
    $scope = vrp_price_list_scope();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Uvoz i arhiva cjenika', 'vocne-rakije-petkovic'); ?></h1>
        <p><?php esc_html_e('Uvezite CSV s proizvodima i cijenama. Uvoz mijenja samo cijene i datume bazne cijene, a prije zamjene sprema dosadašnji cjenik u javno dostupnu arhivu.', 'vocne-rakije-petkovic'); ?></p>
        <p><?php esc_html_e('CSV mora imati stupce ŠIFRA, ARTIKL, trenutna cijena i bazna cijena; podržani su UTF-8 i Windows-1250 te točka-zarez kao razdjelnik. Svaki redak proizvoda mora odgovarati postojećoj šifri i nazivu.', 'vocne-rakije-petkovic'); ?></p>

        <h2><?php esc_html_e('Podaci za objavu', 'vocne-rakije-petkovic'); ?></h2>
        <form action="options.php" method="post">
            <?php settings_fields('vrp_price_list_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="vrp-price-list-oib"><?php esc_html_e('OIB trgovca', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" inputmode="numeric" id="vrp-price-list-oib" name="vrp_price_list_oib" value="<?php echo esc_attr(vrp_price_list_oib()); ?>" required />
                        <p class="description"><?php esc_html_e('Koristi se u nazivu datoteke prema pravilniku.', 'vocne-rakije-petkovic'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Prodajna mjesta', 'vocne-rakije-petkovic'); ?></th>
                    <td>
                        <label><input type="radio" name="vrp_price_list_scope" value="all" <?php checked($scope, 'all'); ?> /> <?php esc_html_e('Isti cjenik vrijedi za sva prodajna mjesta', 'vocne-rakije-petkovic'); ?></label><br />
                        <label><input type="radio" name="vrp_price_list_scope" value="location" <?php checked($scope, 'location'); ?> /> <?php esc_html_e('Cjenik vrijedi za jedno prodajno mjesto', 'vocne-rakije-petkovic'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-price-list-outlet-code"><?php esc_html_e('Oznaka prodajnog mjesta', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-price-list-outlet-code" name="vrp_price_list_outlet_code" value="<?php echo esc_attr(get_option('vrp_price_list_outlet_code', '')); ?>" />
                        <p class="description"><?php esc_html_e('Obavezno ako je odabrano jedno prodajno mjesto; primjerice oznaka internetske trgovine.', 'vocne-rakije-petkovic'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="vrp-price-list-outlet-address"><?php esc_html_e('Adresa prodajnog mjesta', 'vocne-rakije-petkovic'); ?></label></th>
                    <td><input class="regular-text" type="text" id="vrp-price-list-outlet-address" name="vrp_price_list_outlet_address" value="<?php echo esc_attr(get_option('vrp_price_list_outlet_address', vrp_get_text_setting('vrp_contact_address'))); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(__('Spremi podatke cjenika', 'vocne-rakije-petkovic')); ?>
        </form>

        <hr />
        <h2><?php esc_html_e('Učitaj novi CSV cjenik', 'vocne-rakije-petkovic'); ?></h2>
        <p><?php esc_html_e('Iznosi baznih cijena preuzimaju se iz CSV-a. Datum se uzima iz podataka proizvoda: za postojeći asortiman 2. 5. 2025., a za Marelicu 13. 11. 2026. Ako dodajete proizvod uveden kasnije, unesite njegov datum prvog stavljanja u ponudu u obrascu proizvoda prije uvoza.', 'vocne-rakije-petkovic'); ?></p>
        <p><?php esc_html_e('Cijene u kategoriji „Posebna ponuda” ne označavaju same po sebi sniženu cijenu niti poseban oblik prodaje.', 'vocne-rakije-petkovic'); ?></p>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('vrp_import_price_list', 'vrp_price_list_nonce'); ?>
            <input type="hidden" name="action" value="vrp_import_price_list" />
            <input type="file" name="vrp_price_list_csv" accept=".csv,text/csv" required />
            <?php submit_button(__('Provjeri i uvezi cijene', 'vocne-rakije-petkovic'), 'primary', 'submit', false); ?>
        </form>

        <h2><?php esc_html_e('Javno dostupni cjenik', 'vocne-rakije-petkovic'); ?></h2>
        <?php if (is_file($current_file) && $current_name) : ?>
            <p><a href="<?php echo esc_url(home_url('/cjenik/roba.csv')); ?>"><?php esc_html_e('Preuzmi važeći cjenik (CSV)', 'vocne-rakije-petkovic'); ?></a> <code><?php echo esc_html($current_name); ?></code></p>
            <?php $restore_file = vrp_price_list_latest_valid_archive(); ?>
            <p><?php esc_html_e('Ako je aktivni cjenik pogrešan, cijene proizvoda mogu se vratiti prema najnovijem ispravnom cjeniku iz arhive. Pogrešna aktivna verzija ostat će skrivena iz javnog popisa arhive, a izravno dostupna do isteka 15-dnevnog roka.', 'vocne-rakije-petkovic'); ?></p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" onsubmit="return confirm('<?php echo esc_js(__('Označiti aktivni cjenik kao pogrešan i vratiti cijene s posljednjeg ispravnog cjenika iz arhive?', 'vocne-rakije-petkovic')); ?>');">
                <?php wp_nonce_field('vrp_rollback_current_price_list', 'vrp_price_list_nonce'); ?>
                <input type="hidden" name="action" value="vrp_rollback_current_price_list" />
                <button class="button button-secondary" type="submit" <?php disabled(!$restore_file); ?>><?php esc_html_e('Označi aktivni kao pogrešan i vrati prethodne cijene', 'vocne-rakije-petkovic'); ?></button>
                <?php if ($restore_file) : ?><span><?php printf(esc_html__('Vratit će se: %s', 'vocne-rakije-petkovic'), esc_html(basename($restore_file))); ?></span><?php else : ?><span><?php esc_html_e('Nema ispravnog cjenika u arhivi za povrat.', 'vocne-rakije-petkovic'); ?></span><?php endif; ?>
            </form>
        <?php else : ?>
            <p><?php esc_html_e('Važeći CSV još nije objavljen.', 'vocne-rakije-petkovic'); ?></p>
        <?php endif; ?>
        <p><a href="<?php echo esc_url(home_url('/cjenik/arhiva/')); ?>"><?php esc_html_e('Otvori javnu arhivu cjenika', 'vocne-rakije-petkovic'); ?></a></p>
        <p><?php esc_html_e('Prethodni cjenik mora ostati dostupan najmanje 15 dana nakon zamjene. Označena pogrešna verzija skriva se iz javnog popisa, ali ostaje dostupna putem izravne adrese do isteka roka; brisanje se tada omogućuje.', 'vocne-rakije-petkovic'); ?></p>
        <?php $invalid_archives = vrp_price_list_invalid_archives(); ?>
        <?php if ($archive_files) : ?>
            <ul>
                <?php foreach ($archive_files as $file) : ?>
                    <?php
                    $filename = basename($file);
                    $is_invalid = isset($invalid_archives[$filename]);
                    $delete_after = vrp_price_list_archive_delete_after($file);
                    $can_delete = time() >= $delete_after;
                    ?>
                    <li>
                        <a href="<?php echo esc_url(trailingslashit($uploads['baseurl']) . 'cjenik/arhiva/' . rawurlencode($filename)); ?>"><?php echo esc_html($filename); ?></a>
                        <?php if ($is_invalid) : ?><strong><?php esc_html_e('— označen kao pogrešan; ostaje dostupan do isteka obveznog roka', 'vocne-rakije-petkovic'); ?></strong><?php endif; ?>
                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="display:inline-block;margin-left:8px">
                            <?php wp_nonce_field('vrp_set_price_archive_status_' . $filename, 'vrp_archive_nonce'); ?>
                            <input type="hidden" name="action" value="vrp_set_price_archive_status" />
                            <input type="hidden" name="archive_filename" value="<?php echo esc_attr($filename); ?>" />
                            <input type="hidden" name="invalid" value="<?php echo $is_invalid ? '0' : '1'; ?>" />
                            <button class="button button-small" type="submit"><?php echo $is_invalid ? esc_html__('Ukloni oznaku pogreške', 'vocne-rakije-petkovic') : esc_html__('Označi kao pogrešan', 'vocne-rakije-petkovic'); ?></button>
                        </form>
                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="display:inline-block;margin-left:4px" onsubmit="return confirm('<?php echo esc_js(__('Sigurno želite obrisati ovaj arhivirani cjenik?', 'vocne-rakije-petkovic')); ?>');">
                            <?php wp_nonce_field('vrp_delete_price_archive_' . $filename, 'vrp_archive_nonce'); ?>
                            <input type="hidden" name="action" value="vrp_delete_price_archive" />
                            <input type="hidden" name="archive_filename" value="<?php echo esc_attr($filename); ?>" />
                            <button class="button button-small" type="submit" <?php disabled(!$can_delete); ?>><?php esc_html_e('Obriši iz arhive', 'vocne-rakije-petkovic'); ?></button>
                        </form>
                        <?php if (!$can_delete) : ?><small><?php printf(esc_html__('Brisanje dostupno nakon %s.', 'vocne-rakije-petkovic'), esc_html(wp_date('d.m.Y. H:i', $delete_after))); ?></small><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}

function vrp_price_list_normalize_name($name)
{
    $name = remove_accents((string) $name);
    $name = strtolower($name);
    $name = preg_replace('/\be\s+(?=\d)/', ' ', $name);
    return preg_replace('/[^a-z0-9]+/', '', $name);
}

function vrp_price_list_normalize_packaging($label)
{
    $label = strtolower(remove_accents((string) $label));
    $label = preg_replace('/\btamna\b/', '', $label);
    return preg_replace('/[^a-z0-9]+/', '', $label);
}

function vrp_price_list_parse_amount($value)
{
    $value = str_replace(["\xc2\xa0", '€', ' '], '', trim((string) $value));
    if (!preg_match('/^\d+(?:[,.]\d{1,2})?$/', $value)) {
        return false;
    }

    return number_format((float) str_replace(',', '.', $value), 2, ',', '');
}

function vrp_price_list_parse_date($value)
{
    if (!preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', trim((string) $value), $matches)) {
        return false;
    }

    $date = sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date ? $date : false;
}

function vrp_price_list_products()
{
    return get_posts([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_vrp_product_id',
                'compare' => 'EXISTS',
            ],
            [
                'key' => '_vrp_is_product_family',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);
}

function vrp_price_list_target_date($product)
{
    $saved_date = get_post_meta($product->ID, '_vrp_reference_price_date', true);
    // Replace the former shared reference date once; preserve product-specific dates on later imports.
    if ($saved_date && '2026-09-10' !== $saved_date) {
        $parsed_date = DateTimeImmutable::createFromFormat('Y-m-d', $saved_date);
        if ($parsed_date && $parsed_date->format('Y-m-d') === $saved_date) {
            return $saved_date;
        }
    }

    return 'marelica' === get_post_meta($product->ID, '_vrp_product_family', true)
        ? '2026-11-13'
        : '2025-05-02';
}

function vrp_parse_price_list_csv($file)
{
    $contents = file_get_contents($file);
    if (false === $contents) {
        return new WP_Error('read_failed', __('CSV datoteku nije moguće pročitati.', 'vocne-rakije-petkovic'));
    }
    if (0 === strpos($contents, "\xef\xbb\xbf")) {
        $contents = substr($contents, 3);
    }
    if (!preg_match('//u', $contents)) {
        $contents = iconv('Windows-1250', 'UTF-8', $contents);
        if (false === $contents) {
            return new WP_Error('encoding_failed', __('CSV nije valjani UTF-8 ni Windows-1250 zapis.', 'vocne-rakije-petkovic'));
        }
    }

    $stream = fopen('php://temp', 'r+');
    if (!$stream) {
        return new WP_Error('stream_failed', __('CSV sadržaj nije moguće obraditi.', 'vocne-rakije-petkovic'));
    }
    fwrite($stream, $contents);
    rewind($stream);

    $rows = [];
    while (($row = fgetcsv($stream, 0, ';', '"', '\\')) !== false) {
        $rows[] = $row;
    }
    fclose($stream);

    $header_index = false;
    foreach ($rows as $index => $row) {
        if ('sifra' === vrp_price_list_normalize_name($row[0] ?? '') && 'artikl' === vrp_price_list_normalize_name($row[1] ?? '')) {
            $header_index = $index;
            break;
        }
    }
    if (false === $header_index) {
        return new WP_Error('header_missing', __('CSV ne sadrži očekivana zaglavlja ŠIFRA i ARTIKL.', 'vocne-rakije-petkovic'));
    }

    $header = $rows[$header_index];
    if (!isset($header[2], $header[3]) || !vrp_price_list_parse_date($header[2]) || !vrp_price_list_parse_date($header[3])) {
        return new WP_Error('date_missing', __('U zaglavlju CSV-a nedostaje valjani datum cijena.', 'vocne-rakije-petkovic'));
    }

    $products_by_code = [];
    foreach (vrp_price_list_products() as $product) {
        $code = trim((string) get_post_meta($product->ID, '_vrp_product_id', true));
        if ('' !== $code) {
            $products_by_code[$code][] = $product;
        }
    }

    $records = [];
    $errors = [];
    $matched_ids = [];
    foreach (array_slice($rows, $header_index + 1, null, true) as $line_number => $row) {
        $code = trim((string) ($row[0] ?? ''));
        if ('' === $code || !preg_match('/^\d+$/', $code)) {
            continue;
        }

        $name = trim((string) ($row[1] ?? ''));
        $current_price = vrp_price_list_parse_amount($row[2] ?? '');
        $reference_price = vrp_price_list_parse_amount($row[3] ?? '');
        if ('' === $name || false === $current_price || false === $reference_price) {
            $errors[] = sprintf(__('Redak %d: nedostaje naziv ili valjana cijena.', 'vocne-rakije-petkovic'), $line_number + 1);
            continue;
        }

        $matches = [];
        foreach ($products_by_code[$code] ?? [] as $product) {
            if (vrp_price_list_normalize_name($name) === vrp_price_list_normalize_name($product->post_title)) {
                $matches[] = $product;
            }
        }
        if (1 !== count($matches)) {
            $errors[] = sprintf(__('Redak %d: šifra %s i naziv „%s” ne odgovaraju točno jednom objavljenom proizvodu.', 'vocne-rakije-petkovic'), $line_number + 1, $code, $name);
            continue;
        }

        $product = $matches[0];
        if (isset($matched_ids[$product->ID])) {
            $errors[] = sprintf(__('Redak %d: proizvod „%s” pojavljuje se više puta.', 'vocne-rakije-petkovic'), $line_number + 1, $name);
            continue;
        }
        $matched_ids[$product->ID] = true;
        $records[] = [
            'product' => $product,
            'code' => $code,
            'name' => $product->post_title,
            'current_price' => $current_price,
            'reference_price' => $reference_price,
            'reference_date' => vrp_price_list_target_date($product),
            'packaging' => get_post_meta($product->ID, '_vrp_packaging_label', true),
        ];
    }

    $products = vrp_price_list_products();
    if (count($records) !== count($products)) {
        $errors[] = sprintf(__('CSV sadrži %1$d prepoznatih proizvoda, a u katalogu je %2$d objavljenih proizvoda s cijenom.', 'vocne-rakije-petkovic'), count($records), count($products));
    }

    if ($errors) {
        return new WP_Error('rows_invalid', __('Uvoz je zaustavljen; cijene nisu promijenjene.', 'vocne-rakije-petkovic'), $errors);
    }

    return $records;
}

function vrp_price_list_amount_as_number($value)
{
    $value = str_replace(["\xc2\xa0", '€', ' '], '', trim((string) $value));
    $value = str_replace(',', '.', $value);
    return is_numeric($value) ? (float) $value : false;
}

function vrp_price_list_quantity_liters($label, $name = '')
{
    $label = strtolower(str_replace(',', '.', (string) $name . ' ' . (string) $label));
    if (preg_match('/(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*(ml|l)\b/i', $label, $matches)) {
        $quantity = (float) $matches[1] * (float) $matches[2];
        $unit = strtolower($matches[3]);
    } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(ml|l)\b/i', $label, $matches)) {
        $quantity = (float) $matches[1];
        $unit = strtolower($matches[2]);
    } else {
        return false;
    }

    $liters = 'ml' === $unit ? $quantity / 1000 : $quantity;
    return $liters > 0 ? $liters : false;
}

function vrp_price_list_csv($records)
{
    $stream = fopen('php://temp', 'r+');
    if (!$stream) {
        return new WP_Error('csv_stream_failed', __('CSV za objavu nije moguće pripremiti.', 'vocne-rakije-petkovic'));
    }

    $headers = [
        'Naziv robe',
        'Šifra robe',
        'Marka robe',
        'Neto količina',
        'Jedinica mjere',
        'Maloprodajna cijena (EUR)',
        'Posebni oblik prodaje',
        'Cijena za jedinicu mjere (EUR/L)',
        'Barkod',
        'EAN barkod',
        'Bazna cijena (EUR)',
        'Datum bazne cijene',
    ];
    fputcsv($stream, $headers, ';', '"', '\\');

    foreach ($records as $record) {
        $product = $record['product'];
        $current_price = vrp_price_list_amount_as_number($record['current_price']);
        $reference_price = vrp_price_list_amount_as_number($record['reference_price']);
        $liters = vrp_price_list_quantity_liters($record['packaging'], $record['name']);
        if (false === $current_price || false === $reference_price || false === $liters) {
            fclose($stream);
            return new WP_Error('row_not_exportable', sprintf(__('Proizvod „%s” nema valjanu cijenu ili količinu pakiranja.', 'vocne-rakije-petkovic'), $record['name']));
        }

        $row = [
            $record['name'],
            $record['code'],
            '',
            rtrim(rtrim(number_format($liters, 3, '.', ''), '0'), '.'),
            'L',
            number_format($current_price, 2, '.', ''),
            'ne',
            number_format($current_price / $liters, 2, '.', ''),
            '',
            '',
            number_format($reference_price, 2, '.', ''),
            $record['reference_date'],
        ];
        fputcsv($stream, $row, ';', '"', '\\');
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);
    return $csv;
}

function vrp_price_list_parse_published_csv($file)
{
    $contents = file_get_contents($file);
    if (false === $contents) {
        return new WP_Error('published_csv_read_failed', __('Arhivirani cjenik nije moguće pročitati.', 'vocne-rakije-petkovic'));
    }
    if (0 === strpos($contents, "\xef\xbb\xbf")) {
        $contents = substr($contents, 3);
    }

    $stream = fopen('php://temp', 'r+');
    if (!$stream) {
        return new WP_Error('published_csv_stream_failed', __('Arhivirani cjenik nije moguće obraditi.', 'vocne-rakije-petkovic'));
    }
    fwrite($stream, $contents);
    rewind($stream);

    $rows = [];
    while (($row = fgetcsv($stream, 0, ';', '"', '\\')) !== false) {
        $rows[] = $row;
    }
    fclose($stream);

    $header_index = false;
    foreach ($rows as $index => $row) {
        if ('nazivrobe' === vrp_price_list_normalize_name($row[0] ?? '') && 'sifrarobe' === vrp_price_list_normalize_name($row[1] ?? '')) {
            $header_index = $index;
            break;
        }
    }
    if (
        false === $header_index
        || 'maloprodajnacijenaeur' !== vrp_price_list_normalize_name($rows[$header_index][5] ?? '')
        || 'baznacijenaeur' !== vrp_price_list_normalize_name($rows[$header_index][10] ?? '')
        || 'datumbaznecijene' !== vrp_price_list_normalize_name($rows[$header_index][11] ?? '')
    ) {
        return new WP_Error('published_csv_header_invalid', __('Arhivirani cjenik nema očekivani format.', 'vocne-rakije-petkovic'));
    }

    $products_by_code = [];
    $products = vrp_price_list_products();
    foreach ($products as $product) {
        $code = trim((string) get_post_meta($product->ID, '_vrp_product_id', true));
        if ('' !== $code) {
            $products_by_code[$code][] = $product;
        }
    }

    $records = [];
    $errors = [];
    $matched_ids = [];
    foreach (array_slice($rows, $header_index + 1, null, true) as $line_number => $row) {
        $code = trim((string) ($row[1] ?? ''));
        if ('' === $code) {
            continue;
        }

        $name = trim((string) ($row[0] ?? ''));
        $current_price = vrp_price_list_parse_amount($row[5] ?? '');
        $reference_price = vrp_price_list_parse_amount($row[10] ?? '');
        $reference_date = trim((string) ($row[11] ?? ''));
        $parsed_date = DateTimeImmutable::createFromFormat('Y-m-d', $reference_date);
        if (
            '' === $name
            || false === $current_price
            || false === $reference_price
            || !$parsed_date
            || $parsed_date->format('Y-m-d') !== $reference_date
        ) {
            $errors[] = sprintf(__('Redak %d arhiviranog cjenika nema valjane podatke o cijeni.', 'vocne-rakije-petkovic'), $line_number + 1);
            continue;
        }

        $matches = [];
        foreach ($products_by_code[$code] ?? [] as $product) {
            if (vrp_price_list_normalize_name($name) === vrp_price_list_normalize_name($product->post_title)) {
                $matches[] = $product;
            }
        }
        if (1 !== count($matches)) {
            $errors[] = sprintf(__('Redak %d arhiviranog cjenika ne odgovara točno jednom objavljenom proizvodu.', 'vocne-rakije-petkovic'), $line_number + 1);
            continue;
        }

        $product = $matches[0];
        if (isset($matched_ids[$product->ID])) {
            $errors[] = sprintf(__('Proizvod „%s” pojavljuje se više puta u arhiviranom cjeniku.', 'vocne-rakije-petkovic'), $name);
            continue;
        }
        $matched_ids[$product->ID] = true;
        $records[] = [
            'product' => $product,
            'code' => $code,
            'name' => $product->post_title,
            'current_price' => $current_price,
            'reference_price' => $reference_price,
            'reference_date' => $reference_date,
            'packaging' => get_post_meta($product->ID, '_vrp_packaging_label', true),
        ];
    }

    if ($errors || count($records) !== count($products)) {
        if (!$errors) {
            $errors[] = __('Arhivirani cjenik ne sadrži sve objavljene proizvode.', 'vocne-rakije-petkovic');
        }
        return new WP_Error('published_csv_rows_invalid', __('Cijene nije moguće vratiti iz ovog arhiviranog cjenika.', 'vocne-rakije-petkovic'), $errors);
    }

    return $records;
}

function vrp_price_list_current_records()
{
    $records = [];
    foreach (vrp_price_list_products() as $product) {
        $current_price = get_post_meta($product->ID, '_vrp_current_price', true);
        $reference_price = get_post_meta($product->ID, '_vrp_reference_price', true);
        $packaging = get_post_meta($product->ID, '_vrp_packaging_label', true);
        if ('' === $current_price || '' === $reference_price || '' === $packaging) {
            return new WP_Error('product_data_missing', sprintf(__('Proizvodu „%s” nedostaje cijena ili pakiranje.', 'vocne-rakije-petkovic'), $product->post_title));
        }

        $records[] = [
            'product' => $product,
            'code' => get_post_meta($product->ID, '_vrp_product_id', true),
            'name' => $product->post_title,
            'current_price' => $current_price,
            'reference_price' => $reference_price,
            'reference_date' => vrp_price_list_target_date($product),
            'packaging' => $packaging,
        ];
    }
    return $records;
}

function vrp_price_list_filename($number)
{
    $oib = preg_replace('/\D+/', '', (string) vrp_price_list_oib());
    $parts = [$oib];
    if ('all' === vrp_price_list_scope()) {
        $parts[] = 'ZA_SVA_PRODAJNA_MJESTA';
    } else {
        $parts[] = sanitize_title(get_option('vrp_price_list_outlet_code', ''));
        $parts[] = sanitize_title(get_option('vrp_price_list_outlet_address', ''));
    }
    $parts[] = 'ROBA';
    $parts[] = (string) absint($number);
    $parts[] = wp_date('Ymd_Hi');
    return implode('_', array_filter($parts)) . '.csv';
}

function vrp_price_list_configuration_errors()
{
    $errors = [];
    if (!preg_match('/^\d{11}$/', (string) vrp_price_list_oib())) {
        $errors[] = __('Unesite valjani OIB trgovca u postavkama cjenika.', 'vocne-rakije-petkovic');
    }
    if ('location' === vrp_price_list_scope()) {
        if ('' === trim((string) get_option('vrp_price_list_outlet_code', ''))) {
            $errors[] = __('Za pojedino prodajno mjesto unesite njegovu oznaku.', 'vocne-rakije-petkovic');
        }
        if ('' === trim((string) get_option('vrp_price_list_outlet_address', ''))) {
            $errors[] = __('Za pojedino prodajno mjesto unesite adresu.', 'vocne-rakije-petkovic');
        }
    }
    return $errors;
}

function vrp_price_list_update_product($record)
{
    $product_id = $record['product']->ID;
    $packaging_rows = vrp_get_product_packaging($product_id);
    if (!$packaging_rows) {
        $packaging_rows = [['label' => $record['packaging']]];
    }
    foreach ($packaging_rows as &$row) {
        $row['current_price'] = $record['current_price'];
        $row['reference_price'] = $record['reference_price'];
        $row['reference_price_date'] = $record['reference_date'];
    }
    unset($row);

    update_post_meta($product_id, '_vrp_current_price', $record['current_price']);
    update_post_meta($product_id, '_vrp_reference_price', $record['reference_price']);
    update_post_meta($product_id, '_vrp_reference_price_date', $record['reference_date']);
    update_post_meta($product_id, '_vrp_packaging', $packaging_rows);
}

function vrp_price_list_update_family_rows($records)
{
    $records_by_family = [];
    foreach ($records as $record) {
        $family_slug = get_post_meta($record['product']->ID, '_vrp_product_family', true);
        if ($family_slug) {
            $records_by_family[$family_slug][] = $record;
        }
    }

    foreach ($records_by_family as $family_slug => $family_records) {
        $family = get_page_by_path($family_slug, OBJECT, 'product');
        if (!$family) {
            continue;
        }
        $rows = vrp_get_product_packaging($family->ID);
        foreach ($rows as &$row) {
            foreach ($family_records as $record) {
                $variant_product_id = absint($row['variant_product_id'] ?? 0);
                if ($variant_product_id) {
                    if ($variant_product_id !== (int) $record['product']->ID) {
                        continue;
                    }
                } else {
                    $variant_is_dark = false !== stripos($record['name'], 'TAMNA');
                    $row_is_dark = false !== stripos($row['label'] ?? '', 'TAMNA');
                    if ($variant_is_dark !== $row_is_dark || vrp_price_list_normalize_packaging($record['packaging']) !== vrp_price_list_normalize_packaging($row['label'] ?? '')) {
                        continue;
                    }
                }
                $row['current_price'] = $record['current_price'];
                $row['reference_price'] = $record['reference_price'];
                $row['reference_price_date'] = $record['reference_date'];
                break;
            }
        }
        unset($row);
        update_post_meta($family->ID, '_vrp_packaging', $rows);
    }
}

function vrp_price_list_snapshot_meta($records)
{
    $snapshot = [];
    foreach ($records as $record) {
        $product_id = $record['product']->ID;
        foreach (['_vrp_current_price', '_vrp_reference_price', '_vrp_reference_price_date', '_vrp_packaging'] as $key) {
            $snapshot[$product_id][$key] = [
                'exists' => metadata_exists('post', $product_id, $key),
                'value' => get_post_meta($product_id, $key, true),
            ];
        }
        $family_slug = get_post_meta($product_id, '_vrp_product_family', true);
        $family = $family_slug ? get_page_by_path($family_slug, OBJECT, 'product') : null;
        if ($family && !isset($snapshot[$family->ID]['_vrp_packaging'])) {
            $snapshot[$family->ID]['_vrp_packaging'] = [
                'exists' => metadata_exists('post', $family->ID, '_vrp_packaging'),
                'value' => get_post_meta($family->ID, '_vrp_packaging', true),
            ];
        }
    }
    return $snapshot;
}

function vrp_price_list_restore_meta($snapshot)
{
    $restored = true;
    foreach ($snapshot as $post_id => $items) {
        foreach ($items as $key => $item) {
            if ($item['exists']) {
                update_post_meta($post_id, $key, $item['value']);
            } else {
                delete_post_meta($post_id, $key);
            }

            $exists = metadata_exists('post', $post_id, $key);
            if ($exists !== $item['exists'] || ($exists && get_post_meta($post_id, $key, true) !== $item['value'])) {
                $restored = false;
            }
        }
    }

    return $restored;
}

function vrp_apply_price_list_records($records)
{
    $configuration_errors = vrp_price_list_configuration_errors();
    if ($configuration_errors) {
        return new WP_Error('configuration_invalid', __('Postavke cjenika nisu potpune.', 'vocne-rakije-petkovic'), $configuration_errors);
    }

    $old_records = vrp_price_list_current_records();
    if (is_wp_error($old_records)) {
        return new WP_Error('current_data_invalid', __('Trenutni katalog nema potpune cijene.', 'vocne-rakije-petkovic'), [$old_records->get_error_message()]);
    }

    $old_csv = vrp_price_list_csv($old_records);
    $new_csv = vrp_price_list_csv($records);
    if (is_wp_error($old_csv) || is_wp_error($new_csv)) {
        $csv_error = is_wp_error($old_csv) ? $old_csv : $new_csv;
        return new WP_Error('csv_invalid', __('CSV cjenika nije moguće pripremiti.', 'vocne-rakije-petkovic'), [$csv_error->get_error_message()]);
    }

    $archive_path = vrp_price_list_archive_path();
    $current_path = vrp_price_list_current_path();
    if (!wp_mkdir_p($archive_path)) {
        return new WP_Error('archive_unwritable', __('Mapa za arhivu cjenika nije dostupna za pisanje.', 'vocne-rakije-petkovic'));
    }

    $next_number = max(1, absint(get_option('vrp_price_list_storage_number', 1)));
    $archive_name = vrp_price_list_filename($next_number++);
    $archive_file = trailingslashit($archive_path) . $archive_name;
    if (false === file_put_contents($archive_file, $old_csv, LOCK_EX)) {
        return new WP_Error('archive_write_failed', __('Dosadašnji cjenik nije moguće arhivirati.', 'vocne-rakije-petkovic'));
    }

    $temp_current = tempnam(dirname($current_path), 'vrp-cjenik-');
    if (false === $temp_current || false === file_put_contents($temp_current, $new_csv, LOCK_EX)) {
        if ($temp_current && is_file($temp_current)) {
            unlink($temp_current);
        }
        return new WP_Error('current_write_failed', __('Nova datoteka cjenika nije moguće pripremiti.', 'vocne-rakije-petkovic'));
    }

    $snapshot = vrp_price_list_snapshot_meta($records);
    foreach ($records as $record) {
        vrp_price_list_update_product($record);
    }
    vrp_price_list_update_family_rows($records);

    foreach ($records as $record) {
        $product_id = $record['product']->ID;
        if (
            $record['current_price'] !== get_post_meta($product_id, '_vrp_current_price', true)
            || $record['reference_price'] !== get_post_meta($product_id, '_vrp_reference_price', true)
            || $record['reference_date'] !== get_post_meta($product_id, '_vrp_reference_price_date', true)
        ) {
            vrp_price_list_restore_meta($snapshot);
            unlink($temp_current);
            return new WP_Error('price_update_failed', __('Uvoz je poništen jer nije moguće spremiti sve cijene u katalog.', 'vocne-rakije-petkovic'));
        }
    }

    $new_filename = vrp_price_list_filename($next_number++);
    if (!copy($temp_current, $current_path)) {
        vrp_price_list_restore_meta($snapshot);
        unlink($temp_current);
        return new WP_Error('publish_failed', __('Uvoz je poništen jer važeći cjenik nije moguće objaviti. Dosadašnji cjenik spremljen je u arhivu.', 'vocne-rakije-petkovic'));
    }
    unlink($temp_current);

    update_option('vrp_price_list_current_filename', $new_filename, false);
    update_option('vrp_price_list_storage_number', $next_number, false);
    return [
        'count' => count($records),
        'filename' => $new_filename,
    ];
}

function vrp_import_price_list()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemate dopuštenje za uvoz cjenika.', 'vocne-rakije-petkovic'));
    }
    check_admin_referer('vrp_import_price_list', 'vrp_price_list_nonce');

    $errors = vrp_price_list_configuration_errors();
    if (!isset($_FILES['vrp_price_list_csv']) || UPLOAD_ERR_OK !== (int) $_FILES['vrp_price_list_csv']['error']) {
        $errors[] = __('CSV datoteka nije ispravno učitana.', 'vocne-rakije-petkovic');
    }
    if ($errors) {
        vrp_set_price_list_notice('error', __('Uvoz nije izvršen.', 'vocne-rakije-petkovic'), $errors);
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $upload = $_FILES['vrp_price_list_csv'];
    if (!is_uploaded_file($upload['tmp_name']) || 'csv' !== strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION))) {
        vrp_set_price_list_notice('error', __('Uvoz nije izvršen: odaberite CSV datoteku.', 'vocne-rakije-petkovic'));
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $records = vrp_parse_price_list_csv($upload['tmp_name']);
    if (is_wp_error($records)) {
        $details = $records->get_error_data();
        vrp_set_price_list_notice('error', $records->get_error_message(), is_array($details) ? $details : []);
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $result = vrp_apply_price_list_records($records);
    if (is_wp_error($result)) {
        $details = $result->get_error_data();
        vrp_set_price_list_notice('error', $result->get_error_message(), is_array($details) ? $details : []);
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    vrp_set_price_list_notice('success', sprintf(__('Uspješno je uvezeno %d proizvoda. Novi cjenik je objavljen, a prethodna verzija spremljena u arhivu.', 'vocne-rakije-petkovic'), $result['count']));
    wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
    exit;
}
add_action('admin_post_vrp_import_price_list', 'vrp_import_price_list');

function vrp_rollback_current_price_list()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemate dopuštenje za vraćanje cijena cjenika.', 'vocne-rakije-petkovic'));
    }
    check_admin_referer('vrp_rollback_current_price_list', 'vrp_price_list_nonce');

    $archive_file = vrp_price_list_latest_valid_archive();
    $current_file = vrp_price_list_current_path();
    $current_name = sanitize_file_name(get_option('vrp_price_list_current_filename', ''));
    if (!$archive_file || !is_file($current_file) || '' === $current_name) {
        vrp_set_price_list_notice('error', __('Povrat nije izvršen: nedostaje aktivni ili ispravni arhivirani cjenik.', 'vocne-rakije-petkovic'));
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $records = vrp_price_list_parse_published_csv($archive_file);
    if (is_wp_error($records)) {
        $details = $records->get_error_data();
        vrp_set_price_list_notice('error', $records->get_error_message(), is_array($details) ? $details : []);
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $restored_csv = vrp_price_list_csv($records);
    if (is_wp_error($restored_csv)) {
        vrp_set_price_list_notice('error', $restored_csv->get_error_message());
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $archive_path = vrp_price_list_archive_path();
    if (!wp_mkdir_p($archive_path)) {
        vrp_set_price_list_notice('error', __('Povrat nije izvršen: mapa arhive nije dostupna za pisanje.', 'vocne-rakije-petkovic'));
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $invalid_name = $current_name;
    $next_number = max(1, absint(get_option('vrp_price_list_storage_number', 1)));
    if (is_file(trailingslashit($archive_path) . $invalid_name)) {
        do {
            $invalid_name = vrp_price_list_filename($next_number++);
        } while (is_file(trailingslashit($archive_path) . $invalid_name));
    }
    $invalid_file = trailingslashit($archive_path) . $invalid_name;
    if (!copy($current_file, $invalid_file)) {
        vrp_set_price_list_notice('error', __('Povrat nije izvršen: aktivni cjenik nije moguće sačuvati u arhivi.', 'vocne-rakije-petkovic'));
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $temporary_file = tempnam(dirname($current_file), 'vrp-cjenik-');
    if (false === $temporary_file || false === file_put_contents($temporary_file, $restored_csv, LOCK_EX)) {
        if ($temporary_file && is_file($temporary_file)) {
            unlink($temporary_file);
        }
        unlink($invalid_file);
        vrp_set_price_list_notice('error', __('Povrat nije izvršen: prethodni važeći cjenik nije moguće pripremiti.', 'vocne-rakije-petkovic'));
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }

    $snapshot = vrp_price_list_snapshot_meta($records);
    foreach ($records as $record) {
        vrp_price_list_update_product($record);
    }
    vrp_price_list_update_family_rows($records);

    $updates_succeeded = true;
    foreach ($records as $record) {
        $product_id = $record['product']->ID;
        if (
            $record['current_price'] !== get_post_meta($product_id, '_vrp_current_price', true)
            || $record['reference_price'] !== get_post_meta($product_id, '_vrp_reference_price', true)
            || $record['reference_date'] !== get_post_meta($product_id, '_vrp_reference_price_date', true)
        ) {
            $updates_succeeded = false;
            break;
        }
    }

    if (!$updates_succeeded || !copy($temporary_file, $current_file)) {
        $metadata_restored = vrp_price_list_restore_meta($snapshot);
        $current_restored = copy($invalid_file, $current_file);
        unlink($temporary_file);
        if ($metadata_restored && $current_restored) {
            unlink($invalid_file);
            vrp_set_price_list_notice('error', __('Povrat nije dovršen; prethodne cijene i aktivni cjenik vraćeni su u početno stanje.', 'vocne-rakije-petkovic'));
        } else {
            $invalid_archives = vrp_price_list_invalid_archives();
            $invalid_archives[$invalid_name] = time();
            update_option('vrp_price_list_invalid_archives', $invalid_archives, false);
            $details = [];
            if (!$metadata_restored) {
                $details[] = __('Nije potvrđen povrat svih cijena u katalogu.', 'vocne-rakije-petkovic');
            }
            if (!$current_restored) {
                $details[] = __('Nije moguće vratiti sadržaj aktivne CSV datoteke; kopija pogrešnog cjenika sačuvana je u arhivi.', 'vocne-rakije-petkovic');
            }
            vrp_set_price_list_notice('error', __('Povrat nije dovršen. Provjerite cijene i aktivni cjenik; pogrešna kopija označena je u arhivi.', 'vocne-rakije-petkovic'), $details);
        }
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
        exit;
    }
    unlink($temporary_file);

    $invalid_archives = vrp_price_list_invalid_archives();
    $invalid_archives[$invalid_name] = time();
    update_option('vrp_price_list_invalid_archives', $invalid_archives, false);
    update_option('vrp_price_list_current_filename', basename($archive_file), false);
    if ($invalid_name !== $current_name) {
        update_option('vrp_price_list_storage_number', $next_number, false);
    }
    vrp_set_price_list_notice('success', sprintf(__('Aktivni cjenik označen je kao pogrešan. Cijene su vraćene prema cjeniku %s.', 'vocne-rakije-petkovic'), basename($archive_file)));
    wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
    exit;
}
add_action('admin_post_vrp_rollback_current_price_list', 'vrp_rollback_current_price_list');

function vrp_set_price_list_notice($type, $message, $details = [])
{
    set_transient('vrp_price_list_notice_' . get_current_user_id(), [
        'type' => $type,
        'message' => $message,
        'details' => $details,
    ], MINUTE_IN_SECONDS);
}

function vrp_price_list_requested_archive_filename()
{
    if (!isset($_POST['archive_filename']) || !is_string($_POST['archive_filename'])) {
        return '';
    }

    $filename = wp_unslash($_POST['archive_filename']);
    return $filename === basename($filename) ? sanitize_file_name($filename) : '';
}

function vrp_set_price_archive_status()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemate dopuštenje za uređivanje arhive cjenika.', 'vocne-rakije-petkovic'));
    }

    $filename = vrp_price_list_requested_archive_filename();
    check_admin_referer('vrp_set_price_archive_status_' . $filename, 'vrp_archive_nonce');
    $file = vrp_price_list_archive_file_by_name($filename);
    if (!$file) {
        vrp_set_price_list_notice('error', __('Arhivirani cjenik nije pronađen.', 'vocne-rakije-petkovic'));
    } else {
        $invalid_archives = vrp_price_list_invalid_archives();
        if (isset($_POST['invalid']) && '1' === wp_unslash($_POST['invalid'])) {
            $invalid_archives[$filename] = time();
            update_option('vrp_price_list_invalid_archives', $invalid_archives, false);
            vrp_set_price_list_notice('warning', __('Cjenik je označen kao pogrešno uvezen; ostat će dostupan u arhivi do isteka 15-dnevnog roka.', 'vocne-rakije-petkovic'));
        } else {
            unset($invalid_archives[$filename]);
            update_option('vrp_price_list_invalid_archives', $invalid_archives, false);
            vrp_set_price_list_notice('success', __('Oznaka pogreške uklonjena je s cjenika.', 'vocne-rakije-petkovic'));
        }
    }

    wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
    exit;
}
add_action('admin_post_vrp_set_price_archive_status', 'vrp_set_price_archive_status');

function vrp_delete_price_archive()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Nemate dopuštenje za brisanje iz arhive cjenika.', 'vocne-rakije-petkovic'));
    }

    $filename = vrp_price_list_requested_archive_filename();
    check_admin_referer('vrp_delete_price_archive_' . $filename, 'vrp_archive_nonce');
    $file = vrp_price_list_archive_file_by_name($filename);
    if (!$file) {
        vrp_set_price_list_notice('error', __('Arhivirani cjenik nije pronađen.', 'vocne-rakije-petkovic'));
    } elseif (time() < vrp_price_list_archive_delete_after($file)) {
        vrp_set_price_list_notice('error', __('Cjenik se ne može obrisati prije isteka obveznog 15-dnevnog roka.', 'vocne-rakije-petkovic'));
    } elseif (!unlink($file)) {
        vrp_set_price_list_notice('error', __('Datoteku cjenika nije moguće obrisati.', 'vocne-rakije-petkovic'));
    } else {
        $invalid_archives = vrp_price_list_invalid_archives();
        unset($invalid_archives[$filename]);
        update_option('vrp_price_list_invalid_archives', $invalid_archives, false);
        vrp_set_price_list_notice('success', __('Cjenik je obrisan iz arhive.', 'vocne-rakije-petkovic'));
    }

    wp_safe_redirect(admin_url('edit.php?post_type=product&page=vrp-price-list'));
    exit;
}
add_action('admin_post_vrp_delete_price_archive', 'vrp_delete_price_archive');

function vrp_register_price_list_routes()
{
    add_rewrite_rule('^cjenik/roba\.csv$', 'index.php?vrp_current_price_list=1', 'top');
    add_rewrite_rule('^cjenik/arhiva/?$', 'index.php?vrp_price_list_archive=1', 'top');
}
add_action('init', 'vrp_register_price_list_routes');

function vrp_flush_price_list_routes_once()
{
    if (!get_option('vrp_price_list_routes_flushed')) {
        flush_rewrite_rules();
        update_option('vrp_price_list_routes_flushed', '1');
    }
}
add_action('admin_init', 'vrp_flush_price_list_routes_once');

function vrp_price_list_query_vars($vars)
{
    $vars[] = 'vrp_current_price_list';
    $vars[] = 'vrp_price_list_archive';
    return $vars;
}
add_filter('query_vars', 'vrp_price_list_query_vars');

function vrp_serve_price_list_routes()
{
    if (get_query_var('vrp_current_price_list')) {
        $file = vrp_price_list_current_path();
        if (!is_file($file)) {
            status_header(404);
            exit;
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name(get_option('vrp_price_list_current_filename', 'cjenik-roba.csv')) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    if (!get_query_var('vrp_price_list_archive')) {
        return;
    }

    $uploads = wp_upload_dir();
    $invalid_archives = vrp_price_list_invalid_archives();
    $files = array_values(array_filter(vrp_price_list_archive_files(), function ($file) use ($invalid_archives) {
        return !isset($invalid_archives[basename($file)]);
    }));
    get_header();
    ?>
    <main>
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Početna', 'vocne-rakije-petkovic'); ?></a> / <a href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php esc_html_e('Proizvodi', 'vocne-rakije-petkovic'); ?></a> / <?php esc_html_e('Arhiva cjenika', 'vocne-rakije-petkovic'); ?></div>
                <span class="eyebrow"><?php esc_html_e('Cjenici za preuzimanje', 'vocne-rakije-petkovic'); ?></span>
                <h1><?php esc_html_e('Arhiva cjenika', 'vocne-rakije-petkovic'); ?></h1>
                <div class="lead"><?php esc_html_e('Prethodne verzije cjenika ostaju dostupne za preuzimanje.', 'vocne-rakije-petkovic'); ?></div>
            </div>
        </section>
        <section class="section"><div class="container">
            <ul>
                <?php foreach ($files as $file) : ?>
                    <?php $filename = basename($file); ?>
                    <li>
                        <a href="<?php echo esc_url(trailingslashit($uploads['baseurl']) . 'cjenik/arhiva/' . rawurlencode($filename)); ?>"><?php echo esc_html($filename); ?></a>
                        <small><?php echo esc_html(wp_date('d.m.Y. H:i', filemtime($file))); ?></small>
                    </li>
                <?php endforeach; ?>
                <?php if (!$files) : ?><li><?php esc_html_e('Trenutno nema ispravnih cjenika u arhivi.', 'vocne-rakije-petkovic'); ?></li><?php endif; ?>
            </ul>
            <p><a class="button button-dark" href="<?php echo esc_url(home_url('/cjenik/roba.csv')); ?>"><?php esc_html_e('Preuzmi važeći cjenik', 'vocne-rakije-petkovic'); ?></a></p>
        </div></section>
    </main>
    <?php
    get_footer();
    exit;
}
add_action('template_redirect', 'vrp_serve_price_list_routes');
