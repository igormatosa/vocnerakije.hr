<?php

if (!defined('ABSPATH')) {
    exit;
}

function vrp_register_product_type()
{
    register_post_type('product', [
        'labels' => [
            'name' => __('Proizvodi', 'vocne-rakije-petkovic'),
            'singular_name' => __('Proizvod', 'vocne-rakije-petkovic'),
            'add_new_item' => __('Dodaj novi proizvod', 'vocne-rakije-petkovic'),
            'edit_item' => __('Uredi proizvod', 'vocne-rakije-petkovic'),
            'menu_name' => __('Proizvodi', 'vocne-rakije-petkovic'),
        ],
        'public' => true,
        'has_archive' => 'proizvodi',
        'rewrite' => ['slug' => 'proizvodi', 'with_front' => false],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-buddicons',
        'supports' => [],
        'menu_position' => 20,
    ]);

    register_taxonomy('product_category', 'product', [
        'labels' => [
            'name' => __('Kategorije proizvoda', 'vocne-rakije-petkovic'),
            'singular_name' => __('Kategorija proizvoda', 'vocne-rakije-petkovic'),
        ],
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'meta_box_cb' => false,
        'rewrite' => ['slug' => 'kategorija-proizvoda', 'with_front' => false],
    ]);
}
add_action('init', 'vrp_register_product_type');

function vrp_filter_product_archive($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_post_type_archive('product')) {
        $query->set('meta_key', '_vrp_is_product_family');
        $query->set('meta_value', '1');
        $query->set('posts_per_page', -1);
    } elseif ($query->is_tax('product_category')) {
        $term = $query->get_queried_object();
        if ($term && 'special-offers' === $term->slug) {
            $query->set('meta_key', '_vrp_special_offer');
            $query->set('meta_value', '1');
        } else {
            $query->set('meta_key', '_vrp_is_product_family');
            $query->set('meta_value', '1');
        }
        $query->set('posts_per_page', -1);
    }
}
add_action('pre_get_posts', 'vrp_filter_product_archive');

function vrp_flush_rewrite_once()
{
    if (!get_option('vrp_rewrite_flushed')) {
        flush_rewrite_rules();
        update_option('vrp_rewrite_flushed', '1');
    }
}
add_action('admin_init', 'vrp_flush_rewrite_once');
