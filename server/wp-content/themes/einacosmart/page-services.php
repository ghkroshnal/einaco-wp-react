<?php
/*
Template Name: Services
*/
get_header();
?>

<div class="services-background">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="services-title">Our Services</h1>
                <p class="services-description">We offer a wide range of services to meet your needs. Explore our services below to find out more about what we can do for you.</p>
            </div>
        </div>
        <div class="row">
            <?php
            $args = array(
                'post_type' => 'service',
                'posts_per_page' => -1, // Display all services
            );
            $services_query = new WP_Query($args);

            if ($services_query->have_posts()) :
                while ($services_query->have_posts()) : $services_query->the_post();
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="service-card">
                            <div class="card-body">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>" class="img-fluid rounded mb-3">
                                <?php endif; ?>
                                <h5 class="card-title"><?php the_title(); ?></h5>
                                <p class="card-text"><?php the_excerpt(); ?></p>
                                <form action="<?php the_permalink(); ?>" method="get">
                                    <button type="submit" class="rounded-pill btn-rounded border-primary">
                                        See Service<span><i class="fas fa-arrow-right"></i></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No services found</p>';
            endif;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
