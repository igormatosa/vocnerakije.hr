<?php
$slider_images = [];
for ($index = 1; $index <= 4; $index++) {
    $image_id = absint(get_theme_mod("vrp_slider_image_{$index}"));
    if ($image_id) {
        $slider_images[] = [
            'url' => wp_get_attachment_image_url($image_id, 'large'),
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            'caption' => vrp_get_text_setting("vrp_slider_caption_{$index}"),
        ];
    }
}
if (empty($slider_images)) {
    $slider_images = [
        ['url' => vrp_asset_url('hero-pears.jpg'), 'alt' => 'Voćne rakije Petković s kruškama', 'caption' => 'Svaka boca nosi okus ploda i kraja.'],
        ['url' => vrp_asset_url('hero-brandies.jpg'), 'alt' => 'Drveni znak Voćne rakije Petković', 'caption' => 'Obiteljska tradicija iz srca Međimurja.'],
    ];
}
$intro_parts = explode('|', vrp_get_text_setting('vrp_hero_points', 'Provjereno voće|Tradicionalna destilacija|Laboratorijski ispitano'));
?>
<section class="home-hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><?php echo esc_html(vrp_get_text_setting('vrp_hero_eyebrow')); ?></span>
            <h1><?php echo esc_html(vrp_get_text_setting('vrp_hero_title')); ?></h1>
            <p class="lead"><?php echo esc_html(vrp_get_text_setting('vrp_hero_content')); ?></p>
            <div class="button-row">
                <a class="button button-dark" href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_hero_primary_label')); ?> <span>→</span></a>
                <a class="text-link" href="<?php echo esc_url(vrp_get_page_link('vrp_about_page')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_hero_secondary_label')); ?></a>
            </div>
            <div class="hero-trust"><?php foreach (array_slice($intro_parts, 0, 3) as $point) : ?><span><?php echo esc_html(trim($point)); ?></span><?php endforeach; ?></div>
        </div>
        <div class="hero-media hero-slider" aria-roledescription="carousel" aria-label="<?php esc_attr_e('Izdvojeno iz naše ponude', 'vocne-rakije-petkovic'); ?>">
            <div class="hero-slides">
                <?php foreach ($slider_images as $index => $slide) : ?>
                    <figure class="hero-slide<?php echo 0 === $index ? ' active' : ''; ?>" data-caption="<?php echo esc_attr($slide['caption']); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
                        <img src="<?php echo esc_url($slide['url']); ?>" alt="<?php echo esc_attr($slide['alt'] ?: $slide['caption']); ?>" <?php echo 0 === $index ? 'fetchpriority="high"' : 'loading="lazy"'; ?> />
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="hero-stamp"><strong>OPG</strong><span><?php echo esc_html(get_theme_mod('vrp_hero_stamp_name', 'Petković')); ?></span><small><?php echo esc_html(get_theme_mod('vrp_hero_stamp_location', 'Međimurje')); ?></small></div>
            <button class="hero-slider-control previous" type="button" aria-label="<?php esc_attr_e('Prethodna slika', 'vocne-rakije-petkovic'); ?>">‹</button>
            <button class="hero-slider-control next" type="button" aria-label="<?php esc_attr_e('Sljedeća slika', 'vocne-rakije-petkovic'); ?>">›</button>
            <div class="image-caption">
                <span class="hero-slide-count" aria-live="polite">01 / <?php echo esc_html(str_pad((string) count($slider_images), 2, '0', STR_PAD_LEFT)); ?></span>
                <p class="hero-slide-caption"><?php echo esc_html($slider_images[0]['caption']); ?></p>
                <div class="hero-slide-dots" aria-label="<?php esc_attr_e('Odaberite sliku', 'vocne-rakije-petkovic'); ?>">
                    <?php foreach ($slider_images as $index => $slide) : ?><button class="<?php echo 0 === $index ? 'active' : ''; ?>" type="button" data-slide="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Prikaži sliku %d', 'vocne-rakije-petkovic'), $index + 1)); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
