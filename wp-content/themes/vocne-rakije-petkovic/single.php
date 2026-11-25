<?php get_header(); ?>
<main>
    <?php while (have_posts()) : the_post(); ?>
        <article>
            <section class="page-hero"><div class="container article-shell"><time class="eyebrow"><?php echo esc_html(get_the_date()); ?></time><h1><?php the_title(); ?></h1><p class="lead"><?php echo esc_html(get_the_excerpt()); ?></p></div></section>
            <section class="section"><div class="container article-shell">
                <?php if (has_post_thumbnail()) the_post_thumbnail('large', ['class' => 'article-image']); ?>
                <div class="article-copy"><?php the_content(); ?></div>
            </div></section>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
