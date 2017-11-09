<?php
/*
Plugin Name: CBX Multi Criteria Rating & Review System
Plugin URI: http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress
Description: Multi criteria Rating system for wordpress
Version: 3.9.3
Author: Codeboxr Team
Author URI: codeboxr.com
*/

defined('ABSPATH') OR exit;

//define the constants
define('CB_RATINGSYSTEM_PLUGIN_VERSION', '3.9.3'); //need for checking verson
define('CB_RATINGSYSTEM_FILE', __FILE__);
define('CB_RATINGSYSTEM_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('CB_RATINGSYSTEM_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(CB_RATINGSYSTEM_FILE)));
define('CB_RATINGSYSTEM_PLUGIN_NAME', 'Rating & Review');
define('CB_RATINGSYSTEM_PLUGIN_SLUG_NAME', 'cbratingsystem');
define('CB_RATINGSYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CB_RATINGSYSTEM_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('CB_RATINGSYSTEM_PLUGIN_DIR_IMG', plugin_dir_url(__FILE__) . 'images/');
define('CB_RATINGSYSTEM_RAND_MIN', 0);
define('CB_RATINGSYSTEM_RAND_MAX', 999999);
define('CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS', time() + 1209600); //Expiration of 14 days.
define('CB_RATINGSYSTEM_COOKIE_EXPIRATION_7DAYS', time() + 604800); //Expiration of 7 days.
define('CB_RATINGSYSTEM_COOKIE_NAME', 'cbrating-cookie-session');

//to handle multibyte
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('utf-8');
}


//require_once ABSPATH . 'wp-admin/includes/user.php';
//used for maximum database related operations
require_once(CB_RATINGSYSTEM_PATH . '/data.php');

//used for core widgets
require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemWidget.php');

//bootstrap the rating plugin
add_action('init', array('CBRatingSystem', 'init_cookie'));


//actions on install and on uninstall/delete
//plugin activation hook
register_activation_hook(__FILE__, array('CBRatingSystem', 'cbratingsystem_activation'));
//plugin deactivation hook
//register_deactivation_hook( __FILE__, array( 'CBRatingSystem', 'cbratingsystem_deactivation' ) ); //we are not using it still now

//plugin uninstall/delete hook
register_uninstall_hook(__FILE__, array('CBRatingSystem', 'cbratingsystem_uninstall'));


/**
 * Class CBRatingSystem
 */
class CBRatingSystem
{

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    //public static $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the Dashboard and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        load_plugin_textdomain('cbratingsystem', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Runs on plugin activated

        // Buddypress integration, check if buddypress is installed or not

        if (function_exists('bp_is_active')) {
            require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'buddypress' . DIRECTORY_SEPARATOR . 'bp-blogs' . DIRECTORY_SEPARATOR . 'bp-blogs-activity.php');
        }

        require_once(CB_RATINGSYSTEM_PATH . '/cbratinglogreportoutput.php');
        require_once(CB_RATINGSYSTEM_PATH . '/CBRatingSystemFunctions.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemFront.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemFrontReview.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdmin.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminDashboard.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminFormParts.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdminReport.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemCalculation.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemMetaBox.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemTool.php');
        require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemTheme.php');


        //ajax request to save review
        add_action('wp_ajax_nopriv_cbRatingAjaxFunction', array('CBRatingSystemFront', 'cbRatingAjaxFunction'));
        add_action('wp_ajax_cbRatingAjaxFunction', array('CBRatingSystemFront', 'cbRatingAjaxFunction'));


        add_action('wp_ajax_nopriv_cbReviewAjaxFunction', array('CBRatingSystemFrontReview', 'cbReviewAjaxFunction'));
        add_action('wp_ajax_cbReviewAjaxFunction', array('CBRatingSystemFrontReview', 'cbReviewAjaxFunction'));


        //init widgets
        add_action('widgets_init', array($this, 'initWidgets'));

        add_action('admin_init', array($this, 'deleteRatingsandLogs'));


        add_filter('rating_form_array', array('CBRatingSystemMetaBox', 'ratingForm_add_meta_data_filter'));

        /**
         * load only in admin backend part
         *
         */
        if (is_admin()) {

            if (class_exists('CBRatingSystemAdmin')) {
                CBRatingSystemAdmin::init();
            }


            add_action('wp_ajax_nopriv_cbAdminRatingFormListingAjaxFunction', array('CBRatingSystemAdmin', 'cbAdminRatingFormListingAjaxFunction'));
            add_action('wp_ajax_cbAdminRatingFormListingAjaxFunction', array('CBRatingSystemAdmin', 'cbAdminRatingFormListingAjaxFunction'));

            $customPostTypes = self::post_types();

            //cbxdump($customPostTypes);

            add_filter('manage_pages_columns', array($this, 'add_rating_column'));
            add_filter('manage_posts_columns', array($this, 'add_rating_column'));

            add_action('manage_posts_custom_column', array($this, 'rating_column_content'), 10, 2);
            add_action('manage_pages_custom_column', array($this, 'rating_column_content'), 10, 2);

            //add custom columns for custom post types

            if (!empty($customPostTypes['custom']['types'])) {
                foreach ($customPostTypes['custom']['types'] as $type => $typeLabel) {
                    add_filter("manage_{$type}_posts_columns", array($this, 'add_rating_column'));
                    add_action("manage_{$type}_posts_custom_column", array($this, 'rating_column_content'), 10, 2);
                }
            }

            add_action('admin_head', array($this, 'rating_column_style_admin_head'));

            /* Meta box */
            add_action('load-post.php', array($this, 'post_meta_boxes_setup'));
            add_action('load-post-new.php', array($this, 'post_meta_boxes_setup'));

            //tinymce visual editor
            add_action('admin_head', array($this, 'tinymce_shortcode'));
            add_action('admin_enqueue_scripts', array($this, 'cbratingsystem_admin_styles'));


        } else {
            //fontend


            add_filter('query_vars', array($this, 'email_verify_var'));
            add_action('template_redirect', array($this, 'email_verify'), 0);

            //auto integration
            add_filter('the_content', array($this, 'main_content_with_rating_form'));

            //shortcodes
            add_shortcode('cbratingsystem', array($this, 'cbratingsystem_shorttag')); //main shortcode to put the rating form

            //more shortcodes
            add_shortcode('cbratingavg', array($this, 'cbratingsystem_avg')); //there is widget also
            add_shortcode('cbratingtoprateduser', array($this, 'cbratingsystem_top_rated_user')); //there is widget also
            add_shortcode('cbratingtopratedposts', array($this, 'cbratingsystem_top_rated_posts')); //there is widget also

            //new shortcode
            add_shortcode('cbratingreview', array($this, 'cbratingsystem_review')); //show latest reviews
        }
    }

    public function tinymce_shortcode()
    {
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // check if WYSIWYG is enabled
        if ('true' == get_user_option('rich_editing')) {
            add_filter('mce_external_languages', array($this, 'tinymce_shortcode_lang'));
            add_filter('mce_external_plugins', array($this, 'tinymce_shortcode_plg'));
            add_filter('mce_buttons', array($this, 'tinymce_shortcode_mce_buttons'));
        }
    }

    /**
     * mce_external_plugins
     * Adds our tinymce plugin
     *
     * @param  array $plugin_array
     *
     * @return array
     */
    public function tinymce_shortcode_lang($locales)
    {

        $locales['cbratingsystem'] = plugin_dir_path(__FILE__) . 'languages/tinymcelangs.php';

        return $locales;
    }

    /**
     * mce_external_plugins
     * Adds our tinymce plugin
     *
     * @param  array $plugin_array
     *
     * @return array
     */
    public function tinymce_shortcode_plg($plugins_arr)
    {

        $plugins_arr['cbratingsystem'] = plugins_url('js/mce-button-cbratingsystem.js', __FILE__);

        return $plugins_arr;
    }


    /**
     * mce_buttons
     * Adds our tinymce button
     *
     * @param  array $buttons
     *
     * @return array
     */
    function tinymce_shortcode_mce_buttons($buttons)
    {
        array_push($buttons, 'cbratingsystem');

        return $buttons;
    }

    /**
     * admin_enqueue_scripts
     * Used to enqueue custom styles
     *
     * @return void
     */
    function cbratingsystem_admin_styles()
    {
        wp_register_style('cbratingsystem-shortcode', plugins_url('css/mce-button-cbratingsystem.css', __FILE__));
        wp_enqueue_style('cbratingsystem-shortcode');
    }

    /**
     * Rating icon url
     *
     * @return mixed|string|void
     */
    public static function ratingIconUrl()
    {
        $rating_icon_dir_url = CB_RATINGSYSTEM_PLUGIN_DIR_IMG;
        $rating_icon_dir_url = apply_filters('cbratingsystem_ratingimg_url', $rating_icon_dir_url);

        return $rating_icon_dir_url;
    }

    /**
     * Php and js based redirect method based on situation
     *
     * @param $url
     */
    public static function redirect($url)
    {
        if (headers_sent()) {
            $string = '<script type="text/javascript">';
            $string .= 'window.location = "' . $url . '"';
            $string .= '</script>';

            echo $string;
        } else {
            wp_safe_redirect($url);

        }
        exit;
    }

    /**
     * Add params var to wordpress reserver params
     *
     *
     * @param $params
     *
     * @return array
     */

    public static function email_verify_var($params)
    {
        $params[] = 'cbratingemailverify';

        return $params;
    }

    /**
     * Verify Guest User Email
     */

    public static function email_verify()
    {
        $hash = esc_attr(get_query_var('cbratingemailverify'));

        $rating = array();
        global $wpdb;
        $user_id = get_current_user_id();

        //we got hash and user is guest
        if ($hash != '' && $user_id == 0) {

            $user_log_table = CBRatingSystemData::get_user_ratings_table_name();
            $form_table     = CBRatingSystemData::get_ratingForm_settings_table_name();

            $sql = "SELECT *  FROM $user_log_table ";
            $sql .= " WHERE  comment_hash = %s ";

            $sql = $wpdb->prepare($sql, $hash);

            $comment_current_data = $wpdb->get_row($sql);

            //comment/rating found with this hash
            if ($comment_current_data !== null) {

                $form_id         = $comment_current_data->form_id;
                $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);

                if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return;


                $guest_comment_status = 'approved';


                $guest_comment_status = isset($ratingFormArray['guest_comment_default_status']) ? $ratingFormArray['guest_comment_default_status'] : $guest_comment_status;

                $rating_data ['comment_hash']   = '';
                $rating_data ['comment_status'] = $guest_comment_status;

                $success = $wpdb->update($user_log_table, $rating_data, array('id' => $comment_current_data->id), array('%s', '%s'), array('%d'));
            }
            //if the form and comment exists with the hash
        }
    }

    /**
     * Register Widgets
     *
     */
    public static function initWidgets()
    {
        register_widget('CBRatingSystemWidget'); //top rated posts
        register_widget('CBRatingSystemUserWidget'); //top rated user
        register_widget('CBRatingSystemLatestReviewWidget'); //latest reviews
    }

    /**
     * called when plugin is installed
     */
    public static function cbratingsystem_activation()
    {

        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");


        $previous_version = get_site_option('cbratingsystem_plugin_version');

        if ($previous_version === false) {
            //Install the DB tables for this plugin
            CBRatingSystemData::update_table();

            add_site_option('cbratingsystem_plugin_version', CB_RATINGSYSTEM_PLUGIN_VERSION);

        } elseif (CB_RATINGSYSTEM_PLUGIN_VERSION != $previous_version) {
            //Install the DB tables for this plugin version
            CBRatingSystemData::update_table();

            update_site_option('cbratingsystem_plugin_version', CB_RATINGSYSTEM_PLUGIN_VERSION);
        } elseif (CB_RATINGSYSTEM_PLUGIN_VERSION == $previous_version) {
            CBRatingSystemData::update_table();
        }

    }

    public static function cbratingsystem_deactivation()
    {
        if (!current_user_can('activate_plugins'))
            return;

        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("deactivate-plugin_{$plugin}");
    }

    /**
     * called when plugin uninstalled/delete
     * delete all options if delete all saved from tools page
     */

    public static function cbratingsystem_uninstall()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        check_admin_referer('bulk-plugins');

        // Important: Check if the file is the one
        // that was registered during the uninstall hook.
        if (__FILE__ != WP_UNINSTALL_PLUGIN)
            return;


        $checkuninstall = intval(get_option('cbratingsystem_deleteonuninstall'));

        if ($checkuninstall == 1) {

            CBRatingSystemData::delete_tables();
            CBRatingSystemData::delete_options();
            CBRatingSystemData::delete_metakeys();
        }

    }

    /**
     * add_common_styles_scripts
     */
    public static function add_common_styles_scripts()
    {

        //register scripts

        wp_register_style('cbrp-common-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/cbrating.common.style.css', array(), CB_RATINGSYSTEM_PLUGIN_VERSION);
        wp_register_style('jquery-chosen-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/chosen.min.css', array(), CB_RATINGSYSTEM_PLUGIN_VERSION);

        wp_register_script('jquery-chosen', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/chosen.jquery.js', array('jquery'), CB_RATINGSYSTEM_PLUGIN_VERSION);
        wp_register_script('cbrp-common-script', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbrating.common.script.js', array('jquery', 'jquery-chosen'), CB_RATINGSYSTEM_PLUGIN_VERSION);

        //finally enqueue style and js

        wp_enqueue_style('jquery-chosen-style');
        wp_enqueue_style('cbrp-common-style');

        wp_enqueue_script('jquery-chosen');
        wp_enqueue_script('cbrp-common-script');

    }

    /**
     * @return array
     * localize js file for language
     */

    public static function get_language_strings()
    {
        //['bad', 'poor', 'regular', 'good', 'gorgeous'],
        $hitns = array(
            esc_html__('Bad', 'cbratingsystem'),
            esc_html__('Poor', 'cbratingsystem'),
            esc_html__('Regular', 'cbratingsystem'),
            esc_html__('Good', 'cbratingsystem'),
            esc_html__('Gorgeous', 'cbratingsystem'),
        );


        $strings = array(
            'string_prefix'  => esc_html__('', 'cbratingsystem'),
            'string_postfix' => esc_html__('characters', 'cbratingsystem'),
            'noRatedMsg'     => esc_html__('No Rating', 'cbratingsystem'),
            'img_path'       => CBRatingSystem::ratingIconUrl(),
            'hints'          => json_encode($hitns),
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'read_more_less' => array(
                'more' => esc_html__('...More', 'cbratingsystem'),
                'less' => esc_html__('...Less', 'cbratingsystem')
            ),
        );

        return $strings;
    }

    /**
     * load_scripts_and_styles
     */
    public static function load_scripts_and_styles()
    {

        CBRatingSystem::add_common_styles_scripts();
        wp_enqueue_style('dashicons');


        wp_register_style('cbrp-basic-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/basic.style.css', array('dashicons'), CB_RATINGSYSTEM_PLUGIN_VERSION);
        wp_register_style('cbrp-basic-review-style', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'css/basic.review.style.css', array('cbrp-basic-style'), CB_RATINGSYSTEM_PLUGIN_VERSION);


        wp_register_script('jquery-raty-min', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/jquery.raty.min.js', array('jquery'));
        wp_register_script('cbrp-front-js', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbratingsystem.front.js', array('jquery'), CB_RATINGSYSTEM_PLUGIN_VERSION, true);
        wp_localize_script('cbrp-front-js', 'cbratingsystem', self::get_language_strings());


        wp_register_script('cbrp-front-review-js', CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'js/cbratingsystem.front.review.js', array('jquery', 'cbrp-front-js'), CB_RATINGSYSTEM_PLUGIN_VERSION, true);


        wp_enqueue_style('cbrp-basic-style');
        wp_enqueue_style('cbrp-basic-review-style');

        wp_enqueue_script('jquery-raty-min');
        wp_enqueue_script('cbrp-front-js');
        wp_enqueue_script('cbrp-front-review-js');

        //allow 3rd party to load more css and js after loading all cbrating native
        do_action('cbratingsystem_load_scripts_and_styles');


    }

    /**
     * front_end_js_init
     */
    public static function front_end_js_init()
    {
        //self::add_common_styles_scripts();
    }

    /**
     * add_rating_column
     * in backend table
     */
    public static function add_rating_column($columns)
    {
        return array_merge(
            $columns,
            array('rating' => __('Average Rating', 'cbratingsystem'))
        );
    }

    /**
     * @param $column
     * @param $post_id
     */
    public static function rating_column_content($column, $post_id)
    {
        if ('rating' != $column)
            return;
        $ratings = CBRatingSystemData::get_ratings_summary(array('post_id' => array($post_id)), 'form_id', 'ASC', true);

        if (!empty($ratings)) {
            $log_average = '<ul>';

            foreach ($ratings as $rowId => $rows) {
                if ($rows->per_post_rating_summary > 0) {
                    $log_average .= '<li><strong>' . $rows->form_id . ': ' . $rows->name . '</strong>';

                    $summary_val   = (($rows->per_post_rating_summary / 100) * 5);
                    $summary_label = sprintf(__('%s out of 5', 'cbratingsystem'), $summary_val);

                    $log_average .= '<span style="display:block; padding-left:10px;">' . $summary_label . '</span>';
                    $log_average .= '</li>';
                }
            }

            $log_average .= '</ul>';
        } else {
            $log_average = __('No average rating', 'cbratingsystem');

        }

        echo $log_average;
    }

    /**
     * rating_column_style_admin_head
     * change the table col width of backend
     */
    public static function rating_column_style_admin_head()
    {

        echo '<style type="text/css">';
        echo '.column-comment { width: 23% !important; }';
        echo '</style>';
    }

    /**
     * Callback for shortcode [cbratingsystem]
     *
     * @param $atts
     *
     * @return string
     */
    public static function cbratingsystem_shorttag($atts)
    {
        global $post;

        $output = '';

        if (!is_object($post)) {
            return '';
        }

        $options = shortcode_atts(
            array(
                'form_id'    => '',
                'post_id'    => $post->ID, //if post id missing then take from loop
                'theme_key'  => get_option('cbratingsystem_theme_key', 'basic'), // set the default theme
                'showreview' => 1
            ), $atts
        );


        if ($options['theme_key'] == '') {
            $options['theme_key'] = 'basic';
        }
        if ($options['post_id'] == '') {
            $options['post_id'] = $post->ID;
        }

        if (empty($options['form_id']) || $options['form_id'] == '') {
            $defaultFormId = get_option('cbratingsystem_defaultratingForm');
            $form_id       = apply_filters('rating_form_array', $defaultFormId);
        } else {
            $form_id = $options['form_id'];
        }


        if (isset($form_id) && is_numeric($form_id)) {
            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);

            if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return '';


            $ratingFormArray['form_id']   = $form_id;
            $ratingFormArray['post_id']   = $options['post_id'];
            $ratingFormArray['theme_key'] = $options['theme_key'];

            $show_review = intval($options['showreview']);
            $post_id     = $options['post_id'];


            if (class_exists('CBRatingSystemFront') && ($ratingFormArray['is_active'] == 1) && (($ratingFormArray['enable_shorttag'] == 1))) {

                CBRatingSystem::load_scripts_and_styles();
                CBRatingSystemTheme::build_custom_theme_css();


                //get the rating form
                $output .= CBRatingSystemFront::add_ratingForm_to_content($ratingFormArray);


                //get the review list
                if (class_exists('CBRatingSystemFrontReview') && (isset($ratingFormArray['review_enabled']) && $ratingFormArray['review_enabled'] == 1) && $show_review) {
                    if (is_singular()) {
                        $review = CBRatingSystemFrontReview::rating_reviews_shorttag($ratingFormArray, $post_id);

                        if (!empty($review)) {
                            $output .= $review;
                        }
                    }
                }

                //return $form;
            }
        }

        return $output;
    }

    /**
     * this function is currently not used
     *
     * @param $options
     *
     * @return string
     */
    public static function cbratingsystem_shorttag_output($options)
    {
        CBRatingSystem::load_scripts_and_styles();
        CBRatingSystemTheme::build_custom_theme_css();


        if (!empty($options)) {
            if (empty($options['form_id']) || $options['form_id'] == '') {
                $defaultFormId = get_option('cbratingsystem_defaultratingForm');
                $form_id       = apply_filters('rating_form_array', $defaultFormId);
            } else {
                $form_id = $options['form_id'];
            }


            if (isset($form_id) && is_numeric($form_id)) {
                $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
                if (!sizeof($ratingFormArray)) return ''; //if form doesn't exits of wrong form id given


                $ratingFormArray['form_id']   = $form_id;
                $ratingFormArray['post_id']   = $options['post_id'];
                $ratingFormArray['theme_key'] = $options['theme_key'];

                $show_review = intval($options['showreview']);
                $post_id     = $options['post_id'];


                if (class_exists('CBRatingSystemFront') && ($ratingFormArray['is_active'] == 1) && (($ratingFormArray['enable_shorttag'] == 1))) {

                    //get the rating form
                    $form = CBRatingSystemFront::add_ratingForm_to_content($ratingFormArray);


                    //get the review list
                    if (class_exists('CBRatingSystemFrontReview') && (isset($ratingFormArray['review_enabled']) && $ratingFormArray['review_enabled'] == 1) && $show_review) {
                        if (is_singular()) {
                            $review = CBRatingSystemFrontReview::rating_reviews_shorttag($ratingFormArray, $post_id);

                            if (!empty($review)) {
                                $form = $form . $review;
                            }
                        }
                    }

                    return $form;
                }
            }

        }
    }//end cbratingsystem_shorttag_output

    /**
     * Show average rating for multiple posts
     *
     * @param $atts
     *
     * @return string
     */
    public static function cbratingsystem_avg($atts)
    {
        global $post;
        if (!is_object($post)) {
            return '';
        }

        //Example: [cbratingsystem rating_form_id=1]
        $options = shortcode_atts(
            array(
                'post_ids'     => '',
                'form_id'      => '',
                'show_title'   => 0,
                'show_form_id' => 0,
                'show_text'    => 0,
                'show_star'    => 1,
                'show_single'  => 0,
                'text_label'   => esc_html__('Rating:', 'cbratingsystem')
            ), $atts
        );


        if ($options['post_ids'] == '')
            $options['post_ids'] = $post->ID;


        if (intval($options['form_id']) == 0)
            $options['form_id'] = self::get_default_ratingFormId();


        $option = array('post_id' => explode(",", $options['post_ids']), 'form_id' => array(intval($options['form_id'])));

        $output = self::standalone_singlePost_rating_summary($option, $options['show_title'], $options['show_form_id'], $options['show_text'], $options['show_star'], $options['show_single'], $options['text_label']);

        return $output;
    }

    /**
     * Show latest reviews
     *
     * @param $atts
     */
    public function cbratingsystem_review($atts)
    {

        $options = shortcode_atts(
            array(
                'form_id'       => '', //for multiple use comma
                'post_id'       => '', //for multiple use comma
                'user_id'       => '', //for multiple use comma
                'post_type'     => 'post', //for multiple use comma
                'order'         => 'DESC',
                'order_by'      => 'created',
                'limit'         => 10,
                'show_comment'  => 1, //show comment
                'show_criteria' => 0, //show criteria based rating
                'show_post'     => 1 //show criteria based rating
            ), $atts
        );


        if (intval($options['form_id']) == 0) {
            return '';
        }

        return cbratingGetLatestReviews($options);

    }


    // Top rated user shortcode
    /**
     * @param $atts
     *
     * @return string
     */
    public static function cbratingsystem_top_rated_user($atts)
    {
        global $post;

        if (!is_object($post)) {
            return '';
        }

        //Example: [cbratingsystem rating_form_id=1]
        $options = shortcode_atts(
            array(
                'post_id'     => '', // comma separate post id
                'form_id'     => '', // one form id
                'user_id'     => '', // set comma separate user id
                'day'         => '0', // 0 = all time, 1 = 1 day, 7 = 7 days
                'limit'       => 10, // data  limit
                'post_type'   => '', //  post type
                'post_filter' => '', // post_id or post_type,
                'order'       => 'DESC',
                'firstorder'  => 'post_count', //post_count, rating
                'title'       => __('Top Rated Users', 'cbratingsystem')
            ), $atts
        );


        if ($options['form_id'] == '') return '';

        //var_dump($options);
        $limit = $options['limit'];
        if ($options['day'] != 0) {

            $date                 = CBRatingSystemFunctions::get_calculated_date($options['day']);
            $options['post_date'] = $date;
        }

        $data = CBRatingSystemData::get_top_rated_post($options, false, $limit);

        $cbrp_output = '<ul class="cbrp-top-rated-wpanel">';
        if (!empty($data)) :

            foreach ($data as $newdata) :

                $cbrp_output .= '<li class="cbrp-top-rated-userlist">';
                $author_info = get_userdata((int)$newdata['post_author']);
                $cbrp_output .= '<a  href="' . get_author_posts_url((int)$newdata['post_author']) . ' ">' . $author_info->display_name . ' </a> ' . $newdata['post_count'] . '' . __('Posts', 'cbratingsystem');
                $rating = (($newdata['rating'] / 100) * 5);
                ?>

                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#cbrp-top-rated-sc-<?php echo $newdata['post_author'] . '_' . $post->ID; ?>').raty({
                            half: true,
                            path: '<?php echo CBRatingSystem::ratingIconUrl(); ?>',
                            score: <?php echo number_format($rating, 2, '.', ''); ?>,
                            readOnly: true,
                            hintList: ['', '', '', '', '']
                        });
                    });
                </script>
                <?php
                $cbrp_output .= ' <strong>' . number_format($rating, 2, '.', '') . '/5</strong> ';
                $cbrp_output .= '<span id ="cbrp-top-rated-sc-' . $newdata['post_author'] . '_' . $post->ID . '" ></span></li>';
            endforeach;
        else:
            $cbrp_output .= ' <li class="cbrp-top-rated-userlist">' . __('No Results found', 'cbratingsystem') . ' </li>';
        endif;
        $cbrp_output .= '</ul>';

        return $cbrp_output;

    }


    // top rated posts shortcode
    /**
     * @param $atts
     *
     * @return string
     */
    public function cbratingsystem_top_rated_posts($atts)
    {

        global $post;
        if (!is_object($post)) {
            return '';
        }
        //Example: [cbratingsystem rating_form_id=1]
        $options = shortcode_atts(
            array(
                'post_type' => '', //post type
                'day'       => '0', // 0 all time, 1 = 24 hours/1 day  7 = 7 days  15 = 15 days, 30 = 30 days
                'limit'     => 10, // data  limit
                'form_id'   => '', //form id
                'order'     => 'DESC',
            ), $atts
        );


        CBRatingSystem::load_scripts_and_styles();

        $whrOptn = array();

        $type    = $options['type'];
        $date    = $options['day'];
        $limit   = $options['limit']; //limit
        $form_id = $options['form_id'];
        $order   = $options['order'];

        $whrOptn['order'] = $order;


        if ($date != 0) {

            $date                 = self::get_calculated_date($date);
            $whrOptn['post_date'] = $date;
        }

        $whrOptn['post_type'][] = $type;
        $whrOptn['form_id'][]   = $form_id; //added from v3.2.20


        $data = CBRatingSystemData::get_ratings_summary($whrOptn, 'avg', $order, true, $limit);


        ob_start();
        ?>
        <ul class="cbrp-top-rated-wpanel">
            <?php if (!empty($data)) : ?>
                <?php foreach ($data as $newdata) : ?>
                    <li>
                        <script type="text/javascript">
                            jQuery(document).ready(function ($) {
                                $('#cbrp-top-rated-sc-<?php echo $newdata->post_id . '-' . $form_id; ?>').raty({
                                    half: true,
                                    path: '<?php echo CBRatingSystem::ratingIconUrl(); ?>',
                                    score: <?php echo(($newdata->per_post_rating_summary / 100) * 5); ?>,
                                    readOnly: true,
                                    hintList: ['', '', '', '', '']
                                });
                            });
                        </script>
							<span class="cbrp-top-rated-sc"
                                  id="cbrp-top-rated-sc-<?php echo $newdata->post_id . '-' . $form_id; ?>"
                                  style="margin: 0;"></span>
                        <?php echo "<strong>" . (($newdata->per_post_rating_summary / 100) * 5) . "/5</strong> "; ?>
                        <a href="<?php echo get_permalink($newdata->post_id); ?>"><?php echo $newdata->post_title; ?></a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><?php esc_attr_e('No Results found', 'cbratingsystem'); ?></li>
            <?php endif; ?>
        </ul>


        <?php

        $out2 = ob_get_contents();

        ob_end_clean();

        return $out2;


    }


    /**
     * Auto integration method
     *
     * @param $content
     *
     * @return string
     */
    public static function main_content_with_rating_form($content)
    {
        global $post;

        $post_id           = $post->ID;
        $ratingFormEnabled = get_post_meta($post_id, '_cbrating_enable_ratingForm', true);


        //at first honor this post's own selection
        if ($ratingFormEnabled == '0') return $content; // go if  '1' or ''

        //globally default form id
        $defaultFormId = get_option('cbratingsystem_defaultratingForm');

        $form_id = (int)apply_filters('rating_form_array', $defaultFormId);


        $form   = '';
        $review = '';
        $output = $content;


        if ($form_id > 0) {

            CBRatingSystem::load_scripts_and_styles();
            CBRatingSystemTheme::build_custom_theme_css();


            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);

            if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return $output;


            $ratingFormArray['form_id'] = $form_id;
            $ratingFormArray['post_id'] = $post_id;


            //if single and disable on single, if home and disabled in home, if archive and disabled in archive return the content
            if ((is_singular() && $ratingFormArray['show_on_single'] == 0) || (is_home() && ($ratingFormArray['show_on_home'] == 0)) || (is_archive() && ($ratingFormArray['show_on_arcv'] == 0))) {
                return $content;
            }

            //if form not active then return content
            if (intval($ratingFormArray['is_active']) != 1) return $content;

            //if auto integration disabled then return content
            if ($ratingFormArray['position'] == 'none') return $content;

            if (class_exists('CBRatingSystemFront')) {

                $theme_key                    = get_option('cbratingsystem_theme_key', 'basic');
                $ratingFormArray['theme_key'] = $theme_key;

                $form_output = CBRatingSystemFront::add_ratingForm_to_content($ratingFormArray);
                $output      = ($ratingFormArray['position'] == 'top') ? $form_output . $content : $content . $form_output;
            }

            //review is alwayws appened at end of content whatever the form is appeneded before or after content.
            if (class_exists('CBRatingSystemFrontReview') && ($ratingFormArray['review_enabled'] == 1) && is_singular()) {
                $review = CBRatingSystemFrontReview::rating_reviews($ratingFormArray, $post_id);
                $output = $output . $review;
            }
        }

        return $output;
    }//end  main_content_with_rating_form


    /**
     * Standalone Rating Form
     *
     * @param $form_id
     * @param $post_id
     * @param string $theme_key
     * @param bool $showreview
     *
     * @return string
     */
    public static function standalonePostingRatingSystemForm($form_id, $post_id, $theme_key = '', $showreview = true)
    {


        $form   = '';
        $review = '';
        $output = '';

        $theme_key = ($theme_key == '') ? get_option('cbratingsystem_theme_key', 'basic') : $theme_key;
        $form_id   = apply_filters('rating_form_array', $form_id);

        CBRatingSystemTheme::build_custom_theme_css();
        CBRatingSystem::load_scripts_and_styles();


        if (is_int($form_id) || is_numeric($form_id)) {

            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
            if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return $output;

            if (intval($ratingFormArray['is_active']) != 1) return '';


            $ratingFormArray['form_id'] = $form_id;
            $ratingFormArray['post_id'] = $post_id;

            if (class_exists('CBRatingSystemFront')) {


                $ratingFormArray['theme_key'] = $theme_key;
                $ratingFormArray              = apply_filters('cbratingsystem_change_options', $ratingFormArray);

                $form_output = CBRatingSystemFront::add_ratingForm_to_content($ratingFormArray);
                $output      = $form_output;

            }


            //review section
            if (is_singular() && class_exists('CBRatingSystemFrontReview') && ($ratingFormArray['review_enabled'] == 1) && $showreview) {

                $review_output = CBRatingSystemFrontReview::rating_reviews_shorttag($ratingFormArray, $post_id, 0);
                $output        = $output . $review_output;

            }
        }

        return $output;

    }

    /**
     * Standalone Rating Form
     *
     * @param        $form_id
     * @param        $post_id
     * @param string $theme_key
     *
     * @return string
     */
    public static function cbrating_standalone_review($form_id, $post_id, $theme_key = '')
    {


        $form   = '';
        $review = '';
        $output = '';

        $theme_key = ($theme_key == '') ? get_option('cbratingsystem_theme_key', 'basic') : $theme_key;

        $form_id = apply_filters('rating_form_array', $form_id);


        if (is_int($form_id) || is_numeric($form_id)) {
            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
            if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return $output;

            $ratingFormArray['form_id'] = $form_id;
            $ratingFormArray['post_id'] = $post_id;


            $ratingFormArray['theme_key'] = $theme_key;
            $ratingFormArray              = apply_filters('cbratingsystem_change_options', $ratingFormArray);


            CBRatingSystemTheme::build_custom_theme_css();
            CBRatingSystem::load_scripts_and_styles();


            if (class_exists('CBRatingSystemFrontReview') && ($ratingFormArray['review_enabled'] == 1)) {
                //if(is_single() || is_page()){
                if (is_singular()) {
                    $review .= CBRatingSystemFrontReview::rating_reviews_shorttag($ratingFormArray, $post_id, 0);
                    $output = $output . $review;
                }
            }
        }

        return $output;

    }

    //end standalonePostingRatingSystemForm
    /**
     * post_meta_boxes_setup
     * add post meta box for rating
     */

    public static function post_meta_boxes_setup()
    {
        /* Add meta boxes on the 'add_meta_boxes' hook. */
        add_action('add_meta_boxes', array('CBRatingSystemMetaBox', 'add_post_meta_boxes'));

        /* Save post meta on the 'save_post' hook. */
        add_action('save_post', array('CBRatingSystemMetaBox', 'save_post_meta_data'), 10, 2);
    }

    /**
     * Cookie initialization for the every user
     */
    public static function init_cookie()
    {
        //global $current_user;

        if (is_user_logged_in()) {
            $cookie_value = 'user-' . get_current_user_id();
        } else {
            $cookie_value = 'guest-' . rand(CB_RATINGSYSTEM_RAND_MIN, CB_RATINGSYSTEM_RAND_MAX);
        }

        if (!isset($_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]) && empty($_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME])) {
            setcookie(CB_RATINGSYSTEM_COOKIE_NAME, $cookie_value, CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS, SITECOOKIEPATH, COOKIE_DOMAIN);

            //$_COOKIE var accepts immediately the value so it will be retrieved on page first load.
            $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] = $cookie_value;

        } elseif (isset($_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME])) {
            if (substr($_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME], 0, 5) != 'guest') {
                setcookie(CB_RATINGSYSTEM_COOKIE_NAME, $cookie_value, CB_RATINGSYSTEM_COOKIE_EXPIRATION_14DAYS, SITECOOKIEPATH, COOKIE_DOMAIN);

                //$_COOKIE var accepts immediately the value so it will be retrieved on page first load.
                $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME] = $cookie_value;
            }
        }
    }

    /**
     * Get the ip address of the user
     *
     * @return string|void
     */
    public static function get_ipaddress()
    {

        if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {

            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {

            $ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        if (strpos($ip_address, ',') !== false) {

            $ip_address = explode(',', $ip_address);
            $ip_address = $ip_address[0];
        }

        return $ip_address;
    }

    public static function form_default_criteria()
    {
        $form_criteria = array(
            'custom_criteria' => array
            (
                '0' => array
                (
                    'enabled' => 1,
                    'label'   => __('Criteria 1', 'cbratingsystem'),
                    'stars'   => array
                    (
                        '0' => array('enabled' => 1, 'title' => __('Worst', 'cbratingsystem')),
                        '1' => array('enabled' => 1, 'title' => __('Bad', 'cbratingsystem')),
                        '2' => array('enabled' => 1, 'title' => __('Not Bad', 'cbratingsystem')),
                        '3' => array('enabled' => 1, 'title' => __('Good', 'cbratingsystem')),
                        '4' => array('enabled' => 1, 'title' => __('Best', 'cbratingsystem'))
                    )

                ),
                '1' => array
                (
                    'enabled' => 1,
                    'label'   => __('Criteria 2', 'cbratingsystem'),
                    'stars'   => array
                    (
                        '0' => array('enabled' => 1, 'title' => __('Worst', 'cbratingsystem')),
                        '1' => array('enabled' => 1, 'title' => __('Bad', 'cbratingsystem')),
                        '2' => array('enabled' => 1, 'title' => __('Not Bad', 'cbratingsystem')),
                        '3' => array('enabled' => 1, 'title' => __('Good', 'cbratingsystem')),
                        '4' => array('enabled' => 1, 'title' => __('Best', 'cbratingsystem'))
                    )

                ),
                '2' => array
                (
                    'enabled' => 1,
                    'label'   => __('Criteria 3', 'cbratingsystem'),
                    'stars'   => array
                    (
                        '0' => array('enabled' => 1, 'title' => __('Worst', 'cbratingsystem')),
                        '1' => array('enabled' => 1, 'title' => __('Bad', 'cbratingsystem')),
                        '2' => array('enabled' => 1, 'title' => __('Not Bad', 'cbratingsystem')),
                        '3' => array('enabled' => 1, 'title' => __('Good', 'cbratingsystem')),
                        '4' => array('enabled' => 1, 'title' => __('Best', 'cbratingsystem'))
                    )

                )


            )
        );

        return $form_criteria;
    }

    public static function form_default_question()
    {
        $form_question = array(

            'custom_question' => array(
                '0' => array(
                    'title'    => __('Sample Question Title 1', 'cbratingsystem'),
                    'required' => 0,
                    'enabled'  => 0,
                    'field'    => array(
                        'type'     => 'checkbox',
                        'checkbox' => array(
                            'seperated' => 0,
                            'count'     => 2,
                            '0'         => array('text' => __('Yes', 'cbratingsystem')),
                            '1'         => array('text' => __('No', 'cbratingsystem')),
                            '2'         => array('text' => __('Correct', 'cbratingsystem')),
                            '3'         => array('text' => __('Incorrect', 'cbratingsystem')),
                            '4'         => array('text' => __('None', 'cbratingsystem'))
                        ),
                        'radio'    => array(
                            'count' => 2,
                            '0'     => array('text' => __('Yes', 'cbratingsystem')),
                            '1'     => array('text' => __('No', 'cbratingsystem')),
                            '2'     => array('text' => __('Correct', 'cbratingsystem')),
                            '3'     => array('text' => __('Incorrect', 'cbratingsystem')),
                            //'4'         => array('text' => __('None','cbratingsystem'))
                        )


                    )
                )
            )

        );

        return $form_question;
    }

    /**
     * Core extra fields
     *
     * @return array|mixed|void
     */
    public static function form_default_extra_fields()
    {
        $postTypes       = CBRatingSystem::post_types();
        $userRoles       = CBRatingSystem::user_roles();
        $editorUserRoles = CBRatingSystem::editor_user_roles();

        // 9 default extra fields  //note review field is now separeated
        $default_extra_fields = array(
            'view_allowed_users'         => array(
                'label'       => __('Allowed User Roles Who Can View Rating', 'cbratingsystem'),
                'desc'        => __('Which user group can view rating', 'cbratingsystem'),
                'type'        => 'multiselect',
                'user_types'  => true,
                'multiple'    => true,
                'placeholder' => __('Choose User Group ...', 'cbratingsystem'),
                'default'     => array('guest', 'administrator', 'editor'),
                'required'    => true,
                'options'     => $userRoles,
                'extrafield'  => true,
                'errormsg'    => __('You must give access to at least one User Group who can View Rating', 'cbratingsystem')
            ), //view allowed user

            'comment_view_allowed_users' => array(
                'label'       => __('Allowed User Roles Who Can View Rating Review', 'cbratingsystem'),
                'desc'        => __('Which user group can view rating', 'cbratingsystem'),
                'type'        => 'multiselect',
                'user_types'  => true,
                'multiple'    => true,
                'placeholder' => __('Choose User Group ...', 'cbratingsystem'),
                'default'     => array('guest', 'administrator', 'editor'),
                'required'    => true,
                'options'     => $userRoles,
                'extrafield'  => true,
                'errormsg'    => __('You must give access to at least one User Group who can View Comment', 'cbratingsystem')
            ), //review view allowed user
            'comment_required'           => array(
                'label'      => __('Comment required', 'cbratingsystem'),
                'desc'       => __('This option will make the comment box required', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 0,
                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true,

            ), //comment box while rating required
            'show_user_avatar_in_review' => array(
                'label'      => __('Author Avatar in Review', 'cbratingsystem'),
                'desc'       => __('Show/hide reviewer\'s profile picture or avatar in review', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 0,
                'tooltip'    => __('Control reviewers\'s avatar', 'cbratingsystem'),

                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true,

            ), // show user's avater or profile picture in review
            'show_user_link_in_review'   => array(
                'label'      => __('Show Author Link in Review', 'cbratingsystem'),
                'desc'       => __('Link user to their author page in each review', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 0,
                'tooltip'    => __('Control reviewers\'s link', 'cbratingsystem'),

                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true
            ), //show user's link/profile/author link in review
            'show_editor_rating'         => array(
                'label'      => __('Show Editor Rating', 'cbratingsystem'),
                'desc'       => __('Show/hide rating editor user group rating', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 0,
                'tooltip'    => __('Which user group is rating editor is selectable', 'cbratingsystem'),

                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true
            ), // show editor rating on frontend yes/no
            'review_enabled'             => array(
                'label'      => __('Show/Hide Reviews', 'cbratingsystem'),
                'desc'       => __('Control showing reviews on frontend', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 1,
                'tooltip'    => __('Enabled by default', 'cbratingsystem'),

                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true
            ), // show hide reviews
            'review_limit'               => array(
                'label'       => __('Review Limit Per Page', 'cbratingsystem'),
                'desc'        => __('How many reviews will be shown per page or in ajax request', 'cbratingsystem'),
                'type'        => 'text',
                'numeric'     => true,
                'default'     => 10,
                'tooltip'     => __('Review Limit', 'cbratingsystem'),
                'placeholder' => __('Review Limit', 'cbratingsystem'),
                'required'    => true,
                'extrafield'  => true,
                'errormsg'    => __('Review Limit is required, must be numeric value', 'cbratingsystem')
            ), //default per page reviews limit
            'email_verify_guest'         => array(
                'label'      => __('Guest User Email Verify', 'cbratingsystem'),
                'desc'       => __('Review from guest user will not be published instance if this is enabled, guest will need to verify email', 'cbratingsystem'),
                'type'       => 'radio',
                'default'    => 1,
                'required'   => false,
                'options'    => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'extrafield' => true
            )


        );


        $default_extra_fields = apply_filters('cbratingsystem_default_extra_fields', $default_extra_fields);

        return $default_extra_fields;
    }

    /**
     * @return array
     */
    public static function form_default_fields()
    {

        $postTypes       = CBRatingSystem::post_types();
        $userRoles       = CBRatingSystem::user_roles();
        $editorUserRoles = CBRatingSystem::editor_user_roles();

        $form_default = array(

            'id'                      => array(
                'type'    => 'hidden',
                'default' => 0
            ),
            'name'                    => array(
                'label'       => __('Form Title', 'cbratingsystem'),
                'desc'        => __('Write form name', 'cbratingsystem'),
                'type'        => 'text',
                'default'     => __('Example Rating Form', 'cbratingsystem'),
                'tooltip'     => __('Form Name', 'cbratingsystem'),
                'placeholder' => __('Rating Form Name', 'cbratingsystem'),
                'required'    => true,
                'min'         => 5,
                'max'         => 500,
                'errormsg'    => __('Form title missing or empty, maximum length 500, minimum length 5', 'cbratingsystem')
            ),

            'is_active'               => array(
                'label'    => __('Form Status', 'cbratingsystem'),
                'desc'     => __('Enable disable the form', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Enabled', 'cbratingsystem'),
                    '0' => __('Disabled', 'cbratingsystem')
                )

            ), // create the form but will be active or inactive

            'post_types'              => array(
                'label'       => __('Post Type Selection', 'cbratingsystem'),
                'desc'        => __('This form will work for the selected post types', 'cbratingsystem'),
                'type'        => 'multiselect',
                'multiple'    => true,
                'post_types'  => true,
                'default'     => array('post', 'page'),
                'tooltip'     => __('Post type selection, works with builtin or custom post types', 'cbratingsystem'),
                'placeholder' => __('Choose post type(s)...', 'cbratingsystem'),

                'required'    => true,
                'options'     => $postTypes,
                'errormsg'    => __('Post type is missing or at least one post type must be selected', 'cbratingsystem')
            ), // post type supports

            'show_on_single'          => array(
                'label'    => __('Show on single post/page', 'cbratingsystem'),
                'desc'     => __('Enable disable for single article', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            ), // show hide on single article pages

            'show_on_home'            => array(
                'label'    => __('Show on Home/Frontpage', 'cbratingsystem'),
                'desc'     => __('Enable disable for home/frontpage', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            ), //show on home or frontpage
            'show_on_arcv'            => array(
                'label'    => __('Show on Archives', 'cbratingsystem'),
                'desc'     => __('Enable disable for archive pages', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            ), //show on any kind of archive
            'position'                => array(
                'label'    => __('Auto Integration', 'cbratingsystem'),
                'desc'     => __('Enable disable for shortcode', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 'bottom',
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    'top'    => __('Top (Before Content)', 'cbratingsystem'),
                    'bottom' => __('Bottom (After Content)', 'cbratingsystem'),
                    'none'   => __('Disable Auto Integration', 'cbratingsystem')
                )
            ), //other possible, top and none
            'enable_shorttag'         => array(
                'label'    => __('Enable Shortcode', 'cbratingsystem'),
                'desc'     => __('Enable disable for shortcode', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            ), //enable disable shortcode
            'logging_method'          => array(
                'label'       => __('Logging Method', 'cbratingsystem'),
                'desc'        => __('Log user rating by ip or cookie or both to protect multiple rating, useful for guest rating', 'cbratingsystem'),
                'type'        => 'multiselect',
                'multiple'    => 'yes',

                'default'     => array('ip', 'cookie'),
                'tooltip'     => __('Log user rating for guest using ip and cookie', 'cbratingsystem'),
                'placeholder' => __('Choose logging method...', 'cbratingsystem'),

                'required'    => true,
                'options'     => array(
                    'ip'     => __('IP', 'cbratingsystem'),
                    'cookie' => __('Cookie', 'cbratingsystem')
                ),
                'errormsg'    => __('At least one logging method should be enabled', 'cbratingsystem')
            ), // Logging method

            'allowed_users'           => array(
                'label'       => __('Allowed User Roles Who Can Rate', 'cbratingsystem'),
                'desc'        => __('Which user group can rate article with this Rating Form', 'cbratingsystem'),
                'type'        => 'multiselect',
                'user_types'  => true,
                'placeholder' => __('Choose User Group ...', 'cbratingsystem'),
                'multiple'    => true,
                'default'     => array('administrator', 'editor'),
                'required'    => true,
                'options'     => $userRoles,
                'errormsg'    => __('You must select one user group for Rating Editor user', 'cbratingsystem')
            ),

            'editor_group'            => array(
                'label'       => __('Rating Editor User Group', 'cbratingsystem'),
                'desc'        => __('Which group of user will be Rating Editor', 'cbratingsystem'),
                'type'        => 'multiselect',
                'user_types'  => true,
                'placeholder' => __('Choose Rating Editor User Group ...', 'cbratingsystem'),
                'multiple'    => false,
                'default'     => 'administrator',
                'required'    => true,
                'options'     => $editorUserRoles,
                'errormsg'    => __('You must select one user group for Rating Editor user', 'cbratingsystem')
            ), //which group of users will be treated as editor  //administrator'

            'enable_comment'          => array(
                'label'    => __('Enable Comment', 'cbratingsystem'),
                'desc'     => __('Enable Comment with Rating', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            ), //enable comment box
            'comment_limit'           => array(
                'label'       => __('Comment Limit Length', 'cbratingsystem'),
                'desc'        => __('Comment limit length prevents user from submitting long comment', 'cbratingsystem'),
                'type'        => 'text',
                'default'     => 200,
                'numeric'     => true,
                'tooltip'     => __('Comment text length limit', 'cbratingsystem'),
                'placeholder' => __('Comment Length', 'cbratingsystem'),
                'required'    => true,
                'errormsg'    => __('Comment limit can not empty or must be numeric', 'cbratingsystem')
            ), //limit comment box char limit
            'enable_question'         => array(
                'label'    => __('Enable Question', 'cbratingsystem'),
                'desc'     => __('Enable Question with Rating', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),
                'required' => true,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                ),
                'errormsg' => __('Enable question field is missing or value must be 0 or 1', 'cbratingsystem')

            ), // Enable Questions
            'show_credit_to_codeboxr' => array(
                'label'    => __('Show Credit', 'cbratingsystem'),
                'desc'     => __('This will show a small link under rating form to codeboxr.com', 'cbratingsystem'),
                'type'     => 'radio',
                'default'  => 1,
                'tooltip'  => __('Enabled by default', 'cbratingsystem'),

                'required' => false,
                'options'  => array(
                    '1' => __('Yes', 'cbratingsystem'),
                    '0' => __('No', 'cbratingsystem')
                )
            )
        );

        $default_extra_fields = CBRatingSystem::form_default_extra_fields();
        $form_default         = array_merge($form_default, $default_extra_fields);

        return $form_default;
    }

    /**
     * @return array
     */
    public static function post_types()
    {
        $post_type_args = array(
            'builtin' => array(
                'options' => array(
                    'public'   => true,
                    '_builtin' => true,
                    'show_ui'  => true,
                ),
                'label'   => __('Built in post types', 'cbratingsystem'),
            )

        );

        $post_type_args = apply_filters('cbratingsystem_post_types', $post_type_args);

        $output    = 'objects'; // names or objects, note names is the default
        $operator  = 'and'; // 'and' or 'or'
        $postTypes = array();

        foreach ($post_type_args as $postArgType => $postArgTypeArr) {
            $types = get_post_types($postArgTypeArr['options'], $output, $operator);

            if (!empty($types)) {
                foreach ($types as $type) {
                    $postTypes[$postArgType]['label']              = $postArgTypeArr['label'];
                    $postTypes[$postArgType]['types'][$type->name] = $type->labels->name;
                }
            }
        }

        return $postTypes;

    }

    /**
     * Get the user roles for voting purpose
     *
     * @param string $useCase
     *
     * @return array
     */
    public static function user_roles($useCase = 'admin')
    {
        global $wp_roles;

        if (!function_exists('get_editable_roles')) {
            require_once(ABSPATH . '/wp-admin/includes/user.php');

        }

        $userRoles = array();

        switch ($useCase) {
            default:
            case 'admin':
                $userRoles = array(
                    'Anonymous'  => array(
                        'guest' => array(
                            'name' => __("Guest", 'cbratingsystem'),
                        ),
                    ),
                    'Registered' => get_editable_roles(),
                );
                break;

            case 'front':
                foreach (get_editable_roles() as $role => $roleInfo) {
                    $userRoles[$role] = $roleInfo['name'];
                }
                $userRoles['guest'] = __("Guest", 'cbratingsystem');
                break;
        }

        return apply_filters('cbratingsystem_userroles', $userRoles);
    }

    /**
     * get the editor user roles
     *
     * @param string $useCase
     *
     * @return array
     */
    public static function editor_user_roles($useCase = 'admin')
    {
        global $wp_roles;

        $userRoles = array();

        switch ($useCase) {
            default:
            case 'admin':
                $userRoles = array(
                    'Registered' => get_editable_roles(),
                );
                break;

            case 'front':
                foreach (get_editable_roles() as $role => $roleInfo) {
                    $userRoles[$role] = $roleInfo['name'];
                }
                break;
        }


        return $userRoles;

    }

    /**
     * @param        $roles
     * @param string $userId
     *
     * @return bool
     */
    public static function current_user_can_use_ratingsystem($roles, $userId = '')
    {
        //$allUserRoles = self::user_roles('front');

        if (!empty($userId)) {
            $user_id = get_userdata($userId); //echo $user_id;
        } else {
            $user_id = get_current_user_id(); //echo $user_id;
        }


        if (is_user_logged_in()) {
            $user = new WP_User($user_id);

            if (!empty($user->roles) && is_array($user->roles)) {

                $user->roles[] = 'guest';

                $intersectedRoles = array_intersect($roles, $user->roles);

            }
        } else {
            if (in_array('guest', $roles)) {
                $intersectedRoles = array('guest');

            }
            //$intersectedRoles = array('guest');
        }

        if (!empty($intersectedRoles)) {
            return true;
        }

        return false;
    }

    /**
     * @param        $roles
     * @param string $userId
     *
     * @return bool
     */
    public static function current_user_can_view_ratingsystem($roles, $userId = '')
    {

        if (!empty($userId)) {
            $user_id = get_userdata($userId); //echo $user_id;
        } else {
            $user_id = get_current_user_id(); //echo $user_id;
        }


        if (is_user_logged_in()) {
            $user = new WP_User($user_id);

            if (!empty($user->roles) && is_array($user->roles)) {

                $user->roles[] = 'guest';

                $intersectedRoles = array_intersect($roles, $user->roles);

            }
        } else {
            if (in_array('guest', $roles)) {
                $intersectedRoles = array('guest');
            }
        }

        if (!empty($intersectedRoles)) {
            return true;
        }

        return false;
    }

    /**
     * Can this form be marked as default form
     *
     * @param $formId
     *
     * @return bool
     */
    public static function can_automatically_make_deafult_form($formId)
    {
        global $wpdb;
        $table_name = CBRatingSystemData::get_ratingForm_settings_table_name();

        $sql = "SELECT COUNT(id) AS count FROM $table_name";

        $return = false;

        $count = $wpdb->get_var($sql);

        if (!($count > 1)) {
            $return = true;
        }
        if (!empty($formId)) {
            if ($formId == get_option('cbratingsystem_defaultratingForm')) {
                $return = false;
            }
        }

        return $return;
    }

    /**
     * @param $arr
     *
     * @return stdClass
     */
    public static function array_to_object($arr)
    {
        $post = new stdClass;
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $post->$key = $val;
            } else {
                $post->$key = trim(strip_tags($arr[$key]));
            }
        }

        return $post;
    }

    /**
     * Return the default form id with applying filter if form id set different from post id
     *
     * @return mixed|void
     */
    public static function get_default_ratingFormId()
    {
        $defaultFormId = get_option('cbratingsystem_defaultratingForm');


        $form_id = apply_filters('rating_form_array', $defaultFormId);


        return $form_id;
    }

    /**
     * Show avg rating for multiple posts for same form id,
     * calls from shortcode or direct function call or from widget
     *
     * @param     $option
     * @param int $show_title
     * @param int $show_form_id
     * @param int $show_text
     * @param int $show_star
     * @param int $show_single
     * @param int $text_label
     *
     * @return string
     */
    public static function standalone_singlePost_rating_summary($option, $show_title = 0, $show_form_id = 0, $show_text = 0, $show_star = 1, $show_single = 0, $text_label = '')
    {


        CBRatingSystem::load_scripts_and_styles();

        if ($option == null) return '';

        $post_ids = (!isset($option['post_id']) || !is_array($option['post_id']) || (sizeof($option['post_id']) == 0)) ? array(get_the_ID()) : $option['post_id'];
        $form_ids = (!isset($option['form_id']) || !is_array($option['form_id']) || (sizeof($option['form_id']) == 0)) ? array(self::get_default_ratingFormId()) : $option['form_id'];


        $rating_smmary = array('post' => array());
        $show          = '';


        if ($show_single == 1 || sizeof($post_ids) == 1) {

            $option['post_id'] = array($post_ids[0]);
            $option['form_id'] = array($form_ids[0]);

            $average_rating = CBRatingSystemData::get_ratings_summary($option);




            if (!sizeof($average_rating)) { //no rating found for this post and form id
                $average_rating['form_id']                 = $form_ids[0];
                $average_rating['post_id']                 = $post_ids[0];
                $average_rating['post_title']              = get_the_title($post_ids[0]);
                $average_rating['per_post_rating_summary'] = 0;
                $average_rating['found']                   = 0;

            } else {
                $average_rating          = $average_rating[0];
                $average_rating['found'] = 1;
            }

            $show .= '<div class="cbratingavgratelist" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';


            if ($show_title == 1) {
                $show .= '<p>' . esc_html__('Post: ', 'cbratingsystem') . $average_rating['post_title'] . '</p>';
            }
            if ($show_form_id == 1) {
                $show .= '<p>' . esc_html__('Form: ', 'cbratingsystem') . $average_rating['form_id'] . '</p>';
            }

            $text_label_html = '';
            if ($show_text == 1) {

                $text_label_html = '<span class="cbrp-alone-rated_label_html" ><strong itemprop="ratingValue">' . $text_label . number_format((($average_rating['per_post_rating_summary'] / 100) * 5), 2) . '</strong>/<strong itemprop="bestRating">5</strong></span>';
            }


            $star_rating_class = '';
            if ($show_star == 1) {
                $star_rating_class .= 'cbratingreview_listing';

                $single_score_avg  = (($average_rating['per_post_rating_summary'] / 100) * 5);
                $single_score_full = 5;


                $show .= '  <span class="' . $star_rating_class . ' cbrp-alone-rated cbrp-alone-rated' . $average_rating['post_id'] . '" id="cbrp-alone-rated' . $average_rating['post_id'] . '" style="margin: 0;" data-scoreavg="' . $single_score_avg . '" data-scorefull="' . $single_score_full . '"></span>' . $text_label_html . '';
                $show .= '';
            }

            $show .= '</div>'; //end .cbratingavgratelist

        } else {

            if (!empty($post_ids) && !empty($form_ids)) {

                foreach ($post_ids as $post_id) {

                    $option = array();

                    $option['form_id'] = $form_ids;
                    $option['post_id'] = array($post_id);


                    $average_rating = CBRatingSystemData::get_ratings_summary($option);



                    if (!sizeof($average_rating)) {
                        $average_rating['form_id']                 = $form_ids[0];
                        $average_rating['post_id']                 = $post_ids[0];
                        $average_rating['post_title']              = get_the_title($post_ids[0]);
                        $average_rating['per_post_rating_summary'] = 0;
                        $average_rating['found']                   = 0;

                    } else {
                        $average_rating          = $average_rating[0];
                        $average_rating['found'] = 1;
                    }


                    $show .= '<div class="cbratingavgratelist" style="position:relative;">';
                    if ($show_title == 1) {
                        $show .= '<p>' . esc_html__('Post: ', 'cbratingsystem') . $average_rating['post_title'] . '</p>';
                    }
                    if ($show_form_id == 1) {
                        $show .= '<p>' . esc_html__('Form: ', 'cbratingsystem') . $average_rating['form_id'] . '<p>';
                    }


                    $text_label_html = '';
                    if ($show_text == 1) {

                        $text_label_html = '<span class="cbrp-alone-rated_label_html" ><strong itemprop="ratingValue">' . $text_label . number_format((($average_rating['per_post_rating_summary'] / 100) * 5), 2) . '</strong>/<strong itemprop="bestRating">5</strong></span>';
                    }


                    $star_rating_class = '';
                    if ($show_star == 1) {
                        $star_rating_class .= 'cbratingreview_listing';

                        $single_score_avg  = (($average_rating['per_post_rating_summary'] / 100) * 5);
                        $single_score_full = 5;

                        $show .= '  <span class="' . $star_rating_class . ' cbrp-alone-rated cbrp-alone-rated' . $average_rating['post_id'] . '" id="cbrp-alone-rated' . $post_id . '" style="margin: 0;"   data-scoreavg="' . $single_score_avg . '" data-scorefull="' . $single_score_full . '"></span>' . $text_label_html;
                    }
                    $show .= '</div>';

                }


            }

        }

        return $show;
    }

    /**
     * @return array
     */
    public static function user_role_label()
    {
        return array(
            'guest'      => esc_html__('Guest Users', 'cbratingsystem'),
            'registered' => esc_html__('Registered Users', 'cbratingsystem'),
            'editor'     => esc_html__('Editor Users', 'cbratingsystem'),
        );
    }

    function cbrating_if_col_exists($table_name, $column_name)
    {
        global $wpdb;
        foreach ($wpdb->get_col("DESC $table_name", 0) as $column) {
            if ($column == $column_name) {
                return true;
            }
        }

        return false;
    }

    public static function deleteRatingsandLogs()
    {
        add_action('delete_post', array('CBRatingSystem', 'deleteRatingsandLogs_sync'), 10);
    }


    /**
     * Delete user rating and avg logs as per post delete
     *
     * @param $post_id
     */
    public static function deleteRatingsandLogs_sync($post_id)
    {
        global $wpdb;

        $avg_summary_table = CBRatingSystemData::get_user_ratings_summury_table_name(); //cbratingsystem_ratings_summary
        $user_log_table    = CBRatingSystemData::get_user_ratings_table_name(); //cbratingsystem_user_ratings

        $wpdb->query($wpdb->prepare("DELETE FROM $avg_summary_table WHERE post_id = %d", $post_id));
        $wpdb->query($wpdb->prepare("DELETE FROM $user_log_table WHERE post_id = %d", $post_id));

    }


}

//end class cbratingsystem

//init the plugin
new CBRatingSystem();


/**
 * This function  add standalone rating form or custom rating form call.
 * This will add both rating form and review list both based on form setting and permission
 *
 * Also it'll not be cached just like as the comment system of wp
 *
 * @param $form_id      rating form id, if empty then will use the default one
 * @param $post_id      post id, if empty then will use the post id from the loop
 * @param $theme_key    if empty then will use from default setting
 * @param $showreview   shows review if true
 *
 * @return string
 */
function standalonePostingRatingSystem($form_id = '', $post_id = '', $theme_key = 'basic', $showreview = true)
{

    global $post;

    if ($form_id == '') {
        $form_id = get_option('cbratingsystem_defaultratingForm');
        $form_id = apply_filters('rating_form_array', $form_id);

    }

    $form_id = intval($form_id);

    //need to add translation
    if ($form_id == 0) return esc_html__('Form id not found', 'cbratingsystem');

    // get the id of the current post via param or db
    if ($post_id == '') {
        $post_id = $post->ID;
    }


    if (!is_int($post_id)) return esc_html__('Post id not found', 'cbratingsystem');


    return CBRatingSystem::standalonePostingRatingSystemForm($form_id, $post_id, $theme_key, $showreview);
}

//end standalonePostingRatingSystem

/**
 * Show avg rating for multiple posts for same form id,
 * calls from shortcode or direct function call or from widget
 *
 * @param     $option
 * @param int $show_title
 * @param int $show_form_id
 * @param int $show_text
 * @param int $show_star
 * @param int $show_single
 * @param int $text_label
 *
 * @return string
 */

function standaloneSinglePostRatingSummary($option, $show_title = 0, $show_form_id = 0, $show_text = 0, $show_star = 1, $show_single = 1, $text_label = '')
{
    return CBRatingSystem::standalone_singlePost_rating_summary($option, $show_title, $show_form_id, $show_text, $show_star, $show_single, $text_label);
}

/**
 * Standalone Reviews  for a post for any form id
 *
 * @param        $form_id
 * @param        $post_id
 * @param string $theme_key
 *
 * @return string
 */
function cbratingStandaloneReviews($form_id, $post_id, $theme_key = 'basic', $page = 1, $limit = 10)
{
    $form   = '';
    $review = '';
    $output = '';

    $theme_key = ($theme_key == '') ? get_option('cbratingsystem_theme_key', 'basic') : $theme_key;

    $form_id = apply_filters('rating_form_array', $form_id);


    if (is_int($form_id) || is_numeric($form_id)) {
        $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);

        //if the rating form not found
        if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return $output;

        $ratingFormArray['form_id']      = $form_id;
        $ratingFormArray['post_id']      = $post_id;
        $ratingFormArray['review_limit'] = $limit;


        $ratingFormArray['theme_key'] = $theme_key;
        $ratingFormArray              = apply_filters('cbratingsystem_change_options', $ratingFormArray);


        CBRatingSystemTheme::build_custom_theme_css();
        CBRatingSystem::load_scripts_and_styles();


        $review .= CBRatingSystemFrontReview::rating_reviews_shorttag($ratingFormArray, $post_id, $page);

        if (!empty($review)) {

            $output = $output . $review;
        }

    }

    return $output;

}

/**
 * Get Latest Reviews
 *
 * @param array $arr
 *
 * return html
 */
function cbratingGetLatestReviews($arr = array())
{
    //if not form id set then return empty
    if (!isset($arr['form_id'])) return '';

    global $wpdb;

    $form_id   = $arr['form_id']; //form id is must //for multiple use comma
    $post_id   = isset($arr['post_id']) ? $arr['post_id'] : ''; //for multiple use comma
    $user_id   = isset($arr['user_id']) ? $arr['user_id'] : ''; //for multiple use comma
    $post_type = isset($arr['post_type']) ? $arr['post_type'] : 'post'; //for multiple use comma


    $order      = isset($arr['order']) ? $arr['order'] : 'DESC';
    $order_by   = isset($arr['order_by']) ? $arr['order_by'] : 'created'; //created, post_id, post_title, form_id, post_type, user_id, avg, id, comment_status
    $limit      = isset($arr['limit']) ? $arr['limit'] : 10;


    $show_comment  = isset($arr['show_comment']) ? intval($arr['show_comment']) : 1; //show comment
    $show_criteria = isset($arr['show_criteria']) ? intval($arr['show_criteria']) : 0; //show criteria based rating
    $show_post     = isset($arr['show_post']) ? intval($arr['show_post']) : 1; //show post title with link

    $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
    if (!is_array($ratingFormArray) || (is_array($ratingFormArray) && sizeof($ratingFormArray) == 0)) return '';


    $limit_arr            = array();
    $limit_arr['perpage'] = $limit;
    $limit_arr['page']    = 1;

    $form_id   = explode(',', $form_id);
    $post_id   = explode(',', $post_id);
    $user_id   = explode(',', $user_id);
    $post_type = explode(',', $post_type);

    $status      = array('approved');
    $post_status = array('publish');


    CBRatingSystemTheme::build_custom_theme_css();
    CBRatingSystem::load_scripts_and_styles();


    $reviews = CBRatingSystemData::get_user_ratings_with_ratingForm($form_id, $post_id, $user_id, '', $order_by, $order, $limit_arr, true, '', $post_type, $status, $post_status);

    if (!empty($reviews) && is_array($reviews)) {

        $html = '<div class="cbxrating_latestreview_wrap">';
        //reviews loop
        foreach ($reviews as $review) {


            $review_post_id = $review->post_id;

            if (!empty($review->rating) && is_array($review->rating)) {
                $html .= '<div class="cbxrating_latestreview_single">';

                if ($show_post) {
                    $html .= '<p class="cbxrating_latestreview_post"><a href="' . get_permalink($review_post_id) . '">' . get_the_title($review_post_id) . '</a></p>';

                }
                if (intval($review->user_id) > 0) {

                    $user_url = get_author_posts_url($review->user_id);
                    $name     = get_the_author_meta('display_name', $review->user_id);
                    $gravatar = '';

                    //finally check the settings
                    if ($ratingFormArray ['show_user_avatar_in_review'] == '1') {
                        $gravatar = get_avatar($review->user_id, 36);
                        $gravatar = apply_filters('cbrating_single_review_user_avatar', $gravatar, $review->user_id, $ratingFormArray, $review);
                    }


                    $name = apply_filters('cbrating_single_review_user_name', $name, $review->user_id, $ratingFormArray, $review);

                    $user_html = '<span  class="user_gravatar">' . $gravatar . '<span >' . $name . '</span>' . '</span>';

                    if (!empty($user_url) && $ratingFormArray ['show_user_link_in_review'] == '1') {
                        $user_url  = apply_filters('cbrating_single_review_user_link', $user_url, $review->user_id, $ratingFormArray, $review);
                        $user_html = '<a target="_blank" href="' . $user_url . '">' . $user_html . '</a>';
                    }

                } else {
                    //guest part

                    $gravatar = '';
                    $name     = (!empty($review->user_name) ? $review->user_name : esc_html__('Anonymous', 'cbratingsystem'));

                    if ($ratingFormArray ['show_user_avatar_in_review'] == '1') {
                        $gravatar = get_avatar(0, 36, 'gravatar_default');
                        $gravatar = apply_filters('cbrating_single_review_user_avatar', $gravatar, 0, $ratingFormArray, $review);
                    }


                    $name = apply_filters('cbrating_single_review_user_name', $name, $review->user_id, $ratingFormArray, $review);

                    $user_html = '<span  class="user_gravatar">' . $gravatar . '.<span >' . $name . '</span></span>';
                }
                //end user part


                //$modified_review = (array)$review;

                $single_score_avg  = (($review->average / 100) * 5);
                $single_score_full = 5;


                $html .= '<p class="cbxrating_latestreview_avg">' . $user_html . '<span class="cbratingreview_listing" data-scoreavg="' . $single_score_avg . '" data-scorefull="' . $single_score_full . '"></span><span class="user_rate_value"  title="' . sprintf(__('Rated %s out of 5', 'cbratingsystem'), $single_score_avg) . '">(<strong>' . $single_score_avg . '</strong> ' . esc_html__('out of', 'cbratingsystem') . '  <strong>5</strong> )</span>'
                    . '</p>';

                // Comment Display part
                if (!empty($review->comment) && is_string($review->comment) && $show_comment) {
                    $comment = $review->comment;
                    $html .= '<p class="cbxrating_latestreview_comment">' . $comment . '</p>';
                }

                if ($show_criteria) {
                    foreach ($review->rating as $criteriId => $value) {

                        if (is_numeric($criteriId)) {

                            $value = (($value / 100) * $review->rating[$criteriId . '_starCount']);

                            $single_score_avg   = $value;
                            $single_score_full  = intval($review->rating[$criteriId . '_starCount']);
                            $single_score_hints = implode(",", $review->rating[$criteriId . '_stars']);


                            $html .= '<p  class="cbxrating_latestreview_criteria">
											<span>
												<strong>' . esc_html($review->custom_criteria[$criteriId]['label']) . '</strong>
											</span>
											<span class="cbratingreview_listing cbratingreview_listing_criteria" data-scoreavg="' . $single_score_avg . '" data-scorefull="' . $single_score_full . '" data-hints="' . $single_score_hints . '"></span>
											<span class="starTitle">' . (esc_html($review->rating[$criteriId . '_stars'][($value - 1)])) . '</span>															
										 </p> ';

                        }
                    }
                    //end loop
                }


                $html .= '</div>'; //cbxrating_latestreview_single
            }
        }
        $html .= '</div>'; //cbxrating_latestreview_wrap

        return $html;
    } else return '';


}

if (!function_exists('cbxdump')) {
    function cbxdump($arr)
    {
        if (is_array($arr) || is_object($arr)):
            echo '<pre>';
            print_r($arr);
            echo '</pre>';
        else:
            var_dump($arr);
        endif;

    }
}

/**
 * Display's date based on user time zone
 *
 * @param $created mysql timestamp
 *
 * @return string  formatted date
 */


/**
 * Display's date based on user time zone
 *
 * @param $created                 mysql timestamp
 * @param bool $usetimezone        if use timezone offset from wordpress general setting
 * @param string $date_time_format custom date time format
 *
 * @return string
 */
function cbratingsystem_date_display($created, $usetimezone = true, $date_time_format = '')
{

    $date_format = get_option('date_format');
    $time_format = get_option('time_format');

    if ($date_time_format == '') {
        $date_time_format = $date_format . ' ' . $time_format;
    }

    if ($usetimezone) {
        $created = $created + (get_option('gmt_offset') * HOUR_IN_SECONDS);
    }

    return date_i18n($date_time_format, $created);
}