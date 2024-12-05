<?php
/*
Template Name: Portfolios
*/
get_header();
?>

<div class="portfolio-background">
    <div class="container py-5">
        <!-- Heading and Description with Custom Classes -->
        <div class="row justify-content-center mb-5 portfolio-header">
            <div class="col-md-6 bg-white rounded p-4 shadow">
                <h1 class="portfolio-title text-center">Our Portfolios</h1>
                <p class="portfolio-description text-center mb-0">
                    Explore our diverse range of projects showcasing our expertise and creativity. Each project represents a unique story, blending innovation and design to meet client goals.
                </p>
            </div>
        </div>
        
        <!-- Portfolio Items -->
        <div class="row">
            <?php
            // Query to fetch portfolio items
            $args = array(
                'post_type' => 'portfolio',
                'posts_per_page' => -1,
            );
            $portfolio_query = new WP_Query($args);

            if ($portfolio_query->have_posts()) :
                while ($portfolio_query->have_posts()) : $portfolio_query->the_post();
                    $project_url = get_field('project_url');
                    $project_image = get_field('project_image');
            ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card portfolio-card h-100">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <?php if ($project_image) : ?>
                                    <img src="<?php echo esc_url($project_image['url']); ?>" class="card-img-top" alt="<?php echo esc_attr($project_image['alt']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php the_title(); ?></h5>
                                    <p class="card-text"><?php the_excerpt(); ?></p>
                                    <?php if ($project_url) : ?>
                                        <a href="<?php echo esc_url($project_url); ?>" class="btn btn-primary" target="_blank">View Project</a>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No portfolios found</p>';
            endif;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
