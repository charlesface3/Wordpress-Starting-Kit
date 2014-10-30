<?php
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

/**
 * User Functions
 */
function get_title () {
	global $post, $wp_query;
	$site_title = get_bloginfo('name');
	$title = '';

	if (is_front_page() || is_home()) {
		$title = $site_title;
	} else if (is_page() || is_single()) {
		$title = wp_title( '|', false, 'right' ) . $site_title; 
	} else if (is_404()) { 
		$title = $site_title . ' | ' . __('Page not found!');
	} elseif (is_category()) {
		$cat = get_category( get_query_var('cat'), false );
		$title = __('Category: ') . $cat->name . ' | ' . $site_title;
	} elseif (is_search()) { 
		$search_results = $wp_query->found_posts;
		$search_query = get_search_query();
		if ($search_results > 0) {
			$title = __('Results for: ') . $search_query . ' | ' . $site_title;
		} else {
			$title = __('No results for: ') . $search_query . ' | ' . $site_title;
		}
	} else {
		$title = wp_title( '|', false, 'right' ) . $site_title;
	}

	return $title;
}