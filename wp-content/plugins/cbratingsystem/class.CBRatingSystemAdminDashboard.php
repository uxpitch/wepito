<?php

/**
 * Class CBRatingSystemAdminDashboard
 */
class CBRatingSystemAdminDashboard extends CBRatingSystemAdmin
{

   /* public static function display_admin_dashboard()
    {
        self::build_admin_dashboard();
    }*/

    /**
     * [build_admin_dashboard description]
     *
     * @return [type]
     */
    public static function build_admin_dashboard()
    {
        //$data = CBRatingSystemData::get_ratings_summary();


        $formPath   = admin_url('admin.php?page=ratingformedit');
        $reviewPath = admin_url('admin.php?page=rating_reports');

        $ratingForms     = CBRatingSystemData::get_ratings_summary_with_ratingForms(true);
        $totalRatingForm = count($ratingForms);

        $defaultFormId = get_option('cbratingsystem_defaultratingForm');
        ?>

        <div class="wrap">

            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('CBX Rating & Review: Multi Criteria Rating System Dashboard', 'cbratingsystem') ?></h2>

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <!-- main content -->
                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">

                            <div class="postbox">

                                <h3><span><?php _e('Overview', 'cbratingsystem'); ?></span></h3>

                                <div class="inside">

                                    <?php
                                    $url = get_option('siteurl');
                                    $url .= '/wp-admin/admin.php?page=ratingformedit';
                                    ?>
                                    <table class="widefat">
                                        <thead>
                                        <tr>
                                            <th class="row-title">
                                                <?php _e('Default', 'cbratingsystem'); ?>
                                            </th>
                                            <th>
                                                <?php _e('Form', 'cbratingsystem'); ?>
                                            </th>
                                            <th>
                                                <?php _e('Shortcode', 'cbratingsystem'); ?>
                                            </th>
                                            <th><?php _e('Reviews', 'cbratingsystem'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (!empty($ratingForms)):
                                            foreach ($ratingForms as $k => $ratingForm) :
                                                $oddEvenClass = ($k % 2) ? 'odd' : 'even';
                                                $title        = ($ratingForm->name) ? $ratingForm->name : "";
                                                $link         = $formPath . '&id=' . $ratingForm->id;

                                                $reviewLink   = $reviewPath . '&form=' . $ratingForm->id;

                                                $reviewsCount = ($ratingForm->count) ? $ratingForm->count : 0;
                                                $reviewsText  = ((($reviewsCount) > 1) ? __('Reviews', 'cbratingsystem') : __('Review', 'cbratingsystem'));



                                                    ?>
                                                    <tr class="<?php echo $oddEvenClass; ?>">

                                                        <?php if ($defaultFormId != $ratingForm->id) : ?>
                                                        <td class="first b b_pages">
                                                            <span><img  src="<?php echo plugins_url('/images/star-off-big.png', __FILE__); ?>"/></span>
                                                        </td>
                                                        <?php else: ?>
                                                        <td class="first b b_pages">
                                                            <span><img title="<?php _e('Default Rating form','cbratingsystem'); ?>" alt="<?php _e('Default Rating form','cbratingsystem'); ?>"
                                                                       src="<?php echo plugins_url('/images/star-on-big.png', __FILE__); ?>"/></span>
                                                        </td>
                                                        <?php endif; ?>
                                                        <td class="t pages">
                                                            <a href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                                            - <?php _e('ID', 'cbratingsystem'); ?>
                                                            : <?php echo $ratingForm->id; ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            echo '<span id="cbratingsystemshortcode-' . $ratingForm->id . '" class="cbratingsystemshortcode cbratingsystemshortcode-' . $ratingForm->id . '">[cbratingsystem form_id="' . $ratingForm->id . '"]</span>';
                                                            echo '<span class="cbratingsystemshortcodetrigger" data-clipboard-text=\'[cbratingsystem form_id="'.$ratingForm->id.'"]\' title="' . __("Copy to clipboard", 'cbratingsystem') . '">
                        <img style="width: 16px;" src="' . plugins_url('images/clippy.png', __FILE__) . '" alt="' . __('Copy to clipboard', 'cbratingsystem') . '"/>
                     </span>';

                                                            echo '<div class="cbclear"></div>';

                                                            ?>
                                                        </td>
                                                        <td class="t pages">
                                                            <?php

                                                            $review_arr = CBRatingSystemData::getReviewsCountByStatus($ratingForm->id);

                                                           /* $status_arr  = array(
                                                                'all'               => __('Total (%d)', 'cbratingsystemaddon'),
                                                                'approved'          => __('Approved (%d)', 'cbratingsystemaddon'),
                                                                'unapproved'        => __('Unapproved (%d)', 'cbratingsystemaddon'),
                                                                'spam'              => __('Spam (%d)', 'cbratingsystemaddon'),
                                                                'verified'          => __('Verified (%d)', 'cbratingsystemaddon'),
                                                                'unverified'        => __('Unverified (%d)', 'cbratingsystemaddon'),
                                                            );*/

                                                            $status_arr  = array(
                                                                'all'           => __('Total (%d)', 'cbratingsystem'),
                                                            );

                                                            $status_arr = apply_filters('cbratingsystem_status_count_arr', $status_arr);

                                                            $status_html = array();
                                                            foreach($status_arr as $status_key => $status_str){
                                                                if($status_key != 'all'){
                                                                    $reviewLink .= '&comment_status='.$status_key;
                                                                }
                                                                if(isset($review_arr[$status_key])){
                                                                    $status_html[] = '<a target="_blank" href="'.$reviewLink.'">'.sprintf($status_str,$review_arr[$status_key] ).'</a>';
                                                                }
                                                                else {
                                                                    $status_html[]= '<a target="_blank" href="'.$reviewLink.'">'.sprintf($status_str, 0 ).'</a>';
                                                                }
                                                            }

                                                            echo implode(' | ', $status_html);
                                                            ?>

                                                        </td>
                                                    </tr>
                                                <?php

                                            endforeach;
                                        endif;
                                        ?>
                                        </tbody>
                                    </table>
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
}