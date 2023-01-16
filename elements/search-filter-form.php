<?php 
// element-test.php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Stu_Element_Search_Filter_Form extends \Bricks\Element {
	// Element properties
	public $category     = 'Search & Filter'; // Use predefined element category 'general'
	public $name         = 'stu-search-filter-form'; // Make sure to prefix your elements
	public $icon         = 'ti-filter'; // Themify icon font class
	public $scripts      = 'stuSearchFilterBricks';

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Search Form', 'bricks' );
	}

	public function set_control_groups() {}

	// Set builder controls
	public function set_controls() {
		$this->controls['searchAndFilterFormId'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Select a Search Form to Display', 'bricks' ),
			'type'        => 'select',
			'options'     => Stu_Search_Filter_Bricks::get_search_form_options(),
			'placeholder' => esc_html__( 'None', 'bricks' ),
		];
		$this->controls['searchAndFilterFormNote'] = [
			'tab' => 'content',
			'content' => esc_html__( 'You must select "Custom" for the "Display Results Method" setting in your Search Form otherwise the filter won\'t work.' , 'bricks' ),
			'type' => 'info',
		];
	}
	
	// Methods: Frontend-specific
	public function enqueue_scripts() {
		wp_enqueue_script( 'stu-search-filter-bricks' );
	}

	// Render element HTML
	public function render() {

		echo "<div {$this->render_attributes( '_root' )}>";
		echo do_shortcode('[searchandfilter id="' . $this->settings['searchAndFilterFormId'] . '"]');
		echo "</div>";
	}
}
