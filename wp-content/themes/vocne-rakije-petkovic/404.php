<?php get_header(); ?>
<main><section class="page-hero"><div class="container"><span class="eyebrow">404</span><h1><?php esc_html_e('Stranica nije pronađena.', 'vocne-rakije-petkovic'); ?></h1><p class="lead"><?php esc_html_e('Poveznica je možda promijenjena ili više nije dostupna.', 'vocne-rakije-petkovic'); ?></p><a class="button button-dark" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Povratak na početnu', 'vocne-rakije-petkovic'); ?></a></div></section></main>
<?php get_footer(); ?>
