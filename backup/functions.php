<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {

	// Get the theme data
	$the_theme = wp_get_theme();
    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_style( 'google-font', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'jquery');
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
    wp_enqueue_style( 'child-lotus-styles', get_stylesheet_directory_uri() . '/style.css?v='.time(), array('child-understrap-styles') );
}

function add_child_theme_textdomain() {
    load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );

function lotus_signup($request) {
    $params = $request->get_params();


    //$ret = get_posts(array( 'post_type' => 'classes', 'ID' => $params['id']));
    //$ret = get_post($params['id']);
    //$ret = get_fields($params['id']);

    $registeredUsers = get_field('registered_users', $params['id']);
    if (isset($registeredUsers)) {
        $available = get_field('slots_available', $params['id']);

        $pos = array_search($params['user'], $registeredUsers);

        if (gettype($registeredUsers === 'array') && !is_int($pos)) {
            if (count($registeredUsers) == $available) {
                return 'class full!';
            } else {
                array_push($registeredUsers, $params['user']);
                update_post_meta($params['id'], 'registered_users', $registeredUsers);
                
                return 'signed up';
            }
        } else {
            unset($registeredUsers[$pos]);
            update_post_meta($params['id'], 'registered_users', $registeredUsers);

            return 'removed from class';
        }
    } else {
        add_post_meta( $params['id'], 'registered_users', [$params['user']], true);
        return 'register the new field';
    }

    return $ret;
}

add_action( 'rest_api_init', function () {
  register_rest_route('lotus', 'signup', array(
    'methods' => 'POST',
    'callback' => 'lotus_signup',
  ));
});
