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
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
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
