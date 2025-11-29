<?php

/**
 * Handle Frontend Shortcodes and Logic.
 */
class STC_Frontend {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_shortcode( 'submit_testimonial', array( $this, 'render_submission_form' ) );
		add_shortcode( 'testimonials', array( $this, 'render_testimonials_list' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'init', array( $this, 'handle_form_submission' ) );
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'stc-style', STC_PLUGIN_URL . 'assets/css/stc-style.css', array(), STC_VERSION, 'all' );
		wp_enqueue_script( 'stc-script', STC_PLUGIN_URL . 'assets/js/stc-script.js', array(), STC_VERSION, true );
	}

	/**
	 * Render the submission form shortcode.
	 */
	public function render_submission_form() {
		ob_start();
		
		// Check for success message
		if ( isset( $_GET['stc_submitted'] ) && '1' === $_GET['stc_submitted'] ) {
			echo '<div class="stc-success-message">' . esc_html__( 'Thank you! Your testimonial has been submitted for review.', 'simple-testimonials-collector' ) . '</div>';
		}

		$rating_enabled = get_option( 'stc_enable_rating' );
		?>
		<div class="stc-submission-form">
			<form action="" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'stc_submit_testimonial', 'stc_nonce' ); ?>
				
				<p>
					<label for="stc_name"><?php _e( 'Your Name', 'simple-testimonials-collector' ); ?> *</label>
					<input type="text" name="stc_name" id="stc_name" required>
				</p>

				<p>
					<label for="stc_email"><?php _e( 'Your Email', 'simple-testimonials-collector' ); ?> *</label>
					<input type="email" name="stc_email" id="stc_email" required>
				</p>
				
				<p>
					<label for="stc_image"><?php _e( 'Your Photo (Optional)', 'simple-testimonials-collector' ); ?></label>
					<input type="file" name="stc_image" id="stc_image" accept="image/*">
				</p>

				<?php if ( $rating_enabled ) : ?>
				<div class="stc-rating-input">
					<label><?php _e( 'Rating', 'simple-testimonials-collector' ); ?></label>
					<div class="stc-stars">
						<input type="radio" name="stc_rating" id="rate-5" value="5">
						<label for="rate-5" title="5 stars">★</label>
						<input type="radio" name="stc_rating" id="rate-4" value="4">
						<label for="rate-4" title="4 stars">★</label>
						<input type="radio" name="stc_rating" id="rate-3" value="3">
						<label for="rate-3" title="3 stars">★</label>
						<input type="radio" name="stc_rating" id="rate-2" value="2">
						<label for="rate-2" title="2 stars">★</label>
						<input type="radio" name="stc_rating" id="rate-1" value="1">
						<label for="rate-1" title="1 star">★</label>
					</div>
				</div>
				<?php endif; ?>

				<p>
					<label for="stc_testimonial"><?php _e( 'Your Testimonial', 'simple-testimonials-collector' ); ?> *</label>
					<textarea name="stc_testimonial" id="stc_testimonial" rows="5" required></textarea>
				</p>

				<p>
					<input type="submit" name="stc_submit" value="<?php esc_attr_e( 'Submit Testimonial', 'simple-testimonials-collector' ); ?>">
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle form submission.
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST['stc_submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['stc_nonce'] ) || ! wp_verify_nonce( $_POST['stc_nonce'], 'stc_submit_testimonial' ) ) {
			return;
		}

		$name        = sanitize_text_field( $_POST['stc_name'] );
		$email       = sanitize_email( $_POST['stc_email'] );
		$testimonial = sanitize_textarea_field( $_POST['stc_testimonial'] );
		$rating      = isset( $_POST['stc_rating'] ) ? intval( $_POST['stc_rating'] ) : 0;

		if ( empty( $name ) || empty( $email ) || empty( $testimonial ) ) {
			return; // Basic validation
		}

		$post_data = array(
			'post_title'   => $name,
			'post_content' => $testimonial,
			'post_status'  => 'pending',
			'post_type'    => 'testimonial',
		);

		$post_id = wp_insert_post( $post_data );

		if ( $post_id ) {
			update_post_meta( $post_id, '_stc_email', $email );
			if ( $rating > 0 ) {
				update_post_meta( $post_id, '_stc_rating', $rating );
			}

			// Handle Image Upload
			if ( ! empty( $_FILES['stc_image']['name'] ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );

				$attachment_id = media_handle_upload( 'stc_image', $post_id );

				if ( ! is_wp_error( $attachment_id ) ) {
					set_post_thumbnail( $post_id, $attachment_id );
				}
			}

			// Redirect to avoid resubmission and show success message
			wp_redirect( add_query_arg( 'stc_submitted', '1' ) );
			exit;
		}
	}

	/**
	 * Render the testimonials list shortcode.
	 */
	public function render_testimonials_list( $atts ) {
		$atts = shortcode_atts( array(
			'limit' => 10,
		), $atts );

		$args = array(
			'post_type'      => 'testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['limit'] ),
		);

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="stc-testimonials-carousel">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$rating = get_post_meta( get_the_ID(), '_stc_rating', true );
				$email = get_post_meta( get_the_ID(), '_stc_email', true );
				?>
				<div class="stc-testimonial-item">
					<div class="stc-testimonial-image">
						<?php 
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'thumbnail' );
						} else {
							echo get_avatar( $email, 100 );
						}
						?>
					</div>

					<?php if ( $rating ) : ?>
						<div class="stc-rating">
							<?php echo str_repeat( '&#9733;', intval( $rating ) ); ?>
						</div>
					<?php endif; ?>

					<div class="stc-testimonial-content">
						&ldquo;<?php the_content(); ?>&rdquo;
					</div>
					
					<div class="stc-testimonial-author">
						<?php the_title(); ?>
					</div>
				</div>
				<?php
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No testimonials found.', 'simple-testimonials-collector' ) . '</p>';
		}
		return ob_get_clean();
	}
}
