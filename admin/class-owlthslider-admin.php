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


require_once plugin_dir_path(__FILE__) . 'functions/slider/index.php';
require_once plugin_dir_path(__FILE__) . 'functions/reviews/index.php';

// Metaboxes
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/includes/class-owlthslider-metaboxes.php';

class Owlthslider_Admin
{
	private $plugin_name;

	private $version;

	private $plugin_metaboxes;
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_os_auto_save_sliders', 'os_save_data_ajax');
		add_action('save_post_os_slider', 'test_os_save_data');

		// add_action('wp_ajax_os_refresh_reviews', array($this, 'os_ajax_refresh_reviews'));

		add_filter('manage_os_slider_posts_columns', array($this, 'os_add_shortcode_column'));
		add_action('manage_os_slider_posts_custom_column', array($this, 'os_shortcode_column_content'), 10, 2);


		// Metaboxes - remove and add
		$this->plugin_metaboxes = new Class_Owlthslider_Metaboxes();

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


	/**
	 * Register Custom Post Type for Sliders.
	 */
	function os_register_slider_cpt_and_taxonomy()
	{
		$cpt_slug = 'os_slider';
		$cpt_taxonomies = ['os_slider_type'];
		os_register_cpt($cpt_slug, $cpt_taxonomies);
	}





	/**
	 * Admin: Adds column in post table
	 * @param mixed $columns
	 * @return mixed
	 */
	function os_add_shortcode_column($columns)
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
	function os_shortcode_column_content($column, $post_id)
	{
		if ('os_slider_shortcode' === $column) {
			$shortcode = '[os_slider id="' . $post_id . '"]';
			echo '<input type="text" readonly="readonly" value="' . esc_attr($shortcode) . '" class="os-slider-shortcode" onclick="this.select();document.execCommand(\'copy\');alert(\'Shortcode copied to clipboard\');" />';
		}
	}


	/**
	 * Handle AJAX request to refresh reviews.
	 */
	public function os_ajax_refresh_reviews()
	{
		// Verify the universal nonce
		if (
			!isset($_POST['os_slider_universal_nonce']) ||
			!wp_verify_nonce($_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action')
		) {
			wp_send_json_error(__('Unauthorized request.', 'owlthslider'), 403);
		}

		// Get and sanitize post ID
		$post_id = intval($_POST['post_id']);
		if (!$post_id || !current_user_can('edit_post', $post_id)) {
			wp_send_json_error(__('Unauthorized user or invalid post ID.', 'owlthslider'), 403);
		}

		// Get and sanitize Google Place ID
		$google_place_id = isset($_POST['google_place_id']) ? sanitize_text_field($_POST['google_place_id']) : '';
		if (empty($google_place_id)) {
			wp_send_json_error(__('Google Place ID is required.', 'owlthslider'), 400);
		}

		// Delete existing transient to force re-fetch
		$transient_key = 'owlth_google_reviews_' . md5($google_place_id);
		// delete_transient($transient_key);

		// Fetch fresh reviews
		$reviews = os_fetch_google_reviews($google_place_id, true);

		if (empty($reviews)) {
			wp_send_json_error(__('No reviews found or failed to fetch reviews.', 'owlthslider'), 400);
		}

		// Render the updated reviews table
		ob_start();
		$this->plugin_metaboxes->os_render_reviews_table($post_id, $google_place_id, true);
		$table_html = ob_get_clean();

		wp_send_json_success($table_html);
	}

	
	
	public function redirect_new_slider_to_type_selection() {
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
	

	public function add_slider_type_selection_page() {
		add_submenu_page(
			'', // No parent menu, hidden from navigation
			__('Select Slider Type', 'owlthslider'),
			__('Select Slider Type', 'owlthslider'),
			'edit_posts',
			'os_slider_type_selection',
			array($this, 'os_render_slider_type_selection_page')
		);
	}
	
	public function os_render_slider_type_selection_page() {
		$slider_types = get_terms(array(
			'taxonomy' => 'os_slider_type',
			'hide_empty' => false,
		));
	
		if (empty($slider_types) || is_wp_error($slider_types)) {
			echo '<div class="wrap">';
			echo '<h1>' . __('Select Slider Type', 'owlthslider') . '</h1>';
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
							<input type="radio" id="os_slider_type_<?php echo esc_attr($type->term_id); ?>" name="os_slider_type"
								value="<?php echo esc_attr($type->term_id); ?>" required />
							<span><?php echo esc_html($type->name); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php submit_button(__('Create Slider', 'owlthslider')); ?>
			</form>
		</div>
		<style>
			.php-error #adminmenuback, .php-error #adminmenuwrap {margin-top: unset}
		</style>
		<?php
	}
	
	
	
	public function handle_os_slider_creation() {
		// Verify nonce for security
		if (
			!isset($_POST['os_slider_type_nonce']) ||
			!wp_verify_nonce($_POST['os_slider_type_nonce'], 'os_select_slider_type_action')
		) {
			wp_die(__('Nonce verification failed', 'owlthslider'));
		}
	
		// Validate and sanitize taxonomy
		$slider_type = intval($_POST['os_slider_type']);
		$term = get_term($slider_type, 'os_slider_type');
	
		if (!$term || is_wp_error($term)) {
			wp_die(__('Invalid slider type selected', 'owlthslider'));
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
	
		// Redirect to the edit screen of the newly created post
		wp_safe_redirect(admin_url("post.php?post={$post_id}&action=edit"));
		exit;
	}
}

