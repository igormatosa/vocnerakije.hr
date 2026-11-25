<?php
$gift_image = absint(get_theme_mod('vrp_gift_image'));
$gift_url = $gift_image ? wp_get_attachment_image_url($gift_image, 'large') : vrp_asset_url('test-tubes.jpg');
?>
<section class="section gift-section">
    <div class="container gift-grid">
        <div>
            <span class="eyebrow"><?php echo esc_html(vrp_get_text_setting('vrp_gift_eyebrow')); ?></span>
            <h2><?php echo esc_html(vrp_get_text_setting('vrp_gift_title')); ?></h2>
            <p class="lead narrow"><?php echo esc_html(vrp_get_text_setting('vrp_gift_content')); ?></p>
            <a class="button button-dark" href="<?php echo esc_url(vrp_get_page_link('vrp_contact_page')); ?>#inquiry"><?php echo esc_html(vrp_get_text_setting('vrp_gift_button')); ?></a>
        </div>
        <div class="gift-image"><img src="<?php echo esc_url($gift_url); ?>" alt="<?php echo esc_attr(vrp_get_text_setting('vrp_gift_title')); ?>" loading="lazy" /><span><?php echo esc_html(vrp_get_text_setting('vrp_gift_badge')); ?></span></div>
    </div>
</section>
