<?php

/**
 * Handle Admin Menu and Settings.
 */
class STC_Admin {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard.
	 */
	public function add_plugin_admin_menu() {
		// Add a submenu page under the 'edit.php?post_type=testimonial' menu.
		add_submenu_page(
			'edit.php?post_type=testimonial',
			__( 'Testimonial Settings', 'simple-testimonials-collector' ),
			__( 'Settings', 'simple-testimonials-collector' ),
			'manage_options',
			'stc-settings',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Register the settings.
	 */
	public function register_settings() {
		register_setting( 'stc_settings_group', 'stc_enable_rating' );

		add_settings_section(
			'stc_general_section',
			__( 'General Settings', 'simple-testimonials-collector' ),
			null,
			'stc-settings'
		);

		add_settings_field(
			'stc_enable_rating',
			__( 'Enable Ratings', 'simple-testimonials-collector' ),
			array( $this, 'render_enable_rating_field' ),
			'stc-settings',
			'stc_general_section'
		);
	}

	/**
	 * Render the 'Enable Ratings' checkbox.
	 */
	public function render_enable_rating_field() {
		$option = get_option( 'stc_enable_rating' );
		?>
		<input type="checkbox" name="stc_enable_rating" value="1" <?php checked( 1, $option, true ); ?> />
		<p class="description"><?php _e( 'Check this box to allow users to submit a rating (1-5).', 'simple-testimonials-collector' ); ?></p>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'stc_settings_group' );
				do_settings_sections( 'stc-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
