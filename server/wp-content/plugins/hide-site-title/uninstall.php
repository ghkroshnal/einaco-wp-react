<?php
/**
 * @author Bill Minozzi
 * @copyright 2024 01 31
 */
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
$hide_site_title_options = array(
    'hide_site_title_id',
    'hide_site_title_class',
    'hide_site_title_hide_title'
);
foreach ($hide_site_title_options as $option_name => $option_value) {
    if (is_multisite()) {
        delete_site_option($option_name);
    } else {
        delete_option($option_name);
    }
}
?>