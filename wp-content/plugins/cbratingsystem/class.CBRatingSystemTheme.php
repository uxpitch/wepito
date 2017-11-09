<?php

//namespace CBRatingSystem;
/**
 * Class CBRatingSystemTheme
 */
class CBRatingSystemTheme
{
    /**
     * @return string
     */
    public static function admin_ratingForm_theme_settings()
    {
        $output = '';
        $output .= self::build_admin_ratingForm_theme_settings();

        return $output;
    }

    /**
     * build_admin_ratingForm_theme_settings
     */
    public static function build_admin_ratingForm_theme_settings()
    {


        $post            = array();
        $class           = '';
        $saved_theme_key = get_option('cbratingsystem_theme_key', 'basic');



        if (!empty($_POST) && check_admin_referer('cbrp_theme_settings_form_nonce_action', 'cbrp_theme_settings_form_nonce_field')) {

            $post = $_POST['ratingFormTheme'];


            $validation          = array();
            $validation['error'] = false;


            $saved_theme_key = isset($post['theme']) ? sanitize_text_field($post['theme']) : $saved_theme_key;


            update_option('cbratingsystem_theme_key', $saved_theme_key);


            $validation = apply_filters('cbratingsystem_theme_validation', $validation, $post);


            if (($validation['error'] === false)) {


                $validation['message'][] = __("Theme setting saved successfully", 'cbratingsystem');
            } elseif ($validation['error'] === true) {
                $validation['message'][] = __('Error saving theme setting', 'cbratingsystem'); //CB_RATINGSYSTEM_PLUGIN_SLUG_NAMEwrong with the form submission', CB_PLUGIN_SLUG_NAME);
            }


        }
        ?>
        <div class="wrap">

            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('CBX Rating & Review: Theme Settings', 'cbratingsystem') ?></h2>

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <!-- main content -->
                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">

                            <div class="postbox">

                                <h3><span><?php _e("Theme Option", 'cbratingsystem'); ?></span></h3>

                                <div class="inside">
                                    <?php if (isset($validation['message']) && sizeof($validation['message']) > 0): ?>

                                        <div
                                            class="messages <?php echo $class . (($validation['error'] === false) ? ' status' : ' error'); ?>">
                                            <?php
                                            foreach ($validation['message'] as $message) {
                                                echo '<p>' . $message . '</p>';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="table table_content">
                                        <div class="cbrp_theme_settings_form_wrapper"
                                             id="cbrp_theme_settings_form_wrapper">
                                            <form name="cbrp_theme_settings_form" method="post"
                                                  id="cbrp_theme_settings_form_wrapper">
                                                <div class="">
                                                    <table>
                                                        <tr>
                                                            <td style="width:20%;">
                                                                <label
                                                                    for="ratingformthemeselection"><?php _e("Choose Theme", 'cbratingsystem'); ?></label>
                                                            </td>
                                                            <td>
                                                                <div class="">
                                                                    <?php

                                                                    $themes = array(
                                                                        'basic' => __("Basic Theme", 'cbratingsystem')
                                                                    );

                                                                    $themes = apply_filters('cbratingsystem_theme_options', $themes);

                                                                    ?>
        <select id="ratingformthemeselection"   name="ratingFormTheme[theme]" class="">
            <?php
            foreach ($themes as $theme_key => $theme_name) :
                $selected = ($theme_key == $saved_theme_key) ? 'selected ' : '';
                ?>
                <option  <?php echo $selected; ?>value="<?php echo $theme_key; ?>"><?php echo $theme_name; ?></option>
            <?php
            endforeach;
            ?>
        </select>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        do_action('cbratingsystem_theme_custom_option_wrapper', $post)

                                                        ?>

                                                        <!--/td>
                                                    </tr-->
                                                    </table>


                                                </div>

                                                <div class="cbrp_theme_settings_submit">
                                                    <input class="button-primary" name="cbrp_theme_settings"
                                                           type="submit" value="Save Theme Settings"/>
                                                    <?php wp_nonce_field('cbrp_theme_settings_form_nonce_action', 'cbrp_theme_settings_form_nonce_field'); ?>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                                <!-- .inside -->

                            </div>
                            <!-- .postbox -->

                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->

                    </div>
                    <!-- post-body-content -->

                    <!-- sidebar -->
                    <div id="postbox-container-1" class="postbox-container">

                        <div class="meta-box-sortables">

                            <div class="postbox">

                                <h3><span><?php _e('Plugin Information', 'cbratingsystem'); ?></span></h3>

                                <div class="inside">
                                    <?php
                                    define('CB_RATINGSYSTEM_SUPPORT_VIDEO_DISPLAY', true);
                                    require(CB_RATINGSYSTEM_PATH . '/cb-sidebar.php');
                                    ?>
                                </div>
                                <!-- .inside -->

                            </div>
                            <!-- .postbox -->

                        </div>
                        <!-- .meta-box-sortables -->

                    </div>
                    <!-- #postbox-container-1 .postbox-container -->

                </div>
                <!-- #post-body .metabox-holder .columns-2 -->

                <br class="clear">
            </div>
            <!-- #poststuff -->

        </div> <!-- .wrap -->

    <?php
    }


    /**
     *build_custom_theme_css
     */
    public static function build_custom_theme_css()
    {
        $saved_theme_key      = get_option('cbratingsystem_theme_key', 'basic');
        $saved_theme_settings = maybe_unserialize(get_option('cbratingsystem_theme_settings'));
        $output               = '';



        if ($saved_theme_key == 'custom') {

            if (!empty($saved_theme_settings['theme_settings']['custom'])) {

                $output .= '<style type="text/css">';
                $output .= '
                            .cbrp_container_' . $saved_theme_key . '_theme {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .readonly_criteria_wrapper_' . $saved_theme_key . '_theme, .criteria_star_hint_' . $saved_theme_key . '_theme, .criteria_label_wrapper_' . $saved_theme_key . '_theme {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .cbratingsystem-tabswitch {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .cbratingsystem-tabswitch:hover, .cbrp_container_' . $saved_theme_key . '_theme .cbratingsystem-tabswitch-active {
                                color: #000000;
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .form-required {
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme .reviews_rating_' . $saved_theme_key . '_theme:hover {
                                background: ' . $saved_theme_settings['theme_settings']['custom']['bg_color'] . ';
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme a, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a .reviews_wrapper_' . $saved_theme_key . '_theme .user_rate_time a {
                                font-weight: bold;
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            .reviews_wrapper_' . $saved_theme_key . '_theme a:hover, .reviews_wrapper_' . $saved_theme_key . '_theme .cbrating_user_name a:hover, .reviews_wrapper_' . $saved_theme_key . '_theme .user_rate_time a:hover {
                                font-weight: bold;
                                color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                                text-decoration: underline;
                            }
                            .cbrp_container_' . $saved_theme_key . '_theme .ratingFormStatus.error_message {
                                border-color: ' . $saved_theme_settings['theme_settings']['custom']['text_color'] . ';
                            }
                            ';
                $output .= '</style>';
            }
        }

        echo $output;
    }
    // end of build custom css

}// end of class