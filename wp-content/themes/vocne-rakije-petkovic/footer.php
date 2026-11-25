<footer class="site-footer">
    <div class="container footer-top">
        <a class="brand footer-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if (has_custom_logo()) : ?>
                <?php echo wp_get_attachment_image(get_theme_mod('custom_logo'), 'full', false, ['alt' => get_bloginfo('name')]); ?>
            <?php else : ?>
                <img src="<?php echo esc_url(vrp_asset_url('logo.png')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
            <?php endif; ?>
        </a>
        <div>
            <span><?php esc_html_e('Navigacija', 'vocne-rakije-petkovic'); ?></span>
            <?php if (has_nav_menu('footer')) : ?>
                <?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'fallback_cb' => false]); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('product')); ?>"><?php esc_html_e('Proizvodi', 'vocne-rakije-petkovic'); ?></a>
                <a href="<?php echo esc_url(vrp_get_page_link('vrp_about_page')); ?>"><?php esc_html_e('O nama', 'vocne-rakije-petkovic'); ?></a>
                <a href="<?php $posts_page_id = absint(get_option('page_for_posts')); echo esc_url($posts_page_id ? get_permalink($posts_page_id) : home_url('/blog/')); ?>"><?php esc_html_e('Iz voćnjaka', 'vocne-rakije-petkovic'); ?></a>
                <a href="<?php echo esc_url(vrp_get_page_link('vrp_contact_page')); ?>"><?php esc_html_e('Kontakt', 'vocne-rakije-petkovic'); ?></a>
            <?php endif; ?>
        </div>
        <div>
            <span><?php esc_html_e('Posjetite nas', 'vocne-rakije-petkovic'); ?></span>
            <p><?php echo nl2br(esc_html(vrp_get_text_setting('vrp_contact_address', "Zrinskih 4a\n40000 Čakovec\nHrvatska"))); ?></p>
        </div>
        <div>
            <span><?php esc_html_e('Kontakt', 'vocne-rakije-petkovic'); ?></span>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', vrp_get_text_setting('vrp_contact_phone', '+385993173317'))); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317')); ?></a>
            <a href="mailto:<?php echo esc_attr(vrp_get_text_setting('vrp_contact_email', 'info@vocnerakije.hr')); ?>"><?php echo esc_html(vrp_get_text_setting('vrp_contact_email', 'info@vocnerakije.hr')); ?></a>
        </div>
    </div>
    <div class="container footer-bottom">
        <p>© <span data-current-year></span> <?php bloginfo('name'); ?></p>
        <p><?php esc_html_e('Uživajte odgovorno.', 'vocne-rakije-petkovic'); ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
