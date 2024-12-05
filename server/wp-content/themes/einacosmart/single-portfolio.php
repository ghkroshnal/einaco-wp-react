<?php get_header(); ?>

<div class="portfolio-background">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <div class="portfolio-content">
                    <h1 class="portfolio-title"><?php the_title(); ?></h1>
                    <div class="portfolio-description mb-4">
                        <?php the_content(); ?>
                    </div>
                    <?php
                    $technical_stack = get_field('technical_stack') ?: 'Not specified';
                    if ($technical_stack) : ?>
                        <div class="portfolio-stack mb-4">
                            <h2 class="h4">Technical Stack</h2>
                            <p><?php echo esc_html($technical_stack); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <?php
                $project_image = get_field('project_image');
                if ($project_image) : ?>
                    <div class="portfolio-image text-center mb-4">
                        <img src="<?php echo esc_url($project_image['url']); ?>" alt="<?php echo esc_attr($project_image['alt']); ?>" class="img-fluid rounded">
                        <?php
                        $project_url = get_field('project_url');
                        if ($project_url) : ?>
                            <div class="portfolio-link">
                                <a href="<?php echo esc_url($project_url); ?>" class="btn btn-primary mt-3" target="_blank">View Project</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
