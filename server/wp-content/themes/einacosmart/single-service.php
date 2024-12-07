<?php 
/* Single Service Template */
get_header(); 
?>

<div class="services-background">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <div class="services-content">
                    <h1 class="services-title"><?php the_title(); ?></h1>
                    <div class="services-description mt-4 mb-4">
                        <?php the_content(); ?>
                    </div>
                    <div class="services-link">
                        <form action="<?php echo site_url('/home'); ?>" method="get"> <button type="submit" class="rounded-pill btn-rounded border-primary"> Avail Service<span><i class="fas fa-arrow-right"></i></span> </button> 
                        </form>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>" class="img-fluid rounded service-image">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
