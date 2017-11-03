<?php
/**
 * Carousel shortcode
 *
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once trailingslashit( PRESSCORE_SHORTCODES_INCLUDES_DIR ) . 'abstract-dt-shortcode-with-inline-css.php';

if ( ! class_exists( 'DT_Shortcode_Carousel', false ) ) {

	class DT_Shortcode_Carousel extends DT_Shortcode_With_Inline_Css {
		public static $instance = null;

		/**
		 * @return DT_Shortcode_Carousel
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {

			$this->sc_name = 'dt_carousel';
			$this->unique_class_base = 'carousel-shortcode-id';
			$this->taxonomy = 'category';
			$this->post_type = 'post';
			// add_shortcode("dt_carousel", array($this, "dt_carousel_shortcode"));

			$this->default_atts = array(
				'slide_to_scroll' => 'all',
				'slides_on_desk' => '3',
				'slides_on_lapt' => '3',
				'slides_on_h_tabs' => '3',
				'slides_on_v_tabs' => '2',
				'slides_on_mob' => '1',
				'adaptive_height' => 'off',
				'item_space' => '30',
				'speed' => '600',
				'autoplay' => 'on',
				'autoplay_speed' => "6000",
				'arrows' => 'on',
				//'arrow_style' => 'style-1',
				'arrow_icon_size' => '12px',
				'arrow_icon_paddings'          => '25px 30px 30px 30px',
				'arrow_bg_size' => '30px',
				'arrow_border_radius' => '5px',
				'arrow_border_width' => '2px',
				'arrow_icon_color' => '#2d2d2d',
				'arrow_border_color' => '#2d2d2d',
				'arrow_bg_color' => '#2d2d2d',
				'arrow_icon_color_hover' => '#2d2d2d',
				'arrow_border_color_hover' => '#2d2d2d',
				'arrow_bg_color_hover' => '#2d2d2d',
				'r_arrow_v_position' => 'top',
				'r_arrow_h_position' => 'right',
				'r_arrow_margins' => '25px 30px 30px 30px',
				'l_arrow_v_position' => 'top',
				'l_arrow_h_position' => 'left',
				'l_arrow_margins' => '25px 30px 30px 30px',
				'bullets' => 'on',
				'bullets_style' => 'style-1',
				'bullet_size' => '10px',
				'bullet_color' => 'red',
				'bullet_color_hover' => 'yellow',
				'bullet_gap' => "10px",
				'bullets_v_position' => 'bottom',
				'bullets_h_position' => 'center',
				'bullets_margins' => '25px 30px 30px 30px',
				'next_icon' => 'icon-ar-001-r',
				'prev_icon' => 'icon-ar-001-l',

				// 'category' => '',
				// 'order' => 'desc',
				// 'orderby' => 'date',
				// 'number' => '12',
			);

			parent::__construct();
		}
		

		/**
		 * Do shortcode here.
		 */
		protected function do_shortcode( $atts, $content = '' ) {
		//function dt_carousel_shortcode($atts, $content = ''){
			//$shortcode_tags;
			//$output = '';
			 global $shortcode_tags;
			$attributes = &$this->atts;

				//ob_start();

			
				echo '<div ' . $this->get_container_html_class( array( 'owl-carousel carousel-shortcode' ) ) . ' ' . $this->get_container_data_atts() . '>';
					dt_override_shortcodes();
					echo do_shortcode($content);
					dt_restore_shortcodes();
				echo '</div>';
		}
		
		protected function get_container_html_class( $class = array() ) {
			$attributes = &$this->atts;

			// Unique class.
			$class[] = $this->get_unique_class();

			// switch ( $attributes['arrow_style'] ) {
			// 	case 'style-1':
			// 		$class[] = 'arrows-style-1';
			// 		break;
			// 	case 'style-2':
			// 		$class[] = 'arrows-style-2';
			// 		break;
			// 	case 'style-3':
			// 		$class[] = 'arrows-style-3';
			// 		break;
			// }
			switch ( $attributes['bullets_style'] ) {
				case 'style-1':
					$class[] = 'bullets-style-1';
					break;
				case 'style-2':
					$class[] = 'bullets-style-2';
					break;
				case 'style-3':
					$class[] = 'bullets-style-3';
					break;
			}

			return 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
		}

		protected function get_container_data_atts() {
			$data_atts = array(
				'scroll-mode' => ($this->atts['slide_to_scroll'] == "all") ? 'page' : '1',
				'col-num' => $this->atts['slides_on_desk'],
				'laptop-col' => $this->atts['slides_on_lapt'],
				'h-tablet-columns-num' => $this->atts['slides_on_h_tabs'],
				'v-tablet-columns-num' => $this->atts['slides_on_v_tabs'],
				'phone-columns-num' => $this->atts['slides_on_mob'],
				'auto-height' => ($this->atts['adaptive_height'] === 'on') ? 'true' : 'false',
				'col-gap' => $this->atts['item_space'],
				'speed' => $this->atts['speed'],
				'autoplay' => ($this->atts['autoplay'] === 'on') ? 'true' : 'false',
				'autoplay_speed' => $this->atts['autoplay_speed'],
				'arrows' => ($this->atts['arrows'] === 'on') ? 'true' : 'false',
				'bullet' => ($this->atts['bullets'] === 'on') ? 'true' : 'false',
				'next-icon'=> $this->atts['next_icon'],
				'prev-icon'=> $this->atts['prev_icon']
			);


			return presscore_get_inlide_data_attr( $data_atts );
		}
		/**
		 * Setup theme config for shortcode.
		 */
		protected function setup_config() {
			$config = presscore_config();
			// Get terms ids.
			$terms = get_terms( array(
				'taxonomy' => 'category',
				'slug' => presscore_sanitize_explode_string( $this->get_att( 'category' ) ),
			    'fields' => 'ids',
			) );

			$config->set( 'display', array(
				'type' => 'category',
				'terms_ids' => $terms,
				'select' => ( $terms ? 'only' : 'all' ),
			) );
		}
		/**
		 * Return array of prepared less vars to insert to less file.
		 *
		 * @return array
		 */
		protected function get_less_vars() {
			$storage = new Presscore_Lib_SimpleBag();
			$factory = new Presscore_Lib_LessVars_Factory();
			$less_vars = new DT_Blog_LessVars_Manager( $storage, $factory );
			$less_vars->add_keyword( 'unique-shortcode-class-name', 'carousel-shortcode.' . $this->get_unique_class(), '~"%s"' );

			$less_vars->add_pixel_number( 'icon-size', $this->get_att( 'arrow_icon_size' ) );
			$less_vars->add_paddings( array(
				'icon-padding-top',
				'icon-padding-right',
				'icon-padding-bottom',
				'icon-padding-left',
			), $this->get_att( 'arrow_icon_paddings' ) );
			$less_vars->add_pixel_number( 'arrow-width', $this->get_att( 'arrow_bg_size' ) );
			$less_vars->add_pixel_number( 'arrow-border-radius', $this->get_att( 'arrow_border_radius' ) );
			$less_vars->add_pixel_number( 'arrow-border-width', $this->get_att( 'arrow_border_width' ) );

			$less_vars->add_keyword( 'icon-color', $this->get_att( 'arrow_icon_color' ) );
			$less_vars->add_keyword( 'arrow-border-color', $this->get_att( 'arrow_border_color' ) );
			$less_vars->add_keyword( 'arrow-bg', $this->get_att( 'arrow_bg_color' ) );
			$less_vars->add_keyword( 'icon-color-hover', $this->get_att( 'arrow_icon_color_hover' ) );
			$less_vars->add_keyword( 'arrow-border-color-hover', $this->get_att( 'arrow_border_color_hover' ) );
			$less_vars->add_keyword( 'arrow-bg-hover', $this->get_att( 'arrow_bg_color_hover' ) );
			
			$less_vars->add_keyword( 'arrow-right-v-position', $this->get_att( 'r_arrow_v_position' ) );
			$less_vars->add_keyword( 'arrow-right-h-position', $this->get_att( 'r_arrow_h_position' ) );
			$less_vars->add_paddings( array(
				'arrow-r-margin-top',
				'arrow-r-margin-right',
				'arrow-r-margin-bottom',
				'arrow-r-margin-left',
			), $this->get_att( 'r_arrow_margins' ) );

			$less_vars->add_keyword( 'arrow-left-v-position', $this->get_att( 'l_arrow_v_position' ) );
			$less_vars->add_keyword( 'arrow-left-h-position', $this->get_att( 'l_arrow_h_position' ) );
			$less_vars->add_paddings( array(
				'arrow-l-margin-top',
				'arrow-l-margin-right',
				'arrow-l-margin-bottom',
				'arrow-l-margin-left',
			), $this->get_att( 'l_arrow_margins' ) );

			$less_vars->add_pixel_number( 'bullet-size', $this->get_att( 'bullet_size' ) );
			$less_vars->add_keyword( 'bullet-color', $this->get_att( 'bullet_color' ) );
			$less_vars->add_keyword( 'bullet-color-hover', $this->get_att( 'bullet_color_hover' ) );
			$less_vars->add_pixel_number( 'bullet-gap', $this->get_att( 'bullet_gap' ) );
			$less_vars->add_keyword( 'bullets-v-position', $this->get_att( 'bullets_v_position' ) );
			$less_vars->add_keyword( 'bullets-h-position', $this->get_att( 'bullets_h_position' ) );
			$less_vars->add_paddings( array(
				'bullets-margin-top',
				'bullets-margin-right',
				'bullets-margin-bottom',
				'bullets-margin-left',
			), $this->get_att( 'bullets_margins' ) );

			return $less_vars->get_vars();
		}
		protected function get_less_file_name() {
			// @TODO: Remove in production.
			$less_file_name = 'dt-carousel';

			$less_file_path = trailingslashit( get_template_directory() ) . "css/dynamic-less/shortcodes/{$less_file_name}.less";

			return $less_file_path;
		}
		/**
		 * Return dummy html for VC inline editor.
		 *
		 * @return string
		 */
		protected function get_vc_inline_html() {
			$terms_title = _x( 'Display categories', 'vc inline dummy', 'the7mk2' );

			return $this->vc_inline_dummy( array(
				'class' => 'dt_carousel',
				'title' => _x( 'Carousel', 'vc inline dummy', 'the7mk2' ),
				'fields' => array(
					$terms_title => presscore_get_terms_list_by_slug( array( 'slugs' => $this->atts['category'], 'taxonomy' => 'category' ) ),
				),
			) );
		}


	}
	
	// create shortcode
	DT_Shortcode_Carousel::get_instance()->add_shortcode();	
	
	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
		class WPBakeryShortCode_dt_carousel extends WPBakeryShortCodesContainer {
		}
	}


}
if(!function_exists('dt_override_shortcodes')){
	function dt_override_shortcodes() {
	    global $shortcode_tags, $_shortcode_tags;
	    // Let's make a back-up of the shortcodes
	    $_shortcode_tags = $shortcode_tags;
	    // Add any shortcode tags that we shouldn't touch here
	    $disabled_tags = array( '' );
	    foreach ( $shortcode_tags as $tag => $cb ) {
	        if ( in_array( $tag, $disabled_tags ) ) {
	            continue;
	        }
	        // Overwrite the callback function
	        $shortcode_tags[ $tag ] = 'dt_wrap_shortcode_in_div';
		//	$_shortcode_tags["ult_item_space"] = $item_space;
			//$_shortcode_tags["item_animation"] = $item_animation;
	    }
	}
}
// Wrap the output of a shortcode in a div with class "item"
// The original callback is called from the $_shortcode_tags array
if(!function_exists('dt_wrap_shortcode_in_div')){
	function dt_wrap_shortcode_in_div( $attr, $content = '', $tag ) {
	    global $_shortcode_tags;
	    return '<div class="item">' . call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag ) . '</div>';
	}
}
if(!function_exists('dt_restore_shortcodes')){
	function dt_restore_shortcodes() {
	    global $shortcode_tags, $_shortcode_tags;
	    // Restore the original callbacks
	    if ( isset( $_shortcode_tags ) ) {
	        $shortcode_tags = $_shortcode_tags;
	    }
	}
}
