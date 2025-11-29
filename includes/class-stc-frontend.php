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
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'stc-style', STC_PLUGIN_URL . 'assets/css/stc-style.css', array(), STC_VERSION, 'all' );
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
			<form action="" method="post">
				<?php wp_nonce_field( 'stc_submit_testimonial', 'stc_nonce' ); ?>
				
				<p>
					<label for="stc_name"><?php _e( 'Your Name', 'simple-testimonials-collector' ); ?> *</label>
					<input type="text" name="stc_name" id="stc_name" required>
				</p>

				<p>
					<label for="stc_email"><?php _e( 'Your Email', 'simple-testimonials-collector' ); ?> *</label>
					<input type="email" name="stc_email" id="stc_email" required>
				</p>

				<?php if ( $rating_enabled ) : ?>
				<p>
					<label for="stc_rating"><?php _e( 'Rating', 'simple-testimonials-collector' ); ?></label>
					<select name="stc_rating" id="stc_rating">
						<option value="5">5 - <?php _e( 'Excellent', 'simple-testimonials-collector' ); ?></option>
						<option value="4">4 - <?php _e( 'Very Good', 'simple-testimonials-collector' ); ?></option>
						<option value="3">3 - <?php _e( 'Good', 'simple-testimonials-collector' ); ?></option>
						<option value="2">2 - <?php _e( 'Fair', 'simple-testimonials-collector' ); ?></option>
						<option value="1">1 - <?php _e( 'Poor', 'simple-testimonials-collector' ); ?></option>
					</select>
				</p>
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
			echo '<div class="stc-testimonials-list">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$rating = get_post_meta( get_the_ID(), '_stc_rating', true );
				?>
				<div class="stc-testimonial-item">
					<div class="stc-testimonial-content">
						<?php the_content(); ?>
					</div>
					<div class="stc-testimonial-author">
						<strong><?php the_title(); ?></strong>
						<?php if ( $rating ) : ?>
							<span class="stc-rating"><?php echo str_repeat( '&#9733;', intval( $rating ) ); ?></span>
						<?php endif; ?>
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
