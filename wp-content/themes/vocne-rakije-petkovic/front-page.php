<?php get_header(); ?>
<?php
$contact_phone = vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317');
$contact_phone_href = preg_replace('/[^+0-9]/', '', $contact_phone);
$contact_email = vrp_get_text_setting('vrp_contact_email', 'info@vocnerakije.hr');
?>
<main>
    <?php get_template_part('template-parts/home/hero'); ?>
    <?php get_template_part('template-parts/home/intro'); ?>
    <?php get_template_part('template-parts/home/products'); ?>
    <?php get_template_part('template-parts/home/special-offers'); ?>
    <?php get_template_part('template-parts/home/story'); ?>
    <?php if (get_theme_mod('vrp_gift_enabled', true)) : ?>
        <?php get_template_part('template-parts/home/gift'); ?>
    <?php endif; ?>
    <section class="home-contact">
        <div class="container contact-callout">
            <span class="eyebrow light"><?php echo esc_html(vrp_get_text_setting('vrp_cta_eyebrow', 'Šenkovec · Međimurje')); ?></span>
            <h2><?php echo esc_html(vrp_get_text_setting('vrp_cta_title', 'Želite degustirati ili naručiti?')); ?></h2>
            <p><?php echo esc_html(vrp_get_text_setting('vrp_cta_content', 'Javite nam se i zajedno ćemo pronaći pravo pakiranje za vas.')); ?></p>
            <div class="button-row centered">
                <a class="button button-light" href="tel:<?php echo esc_attr($contact_phone_href); ?>" data-contact-options-trigger><?php echo esc_html(vrp_get_text_setting('vrp_cta_button', 'Nazovite nas')); ?></a>
            </div>
        </div>
        <dialog class="contact-options-dialog" aria-labelledby="contact-options-title">
            <div class="contact-dialog-content">
                <button class="contact-dialog-close" type="button" aria-label="<?php esc_attr_e('Zatvori', 'vocne-rakije-petkovic'); ?>" data-contact-dialog-close>&times;</button>
                <span class="eyebrow"><?php esc_html_e('Javite nam se', 'vocne-rakije-petkovic'); ?></span>
                <h2 id="contact-options-title"><?php esc_html_e('Odaberite način kontakta', 'vocne-rakije-petkovic'); ?></h2>
                <div class="contact-dialog-links">
                    <a href="tel:<?php echo esc_attr($contact_phone_href); ?>">
                        <span><?php esc_html_e('Nazovite nas', 'vocne-rakije-petkovic'); ?></span>
                        <strong><?php echo esc_html($contact_phone); ?></strong>
                    </a>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>">
                        <span><?php esc_html_e('Pošaljite e-mail', 'vocne-rakije-petkovic'); ?></span>
                        <strong><?php echo esc_html($contact_email); ?></strong>
                    </a>
                </div>
            </div>
        </dialog>
    </section>
</main>
<?php get_footer(); ?>
