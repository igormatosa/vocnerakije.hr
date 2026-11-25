<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
            <?php if (has_custom_logo()) : ?>
                <?php echo wp_get_attachment_image(get_theme_mod('custom_logo'), 'full', false, ['alt' => get_bloginfo('name')]); ?>
            <?php else : ?>
                <img src="<?php echo esc_url(vrp_asset_url('logo.png')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
            <?php endif; ?>
        </a>
        <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="<?php esc_attr_e('Otvori izbornik', 'vocne-rakije-petkovic'); ?>"><span></span><span></span><span></span></button>
        <nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e('Glavna navigacija', 'vocne-rakije-petkovic'); ?>">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'items_wrap' => '%3$s',
                    'depth' => 1,
                    'fallback_cb' => false,
                ]);
            } else {
                vrp_primary_menu_fallback();
            }
            ?>
        </nav>
    </div>
</header>
