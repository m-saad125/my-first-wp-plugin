<?php

/**
 * Register Custom Post Types and Meta Boxes.
 */
class STC_Post_Types {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_testimonial_cpt' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
	}

	/**
	 * Register the 'testimonial' custom post type.
	 */
	public function register_testimonial_cpt() {
		$labels = array(
			'name'                  => _x( 'Testimonials', 'Post Type General Name', 'simple-testimonials-collector' ),
			'singular_name'         => _x( 'Testimonial', 'Post Type Singular Name', 'simple-testimonials-collector' ),
			'menu_name'             => __( 'Testimonials', 'simple-testimonials-collector' ),
			'name_admin_bar'        => __( 'Testimonial', 'simple-testimonials-collector' ),
			'archives'              => __( 'Testimonial Archives', 'simple-testimonials-collector' ),
			'attributes'            => __( 'Testimonial Attributes', 'simple-testimonials-collector' ),
			'parent_item_colon'     => __( 'Parent Testimonial:', 'simple-testimonials-collector' ),
			'all_items'             => __( 'All Testimonials', 'simple-testimonials-collector' ),
			'add_new_item'          => __( 'Add New Testimonial', 'simple-testimonials-collector' ),
			'add_new'               => __( 'Add New', 'simple-testimonials-collector' ),
			'new_item'              => __( 'New Testimonial', 'simple-testimonials-collector' ),
			'edit_item'             => __( 'Edit Testimonial', 'simple-testimonials-collector' ),
			'update_item'           => __( 'Update Testimonial', 'simple-testimonials-collector' ),
			'view_item'             => __( 'View Testimonial', 'simple-testimonials-collector' ),
			'view_items'            => __( 'View Testimonials', 'simple-testimonials-collector' ),
			'search_items'          => __( 'Search Testimonial', 'simple-testimonials-collector' ),
			'not_found'             => __( 'Not found', 'simple-testimonials-collector' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'simple-testimonials-collector' ),
			'featured_image'        => __( 'Featured Image', 'simple-testimonials-collector' ),
			'set_featured_image'    => __( 'Set featured image', 'simple-testimonials-collector' ),
			'remove_featured_image' => __( 'Remove featured image', 'simple-testimonials-collector' ),
			'use_featured_image'    => __( 'Use as featured image', 'simple-testimonials-collector' ),
			'insert_into_item'      => __( 'Insert into testimonial', 'simple-testimonials-collector' ),
			'uploaded_to_this_item' => __( 'Uploaded to this testimonial', 'simple-testimonials-collector' ),
			'items_list'            => __( 'Testimonials list', 'simple-testimonials-collector' ),
			'items_list_navigation' => __( 'Testimonials list navigation', 'simple-testimonials-collector' ),
			'filter_items_list'     => __( 'Filter testimonials list', 'simple-testimonials-collector' ),
		);
		$args = array(
			'label'                 => __( 'Testimonial', 'simple-testimonials-collector' ),
			'description'           => __( 'Customer Testimonials', 'simple-testimonials-collector' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-format-quote',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false, // We only want to display them via shortcode
			'capability_type'       => 'post',
		);
		register_post_type( 'testimonial', $args );
	}

	/**
	 * Add meta boxes for the testimonial CPT.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'stc_testimonial_details',
			__( 'Testimonial Details', 'simple-testimonials-collector' ),
			array( $this, 'render_meta_box' ),
			'testimonial',
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'stc_save_testimonial_details', 'stc_testimonial_nonce' );

		$rating = get_post_meta( $post->ID, '_stc_rating', true );
		$email  = get_post_meta( $post->ID, '_stc_email', true );
		?>
		<p>
			<label for="stc_rating"><?php _e( 'Rating (1-5)', 'simple-testimonials-collector' ); ?></label>
			<select name="stc_rating" id="stc_rating" class="widefat">
				<option value=""><?php _e( 'Select Rating', 'simple-testimonials-collector' ); ?></option>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( $i ); ?></option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label for="stc_email"><?php _e( 'Submitter Email (Private)', 'simple-testimonials-collector' ); ?></label>
			<input type="email" name="stc_email" id="stc_email" value="<?php echo esc_attr( $email ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Save the meta box data.
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_meta_boxes( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['stc_testimonial_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['stc_testimonial_nonce'], 'stc_save_testimonial_details' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sanitize and save the data.
		if ( isset( $_POST['stc_rating'] ) ) {
			$rating = intval( $_POST['stc_rating'] );
			if ( $rating >= 1 && $rating <= 5 ) {
				update_post_meta( $post_id, '_stc_rating', $rating );
			} else {
				delete_post_meta( $post_id, '_stc_rating' );
			}
		}

		if ( isset( $_POST['stc_email'] ) ) {
			$email = sanitize_email( $_POST['stc_email'] );
			update_post_meta( $post_id, '_stc_email', $email );
		}
	}
}
