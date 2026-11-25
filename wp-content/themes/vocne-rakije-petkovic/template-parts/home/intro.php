<section class="intro-strip" aria-label="<?php esc_attr_e('Naše vrijednosti', 'vocne-rakije-petkovic'); ?>">
    <div class="container intro-grid">
        <p><?php echo esc_html(vrp_get_text_setting('vrp_intro_title')); ?></p>
        <?php for ($index = 1; $index <= 3; $index++) : $parts = explode('|', vrp_get_text_setting("vrp_intro_{$index}"), 2); ?>
            <div><span><?php echo esc_html(str_pad((string) $index, 2, '0', STR_PAD_LEFT)); ?></span><h3><?php echo esc_html($parts[0] ?? ''); ?></h3><p><?php echo esc_html($parts[1] ?? ''); ?></p></div>
        <?php endfor; ?>
    </div>
</section>
