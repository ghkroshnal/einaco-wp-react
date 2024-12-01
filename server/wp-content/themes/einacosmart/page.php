/* Template Name: Custom Page Template */

<?php get_header(); ?>

<div class="content">
    <?php
    while ( have_posts() ) : the_post();
        // Check if we're on the page and suppress title in content
        if (!is_page()) {
            echo '<h1 class="entry-title">' . get_the_title() . '</h1>';
        }
        the_content();
    endwhile;
    ?>
</div>

<?php get_footer(); ?>
