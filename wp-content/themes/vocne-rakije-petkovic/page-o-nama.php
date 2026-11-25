<?php

$about = vrp_get_about_page_settings();
$image_id = absint($about['orchard_image_id']);
?>
<?php get_header(); ?>
<main>
    <section class="page-hero">
        <div class="container">
            <div class="breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Početna', 'vocne-rakije-petkovic'); ?></a> / <?php esc_html_e('O nama', 'vocne-rakije-petkovic'); ?></div>
            <span class="eyebrow"><?php echo esc_html($about['hero_eyebrow']); ?></span>
            <h1><?php echo esc_html($about['hero_title']); ?><br /><em><?php echo esc_html($about['hero_title_accent']); ?></em></h1>
            <p class="lead"><?php echo esc_html($about['hero_intro']); ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container about-grid">
            <div class="about-story">
                <span class="eyebrow"><?php echo esc_html($about['orchard_eyebrow']); ?></span>
                <h2><?php echo esc_html($about['orchard_title']); ?></h2>
                <?php echo wpautop(esc_html($about['orchard_paragraphs'])); ?>
            </div>
            <div class="about-media">
                <?php
                if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'large', false, ['alt' => $about['orchard_image_alt']]);
                } else {
                    ?>
                    <img src="<?php echo esc_url(vrp_asset_url('orchard.jpg')); ?>" alt="<?php echo esc_attr($about['orchard_image_alt']); ?>" />
                    <?php
                }
                ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow"><?php echo esc_html($about['process_eyebrow']); ?></span>
                <h2><?php echo esc_html($about['process_title']); ?></h2>
            </div>
            <div class="process-grid">
                <?php for ($step = 1; $step <= 4; $step++) : ?>
                    <article>
                        <span><?php echo esc_html(sprintf('%02d', $step)); ?></span>
                        <h3><?php echo esc_html($about['process_' . $step . '_title']); ?></h3>
                        <p><?php echo esc_html($about['process_' . $step . '_description']); ?></p>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="quality-block">
                <div>
                    <span class="eyebrow light"><?php echo esc_html($about['quality_eyebrow']); ?></span>
                    <h2><?php echo esc_html($about['quality_title']); ?></h2>
                </div>
                <div>
                    <ul>
                        <?php foreach (preg_split('/\r\n|\r|\n/', $about['quality_items']) as $item) : ?>
                            <?php if ('' !== trim($item)) : ?><li><?php echo esc_html($item); ?></li><?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
