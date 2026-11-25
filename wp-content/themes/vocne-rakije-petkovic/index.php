<?php
$blog_settings = vrp_get_blog_archive_settings();
get_header();
?>
<main>
    <section class="page-hero">
        <div class="container">
            <div class="breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Početna', 'vocne-rakije-petkovic'); ?></a> / <?php esc_html_e('Iz voćnjaka', 'vocne-rakije-petkovic'); ?></div>
            <span class="eyebrow"><?php echo esc_html($blog_settings['eyebrow']); ?></span>
            <h1><?php echo esc_html($blog_settings['title']); ?><br /><em><?php echo esc_html($blog_settings['title_accent']); ?></em></h1>
            <p class="lead"><?php echo esc_html($blog_settings['intro']); ?></p>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php the_post(); ?>
                <article <?php post_class('blog-feature'); ?>>
                    <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(vrp_asset_url('orchard.jpg')); ?>" alt="" />
                        <?php endif; ?>
                    </a>
                    <div class="blog-feature-copy">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j. F Y.')); ?></time>
                        <h2><?php the_title(); ?></h2>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                        <a href="<?php the_permalink(); ?>"><?php esc_html_e('Pročitajte priču', 'vocne-rakije-petkovic'); ?></a>
                    </div>
                </article>
                <?php if (have_posts()) : ?>
                    <div class="blog-grid">
                        <?php while (have_posts()) : the_post(); ?>
                            <article <?php post_class('blog-card'); ?>>
                                <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large'); ?>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url(vrp_asset_url('orchard.jpg')); ?>" alt="" />
                                    <?php endif; ?>
                                </a>
                                <div>
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j. F Y.')); ?></time>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <a href="<?php the_permalink(); ?>"><?php esc_html_e('Pročitajte priču', 'vocne-rakije-petkovic'); ?></a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                <div class="center-action"><?php the_posts_pagination(); ?></div>
            <?php else : ?>
                <p><?php esc_html_e('Trenutno nema objava.', 'vocne-rakije-petkovic'); ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
