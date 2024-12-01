<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<nav class="navbar navbar-expand-lg navbar-dark menu shadow fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo home_url(); ?>">
            <?php 
            if (has_custom_logo()) : 
                the_custom_logo(); 
            else : 
                echo '<img src="' . get_template_directory_uri() . '/assets/images/logos/logo.png" alt="smart logo">';
            endif; 
            ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class'     => 'navbar-nav',
                'container'      => false,
            ));
            ?>
            <button type="button" class="rounded-pill btn-rounded">
                +1 728365413
                <span>
                    <i class="fas fa-phone-alt"></i>
                </span>
            </button>
        </div>
    </div>
</nav>
