<?php
/**
 * Template Name: Contact Page
 */
get_header();
?>
<main>
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $contact_settings = vrp_get_contact_page_settings();

        $contact_status = isset($_GET['contact']) && is_scalar($_GET['contact'])
            ? sanitize_key(wp_unslash($_GET['contact']))
            : '';
        $contact_messages = [
            'sent' => __('Poruka je poslana. Hvala vam na upitu!', 'vocne-rakije-petkovic'),
            'invalid' => __('Provjerite obavezna polja i unesite ispravnu e-mail adresu.', 'vocne-rakije-petkovic'),
            'human' => __('Provjera nije uspjela. Odgovorite na novo pitanje i pokušajte ponovno.', 'vocne-rakije-petkovic'),
            'limited' => __('Dosegnut je limit pokušaja. Pokušajte ponovno za nekoliko minuta ili nas nazovite.', 'vocne-rakije-petkovic'),
            'failed' => __('Poruku trenutačno nije moguće poslati. Pokušajte ponovno ili nam se javite telefonom.', 'vocne-rakije-petkovic'),
        ];
        $human_left = wp_rand(2, 9);
        $human_right = wp_rand(1, 9);
        $human_answer = $human_left + $human_right;
        $map_url = vrp_get_text_setting('vrp_map_url', 'https://www.google.com/maps/search/?api=1&query=Zrinskih+4A%2C+Šenkovec%2C+Croatia');
        $map_embed_url = 'https://www.google.com/maps?q=' . rawurlencode('Zrinskih 4A, Šenkovec, Croatia') . '&output=embed';
        ?>
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Početna', 'vocne-rakije-petkovic'); ?></a> / <?php esc_html_e('Kontakt', 'vocne-rakije-petkovic'); ?></div>
                <span class="eyebrow"><?php echo esc_html($contact_settings['hero_eyebrow']); ?></span>
                <h1><?php echo esc_html($contact_settings['hero_title']); ?><br /><em><?php echo esc_html($contact_settings['hero_title_accent']); ?></em></h1>
                <p class="lead"><?php echo esc_html($contact_settings['hero_intro']); ?></p>
            </div>
        </section>
    <?php endwhile; ?>
    <section class="section" id="inquiry">
        <div class="container contact-layout">
            <div class="contact-details">
                <span class="eyebrow"><?php echo esc_html($contact_settings['details_eyebrow']); ?></span>
                <h2><?php echo esc_html(vrp_get_text_setting('vrp_contact_company', 'OPG Petković Nada')); ?></h2>
                <div class="contact-item">
                    <span><?php esc_html_e('Adresa', 'vocne-rakije-petkovic'); ?></span>
                    <p><?php echo nl2br(esc_html(vrp_get_text_setting('vrp_contact_address', "Zrinskih 4a, Šenkovec\n40000 Čakovec, Hrvatska"))); ?></p>
                </div>
                <div class="contact-item">
                    <span><?php esc_html_e('Telefon', 'vocne-rakije-petkovic'); ?></span>
                    <?php $landline = vrp_get_text_setting('vrp_contact_landline', '+385 40 343 314'); ?>
                    <?php $mobile = vrp_get_text_setting('vrp_contact_phone', '+385 99 317 3317'); ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', $landline)); ?>"><?php echo esc_html($landline); ?></a><br />
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', $mobile)); ?>"><?php echo esc_html($mobile); ?></a>
                </div>
                <div class="contact-item">
                    <span><?php esc_html_e('E-mail', 'vocne-rakije-petkovic'); ?></span>
                    <?php $contact_email = vrp_get_text_setting('vrp_contact_email', 'info@vocnerakije.hr'); ?>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                </div>
                <div class="contact-item">
                    <span><?php esc_html_e('Identifikacija', 'vocne-rakije-petkovic'); ?></span>
                    <p><?php esc_html_e('OIB:', 'vocne-rakije-petkovic'); ?> <?php echo esc_html(vrp_get_text_setting('vrp_contact_oib', '42115479386')); ?><br /><?php esc_html_e('MBPG:', 'vocne-rakije-petkovic'); ?> <?php echo esc_html(vrp_get_text_setting('vrp_contact_registration', '50302922')); ?><br /><?php esc_html_e('Privredna banka Zagreb', 'vocne-rakije-petkovic'); ?></p>
                </div>
            </div>
            <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('vrp_contact_form', 'vrp_contact_nonce'); ?>
                <?php wp_nonce_field('vrp_contact_human_' . $human_answer, 'vrp_human_nonce'); ?>
                <input type="hidden" name="action" value="vrp_contact" />
                <div class="form-field contact-honeypot" aria-hidden="true">
                    <label for="vrp-website"><?php esc_html_e('Ostavite prazno', 'vocne-rakije-petkovic'); ?></label>
                    <input id="vrp-website" name="vrp_website" tabindex="-1" autocomplete="off" />
                </div>
                <span class="eyebrow"><?php echo esc_html($contact_settings['form_eyebrow']); ?></span>
                <h2><?php echo esc_html($contact_settings['form_title']); ?></h2>
                <?php if (isset($contact_messages[$contact_status])) : ?>
                    <p class="form-note <?php echo 'sent' === $contact_status ? 'form-note-success' : 'form-note-error'; ?>" role="<?php echo 'sent' === $contact_status ? 'status' : 'alert'; ?>">
                        <?php echo esc_html($contact_messages[$contact_status]); ?>
                    </p>
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="vrp-name"><?php esc_html_e('Ime i prezime', 'vocne-rakije-petkovic'); ?></label>
                        <input id="vrp-name" name="vrp_name" autocomplete="name" maxlength="120" required />
                    </div>
                    <div class="form-field">
                        <label for="vrp-email"><?php esc_html_e('E-mail', 'vocne-rakije-petkovic'); ?></label>
                        <input id="vrp-email" name="vrp_email" type="email" autocomplete="email" maxlength="254" required />
                    </div>
                    <div class="form-field full">
                        <label for="vrp-phone"><?php esc_html_e('Telefon (nije obavezno)', 'vocne-rakije-petkovic'); ?></label>
                        <input id="vrp-phone" name="vrp_phone" type="tel" autocomplete="tel" maxlength="40" />
                    </div>
                    <div class="form-field full">
                        <label for="vrp-message"><?php esc_html_e('Vaš upit', 'vocne-rakije-petkovic'); ?></label>
                        <textarea id="vrp-message" name="vrp_message" maxlength="10000" required><?php echo esc_textarea(isset($_GET['product']) && is_scalar($_GET['product']) ? sprintf(__('Zanima me ponuda za proizvod: %s.', 'vocne-rakije-petkovic'), sanitize_text_field(wp_unslash($_GET['product']))) : ''); ?></textarea>
                    </div>
                    <div class="form-field full human-check">
                        <label for="vrp-human-answer"><?php printf(esc_html__('Provjera: koliko je %1$d + %2$d?', 'vocne-rakije-petkovic'), $human_left, $human_right); ?></label>
                        <input id="vrp-human-answer" name="vrp_human_answer" type="number" inputmode="numeric" min="0" max="18" required />
                        <small><?php esc_html_e('Odgovorite na kratko pitanje kako bismo spriječili automatske poruke.', 'vocne-rakije-petkovic'); ?></small>
                    </div>
                </div>
                <button class="button button-dark full-button" type="submit"><?php esc_html_e('Pošalji upit', 'vocne-rakije-petkovic'); ?> <span>→</span></button>
                <p class="form-note"><?php echo esc_html($contact_settings['privacy_note']); ?></p>
            </form>
        </div>
    </section>
    <section class="section map-section">
        <div class="container">
            <div class="map-embed">
                <iframe
                    src="<?php echo esc_url($map_embed_url); ?>"
                    title="<?php esc_attr_e('Google karta lokacije OPG-a Petković u Šenkovcu', 'vocne-rakije-petkovic'); ?>"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
            </div>
            <a class="map-card" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">
                <img class="map-card-logo" src="<?php echo esc_url(vrp_asset_url('logo.png')); ?>" alt="" aria-hidden="true" />
                <strong><?php echo esc_html(vrp_get_text_setting('vrp_contact_company', 'OPG Petković Nada')); ?></strong>
                <span><?php esc_html_e('Zrinskih 4a, Šenkovec', 'vocne-rakije-petkovic'); ?></span>
                <span><?php esc_html_e('Otvori u Google kartama →', 'vocne-rakije-petkovic'); ?></span>
            </a>
        </div>
    </section>
    <section class="section contact-faq-section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow"><?php echo esc_html($contact_settings['faq_eyebrow']); ?></span>
                <h2><?php echo esc_html($contact_settings['faq_title']); ?></h2>
            </div>
            <div class="faq">
                <details class="faq-item">
                    <summary class="faq-question"><?php echo esc_html($contact_settings['faq_1_question']); ?><span aria-hidden="true">+</span></summary>
                    <div class="faq-answer"><p><?php echo esc_html($contact_settings['faq_1_answer']); ?></p></div>
                </details>
                <details class="faq-item">
                    <summary class="faq-question"><?php echo esc_html($contact_settings['faq_2_question']); ?><span aria-hidden="true">+</span></summary>
                    <div class="faq-answer"><p><?php echo esc_html($contact_settings['faq_2_answer']); ?></p></div>
                </details>
                <details class="faq-item">
                    <summary class="faq-question"><?php echo esc_html($contact_settings['faq_3_question']); ?><span aria-hidden="true">+</span></summary>
                    <div class="faq-answer"><p><?php echo esc_html($contact_settings['faq_3_answer']); ?></p></div>
                </details>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
