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

	<form action="" id='form-review' method='post' class='form' >
		<fieldset>
			<legend class="form-legend">Movie Review</legend>
			<?php wp_nonce_field(basename( __FILE__ ),'dwwp-review-nonce'); ?>
		    <div class='form-group'>
		    	<label for="movie_name" class="form-control"><?php _e( 'Movie Name', 'dwwp-textdomain' )?></label>
				<input type="text" class="form-input" name="movie_name" id="movie_name" value="" placeholder="Enter Movie Title Here."/>
		    </div>
		    <div class='form-group'>
		    	<label for="movie_rating" class="form-control"><?php _e( 'Movie Name', 'dwwp-textdomain' )?></label>
				<select name="movie_rating" id="movie_rating" class="form-input" form='movie-review'>
		          
		          <option value="Five"><?php _e( 'Five', 'dwwp-textdomain' )?></option>
		          <option value="Four"><?php _e( 'Four', 'dwwp-textdomain' )?></option>
		          <option value="Three"><?php _e( 'Three', 'dwwp-textdomain' )?></option>
		          <option value="Two"><?php _e( 'Two', 'dwwp-textdomain' )?></option>
		          <option value="One"><?php _e( 'One', 'dwwp-textdomain' )?></option>
		          
		      	</select>
		    </div>
			<div class='form-group'>
				<label for="user_review" class="form-control"><?php _e( 'User Review', 'dwwp-textdomain' )?></label>
	      		<textarea name="user_review" class="form-input" id="user_review" rows="8" placeholder="Tell US what You Thought..."></textarea>
			</div>
			<input id="xyq" type="hidden" name="<?php echo apply_filters( 'honeypot_name', 'date-submitted'); ?>" class="date-submitted" value="" />
			
			<input id="form-review-submit" type="submit">
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
	        'supports'            => array(null)
	);
	register_post_type( $singular, $args );
}

add_action( 'init', 'dwwp_review_post_type' );

/**
 * Save User Submitted Data as Draft Review.
 */

function review_save_ajax() {
	//Verify Form has content.
	if ( ! empty( $_POST['submission'] ) ) {
      return;
    }
    // Verify correct nonce
    check_ajax_referer( 'dwwp-review-nonce', 'security' );

    // Programmatically create new draft post.
	$review_post = array(
		'post_title' => sanitize_text_field( $_POST['data']['movie_name'] . '-' . current_time('Y-m-d') ),
		'post_status' => 'draft',
		'post_type' => 'review',

	);

	$the_post_id = wp_insert_post( $review_post, true );

	//Store Custom Field Values.
	$movie_rating = sanitize_text_field( $_POST['data']['movie_rating'] );
	$user_review = sanitize_text_field( $_POST['data']['user_review'] );

	//Process Custom fields into database.
	update_post_meta( $the_post_id, 'movie_rating', $movie_rating );
	update_post_meta( $the_post_id, 'user_review', $user_review );

	wp_send_json_success();
}
add_action( 'wp_ajax_review_save_ajax', 'review_save_ajax' );
add_action( 'wp_ajax_nopriv_review_save_ajax', 'review_save_ajax' );

/**
 * Enqueue Admin Styles and Scripts
 */

function dwwp_admin_review_styles() {

	$screen = get_current_screen();

	if ( is_object($screen) && 'review' == $screen->post_type ) {
		wp_enqueue_style( 'admin-review-styles', plugins_url( '/css/admin-review.css', __FILE__ ) );
	}

}
add_action( 'admin_enqueue_scripts', 'dwwp_admin_review_styles' );

function dwwp_public_review_styles() {

	global $post;

	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wp-user-review' ) ) {
		wp_enqueue_style( 'review-styles', plugins_url( '/css/review.css', __FILE__ ) );
		wp_enqueue_script( 'review-js', plugins_url( '/js/review-ajax.js', __FILE__), array( 'jquery' ), '', true );
		wp_localize_script( 'review-js', 'review_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( 'dwwp-review-nonce' ) ) );
	}

}
add_action( 'wp_enqueue_scripts', 'dwwp_public_review_styles' );

/**
 * Add Custom Columns to Review list post table.
 */

function dwwp_add_review_columns($columns) {

	//Append Columns Array with your custom columns.
	$columns = array_merge( $columns,
		array( 'movie_rating' => __('Movie Rating'),
				'user_review' => __('User Review') ) );

	return $columns;

}
add_filter( 'manage_review_posts_columns', 'dwwp_add_review_columns' );

/**
 * Populate the custom columns in the Review list post table with post meta.
 */

function dwwp_custom_review_column( $column ) {
	
	switch ( $column ) {
		case 'movie_rating':
			echo get_post_meta( get_the_ID(), 'movie_rating', true );
			break;

		case 'user_review':
			echo get_post_meta( get_the_ID(), 'user_review', true );
			break;
	}
}
add_action( 'manage_review_posts_custom_column', 'dwwp_custom_review_column' );

/**
 * Make review custom column user_rating sortable.
 */

function dwwp_user_reivew_sortable( $columns ) {

	$columns['movie_rating'] = 'Movie Rating';

	return $columns;

}
add_filter( 'manage_edit-review_sortable_columns', 'dwwp_user_reivew_sortable' );

 //This doesn't work yet.
function dwwp_review_column_orderby( $query ) {  
    if( ! is_admin() )  
        return;  

    $orderby = $query->get( 'orderby');  

    if( 'event_date' == $orderby ) {  
        $query->set('meta_key','movie_rating');  
        $query->set('orderbt','meta_value');  
    }  
} 
add_action( 'pre_get_posts', 'dwwp_review_column_orderby' ); 

/**
 * Adds a meta box to the post editing screen
 */
function dwwp_review_meta_box() {
    add_meta_box(
      'review_meta_box',
      __( 'User Review', 'dwwp-textdomain' ),
      'review_meta_callback',
      'review'
    );
}
add_action( 'add_meta_boxes', 'dwwp_review_meta_box' );

// Callback function for review post type.
function review_meta_callback( $post ) {

    $review_stored_meta = get_post_meta( $post->ID );
    ?>
		<div>
			<div class='review-title'>
				<h2><?php the_title() ?></h2>
			</div>
			<div class='review-content'>
    			<h2><?php echo $review_stored_meta['movie_rating'][0]; ?></h2>
    			<p><?php echo $review_stored_meta['user_review'][0]; ?></p>
			</div>
		</div>
	   
<?php
}
