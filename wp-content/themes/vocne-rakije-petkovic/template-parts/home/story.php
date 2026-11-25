<?php
$story_image = absint(get_theme_mod('vrp_story_image'));
$story_url = $story_image ? wp_get_attachment_image_url($story_image, 'large') : vrp_asset_url('orchard.jpg');
?>
<section class="story-band">
    <div class="story-photo"><img src="<?php echo esc_url($story_url); ?>" alt="<?php echo esc_attr(vrp_get_text_setting('vrp_story_title')); ?>" loading="lazy" /></div>
    <div class="story-content">
        <span class="eyebrow light"><?php echo esc_html(vrp_get_text_setting('vrp_story_eyebrow')); ?></span>
        <h2><?php echo esc_html(vrp_get_text_setting('vrp_story_title')); ?></h2>
        <p><?php echo esc_html(vrp_get_text_setting('vrp_story_content')); ?></p>
        <dl class="story-stats">
            <?php for ($index = 1; $index <= 3; $index++) : $parts = explode('|', vrp_get_text_setting("vrp_story_stat_{$index}"), 2); ?>
                <div><dt><?php echo esc_html($parts[0] ?? ''); ?></dt><dd><?php echo esc_html($parts[1] ?? ''); ?></dd></div>
            <?php endfor; ?>
        </dl>
        <a class="button button-light" href="<?php echo esc_url(vrp_get_page_link('vrp_about_page')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_story_button')); ?></a>
    </div>
</section>
