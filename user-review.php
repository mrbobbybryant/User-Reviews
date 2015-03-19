<?php
/**
 * Plugin Name: User Reviews
 * Plugin URI: http://hatrackmedia.com
 * Description: This plugin provides a simple form, which lets website visitors submit reviews.
 * Author: Bobby Bryant
 * Author URI: http://hatrackmedia.com
 * Version: 0.0.1
 * License: GPLv2
 */

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Jobs Shortcode
 */

function dwwp_review_form_shortcode ( $atts, $content = null ) {

	?>

	<form action="" id='movie-review' method='post' class='form' >
		<fieldset>
			<legend>Movie Review</legend>
			<?php wp_nonce_field(basename( __FILE__ ),'dwwp-review-nonce'); ?>
		    
		    <label for="movie_name" class=""><?php _e( 'Movie Name', 'dwwp-textdomain' )?></label>
			<input type="text" class="required" name="movie_name" id="movie_name" value="" />

			<select name="movie-rating" id="movie-rating" form='movie-review'>
	          
	          <option value="Five"><?php _e( 'Five', 'dwwp-textdomain' )?></option>
	          <option value="Four"><?php _e( 'Four', 'dwwp-textdomain' )?></option>
	          <option value="Three"><?php _e( 'Three', 'dwwp-textdomain' )?></option>
	          <option value="Two"><?php _e( 'Two', 'dwwp-textdomain' )?></option>
	          <option value="One"><?php _e( 'One', 'dwwp-textdomain' )?></option>
	          
	      	</select>
			
			<label for="user-review" class=""><?php _e( 'User Review', 'dwwp-textdomain' )?></label>
	      	<textarea name="user-review" id="user-review" rows="8"></textarea>

	      	<input type="submit">
		</fieldset>
	</form>

	<?php

}

add_shortcode ( 'wp-user-review', 'dwwp_review_form_shortcode');

/**
 * Create Review Custom Post Type
 */

function dwwp_review_post_type() {
	
	// Define CPT Variables. Changing these will alter the post type's name everywhere.
	$singular = 'Review';
	$plural = 'Reviews';

	$labels = array(
		'name' 			=> $plural,
		'singular_name' 	=> $singular,
		'add_new' 		=> 'Add New',
		'add_new_item'  	=> 'Add New ' . $singular,
		'edit'		        => 'Edit',
		'edit_item'	        => 'Edit ' . $singular,
		'new_item'	        => 'New ' . $singular,
		'view' 			=> 'View ' . $singular,
		'view_item' 		=> 'View ' . $singular,
		'search_term'   	=> 'Search ' . $plural,
		'parent' 		=> 'Parent ' . $singular,
		'not_found' 		=> 'No ' . $plural .' found',
		'not_found_in_trash' 	=> 'No ' . $plural .' in Trash'
		);

	$args = array(
		'labels'              => $labels,
	        'public'              => true,
	        'publicly_queryable'  => true,
	        'exclude_from_search' => false,
	        'show_in_nav_menus'   => true,
	        'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 10,
	        'menu_icon'           => 'dashicons-star-half',
	        'can_export'          => true,
	        'delete_with_user'    => false,
	        'hierarchical'        => false,
	        'has_archive'         => true,
	        'query_var'           => true,
	        'capability_type'     => 'post',
	        'map_meta_cap'        => true,
	        // 'capabilities' => array(),
	        'rewrite'             => array( 
	        	'slug' => $singular,
	        	'with_front' => true,
	        	'pages' => true,
	        	'feeds' => true,

	        ),
	        'supports'            => array( 
	        	'title',
	        )
	);
	register_post_type( $singular, $args );
}

add_action( 'init', 'dwwp_review_post_type' );

/**
 * Save User Submitted Data as Draft Review.
 */

function dwwp_process_review_post() {
	//Verify Form has content.
	if ( ! isset( $_POST['dwwp-review-nonce'] ) ) {
      return;
    }
    // Verify correct nonce
    if ( ! wp_verify_nonce( $_POST['dwwp-review-nonce'], basename( __FILE__ ) ) ) {
    	return;
    }

    // Programmatically create new draft post.
	$review_post = array(
		'post_title' => sanitize_text_field( $_POST['movie_name'] . '-' . current_time('Y-m-d') ),
		'post_status' => 'draft',
		'post_type' => 'review',

	);

	$the_post_id = wp_insert_post( $review_post, true );

	//Store Custom Field Values.
	$movie_rating = sanitize_text_field( $_POST['movie-rating'] );
	$user_review = sanitize_text_field( $_POST['user-review'] ); 

	//Process Custom fields into database.
	update_post_meta( $the_post_id, 'movie-rating', $movie_rating );
	update_post_meta( $the_post_id, 'user-review', $user_review );
}
add_action( 'init', 'dwwp_process_review_post' );

/**
 * Enqueue Review Styles and Scripts
 */

function dwwp_review_styles() {

	wp_enqueue_style( 'review-styles', plugins_url( '/css/review.css', __FILE__ ) );

}
add_action( 'wp_enqueue_scripts', 'dwwp_review_styles' );



