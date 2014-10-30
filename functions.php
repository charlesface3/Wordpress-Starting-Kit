<?php
/**
 * Include & Init the loader
 */
include_once "Core/Loading/Loader.php";
\Core\Loader::initialise( get_template_directory() . '/Core/Loading/autoload.json' );

/**
 * Enqueued Scripts & Styles
 */
function my_enqueued_scripts () {
	wp_deregister_script( 'jquery' ); // Remove Jquery from head
}

function my_enqueued_styles () {
	wp_enqueue_style( 'main-style', get_template_directory_uri() . '/style.css');
}

add_action( 'wp_enqueue_scripts', 'my_enqueued_scripts' );
add_action( 'wp_enqueue_scripts', 'my_enqueued_styles' );