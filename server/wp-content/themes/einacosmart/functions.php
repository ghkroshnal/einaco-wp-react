<?php
// Theme setup
function einaco_smart_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'einaco-smart-theme'),
    ));
}
add_action('after_setup_theme', 'einaco_smart_theme_setup');

// Enqueue styles and scripts
function einaco_smart_theme_enqueue_scripts() {
    
    wp_enqueue_style('einaco-fontawesome', get_template_directory_uri() . '/assets/css/fontawesome.css');
    wp_enqueue_style('einaco-glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css', array(), null);
    wp_enqueue_style('einaco-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap', array(), null);
    wp_enqueue_style('einaco-bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.css', array(), '5.3', 'all');
    wp_enqueue_style('einaco-main-style', get_template_directory_uri() . '/style.css', array(), null, 'all');
    wp_enqueue_script('einaco-glightbox', 'https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js', array(), null, true);
    wp_enqueue_script('einaco-bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.js', array('jquery'), '5.3', true);

    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'einaco_smart_theme_enqueue_scripts');

// Enable CORS
function enable_cors($value) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept");
    return $value;
}
add_filter('rest_pre_serve_request', 'enable_cors');

// Register REST API endpoints for header and footer
function register_custom_header_footer_endpoints() {
    register_rest_route('custom/v1', '/header', array(
        'methods' => 'GET',
        'callback' => 'get_einaco_header', 
    ));

    register_rest_route('custom/v1', '/footer', array(
        'methods' => 'GET',
        'callback' => 'get_einaco_footer', 
    ));
}
add_action('rest_api_init', 'register_custom_header_footer_endpoints');

function get_einaco_header() { 
    ob_start();
    get_template_part('header');
    $header = ob_get_clean();

    if (!$header) {
        return new WP_Error('no_header', 'Header content could not be loaded.', array('status' => 500));
    }

    return array('content' => $header);
}

function get_einaco_footer() { 
    ob_start();
    get_template_part('footer');
    $footer = ob_get_clean();

    if (!$footer) {
        return new WP_Error('no_footer', 'Footer content could not be loaded.', array('status' => 500));
    }

    return array('content' => $footer);
}

//To hide page title
function hide_page_title_on_frontend($title, $id) {
    // Do not alter titles in the admin area
    if (is_admin()) {
        return $title;
    }

    // Check if we're dealing with a nav menu item
    if ('nav_menu_item' === get_post_type($id)) {
        return $title; // Keep the title for navigation menu items
    }

    // Hide titles on pages in the frontend
    if (is_page($id) && !is_main_query()) {
        return ''; // Return an empty string for page titles in frontend content
    }

    return $title; // Keep the title for everything else
}
add_filter('the_title', 'hide_page_title_on_frontend', 10, 2);


// Form Submission handling
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/send-email', array(
        'methods' => 'POST',
        'callback' => 'send_contact_email',
        'permission_callback' => '__return_true', // Make sure to secure this in production
    ));
});

function send_contact_email(WP_REST_Request $request) {
    $params = $request->get_json_params();

    $first_name = sanitize_text_field($params['firstName']);
    $last_name = sanitize_text_field($params['lastName']);
    $email = sanitize_email($params['email']);
    $message = sanitize_textarea_field($params['message']);

    $to = 'your-email@example.com'; // Replace with your email
    $subject = 'New Contact Form Submission';
    $body = "Name: $first_name $last_name\nEmail: $email\n\nMessage:\n$message";
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        return rest_ensure_response(array('status' => 'success', 'message' => 'Email sent successfully'));
    } else {
        return rest_ensure_response(array('status' => 'error', 'message' => 'Failed to send email'), 500);
    }
}

// REST API endpoint for wp-blocks
function register_patterns_endpoint() {
    register_rest_route('custom/v1', '/patterns', array(
        'methods' => 'GET',
        'callback' => 'get_patterns',
    ));
}

function get_patterns() {
    $patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
    $output = array();

    foreach ($patterns as $name => $pattern) {
        $output[] = array(
            'name' => $name,
            'title' => $pattern['title'],
            'content' => $pattern['content'],
        );
    }

    return $output;
}

add_action('rest_api_init', 'register_patterns_endpoint');

// CPT Portfolio
function create_portfolio_cpt() {
    $labels = array(
        'name' => _x('Portfolios', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Portfolio', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Portfolios', 'textdomain'),
        'name_admin_bar' => __('Portfolio', 'textdomain'),
        'archives' => __('Portfolio Archives', 'textdomain'),
        'attributes' => __('Portfolio Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Portfolio:', 'textdomain'),
        'all_items' => __('All Portfolios', 'textdomain'),
        'add_new_item' => __('Add New Portfolio', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Portfolio', 'textdomain'),
        'edit_item' => __('Edit Portfolio', 'textdomain'),
        'update_item' => __('Update Portfolio', 'textdomain'),
        'view_item' => __('View Portfolio', 'textdomain'),
        'view_items' => __('View Portfolios', 'textdomain'),
        'search_items' => __('Search Portfolio', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Portfolio', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Portfolio', 'textdomain'),
        'items_list' => __('Portfolios list', 'textdomain'),
        'items_list_navigation' => __('Portfolios list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Portfolios list', 'textdomain'),
    );
    $args = array(
        'label' => __('Portfolio', 'textdomain'),
        'description' => __('Portfolio Description', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies' => array('category', 'post_tag'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
    );
    register_post_type('portfolio', $args);
}

add_action('init', 'create_portfolio_cpt', 0);

// Custom meta boxes for CPT portfolio
function portfolio_custom_meta() {
    add_meta_box('portfolio_meta', 'Portfolio Details', 'portfolio_meta_callback', 'portfolio', 'normal', 'high');
}

function portfolio_meta_callback($post) {
    wp_nonce_field(basename(__FILE__), 'portfolio_nonce');
    $portfolio_stored_meta = get_post_meta($post->ID);

    ?>
    <p>
        <label for="meta-stack" class="portfolio-row-title"><?php _e('Technical Stack', 'textdomain') ?></label>
        <input type="text" name="meta-stack" id="meta-stack" value="<?php if (isset($portfolio_stored_meta['meta-stack'])) echo $portfolio_stored_meta['meta-stack'][0]; ?>" />
    </p>
    <p>
        <label for="meta-url" class="portfolio-row-title"><?php _e('Project URL', 'textdomain') ?></label>
        <input type="url" name="meta-url" id="meta-url" value="<?php if (isset($portfolio_stored_meta['meta-url'])) echo $portfolio_stored_meta['meta-url'][0]; ?>" />
    </p>
    <?php
}

function save_portfolio_meta($post_id) {
    if (!isset($_POST['portfolio_nonce']) || !wp_verify_nonce($_POST['portfolio_nonce'], basename(__FILE__))) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ('portfolio' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) return;
    } else {
        if (!current_user_can('edit_page', $post_id)) return;
    }

    if (isset($_POST['meta-stack'])) {
        update_post_meta($post_id, 'meta-stack', sanitize_text_field($_POST['meta-stack']));
    }

    if (isset($_POST['meta-url'])) {
        update_post_meta($post_id, 'meta-url', esc_url_raw($_POST['meta-url']));
    }
}

add_action('add_meta_boxes', 'portfolio_custom_meta');
add_action('save_post', 'save_portfolio_meta');

// Shortcode generator for Portfolio items
function display_portfolios_shortcode($atts) {
    ob_start();
    
    $args = array(
        'post_type' => 'portfolio',
        'posts_per_page' => -1,
    );
    $portfolio_query = new WP_Query($args);

    if ($portfolio_query->have_posts()) : ?>
        <div class="container py-5">
            <div class="row">
                <?php while ($portfolio_query->have_posts()) : $portfolio_query->the_post(); 
                    $project_url = get_field('project_url');
                    $project_image = get_field('project_image');
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card portfolio-card h-100">
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
                        </div>
                    </div>
                <?php endwhile; 
                wp_reset_postdata(); ?>
            </div>
        </div>
    <?php else : 
        echo '<p>No portfolios found</p>';
    endif;

    return ob_get_clean();
}
add_shortcode('display_portfolios', 'display_portfolios_shortcode');

// CPT for Services
function create_services_cpt() {
    $labels = array(
        'name' => _x('Services', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Service', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => _x('Services', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Service', 'Add New on Toolbar', 'textdomain'),
        'archives' => __('Service Archives', 'textdomain'),
        'attributes' => __('Service Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Service:', 'textdomain'),
        'all_items' => __('All Services', 'textdomain'),
        'add_new_item' => __('Add New Service', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Service', 'textdomain'),
        'edit_item' => __('Edit Service', 'textdomain'),
        'update_item' => __('Update Service', 'textdomain'),
        'view_item' => __('View Service', 'textdomain'),
        'view_items' => __('View Services', 'textdomain'),
        'search_items' => __('Search Service', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into service', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this service', 'textdomain'),
        'items_list' => __('Services list', 'textdomain'),
        'items_list_navigation' => __('Services list navigation', 'textdomain'),
        'filter_items_list' => __('Filter services list', 'textdomain'),
    );
    $args = array(
        'label' => __('Service', 'textdomain'),
        'description' => __('Custom Post Type for Services', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'custom-fields'),
        'taxonomies' => array('category', 'post_tag'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true, // Enable REST API
    );
    register_post_type('service', $args);
}

add_action('init', 'create_services_cpt', 0);



