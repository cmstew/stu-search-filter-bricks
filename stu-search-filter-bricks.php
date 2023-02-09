<?php
/*
 * Plugin Name: Search & Filter - Bricks Extension
 * Description: Makes it easier to use Search & Filter with Bricks
 * Author: Curtis Stewart
 * Version: 1.1
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', array( 'Stu_Search_Filter_Bricks', 'get_instance' ) );

Class Stu_Search_Filter_Bricks {

	protected static $instance = null;

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		
		add_action( 'wp_enqueue_scripts', function() {
			// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
			if ( ! bricks_is_builder_main() ) {	
				wp_register_script( 'stu-search-filter-bricks', plugin_dir_url( __FILE__ ) . 'assets/js/stu-search-filter-bricks.js', array('jquery'), filemtime(plugin_dir_path( __FILE__ ) . 'assets/js/stu-search-filter-bricks.js'));
			}
		} );

		// List of elements to add controls to
		$elements = [
			'container',
			'div',
			'block',
			'accordion',
			'slider',
			'posts',
			'woocommerce-products'
		];

		// Loop through elements and add controls
		foreach($elements as $element) {
			add_filter( 'bricks/elements/' . $element . '/controls', function( $controls ) {
				return self::get_bricks_controls($controls);
			} );
		}

		// Add Search & Filter note to Pagination Element
		add_filter( 'bricks/elements/pagination/controls', function( $controls ) {
			$new_controls = [ 'searchAndFilterFormNote' => [
				'tab' => 'content',
				'content' => esc_html__( 'Search & Filter Note: If you want to use this with Search & Filter, you must put it inside the same container that you put your query loop.' , 'bricks' ),
				'type' => 'info',
			]];
			// Combining this way ensures the note show up first in Bricks
			return $new_controls + $controls;
		} );

		// Inject Search & Filter Id into query loop
		add_filter( 'bricks/posts/query_vars', function( $query_vars, $settings, $element_id ) {
			if (isset( $settings['searchAndFilterFormId'] )) {
				$query_vars['search_filter_id'] = $settings['searchAndFilterFormId'];
			}
			return $query_vars;
		}, 10, 3 );

		// Setup Search & Filter Form Element
		add_action( 'init', function() {
			$file = '/elements/search-filter-form.php';
			$element_name = 'stu-search-filter-form';
			$class_name = 'Stu_Element_Search_Filter_Form';
			\Bricks\Elements::register_element( __DIR__ . $file, $element_name, $class_name);
		}, 11 );

	}

	// This function does the brunt of the work of setting up
	// the controls for each supported Bricks Element
	private static function get_bricks_controls($controls) {
		$ordered_controls = [];

		// Loop over existing controls so we can inject our controls exactly where we want them
		foreach($controls as $k => $v) {
			$ordered_controls[$k] = $v;

			// Place after the query control with the
			// exception of the Product element which we
			// place after the out of stock control
			if ($k === 'query' ||
				$k === 'hideOutOfStock') {

				$ordered_controls['searchAndFilterFormId'] = [
					'tab'         => 'content',
					'label'       => esc_html__( 'Link with a Search and Filter Form', 'bricks' ),
					'type'        => 'select',
					'options'     => self::get_search_form_options(),
					'placeholder' => esc_html__( 'None', 'bricks' ),
				];
				$ordered_controls['searchAndFilterFormNote'] = [
					'tab' => 'content',
					'content' => esc_html__( 'Search & Filter Note: If you have Search & Filter Pro and want ajax to work, you must put your query loop inside a container and then put the container ID in the Search Form options.' , 'bricks' ),
					'type' => 'info',
				];
			}
		}

		// Only make the hasLoop control required if it exists
		if ( array_key_exists('hasLoop', $controls) ) {
			$ordered_controls['searchAndFilterFormId']['required'] = [
				['hasLoop', '!=', ''],
			];
			$ordered_controls['searchAndFilterFormNote']['required'] = [
				['hasLoop', '!=', ''],
			];
		}

		// Only add the controls to the query group if
		// we're in the product element
		if ( array_key_exists('hideOutOfStock',$controls) ) {
			$ordered_controls['searchAndFilterFormId']['group'] = 'query';
			$ordered_controls['searchAndFilterFormNote']['group'] = 'query';
		};

		return $ordered_controls;
	}

	// Method to get an array of Search & Filter Forms
	public static function get_search_form_options() {
		$args = [
			'post_type' => 'search-filter-widget',
			'post_status' => 'publish',
			'posts_per_page' => -1
		];

		$custom_posts = new WP_Query($args);
		$search_form_options = [];
		
		while ($custom_posts->have_posts()) {
			$custom_posts->the_post();
			$search_form_options[get_the_ID()] = html_entity_decode(get_the_title());
		}

		wp_reset_postdata();

		return $search_form_options;
	}
}
