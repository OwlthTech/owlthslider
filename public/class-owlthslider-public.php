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
class Owlthslider_Public {


	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_filter('the_content', array($this, 'os_enqueue_assets_if_slider_shortcode'));
		add_filter('the_content', array($this, 'os_render_slider_in_preview'));
		add_shortcode('os_slider', array($this, 'os_slider_shortcode'));
	
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/owlthslider-public.css', array(), $this->version, 'all' );
		if (is_preview() && get_post_type() === 'os_slider' && is_dir(plugin_dir_url(__FILE__) . 'build')) {
			wp_enqueue_script('owlthslider-js', OS_PLUGIN_URL . 'build/frontend/js/owlthslider.min.js', array(), OS_PLUGIN_VERSION, true);
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/owlthslider-public.js', array( 'jquery' ), $this->version, false );
		if (is_preview() && get_post_type() === 'os_slider' && is_dir(plugin_dir_url(__FILE__) . 'build')) {
			wp_enqueue_script('owlthslider-js', OS_PLUGIN_URL . 'build/frontend/js/owlthslider.min.js', array(), OS_PLUGIN_VERSION, true);
		}
	}

	/**
 * Enqueue scripts and styles if the [os_slider] shortcode is present in the content.
 *
 * @param string $content The content of the post.
 * @return string Modified post content.
 */
public function os_enqueue_assets_if_slider_shortcode($content)
{
    if (has_shortcode($content, 'os_slider')) {
        // Enqueue script and style only if the [os_slider] shortcode is found
        wp_enqueue_script('owlthslider-js', OS_PLUGIN_URL . 'build/frontend/js/owlthslider.min.js', array(), OS_PLUGIN_VERSION, true);
        wp_enqueue_style('owlthslider-css', OS_PLUGIN_URL . 'build/frontend/css/owlthslider.min.css', array(), OS_PLUGIN_VERSION, 'all');
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
		$slider_data = get_post_meta($post_id, '_os_slider_data', true);
		$slider_data = is_array($slider_data) ? $slider_data : array();

		error_log(print_r("owlthslider:" . $slider_data, true));
		error_log(print_r($slider_data, true));
		if (empty($slider_data)) {
			return ''; // No slider data found.
		}

		// Slider settings
		$slider_type = get_post_meta($post_id, '_os_slider_type', true);
		$autoplay_duration = get_post_meta($post_id, '_os_slider_autoplay_duration', true);
		$autoplay_delay = get_post_meta($post_id, '_os_slider_autoplay_delay', true);

		ob_start();
		?>
		<div class="os-slider embla" <?php echo (($slider_type === 'autoplay')) ? ' data-autoplay="yes"' : 'data-autoscroll="yes"'; ?> 		<?php echo (isset($autoplay_duration) && !empty($autoplay_duration)) ? ' data-duration="' . $autoplay_duration . '"' : ''; ?> 		<?php echo (isset($autoplay_delay) && !empty($autoplay_delay)) ? ' data-delay="' . $autoplay_delay . '"' : ''; ?> data-loop="true">
			<div class="os-slider__viewport">
				<div class="os-slider__container">
					<?php foreach ($slider_data as $data): ?>
						<?php if ($data['enabled'] === 'yes'): ?>
							<div class="os-slider__slide" style="background-image: url('');">
								<div class="embla__parallax">
									<div class="embla__parallax__layer">
										<div class="overlay"></div>
										<img class="embla__parallax__img" src="<?php echo esc_url($data['background_image']); ?>" alt=""
											srcset="">
									</div>
									<div class="os-slider-content" style="color: white; z-index: 1;">
										<h2><?php echo esc_html($data['heading']); ?></h2>
										<p><?php echo $data['caption']; ?></p>
										<?php if ($data['cta_text'] && $data['cta_link']): ?>
											<a href="<?php echo esc_url($data['cta_link']); ?>" class="os-slider-cta-button">
												<?php echo esc_html($data['cta_text']); ?>
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
		return ob_get_clean();
	}

}
