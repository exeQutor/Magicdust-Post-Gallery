<?php
/*
Plugin Name: Magicdust Post Gallery
Plugin URI: https://github.com/exeQutor/Magicdust-Post-Gallery/
Description: Turn your blog posts into a simple gallery!
Version: 1.0.0
Author: Magicdust
Author URI: http://magicdust.com.au/
*/

if ( !class_exists( 'RationalOptionPages' ) ) {
	require_once('includes/RationalOptionPages/RationalOptionPages.php');
}
$pages = array(
	'mdpg-options'	=> array(
		'page_title'	=> __( 'Magicdust Post Gallery', 'magicdust' ),
		'sections' => array(
			'section-one' => array(
				'title' => __( 'Post Query', 'magicdust' ),
				'fields' => array(
					'post_type' => array(
						'title' => __( 'Post Type', 'magicdust' ),
						'value' => 'post',
					),
					'posts_per_page' => array(
						'title' => __( 'Posts Per Page', 'magicdust' ),
						'type' => 'number',
						'value' => 10
					)
				)
			)
		)
	),
);

$option_page = new RationalOptionPages( $pages );

class Magicdust_Post_Gallery
{
	private $plugin_dir;
	private $plugin_url;

	function __construct()
	{
		$this->plugin_dir = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
		add_shortcode('magicdust-post-gallery', array($this, 'render_shortcode'));
	}

	function wp_enqueue_scripts()
	{
		wp_enqueue_style('magicdust-post-gallery', $this->plugin_url . 'styles.css', array(), time());
	}

	function render_shortcode()
	{
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$options = get_option('mdpg-options');
		$out = '';

		$the_query = 	new WP_Query(array(
			'post_type' => $options['post_type'] ? $options['post_type'] : 'post',
			'posts_per_page' => $options['posts_per_page'] ? $options['posts_per_page'] : 10,
			'paged' => $paged
		));

		if ($the_query->have_posts()) {
			$out .= '<div class="md-post-gallery">';
			$out .= '<ul class="md-post-gallery__list">';

			while ($the_query->have_posts()) {
				$the_query->the_post();

				if (has_post_thumbnail()) {
					$out .= '<li class="md-post-gallery__item">';
					$out .= '<a class="md-post-gallery__link" href="' . get_permalink() . '">';
					$out .= get_the_post_thumbnail();
					$out .= '</a>';
					$out .= '</li>';
				}
			}

			wp_reset_postdata();

			$out .= '</ul>';
			$out .= '</div>';

			$out .= '<div class="md-post-gallery__nav">';
			$out .= get_previous_posts_link('&laquo; Newer');
			$out .= get_next_posts_link('Older &raquo;', $the_query->max_num_pages);
			$out .= '</div>';
		}

		return $out;
	}
}

new Magicdust_Post_Gallery;
