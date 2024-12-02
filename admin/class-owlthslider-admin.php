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

require_once plugin_dir_path(__FILE__) . 'functions/index.php';

class Owlthslider_Admin
{
	private $plugin_name;

	private $version;


	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_os_auto_save_sliders', array($this, 'os_auto_save_sliders'));
		add_action('save_post_os_slider', array($this, 'os_slider_save_meta_fields'));
		// add_action('admin_init', array($this, 'os_redirect_new_slider_to_type_selection'));
		// add_action('admin_menu', array($this, 'os_add_slider_type_selection_page'));
		add_action('wp_ajax_os_refresh_reviews', array($this, 'os_ajax_refresh_reviews'));

		add_filter('manage_os_slider_posts_columns', array($this, 'os_add_shortcode_column'));
		add_action('manage_os_slider_posts_custom_column', array($this, 'os_shortcode_column_content'), 10, 2);

	}


	public function enqueue_styles($hook)
	{
		if (!in_array($hook, ['post.php', 'post-new.php'])) {
			return;
		}

		global $post;
		if (isset($post->post_type) && $post->post_type != 'os_slider') {
			return;
		}

		// CSS
		if (is_dir(plugin_dir_path(__FILE__) . 'build')) {
			wp_enqueue_style('owlthslider-admin', plugin_dir_url(__FILE__) . 'build/admin/css/owlthslider.min.css');
		}
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/owlthslider-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts($hook)
	{
		if (!in_array($hook, ['post.php', 'post-new.php'])) {
			return;
		}

		global $post;
		if (isset($post->post_type) && $post->post_type != 'os_slider') {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_editor();

		if (is_dir(plugin_dir_path(__FILE__) . 'build')) {
			wp_enqueue_script('owlth-slider-admin', plugin_dir_url(__FILE__) . 'build/admin/js/owlthslider.min.js', array('jquery'), '1.0', true);
		} else {
			wp_enqueue_script('owlth-slider-admin', plugin_dir_url(__FILE__) . 'js/owlthslider-admin.js', array('jquery'), '1.0', true);
		}
		wp_localize_script('owlth-slider-admin', 'os_slider_params', array(
			'nonce' => wp_create_nonce('os_save_slider_universal_nonce_action'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'post_id' => $post->ID,
		));
	}

	/**
	 * Handle AJAX Auto-Save Slider Data
	 */
	public function os_auto_save_sliders()
	{
		// Verify the universal nonce
		if (!isset($_POST['os_slider_universal_nonce']) || !wp_verify_nonce($_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action')) {
			wp_send_json_error('Unauthorized nonce verification failed', 403);
			return;
		}

		// Get and sanitize post ID
		$post_id = intval($_POST['post_id']);
		if (!$post_id || !current_user_can('edit_post', $post_id)) {
			wp_send_json_error('Unauthorized user or invalid post ID', 403);
			return;
		}

		// Optional: Check if the post exists and is of type 'os_slider'
		$post = get_post($post_id);
		if (!$post || $post->post_type !== 'os_slider') {
			wp_send_json_error('Invalid post type', 400);
			return;
		}

		// Parse the slider data
		if (isset($_POST['slider_data'])) {
			parse_str($_POST['slider_data'], $slider_data);

			// Save slider data using the unified save function
			$result = os_save_all_slider_data($post_id, $slider_data);

			if (is_wp_error($result)) {
				wp_send_json_error($result->get_error_message(), 400);
				return;
			}
		}

		wp_send_json_success('Slider data saved successfully');
	}

	/**
	 * Handle Normal Save of Slider Meta Fields
	 *
	 * @param int $post_id The post ID.
	 */
	public function os_slider_save_meta_fields($post_id)
	{
		// Verify the universal nonce
		if (!isset($_POST['os_slider_universal_nonce']) || !wp_verify_nonce($_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action')) {
			return;
		}

		// Check if auto-saving, do nothing
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check user permissions
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Collect data from $_POST
		$data = $_POST;

		// Use the unified save function
		$result = os_save_all_slider_data($post_id, $data);

		if (is_wp_error($result)) {
			// Optionally, add an admin notice or log the error
			error_log('Slider Save Error: ' . $result->get_error_message());
		}
	}

	/**
	 * Redirect to the slider type selection screen when initializing a new slider.
	 */
	public function os_redirect_new_slider_to_type_selection()
	{
		global $pagenow;

		// Check if we are on the add-new page for the 'os_slider' post type
		if (
			$pagenow === 'post-new.php' &&
			isset($_GET['post_type']) &&
			$_GET['post_type'] === 'os_slider' &&
			!isset($_GET['slider_type'])
		) {
			// Redirect to the type selection screen
			wp_redirect(admin_url('admin.php?page=os_slider_type_selection'));
			exit;
		}
	}

	/**
	 * Add a submenu page for selecting slider type.
	 */
	public function os_add_slider_type_selection_page()
	{
		add_submenu_page(
			null, // No parent, hidden from menu
			__('Select Slider Type', 'owlthslider'),
			__('Select Slider Type', 'owlthslider'),
			'edit_posts',
			'os_slider_type_selection',
			array($this, 'os_render_slider_type_selection_page')
		);
	}


	/**
	 * Render the slider type selection page.
	 */
	public function os_render_slider_type_selection_page()
	{
		var_dump($_POST);
		// Handle form submission
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['os_slider_type'])) {
			// Verify nonce
			if (
				!isset($_POST['os_slider_type_nonce']) ||
				!wp_verify_nonce($_POST['os_slider_type_nonce'], 'os_select_slider_type_action')
			) {
				wp_die(__('Nonce verification failed', 'owlthslider'));
			}

			$slider_type = intval($_POST['os_slider_type']);

			// Validate slider type
			$term = get_term($slider_type, 'os_slider_type');
			if (!$term || is_wp_error($term)) {
				wp_die(__('Invalid slider type selected', 'owlthslider'));
			}

			// Create a new auto-draft slider with the selected type
			$new_slider = array(
				'post_type' => 'os_slider',
				'post_status' => 'auto-draft',
				'post_title' => __('Auto Draft Slider', 'owlthslider'),
			);

			$post_id = wp_insert_post($new_slider);

			if (is_wp_error($post_id)) {
				wp_die(__('Failed to create new slider', 'owlthslider'));
			}

			// Assign the selected slider type
			wp_set_post_terms($post_id, array($slider_type), 'os_slider_type', false);

			// Redirect to the edit page of the new slider
			$edit_url = admin_url("post.php?post={$post_id}&action=edit");
			wp_redirect($edit_url);
			exit;
		}

		// Fetch all available slider types
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

		// Display the type selection form
		?>
		<div class="wrap">
			<h1><?php _e('Select Slider Type', 'owlthslider'); ?></h1>
			<form method="post" action="">
				<?php wp_nonce_field('os_select_slider_type_action', 'os_slider_type_nonce'); ?>
				<table class="form-table">
					<tbody>
						<?php foreach ($slider_types as $type): ?>
							<tr>
								<th scope="row">
									<label for="os_slider_type_<?php echo esc_attr($type->term_id); ?>">
										<?php echo esc_html($type->name); ?>
									</label>
								</th>
								<td>
									<input type="radio" id="os_slider_type_<?php echo esc_attr($type->term_id); ?>"
										name="os_slider_type" value="<?php echo esc_attr($type->term_id); ?>" required />
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(__('Create Slider', 'owlthslider')); ?>
			</form>
		</div>
		<?php
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
		delete_transient($transient_key);

		// Fetch fresh reviews
		$reviews = owlth_fetch_google_reviews($google_place_id);

		if (empty($reviews)) {
			wp_send_json_error(__('No reviews found or failed to fetch reviews.', 'owlthslider'), 400);
		}

		// Render the updated reviews table
		ob_start();
		os_render_reviews_table($post_id, $google_place_id);
		$table_html = ob_get_clean();

		wp_send_json_success($table_html);
	}


}




// Template row for adding new rows
if (!function_exists('render_table_row_template')):
	function render_table_row_template()
	{
		ob_start();
		render_table_rows('index_count');
		return ob_get_clean();
	}
endif;



