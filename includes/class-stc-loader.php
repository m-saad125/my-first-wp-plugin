<?php

/**
 * Register all actions and filters for the plugin.
 */
class STC_Loader {

	/**
	 * The array of actions registered with WordPress.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Register the filters and actions with WordPress.
	 */
	public function run() {
		require_once STC_PLUGIN_DIR . 'includes/class-stc-post-types.php';
		$post_types = new STC_Post_Types();

		require_once STC_PLUGIN_DIR . 'includes/class-stc-admin.php';
		$admin = new STC_Admin();

		require_once STC_PLUGIN_DIR . 'includes/class-stc-frontend.php';
		$frontend = new STC_Frontend();
	}
}
