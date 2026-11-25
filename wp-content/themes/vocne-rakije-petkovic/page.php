<?php get_header(); ?>
<main>
    <?php while (have_posts()) : the_post(); ?>
        <section class="page-hero">
            <div class="container"><span class="eyebrow"><?php bloginfo('name'); ?></span><h1><?php the_title(); ?></h1></div>
        </section>
        <section class="section">
            <div class="container article-shell">
                <div class="article-copy"><?php the_content(); ?></div>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
