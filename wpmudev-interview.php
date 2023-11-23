<?php

/*
 * Plugin Name: WPMUDev Interview
 * Plugin URI: https://github.com/susantohenri/wpmudev-interview
 * Description: Make Magic
 * Version: 1.0
 * Requires at least: 6.4.1
 * Requires PHP: 8.1.23
 * Author: Henri Susanto
 * Author URI: https://github.com/susantohenri
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI: https://github.com/susantohenri/wpmudev-interview
 * Text Domain: WPMUDevInterview
 * Domain Path: /wpmudev-interview
 */

require_once(plugin_dir_path(__FILE__) . 'wpmudev-interview.class.php');
$wpmudev = new WPMUInterview();

register_activation_hook(__FILE__, [$wpmudev, 'createTable']);
register_deactivation_hook(__FILE__, [$wpmudev, 'dropTable']);

add_shortcode('my_form', [$wpmudev, 'form']);
add_shortcode('my_list', [$wpmudev, 'list']);

add_action('rest_api_init', function () use ($wpmudev) {
    register_rest_route(
        'wpmudev-interview/v1',
        '/data',
        array(
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => [$wpmudev, 'tbody']
        )
    );
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('wpmudev_interview', plugin_dir_url(__FILE__) . 'wpmudev-interview.js', array('jquery'), '');
    wp_localize_script(
        'wpmudev_interview',
        'wpmudev_interview_list',
        ['url' => site_url('wp-json/wpmudev-interview/v1/data')]
    );
});



// function my_shortcode_list()
// {
//     $data = get_my_table_data();
// }

// function get_my_table_data($page, $per_page, $orderby, $order, $search)
// {
//     return [];
// }

// function insert_data_to_my_table()
// {
// }
