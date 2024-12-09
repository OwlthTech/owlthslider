<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/admin
 * @author     Owlth Tech <nil@owlth.tech>
 */

require_once plugin_dir_path(__FILE__) . 'schema.php';

require_once plugin_dir_path(__FILE__) . 'includes/slider/index.php';
require_once plugin_dir_path(__FILE__) . 'includes/reviews/index.php';

// Metaboxes
require_once plugin_dir_path(__FILE__) . 'includes/class-owlthslider-metaboxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-slider-post-table.php';

class Owlthslider_Admin
{
	private $plugin_name;

	private $version;

	private $plugin_metaboxes;

	private $slider_post_table;
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_os_auto_save_sliders', 'os_save_data_ajax');
		add_action('save_post_os_slider', 'test_os_save_data');

		// Metaboxes - remove and add
		$this->plugin_metaboxes = new Owlthslider_Metaboxes();
		$this->slider_post_table = new Slider_Post_Table();
	}


	public function enqueue_styles_scripts($hook)
	{
		$this->enqueue_page_selection();
		if (!in_array($hook, ['post.php', 'post-new.php'])) {
			return;
		}

		global $post;
		if (isset($post->post_type) && $post->post_type != 'os_slider') {
			return;
		}

		// CSS
		if (file_exists(OWLTHSLIDER_PLUGIN_DIR . 'build/admin/css/owlthslider.min.css')) {
			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/admin/css/owlthslider.min.css', array(), $this->version, 'all');
		} else {
			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'admin/css/owlthslider-admin.css', array(), $this->version, 'all');
		}

		// JS
		wp_enqueue_media();
		wp_enqueue_editor();

		if (file_exists(OWLTHSLIDER_PLUGIN_DIR . 'build/admin/js/owlthslider.min.js')) {
			wp_enqueue_script($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/admin/js/owlthslider.min.js', array('jquery'), $this->version, true);
		} else {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/owlthslider-admin.js', array('jquery'), $this->version, true);
		}
		wp_localize_script($this->plugin_name, 'os_slider_params', array(
			'nonce' => wp_create_nonce('os_save_slider_universal_nonce_action'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'post_id' => $post->ID,
			'slider_schema' => os_get_slides_schema()
		));
	}

	public function enqueue_page_selection()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['page']) || $_GET['page'] !== 'os_slider_type_selection')
			return;
		// CSS
		if (file_exists(OWLTHSLIDER_PLUGIN_DIR . 'build/admin/css/owlthslider.min.css')) {
			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/admin/css/owlthslider.min.css', array(), $this->version, 'all');
		} else {
			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'admin/css/owlthslider-admin.css', array(), $this->version, 'all');
		}
	}

	public function os_register_slider_cpt_and_taxonomy()
	{
		// Register the CPT
		$labels = array(
			'name' => __('Sliders', 'owlthslider'),
			'singular_name' => __('Slider', 'owlthslider'),
			'menu_name' => __('Owlth Sliders', 'owlthslider'),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'supports' => array('title', 'custom-fields'),
			'taxonomies' => array('os_slider_type', 'os_slider_template'),
		);

		register_post_type('os_slider', $args);

		// Register Taxonomies
		$slider_type_args = array(
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'label' => __('Slider Type', 'owlthslider'),
		);

		$slider_template_args = array(
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'label' => __('Slider Template', 'owlthslider'),
		);

		register_taxonomy('os_slider_type', 'os_slider', $slider_type_args);
		register_taxonomy('os_slider_template', 'os_slider', $slider_template_args);
	}


	public function redirect_new_slider_to_type_selection()
	{
		global $pagenow;

		if (
			$pagenow === 'post-new.php' &&
			isset($_GET['post_type']) &&
			$_GET['post_type'] === 'os_slider' &&
			!isset($_GET['os_slider_type'])
		) {
			wp_safe_redirect(admin_url('admin.php?page=os_slider_type_selection'));
			exit;
		}
	}


	public function add_slider_type_selection_page()
	{
		add_submenu_page(
			'', // No parent menu, hidden from navigation
			__('Select Slider Type', 'owlthslider'),
			__('Select Slider Type', 'owlthslider'),
			'edit_posts',
			'os_slider_type_selection',
			array($this, 'os_render_slider_type_selection_page')
		);
	}

	public function os_render_slider_type_selection_page()
	{
		$slider_types = get_terms(array(
			'taxonomy' => 'os_slider_type',
			'hide_empty' => false,
		));

		$slider_templates = get_terms(array(
			'taxonomy' => 'os_slider_template',
			'hide_empty' => false,
		));

		if (empty($slider_types) || is_wp_error($slider_types)) {
			echo '<div class="wrap">';
			echo '<h1>' . __('Select Slider Type', 'owlthslider') . '</h1>';
			echo '<p>' . __('No slider types available. Please create a slider type first.', 'owlthslider') . '</p>';
			echo '</div>';
			return;
		}

		if (empty($slider_templates) || is_wp_error($slider_templates)) {
			echo '<div class="wrap">';
			echo '<h1>' . __('Select Slider Templates', 'owlthslider') . '</h1>';
			echo '<p>' . __('No slider types available. Please create a slider type first.', 'owlthslider') . '</p>';
			echo '</div>';
			return;
		}

		?>
		<div class="wrap" id="slide-selection">
			<h1><?php _e('Select Slider Type', 'owlthslider'); ?></h1>
			<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				<?php wp_nonce_field('os_select_slider_type_action', 'os_slider_type_nonce'); ?>
				<input type="hidden" name="action" value="create_os_slider">
				<div class="slider-types">
					<?php foreach ($slider_types as $type): ?>
						<label class="form-control" for="os_slider_type_<?php echo esc_attr($type->term_id); ?>">
							<input type="radio" id="os_slider_type_<?php echo esc_attr($type->term_id); ?>"
								name="os_slider_type" value="<?php echo esc_attr($type->term_id); ?>" required />
							<span><?php echo esc_html($type->name); ?></span>
						</label>
					<?php endforeach; ?>
				</div>

				<div class="slider-templates">
					<?php foreach ($slider_templates as $template): ?>
						<label class="form-control" for="os_slider_template_<?php echo esc_attr($template->term_id); ?>">
							<input type="radio" id="os_slider_template_<?php echo esc_attr($template->term_id); ?>"
								name="os_slider_template" value="<?php echo esc_attr($template->term_id); ?>" required />
							<span><?php echo esc_html($template->name); ?></span>
						</label>
					<?php endforeach; ?>
				</div>

				<?php submit_button(__('Create Slider', 'owlthslider')); ?>
			</form>
		</div>
		<style>
			.php-error #adminmenuback,
			.php-error #adminmenuwrap {
				margin-top: unset
			}
		</style>
		<?php
	}



	public function handle_os_slider_creation()
	{
		// Verify nonce for security
		if (
			!isset($_POST['os_slider_type_nonce']) ||
			!wp_verify_nonce($_POST['os_slider_type_nonce'], 'os_select_slider_type_action')
		) {
			wp_die(__('Nonce verification failed', 'owlthslider'));
		}

		// Validate and sanitize taxonomy
		$slider_type = intval($_POST['os_slider_type']);
		$term_type = get_term($slider_type, 'os_slider_type');

		if (!$term_type || is_wp_error($term_type)) {
			wp_die(__('Invalid slider type selected', 'owlthslider'));
		}

		// Validate and sanitize taxonomy
		$slider_template = intval($_POST['os_slider_template']);
		$term_template = get_term($slider_template, 'os_slider_template');

		if (!$term_template || is_wp_error($term_template)) {
			wp_die(__('Invalid slider template selected', 'owlthslider'));
		}

		// Create the custom post type post
		$new_slider = array(
			'post_type' => 'os_slider',
			'post_status' => 'auto-draft',
			'post_title' => __('Auto Draft Slider', 'owlthslider'),
		);

		$post_id = wp_insert_post($new_slider);

		if (is_wp_error($post_id)) {
			wp_die(__('Failed to create new slider', 'owlthslider'));
		}

		// Assign taxonomy
		wp_set_post_terms($post_id, array($slider_type), 'os_slider_type', false);
		wp_set_post_terms($post_id, array($slider_template), 'os_slider_template', false);

		// Redirect to the edit screen of the newly created post
		wp_safe_redirect(admin_url("post.php?post={$post_id}&action=edit"));
		exit;
	}
}

