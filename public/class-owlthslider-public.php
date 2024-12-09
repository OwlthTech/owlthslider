<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/public
 * @author     Owlth Tech <nil@owlth.tech>
 */



class Owlthslider_Public
{


	private $plugin_name;

	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_filter('the_content', array($this, 'os_slider_conditional_enqueue'));
		add_filter('the_content', array($this, 'os_render_slider_in_preview'));
		add_shortcode('os_slider', array($this, 'os_slider_shortcode'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_scripts()
	{
		if (is_preview() && get_post_type() === 'os_slider' && is_dir(OWLTHSLIDER_PLUGIN_DIR . 'build/public/')) {

			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/public/css/owlthslider.min.css', array(), OWLTHSLIDER_VERSION, 'all');
			wp_enqueue_script($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/public/js/owlthslider.min.js', array(), OWLTHSLIDER_VERSION, true);

		}
	}

	/**
	 * Enqueue scripts and styles if the [os_slider] shortcode is present in the content.
	 *
	 * @param string $content The content of the post.
	 * @return string Modified post content.
	 */
	public function os_slider_conditional_enqueue($content)
	{
		if (has_shortcode($content, 'os_slider') && is_dir(OWLTHSLIDER_PLUGIN_DIR . 'build/public/')) {
			wp_enqueue_style($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/public/css/owlthslider.min.css', array(), OWLTHSLIDER_VERSION, 'all');
			wp_enqueue_script($this->plugin_name, OWLTHSLIDER_PLUGIN_URL . 'build/public/js/owlthslider.min.js', array(), OWLTHSLIDER_VERSION, true);
		}

		return $content;
	}

	/**
	 * Render the actual slider shortcode when previewing an os_slider post.
	 *
	 * @param string $content The original post content.
	 * @return string Modified post content with the slider shortcode output.
	 */
	public function os_render_slider_in_preview($content)
	{
		global $post;

		// Check if it's a preview, the post type is 'os_slider', and we're viewing in admin or front-end preview
		if (is_preview() && $post && $post->post_type === 'os_slider') {
			// Generate the shortcode for the current slider
			$shortcode = '[os_slider id="' . $post->ID . '"]';

			// Render the slider using the shortcode and return the content
			$slider_output = do_shortcode($shortcode);

			// Replace the original content with the rendered slider
			return $slider_output;
		}

		return $content;
	}

	/**
	 * Public: Shortcode Rendering
	 * @param mixed $atts
	 * @return bool|string
	 */
	public function os_slider_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'id' => '',
		), $atts, 'os_slider');

		if (empty($atts['id'])) {
			return ''; // No slider ID provided.
		}

		$post_id = intval($atts['id']);
		$slider_data = get_post_meta($post_id, 'os_slider_data', true);
		$slider_data = is_array($slider_data) ? $slider_data : array();

		if (empty($slider_data)) {
			return ''; // No slider data found.
		}

		// Determine slider type from taxonomy
		$terms = wp_get_post_terms($post_id, 'os_slider_type');
		$slider_type = isset($terms[0]) ? $terms[0]->slug : 'default';

		// Slider settings
		$slider_options = get_post_meta($post_id, 'os_slider_options', true);
		$slider_options = is_array($slider_options) ? $slider_options : array();

		ob_start();

		// Render slider based on taxonomy type
		switch ($slider_type) {
			case 'reviews':
				?>
				<div class="os-slider embla" <?php echo isset($slider_options['os_slider_autoplay']) ? 'data-autoplay="yes"' : 'data-autoscroll="yes"'; ?> data-loop="true">
					<div class="os-slider__viewport">
						<div class="os-slider__container">
							<?php foreach ($slider_data as $data): ?>
								<?php if ($data['enabled']): ?>
									<div class="os-slider__slide">
										<div class="embla__parallax">
											<div class="review-author">
												<img src="<?php echo esc_url($data['author_details']['author_avatar']); ?>"
													alt="<?php echo esc_attr($data['author_details']['author_name']); ?>">
												<h3><?php echo esc_html($data['author_details']['author_name']); ?></h3>
											</div>
											<div class="review-content">
												<p><?php echo esc_html($data['review_body']); ?></p>
												<div class="review-meta">
													<span class="review-rating"><?php echo esc_html($data['rating']); ?> ★</span>
													<span class="review-date"><?php echo esc_html($data['review_date']); ?></span>
												</div>
											</div>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php
				break;

			default: // Default slider type
				?>
				<div class="os-slider embla" <?php echo isset($slider_options['os_slider_autoplay']) ? 'data-autoplay="yes"' : 'data-autoscroll="yes"'; ?> data-loop="true">
					<div class="os-slider__viewport">
						<div class="os-slider__container">
							<?php foreach ($slider_data as $data): ?>
								<?php if ($data['enabled']): ?>
									<div class="os-slider__slide"
										style="background-image: url('<?php echo esc_url($data['background_image']); ?>');">
										<div class="embla__parallax">
											<div class="os-slider-content">
												<h2><?php echo esc_html($data['heading']); ?></h2>
												<p><?php echo $data['caption']; ?></p>
												<?php if (isset($data['cta_details']['cta_text']) && isset($data['cta_details']['cta_link'])): ?>
													<a href="<?php echo esc_url($data['cta_details']['cta_link']); ?>"
														class="os-slider-cta-button">
														<?php echo esc_html($data['cta_details']['cta_text']); ?>
													</a>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php
				break;
		}

		return ob_get_clean();
	}


}
