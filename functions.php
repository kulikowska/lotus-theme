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
    wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css?family=Leckerli+One|Raleway&display=swap', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'jquery');
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom-functions.js');
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

    wp_enqueue_style( 'child-lotus-styles', get_stylesheet_directory_uri() . '/style.css?v='.time(), array('child-understrap-styles') );
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_script( 'moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/af.js', true );
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
    $available = get_field('slots_available', $params['id']);

    if (isset($registeredUsers)) {

        $pos = array_search($params['user'], $registeredUsers);
        //return is_int($pos);
        //return (gettype($registeredUsers) === 'array');

        if (gettype($registeredUsers) === 'array' && !is_int($pos)) {
            if (count($registeredUsers) === $available) {


                $resp = array(
                    "success" => false,
                    "action"  => "signup",
                    "msg"     => "Class Full!"
                );
                return $resp;
            } else {
                array_push($registeredUsers, $params['user']);
                update_post_meta($params['id'], 'registered_users', $registeredUsers);

                $resp = array(
                    "success" => true,
                    "data"    => array( "registered" => $registeredUsers, "slots" => $available),
                    "action"  => "signup"
                );
                return $resp;
            }
        } else {


            array_splice($registeredUsers, $pos, 1);
            update_post_meta($params['id'], 'registered_users', $registeredUsers);

            $resp = array(
                "success" => true,
                "data"    => array( "registered" => $registeredUsers, "slots" => $available),
                "action"  => "unregister"
            );
            return $resp;
        }
    } else {
        add_post_meta( $params['id'], 'registered_users', [$params['user']], true);
        $resp = array(
            "success" => true,
            "data"    => array( "registered" => [$params['user']], "slots" => $available),
            "action"  => "register"
        );
        return $resp;
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

function lotus_get_sessions($request) {
    $classes = get_posts(array( 'post_type' => 'classes', 'numberposts' => 999999));
    foreach($classes as $i => $class) {
        $meta = get_fields($class->ID);
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $class->ID ), 'single-post-thumbnail' );

        $classes[$i]->meta          = $meta;
        $classes[$i]->thumbnail     = $thumbnail;
        $classes[$i]->registered_users = get_field('registered_users', $class->ID);
        $classes[$i]->host_profile  = um_user_profile_url($meta->host_name_->ID);
    }
    return $classes;
}

add_action( 'rest_api_init', function () {
  register_rest_route('lotus', 'getSessions', array(
    'methods' => 'GET',
    'callback' => 'lotus_get_sessions',
  ));
});


/* GLOBALS */

$classes = get_posts(array( 'post_type' => 'classes', 'numberposts' => 999999));
foreach($classes as $i => $class) {
    $meta = get_fields($class->ID);
    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $class->ID ), 'single-post-thumbnail' );

    $classes[$i]->meta          = $meta;
    $classes[$i]->thumbnail     = $thumbnail;
    $classes[$i]->registered_users = get_field('registered_users', $class->ID);
    $classes[$i]->host_profile  = um_user_profile_url($meta->host_name_->ID);
}

$currentUser = wp_get_current_user(); 

$users = get_users();
foreach($users as $i => $user) {
    $users[$i]->profile_url = um_user_profile_url($user->ID);
}
