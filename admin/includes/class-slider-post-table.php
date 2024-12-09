<?php

class Slider_Post_Table {

      function __construct() {
		add_filter('manage_os_slider_posts_columns', array($this, 'os_add_shortcode_column'));
		add_action('manage_os_slider_posts_custom_column', array($this, 'os_shortcode_column_content'), 10, 2);
      }

      /**
	 * Admin: Adds column in post table
	 * @param mixed $columns
	 * @return mixed
	 */
	public function os_add_shortcode_column($columns)
	{
		$columns['os_slider_shortcode'] = __('Shortcode', 'owlthslider');
		return $columns;
	}


	/**
	 * Admin: Adds content in column of post table
	 * @param mixed $column
	 * @param mixed $post_id
	 * @return void
	 */
	public function os_shortcode_column_content($column, $post_id)
	{
		$slider_types = wp_get_post_terms($post_id, 'os_slider_type', array('fields' => 'slugs'));
		$slider_type = isset($slider_types[0]) ? $slider_types[0] : 'default';

		$slider_templates = wp_get_post_terms($post_id, 'os_slider_template', array('fields' => 'slugs'));
		$slider_template = isset($slider_templates[0]) ? $slider_templates[0] : 'default';

		if ('os_slider_shortcode' === $column) {
			$shortcode = '[os_slider id="' . $post_id . '" type="' . $slider_type . '" template="'. $slider_template .'"]';
			echo '<input type="text" style="width:100%" readonly="readonly" value="' . esc_attr($shortcode) . '" class="os-slider-shortcode" onclick="this.select();document.execCommand(\'copy\');alert(\'Shortcode copied to clipboard\');" />';
		}
	}
}