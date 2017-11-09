<?php
/**
 * Class CBRatingSystemFront
 */
class CBRatingSystemFront
{

    /**
     * Front end content builder function
     *
     * @param $ratingFormArray
	 *
	 * @return string
     */
    public static function add_ratingForm_to_content($ratingFormArray)
    {

        global $post, $wpdb, $wp_roles;
		global $current_user;


        $post_id   = (isset($ratingFormArray['post_id']) && $ratingFormArray['post_id'] != '') ? $ratingFormArray['post_id'] : get_the_ID();
        $post_id   = (int)$post_id;

        $post_type = get_post_type($post_id);
        $user_id   = get_current_user_id();


        $theme_key = $ratingFormArray['theme_key'];



        $whrOpt['form_id'][] = $ratingFormArray['id'];
        $whrOpt['post_id'][] = $post_id;



        if ($user_id == 0) {
            $userRoles = array('guest');
        } else {

            $userRoles = $current_user->roles;
        }





        //registered user or guest user
        if ($user_id == 0) {
            $user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();

        } elseif ($user_id > 0) {
            $user_session = 'user-' . $user_id; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();
            $user_info    = get_userdata($user_id);
        }


        //if form is not active then return notihng
        if(intval($ratingFormArray['is_active']) != 1 ) return '';

        //if current post type is not supported
        if(!in_array($post->post_type, $ratingFormArray['post_types'])) return '';



        $summary_table = CBRatingSystemData::get_user_ratings_summury_table_name();
        $userlog_table = CBRatingSystemData::get_user_ratings_table_name();
        $form_table    = CBRatingSystemData::get_ratingForm_settings_table_name();


        $sql_get_loggin = "SELECT logging_method FROM $form_table where id=" . $ratingFormArray['id'];
        $query_result   = $wpdb->get_results($sql_get_loggin);
        $query_result2  = maybe_unserialize($query_result[0]->logging_method);

        //getting the data according to the administrative settings using IP/Cookie for the last comment

        $count = 0;

        if ($user_id > 0) {
            $sql   = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d", $ratingFormArray['id'], $post_id, $user_id);
            $count = $wpdb->get_var($sql);
            //}
        } else {
            //for guest
            if (in_array("cookie", $query_result2) && !in_array("ip", $query_result2)) {
                $sql   = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_session = %s", $ratingFormArray['id'], $post_id, $user_session);
                $count = $wpdb->get_var($sql);
            } else if (!in_array("cookie", $query_result2) && in_array("ip", $query_result2)) {
                $sql   = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_ip = %s", $ratingFormArray['id'], $post_id, $user_ip);
                $count = $wpdb->get_var($sql);
            } else {
                $sql   = $wpdb->prepare("SELECT COUNT(ur.id) AS count FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_ip = %s AND ur.user_session = %s", $ratingFormArray['id'], $post_id, $user_ip, $user_session);
                $count = $wpdb->get_var($sql);
            }
        }



        //get rating summary
        $avgRatingData = CBRatingSystemData::get_ratings_summary($whrOpt);

        if ($user_id == 0) {
            $isUserSubmittedRating = CBRatingSystemData::get_user_ratings_with_ratingForm(array($ratingFormArray['id']), array($post_id), array($user_id), $user_session);
        } elseif ($user_id > 0) {
            $isUserSubmittedRating = CBRatingSystemData::get_user_ratings_with_ratingForm(array($ratingFormArray['id']), array($post_id), array($user_id));
        }


        $user_can_give_vote = (CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['allowed_users']) && $count < 1);
        $user_can_edit_vote = (CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['allowed_users']) &&  $count == 1 && defined('CB_RATINGSYSTEMADDON_PLUGIN_VERSION') && isset($ratingFormArray['comment_edit_allowed_users']) &&  CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['comment_edit_allowed_users']));
        $user_can_view_vote = CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['allowed_users']) || CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['view_allowed_users']);




        $submit_data = isset($isUserSubmittedRating[0])? $isUserSubmittedRating[0]: array();

        //if at least user can not view rating summery we can completely return ''
        if(!$user_can_view_vote) return '';


        if (sizeof($avgRatingData) > 0 && $avgRatingData[0]['per_post_rating_summary'] > 100) {


            $ratingAverage                  = self::viewPerCriteriaRatingResult($ratingFormArray['id'], $post_id, $user_id);

            $perPostAverageRating           = $ratingAverage['perPost'][$post_id];
            $perCriteriaAverageRating       = $ratingAverage['avgPerCriteria'];
            $customPerPostAverageRating     = $ratingAverage['customUser']['perPost'];
            $customPerCriteriaAverageRating = $ratingAverage['customUser']['perCriteria'];
            $customPerPostRateCount         = $ratingAverage['customUser']['perPostRateCount'];
            $rating                         = array(
                'form_id'                     => $ratingFormArray['id'],
                'post_id'                     => $post_id,
                'post_type'                   => $post_type,
                'per_post_rating_count'       => (`per_post_rating_count`),
                'per_post_rating_summary'     => number_format($perPostAverageRating, 2),
                'custom_user_rating_summary'  => maybe_serialize($ratingAverage['customUser']),
                'per_criteria_rating_summary' => maybe_serialize($ratingAverage['avgPerCriteria']),
            );

            $return = CBRatingSystemData::update_rating_summary($rating);

        } else {
            //summary already updated
            if (sizeof($avgRatingData) > 0) {
                $perPostAverageRating           = $avgRatingData[0]['per_post_rating_summary'];
                $perCriteriaAverageRating       = $avgRatingData[0]['per_criteria_rating_summary'];
                $customPerPostAverageRating     = $avgRatingData[0]['custom_user_rating_summary']['perPost'];
                $customPerCriteriaAverageRating = $avgRatingData[0]['custom_user_rating_summary']['perCriteria'];
                $customPerPostRateCount         = $avgRatingData[0]['custom_user_rating_summary']['perPostRateCount'];
            } else {

                $perPostAverageRating           = 0;
                $perCriteriaAverageRating       = array();
                $customPerPostAverageRating     = array('registered' => 0, 'editor' => 0);
                $customPerPostRateCount         = array('registered' => 0, 'editor' => 0);
                $customPerCriteriaAverageRating = array();
            }
        }


        $ratethis_label = (empty($isUserSubmittedRating))? esc_html__('Rate this','cbratingsystem'): esc_html__('Your rating', 'cbratingsystem');

        $output = '<div class="cbrp_front_content">';
            $output .= '<h3  class="cbratingfrom_title">' . esc_html__('Ratings', 'cbratingsystem') . ' </h3>';
            $output .= '<div id="cbrp_container_' . $post_id . '" class="cbrp_container_' . $theme_key . '_theme cbrp-content-container cbrp-content-container-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '" data-count= "' . $count . '">
                            <div class="cbrp_wrapper_' . $theme_key . '_theme cbrp-content-wprapper cbrp-content-wprapper-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-form-id="' . $ratingFormArray['id'] . '">';

            //if user can vote or can edit vote we need to show as tab/ul
            if($user_can_give_vote || $user_can_edit_vote){
                $output .= '<ul class="cbratingsystem-tabswitch-wrap">
                                <li id="cbrp-form" data-show-div="cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '" class="cbratingsystem-tabswitch toolbar-cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . ' cbrp-switch-report-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-post-id ="' . $post_id . '">' .$ratethis_label . '</li>
                                <li id="cbrp-report" data-show-div="cbrp-switch-report-form-' . $ratingFormArray['id'] . '" class="cbratingsystem-tabswitch toolbar-cbrp-rating-buffer cbrp-rating-buffer-form-' . $ratingFormArray['id'] . ' cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '" data-post-id ="' . $post_id . '">' . __('Summary', 'cbratingsystem') . '</li>
                            </ul>';
            }


            //summary box(near problem)
            $summary_output = '<div class="cbratingsystem-tabswitch-target cbrp_switch_report_' . $theme_key . '_theme cbrp-switch-report cbrp-switch-report-form-' . $ratingFormArray['id'] . ' cbrp-switch-report-form-' . $ratingFormArray['id'] . '-post-' . $post_id . '"  data-form-id="' . $ratingFormArray['id'] . '" data-post-id="' . $post_id . '">
                                    <div class="allUser_criteria user_criteria">
                                        <div class="report-title" id="cbrp-report-title">
                                            <span style="line-height: 30px;">' . esc_html__('Current Average Ratings', 'cbratingsystem') . '</span>
                                        </div>
                                        <div class="clear" style="clear:both"></div>
                                            <div class="criteria-container">';

                                        if (!empty($perCriteriaAverageRating)) {


                                            foreach ($perCriteriaAverageRating as $cId => $criteria) {


                                                $stars = $perCriteriaAverageRating[$cId]['stars'];

                                                $labels = array();
                                                foreach ($criteria['stars'] as $star) {
                                                    if ($star['title'] == '' || $star['enabled'] != 1) continue;
                                                    $labels[] = $star['title'];
                                                }

                                                $stars = $perCriteriaAverageRating[$cId]['stars'];


                                                $star_arr = array();
                                                foreach ($stars as $star) {
                                                    if (isset($star['enabled']) && intval($star['enabled'])) {
                                                        $star_arr[] = $star['title'];
                                                    }
                                                }

                                                $star_arr = (wp_json_encode($star_arr));
                                                $star_arr = htmlentities($star_arr, ENT_QUOTES, 'UTF-8');




                                                $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count($labels);
                                                $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
                                                $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;


                                                $summary_output .= '
                                                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '"  class="readonly-criteria-wrapper readonly_criteria_wrapper_' . $theme_key . '_theme  readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                                                                <div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
                                                                                                    <span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . $ratingFormArray['custom_criteria'][$cId]['label'] . '</span>
                                                                                                </div>
                                                                                                <div data-hints="' . $star_arr . '" data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '"  class="criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">

                                                                                                </div>
                                                                                                <div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '"   class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
                                                                                                <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly-criteria-average-label readonly_criteria_average_label_' . $theme_key . '_theme  criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . ' ">
                                                                                                    <span>' . __('Avg', 'cbratingsystem') . ':  </span>
                                                                                                    <span class="rating">' . (number_format((($criteria['value'] / 100) * count($ratingFormArray['custom_criteria'][$cId]['stars'])), 2)) . '/' . (count($ratingFormArray['custom_criteria'][$cId]['stars'])) . '</span>
                                                                                                </div>
                                                                                            </div>
                                                                                            ';
                                            }
                                        } else {


                                            //we don't need label for no rating or empty ratings



                                            foreach ($ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray) {
                                                if ($firstLabelArray['label'] == '' || $firstLabelArray['enabled'] != 1) continue;


                                                $labels = array();
                                                foreach ($firstLabelArray['stars'] as $star) {
                                                    if ($star['title'] == '' || $star['enabled'] != 1) continue;
                                                    $labels[] = $star['title'];
                                                }


												$star_arr = (wp_json_encode($labels));
												$star_arr = htmlentities($star_arr, ENT_QUOTES, 'UTF-8');

												//readonly-criteria-label



												$cCriteria['readonly-criteria-stars-' . $firstLabel]                                                 = json_encode(array_values($labels));
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values($labels);
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count($labels);
												$cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-value'] =  0;



                                                $summary_output .= '
                                                                                        <div data-form-id="' . $ratingFormArray['id'] . '"   data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                                                            <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
                                                                                                <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . $firstLabelArray['label']. '</span>
                                                                                            </div>
                                                                                            <div data-hints="'.$star_arr.'" data-form-id="' . $ratingFormArray['id'] . '"  data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '" id="criteria-star-wrapper">
                                                                                            </div>
                                                                                            <div class="criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly-criteria-average-label readonly_criteria_average_label_' . $theme_key . '_theme  criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
                                                                                                <span>' . __('Avg', 'cbratingsystem') . ':</span>
                                                                                                <span class="rating">0/' . (count($firstLabelArray['stars'])) . '</span>
                                                                                            </div>
                                                                                        </div>
                                                                                            ';
                                            }

                                        }
        $summary_output .= '</div>'; //end criteria-container

        $summary_output .= '<div class="clear" style="clear:both"></div>
							<span style="display: none" itemprop="name">'.get_the_title($post_id).'</span>
                            <div  class="rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly-criteria-average-label readonly_criteria_average_label_form_' . $theme_key . '_theme -form rating-average-label-form-' . $ratingFormArray['id'] . '" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                <span>' . esc_html__('Total Avg Rating', 'cbratingsystem') . ': </span>
                                <span class="rating" itemprop="ratingValue">' . (number_format(($perPostAverageRating / 100) * 5, 2)) . '</span> ' . esc_html__('out of', 'cbratingsystem') . '
                                <span itemprop="bestRating">5</span> ' . esc_html__('with', 'cbratingsystem') . '
                                <span  class="total_rates">  ' . esc_html__('based on', 'cbratingsystem') . ' <strong class="total_rates_count" itemprop="ratingCount">' . (!empty($avgRatingData[0]['per_post_rating_count']) ? (int)$avgRatingData[0]['per_post_rating_count'] : '0') . '</strong> ' . esc_html__('rating(s)', 'cbratingsystem') . ' </span>
                            </div>';

        $summary_output .= '</div>'; //end of  <div class="allUser_criteria user_criteria">


        //show editor rating if enabled
        if (isset($ratingFormArray ['show_editor_rating']) && intval($ratingFormArray ['show_editor_rating'])) {
            if (!empty($customPerCriteriaAverageRating['editor'])) {
                $summary_output .= '<div class="editor_criteria user_criteria">
                                                    <div class="report-title" id="cbrp-report-title">
                                                        <span style="line-height: 30px;">' . esc_html__('Editor Avg. Rating ', 'cbratingsystem') . '</span>
                                                    </div>
                                                    <div class="clear" style="clear:both"></div>
                                                        <div class="criteria-container">';


                foreach ($customPerCriteriaAverageRating['editor'] as $cId => $criteria) {


                    $stars = $perCriteriaAverageRating[$cId]['stars'];


                    $star_arr = array();
                    foreach ($stars as $star) {
                        if (isset($star['enabled']) && intval($star['enabled'])) {
                            $star_arr[] = $star['title'];
                        }
                    }

                    $star_arr = (wp_json_encode($star_arr));
                    $star_arr = htmlentities($star_arr, ENT_QUOTES, 'UTF-8');


                    $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-count']       = count($ratingFormArray['custom_criteria'][$cId]['stars']);
                    $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $cId . '-value']       = $criteria['value'];
                    $cCriteria['editor-readonly-criteria-label-' . $ratingFormArray['id'] . '-post-' . $post_id . '-avgvalue'] = $perPostAverageRating;
                    $summary_output .= '
																			<div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="readonly-criteria-wrapper readonly_criteria_wrapper_' . $theme_key . '_theme  readonly-criteria-id-wrapper-' . $cId . ' readonly-criteria-wrapper-form-' . $ratingFormArray['id'] . '">
																				<div class="readonly_criteria_label_wrapper_' . $theme_key . '_theme readonly-criteria-label-wrapper readonly-criteria-label-wrapper-form-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '">
																					<span class="readonly-criteria-label criteria-label-form-' . $ratingFormArray['id'] . ' readonly-criteria-label-id-' . $cId . '" >' . $ratingFormArray['custom_criteria'][$cId]['label'] . '</span>
																				</div>
																				<div data-hints="' . $star_arr . '" data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="editor-criteria-star-wrapper readonly-criteria-star-wrapper-id-' . $cId . '-form-' . $ratingFormArray['id'] . ' readonly-criteria-star-wrapper-id-' . $cId . ' criteria-star-wrapper-form-' . $ratingFormArray['id'] . '" >

																				</div>
																				<div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $cId . '" class="criteria-star-hint readonly-criteria-star-hint-form-' . $ratingFormArray['id'] . '-id-' . $cId . ' criteria-star-hint-id-' . $cId . '"></div>
																				<div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '-postid-' . $post_id . ' readonly-criteria-average-label readonly_criteria_average_label_' . $theme_key . '_theme  editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $cId . '">
																					<span>' . __('Avg ', 'cbratingsystem') . ': </span>
																					<span class="rating">' . (number_format((($criteria['value'] / 100) * count($ratingFormArray['custom_criteria'][$cId]['stars'])), 2)) . '/' . (count($ratingFormArray['custom_criteria'][$cId]['stars'])) . '</span>
																				</div>
																			</div>
																			';
                }
                //end foreach
            } else {

            	//
                //as there is no rating yet we don't need to show any label
                $summary_output .= '<div class="editor_criteria user_criteria">
														 <div class="report-title" id="cbrp-report-title">
															 <span style="line-height: 30px;">' . esc_html__('Editors Average Rating', 'cbratingsystem') . '</span>
														</div>
														 <div class="clear" style="clear:both"></div>

														 <div class="criteria-container">';
                foreach ($ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray) {

                    //as there is no rating yet we don't need to show any label
                    if (isset($firstLabelArray['enabled']) && $firstLabelArray['enabled'] != 1) continue;

                    $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]            = array_values($firstLabelArray['stars']);
                    $cCriteria['criteria-stars-' . $firstLabel]                                                        = json_encode(array_values($firstLabelArray['stars']));
                    $cCriteria['editor-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count($firstLabelArray['stars']);
                    $summary_output .= '
														<div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
															<div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
																<span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . $firstLabelArray['label']. '</span>
															</div>
															<div data-form-id="' . $ratingFormArray['id'] . '" data-label-id="' . $firstLabel . '" class="editor-criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '">
															</div>
															<div class="editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '-postid-' . $post_id . ' readonly-criteria-average-label readonly_criteria_average_label_' . $theme_key . '_theme  editor-criteria-average-label-form-' . $ratingFormArray['id'] . '-label-' . $firstLabel . '">
																<span>' . __('Avg ', 'cbratingsystem') . ': </span>
																<span class="rating">0/' . (count($firstLabelArray['stars'])) . '</span>
															</div>
														</div>
															';
                }
            }
            //end of else for editor
            $summary_output .= '</div>'; //end of editor editor_criteria

            $summary_output .= ' <div class="clear" style="clear:both"></div>
                                                <div class="editor-rating-average-label-form-' . $ratingFormArray['id'] . '-postid-' . $post_id . ' readonly-criteria-average-label-form readonly_criteria_average_label_form_' . $theme_key . '_theme  editor-rating-average-label-form-' . $ratingFormArray['id'] . '">
                                                     <span>' . __('Total Avg. Rating ', 'cbratingsystem') . ': </span>
                                                    <span class="rating">0/5' . '</span>
                                                    <span class="total_rates">  ' . __('based on ', 'cbratingsystem') . ' <strong class="total_rates_count">' . (!empty($customPerPostRateCount['editor']) ? (integer)$customPerPostRateCount['editor'] : '0') . '</strong> ' . __('rating(s)', 'cbratingsystem') . ' </span>
                                                </div>';
            $summary_output .= '</div>'; //end of editor editor_criteria_container
        }
        // end of if show editor rating

        $summary_output .= '</div>'; //end of div cbrp_switch_report_
            //end summary output

            $output .= $summary_output;

            //form box
            if($user_can_give_vote || $user_can_edit_vote){
                $form_output = '<div class="cbratingsystem-tabswitch-target  cbrp_rating_buffer_' . $theme_key . '_theme cbrp-rating-buffer cbrp-rating-buffer-form-' . $ratingFormArray['id'] . ' cb-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . ' cbrp-rating-buffer-form-' . $ratingFormArray['id'] . '-post-' . $post_id . ' ">
                            <div style="clear:both;"></div>
                                <div class="criteria_listings_' . $theme_key . '_theme criteria-listings criteria-listings-form-' . $ratingFormArray['id'] . '">';

                $form_output .= '<form action="#">';

                //start criteria
                if (!empty($ratingFormArray['custom_criteria'])) {
                    foreach ($ratingFormArray['custom_criteria'] as $firstLabel => $firstLabelArray) {
                        if ($firstLabelArray['label'] == '' || $firstLabelArray['enabled'] != 1) continue;

                        $score_value = isset($submit_data['rating'][$firstLabel.'_actualValue'])? intval($submit_data['rating'][$firstLabel.'_actualValue']): 0;

                        $star_labels = array();
                        foreach ($firstLabelArray['stars'] as $star) {
                            if (!$star['enabled']) continue;
                            $star_labels[] = $star['title'];
                        }


                        $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel]                     = array_values($star_labels);
                        $cCriteria['criteria-stars-' . $firstLabel]                                                          = json_encode(array_values($star_labels));
                        $cCriteria['criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count']          = count($star_labels);
                        $cCriteria['readonly-criteria-label-' . $ratingFormArray['id'] . '-stars-' . $firstLabel . '-count'] = count($star_labels);
                        $form_output .= '
                                                                                        <div data-form-id="' . $ratingFormArray['id'] . '" data-score-val="'.$score_value.'"  data-label-id="' . $firstLabel . '" class="criteria_wrapper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $firstLabel . ' criteria-id-wrapper-' . $firstLabel . '-form-' . $ratingFormArray['id'] . ' criteria-wrapper-form-' . $ratingFormArray['id'] . '">
                                                                                            <div class="criteria_label_wrapper_' . $theme_key . '_theme criteria-label-wrapper">
                                                                                                <span class="criteria-label criteria-label-id-' . $firstLabel . '" >' . $firstLabelArray['label']. '</span>
                                                                                            </div>
                                                                                            <div data-form-id="' . $ratingFormArray['id'] . '" data-score-val="'.$score_value.'"  data-label-id="' . $firstLabel . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '">

                                                                                            </div>
                                                                                            <div class="criteria_star_hint_' . $theme_key . '_theme criteria-star-hint criteria-star-hint-id-' . $firstLabel . ' criteria-star-hint-id-' . $firstLabel . '-form-' . $ratingFormArray['id'] . '"></div>
                                                                                        </div>';

                    }
                }
                //end if any enabled criteria


				$form_output = apply_filters('cbratingsystem_form_extra_fields_before', $form_output, $ratingFormArray, $post_id, $theme_key);

                //show custom question
                if (isset($ratingFormArray['custom_question']) && sizeof($ratingFormArray['custom_question']) > 0 && ($ratingFormArray['enable_question'] == 1)) {
                    $form_output .= '<div style="clear:both;"></div>';
                    $form_output .= '<div class="question_box">';

                    foreach ($ratingFormArray['custom_question'] as $q_id => $q_arr) {

                        $submit_data_q = (isset($submit_data['question']) && isset($submit_data['question'][$q_id]))? $submit_data['question'][$q_id]: '';


                        //skip question that are not enabled
                        if (!isset($q_arr['enabled']) || $q_arr['enabled'] != 1) continue;

                        $required_status = 0;
                        if (isset($q_arr['required']) && $q_arr['required'] == 1) {
                            $required_status = 1;
                            $requiredClass = 'required';
                            $requiredIcon  = '<span class="form-required" title="' . __('Required Field', 'cbratingsystem') . '">*</span>';
                        } else {
                            $requiredClass = '';
                            $requiredIcon  = '';
                        }

                        $method = 'display_' . $q_arr['field']['type'] . '_field';


                        if (method_exists('CBRatingSystemFront', $method)) {
                            $fieldDisplay = self::$method(
                                $q_id,
                                $q_arr,
                                array(
                                    'required_status'   => $required_status,
                                    'required_class'    => $requiredClass,
                                    'required_text'     => $requiredIcon
                                ),
                                $ratingFormArray,
                                $submit_data_q

                            );
                        } else {
                            $fieldDisplay = '';
                        }

                        //print single question
                        $form_output .= '<div class="item-question">';
                        $form_output .= $fieldDisplay;
                        $form_output .= '</div>';

                    }
                    //end foreach question

                    $form_output .= '</div>';
                }
                //end custom question


                //$ratingreview_hide_name_html = '';
                //$form_output .= apply_filters('cbratingsystem_hide_current_user_name', $ratingreview_hide_name_html, $ratingFormArray, $post_id, $theme_key);



                //show comment form
                if (($ratingFormArray['enable_comment'] == 1)) {
                    if ($ratingFormArray['comment_required'] == '1') {
                        $comment_class     = '<span class="form-required" title="' . __('This field is required.', 'cbratingsystem') . '">*</span>';
                        $comment_div_class = '1';
                    } else {
                        $comment_class     = '';
                        $comment_div_class = '0';
                    }

                    $submit_data_comment = (isset($submit_data['comment']))? $submit_data['comment']: '';

                    $form_output .= '
                                                        <div style="clear:both;"></div>
                                                        <div class="cbratingsystem_comment_box">';
                    $form_output .= '<label class = "">' . __('Comment/Note:', 'cbratingsystem') . '</label>' . $comment_class;
                    $form_output .= '<textarea class="cbrating_commentarea cbrs_comment_textarea"  data-required = "' . $comment_div_class . '" name="comment[' . $ratingFormArray['id'] . ']" data-formid="' . $ratingFormArray['id'] . '" style="width:97.5%; height:50px;">'.$submit_data_comment.'</textarea>';
                    $form_output .= '<span class="cbrating_comment_limit_label cbrating_comment_limit_label_' . $theme_key . '_theme  cbrating_comment_limit_label_form_' . $ratingFormArray['id'] . '_post_' . $post_id . '"></span>';
                    $form_output .= '
                                                        </div>
                                                        <div style="clear:both;"></div>

                                                ';
                }

                if ($user_id == 0) {
                    $form_output .= '<div class="user_info">
                                                                <div class="user_name">
                                                                    <label for="user_name_field-' . $ratingFormArray['id'] . '">' . esc_html__('Name', 'cbratingsystem') . '</label>
                                                                    <input id="user_name_field-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '" class="user_name_field required" type="text" name="userinfo[' . $ratingFormArray['id'] . '][name]" value="" placeholder="'.esc_html__('Your Name', 'cbratingsystem').'" required />
                                                                </div>
                                                                <div class="user_email">
                                                                    <label for="user_email_field-' . $ratingFormArray['id'] . '">' . esc_html__('Email', 'cbratingsystem') . '</label>
                                                                    <input id="user_email_field-' . $ratingFormArray['id'] . '" data-form-id="' . $ratingFormArray['id'] . '" class="user_email_field required" type="text" name="userinfo[' . $ratingFormArray['id'] . '][email]" value="" placeholder="'.esc_html__('Your Email','cbratingsystem').'" required />
                                                                </div>
                                                             </div>

                                                ';
                }

                //allow other plugins to put more input fields
				$form_output = apply_filters('cbratingsystem_form_extra_fields_after', $form_output, $ratingFormArray, $post_id, $theme_key);




                $user_hash = '';
                $form_output .= '
                                    <div style="clear:both;"></div>
                                    </div>
                                    <div style="clear:both;"></div>
                                    <div class="submit_button_wrapper">
                                        <button class="button cbrp-button cbrp-button-form-' . $ratingFormArray['id'] . '" data-hash = "' . $user_hash . '"   id="submit-rating" type="submit" name="op" value=""><span id="cbrp-button-label">' . __('Submit', 'cbratingsystem') . '</span></button>
                                        <div style="display: none;" class="cbrp_load_more_waiting_icon cbrp_load_more_waiting_icon_form-' . $ratingFormArray['id'] . '_post-' . $post_id . '"><img alt="' . __('Loading', 'cbratingsystem') . '" src="' . CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'images/ajax-loader.gif" /></div>
                                    </div>
                                    <div style="clear:both;"></div>
                                    </div>
                                    <div  class="ratingFormStatus ratingFormStatus-form-' . $ratingFormArray['id'] . '"></div>

                                 ';

                $form_output .= '<input type="hidden" name="rp_id" value="' . $ratingFormArray['id'] . '-' . $post_id . '" />
                        <input type="hidden" name="cbrnonce" value="' . wp_create_nonce('cb_ratingForm_front_form_nonce_field') . '" />
                        <input type="hidden" name="formId" value="ratingForm" />';

                $form_output .= '</form>';

                $output .= $form_output;

            }
            //end form box

            ///js related
            $jsSettings = self::front_end_js_settings($ratingFormArray, $cCriteria, $post_id);
            $output .= '<script type="text/javascript">' . $jsSettings . '</script>';


        //credit section
        if ($ratingFormArray['show_credit_to_codeboxr'] == '1') {
            $cbrating_credit_msg = esc_html__('Rating System by Codeboxr', 'cbratingsystem');
            $credit              = '<span class ="codeboxr_rating_credit"><a rel="external" href="http://codeboxr.com?utm_source=cbratingsystem&utm_medium=clientsite&utm_campaign=cbratingsystem" target="_blank">' . $cbrating_credit_msg . '</a></span>';
            $output .= apply_filters('cbratingsystem_codeboxr_credit', $credit);
        }


            $output .= '    </div>
                        </div>'; //cbrp_wrapper_,  //cbrp_container_
        $output .= '</div>'; //cbrp_front_content



        //need to find why this
        self::viewPerCriteriaRatingResult($ratingFormArray, $post_id, $user_id);

        return $output;
    }



    /**
     * Front end js settings
     *
     * @param $ratingFormArray
     * @param $cCriteria
     * @param $post_id
     *
     * @return string
     */
    public static function front_end_js_settings($ratingFormArray, $cCriteria, $post_id)
    {
        global $post;



        $js = '
            var ratingFormOptions_form_' . $ratingFormArray['id'] . '= ' . json_encode(
                array(
                    'limit' => $ratingFormArray['comment_limit'],
                )
            ) . ';
        ';

        $js .= '
            var ratingForm_post_' . $post_id . '_form_' . $ratingFormArray['id'] . ' = ' . json_encode(
                array(
                    'img_path'              => CBRatingSystem::ratingIconUrl(),
                    'hints'                 => json_encode($cCriteria),
                    'cancel_hint'           => esc_html__('Click to cancel given rating', 'cbratingsystem'),
                    //'is_rated'              =>   $is_rated, //need to find why this is not defined
                    'is_rated'              => '',
                    'thanks_msg'            => esc_html__('Thank you for rating', 'cbratingsystem'),
                    'pleaseFillAll_msg'     => esc_html__('Please rate to all criteria', 'cbratingsystem'),
                    'pleaseCheckTheBox_msg' => esc_html__('Please fill all required fields', 'cbratingsystem'),
                    'failure_msg'           => esc_html__('Rating save error', 'cbratingsystem'),
                )
            ) . ';
        ';

        $js .= '
            var readOnlyRatingForm_post_' . $post_id . '_form_' . $ratingFormArray['id'] . ' = ' . json_encode(
                array(
                    'img_path' => CBRatingSystem::ratingIconUrl(),
                    'hints'    => json_encode($cCriteria),
                    'is_rated' => 1,
                )
            ) . ';
        ';

       /* $js .= '
            var ratingFormAjax = ' . json_encode(
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                )
            ) . ';
        ';*/

        return $js;
    }

    /**
     * Ajax Review Submission
     *
     */
    public static function cbRatingAjaxFunction()
    {

        ob_clean();

        global $wpdb;

        $user_id   = get_current_user_id(); //returns 0 if guest
        $user_info = '';

        if (isset($_POST['cbRatingData']) && !empty($_POST['cbRatingData'])) {
            $returnedData = $_POST['cbRatingData'];


            if (wp_verify_nonce($returnedData['cbrp_nonce'], 'cb_ratingForm_front_form_nonce_field')) {
                if (!empty($returnedData['values'])) {

                    list($insertArray['form_id'], $insertArray['post_id']) = explode('-', $returnedData['rp_id']);

                    if ($user_id == 0) {
                        $user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
                        $user_ip      = CBRatingSystem::get_ipaddress();

                    } elseif ($user_id > 0) {
                        $user_session = 'user-' . $user_id; //this is string
                        $user_ip      = CBRatingSystem::get_ipaddress();
                        $user_info    = get_userdata($user_id);
                    }


                    $summary_table 		= CBRatingSystemData::get_user_ratings_summury_table_name();
                    $userlog_table 		= CBRatingSystemData::get_user_ratings_table_name();
                    $form_table    		= CBRatingSystemData::get_ratingForm_settings_table_name();


                    $ratingFormArray 	= CBRatingSystemData::get_ratingForm($insertArray['form_id']);
                    $rating_Logmethod 	=  maybe_unserialize($ratingFormArray['logging_method']);


                    $rating_history = '';
                    $count          = 0;
                    $rating_logid   = 0;


                    if ($user_id > 0) {

                        $sql   = $wpdb->prepare("SELECT ur.id  FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_id=%d", $insertArray['form_id'], $insertArray['post_id'], $user_id);

                        //$count = $wpdb->get_var($sql);
                        $rating_history = $wpdb->get_results($sql, ARRAY_A);

                    } else {
                        //if guest user
                        if (in_array("cookie", $rating_Logmethod) && !in_array("ip", $rating_Logmethod)) {

                            $sql   = $wpdb->prepare("SELECT ur.id FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_session = %s", $insertArray['form_id'], $insertArray['post_id'], $user_session);

                            $rating_history = $wpdb->get_results($sql, ARRAY_A);
                        } else if (!in_array("cookie", $rating_Logmethod) && in_array("ip", $rating_Logmethod)) {

                            $sql   = $wpdb->prepare("SELECT ur.id  FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_ip = %s", $insertArray['form_id'], $insertArray['post_id'], $user_ip);

                            $rating_history = $wpdb->get_results($sql, ARRAY_A);
                        } else {

                            $sql   = $wpdb->prepare("SELECT ur.id  FROM $userlog_table ur WHERE ur.form_id=%d AND ur.post_id=%d AND ur.user_ip = %s AND ur.user_session = %s", $insertArray['form_id'], $insertArray['post_id'], $user_ip, $user_session);
                            //$count = $wpdb->get_var($sql);
                            $rating_history = $wpdb->get_results($sql, ARRAY_A);
                        }
                    }




                    if(sizeof($rating_history) > 0){
                        $count = 1;
                        $rating_history = $rating_history[0];
                        $rating_logid = intval($rating_history['id']);
                    }

                    $edit_mode = 0;


                    //if user is editing his/her vote, let's delete the old vote, adjust and recote
                    if($rating_logid > 0){


                        $edit_mode = 1;


                        $formIds = array();
                        $postIds = array();


                        $sql     = $wpdb->prepare("SELECT post_id ,form_id, user_id FROM $userlog_table WHERE id=%d ", $rating_logid);
                        $logresults = $wpdb->get_results($sql, ARRAY_A);

                        array_push($formIds, $logresults[0]['form_id']);
                        array_push($postIds, $logresults[0]['post_id']);

                        //help 3rd party plugins to do extra
                        do_action('cbratingsystem_before_comment_deleted', $rating_logid, $logresults[0]['form_id'], $logresults[0]['post_id'], $logresults[0]['user_id']);

                        //now delete the user log
                        $sql = $wpdb->prepare("DELETE FROM $userlog_table WHERE id=%d", $rating_logid);
                        $wpdb->query($sql);

                        //help 3rd party plugins to do extra, not user it will have any use though
                        do_action('cbratingsystem_after_comment_deleted', $rating_logid, $logresults[0]['form_id'], $logresults[0]['post_id'], $logresults[0]['user_id']);


                        foreach ($postIds as $index => $id) {

                            $formId        = $formIds[$index];
                            $postId        = $id;
                            $ratingAverage = CBRatingSystemFront::viewPerCriteriaRatingResult($formId, $postId);


                            if (isset($ratingAverage['avgPerRating'])) {

                                //at least on another user rating exists for this post and form id
                                //$perPostAverageRating = isset($ratingAverage['perPost'][$postId])? $ratingAverage['perPost'][$postId] : '';
                                $perPostAverageRating = $ratingAverage['perPost'][$postId];
                                $postType             = get_post_type($postId);

                                $ratingsCount = $ratingAverage['ratingsCount'][$formId . '-' . $postId];

                                $rating = array(
                                    'form_id'                     => $formId,
                                    'post_id'                     => $postId,
                                    'post_type'                   => $postType,
                                    'per_post_rating_count'       => $ratingsCount,
                                    'per_post_rating_summary'     => $perPostAverageRating,
                                    'per_criteria_rating_summary' => maybe_serialize($ratingAverage['avgPerCriteria']),
                                );

                                $success = CBRatingSystemData::update_rating_summary($rating);
                            } else {
                                //there no other user rating for this post id and form id, so delete the avg log entry for this matching

                                $sql = $wpdb->prepare("DELETE FROM $summary_table WHERE form_id=%d AND post_id=%d", $formId, $postId);
                                $wpdb->query($sql);

                                //we also delete all meta keys created for this post and form id
                                delete_post_meta($postId, 'cbrating' . $formId);

                            }

                        }
                    }


                    $user_allowed_vote      = (CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['allowed_users']) && $count < 1);
                    $user_allowed_revote    = (CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['allowed_users']) &&  $count == 1 && defined('CB_RATINGSYSTEMADDON_PLUGIN_VERSION') && isset($ratingFormArray['comment_edit_allowed_users']) &&  CBRatingSystem::current_user_can_use_ratingsystem($ratingFormArray['comment_edit_allowed_users']));


                    // first time rating

                    if ($user_allowed_vote || $user_allowed_revote) {

                        $insertArray['post_type'] = get_post_type($insertArray['post_id']);
                        $insertArray['created']   = time();

                        $question = array();


                        if (!empty($returnedData['question'][$insertArray['form_id']]) && is_array($returnedData['question'][$insertArray['form_id']])) {
                            foreach ($returnedData['question'][$insertArray['form_id']] as $qID => $qValue) {
                                if (is_array($qValue) && !empty($qValue)) {
                                    foreach ($qValue as $key => $val) {
                                        $type = $qValue[$qID . '_type'];
                                        if (isset($qValue[$type . '-' . $qID]) && !empty($qValue[$type . '-' . $qID])) {
                                            $question[$qID] = $qValue[$type . '-' . $qID];
                                        } elseif (($key != ($qID . '_type')) && ($key != ($type . '-' . $qID)) && !empty($val)) {
                                            $key = str_replace($qID . '_', '', $key);

                                            if (is_numeric($key)) {
                                                $question[$qID][$key] = $val;
                                            }
                                        }
                                    }
                                } else {
                                    $question[$qID] = $qValue;
                                }
                            }
                        }

                        $insertArray['question'] = maybe_serialize($question);

                        $comment = esc_html(sanitize_text_field($returnedData['comment']));

                        if (strlen($comment) <= $returnedData['comment_limit']) {
                            $insertArray['comment'] = $comment;
                        } elseif (strlen($comment) > $returnedData['comment_limit']) {
                            $insertArray['comment'] = substr($comment, 0, intval($returnedData['comment_limit']));
                        }

                        //check if guest user
                        if (!is_user_logged_in()) {
                            if (!empty($returnedData['user_name'])) {
                                $insertArray['user_name'] = sanitize_text_field(trim($returnedData['user_name']));
                            } else {
                                $encoded = json_encode(
                                    array(
                                        'validation'   => 1,
                                        'errorMessage' => __('Name field can\'t be left blank.', 'cbratingsystem')
                                    )
                                );

                                echo $encoded;
                                wp_die();
                            }


                            $email_valid     = true;
                            $email_error_msg = '';

                            //check if email is empty or not
                            if (!empty($returnedData['user_email'])) {


                                if (is_email(trim($returnedData['user_email']))) {
                                    if (!email_exists(trim($returnedData['user_email']))) {
                                        $insertArray['user_email'] = sanitize_text_field(trim($returnedData['user_email']));
                                    } else {
                                        $email_valid     = false;
                                        $email_error_msg = esc_html__('Sorry, this email address is associated with a registered user and can not be use for guest voting.', 'cbratingsystem');
                                    }
                                } else {
                                    $email_valid     = false;
                                    $email_error_msg = esc_html__('Invalid Email address', 'cbratingsystem');
                                }

                            } else {
                                $email_valid     = false;
                                $email_error_msg = esc_html__('Email field can\'t be empty', 'cbratingsystem');
                            }

                            //email invalid
                            if ($email_valid == false) {
                                $encoded = json_encode(
                                    array(
                                        'validation'   => 1,
                                        'errorMessage' => $email_error_msg
                                    )
                                );


                                echo $encoded;
                                wp_die();
                            }


                            if (!empty($returnedData['user_email']) && is_email(trim($returnedData['user_email']))) {
                                $insertArray['user_email'] = sanitize_text_field(trim($returnedData['user_email']));
                            } else {
                                $encoded = json_encode(
                                    array(
                                        'validation'   => 1,
                                        'errorMessage' => esc_html__('Please enter a valid email address.', 'cbratingsystem')
                                    )
                                );


                                echo $encoded;
                                wp_die();
                            }

                            $guest_comment_status = 'approved';
                            $guest_comment_status = apply_filters('cbratingsystem_comment_status_before_save', $guest_comment_status, $user_id, $insertArray['form_id']);

                            $returnedData['comment_status'] = $guest_comment_status;

                        } else {
                            //logged in user

                            $user_comment_status            = 'approved';
                            $user_comment_status            = apply_filters('cbratingsystem_comment_status_before_save', $user_comment_status, $user_id, $insertArray['form_id']);
                            $returnedData['comment_status'] = $user_comment_status;

                        }

                        $insertArray['user_name']    = ($user_id > 0) ? $user_info->user_login : $returnedData['user_name'];
                        $insertArray['user_email']   = ($user_id > 0) ? $user_info->user_email : $returnedData['user_email'];
                        $insertArray['user_ip']      = $user_ip;
                        $insertArray['user_session'] = $user_session;


                        $insertArray['rating'] = maybe_serialize($returnedData['values']);

                        foreach ($returnedData['values'] as $key => $val) {
                            if (is_numeric($key)) {
                                $average[$key] = $val;
                            }
                        }

                        $hash_comment = $insertArray['user_ip'] . $insertArray['user_session'] . $insertArray['user_email'] . time();
                        $hash_comment = md5($hash_comment);

						$insertArray['average']            = (array_sum($average) / count($average));
						$insertArray['user_id']            = $user_id;
						$insertArray['form_id']            = (int)$insertArray['form_id'];
						$insertArray['post_id']            = (int)$insertArray['post_id'];
						$insertArray['comment_status']     = $returnedData['comment_status'];
						$insertArray['comment_hash']       = $hash_comment;
						$insertArray['allow_user_to_hide'] = isset($returnedData["hide_this_user_name"]) ? $returnedData["hide_this_user_name"] : 0;




                        $rating_id = CBRatingSystemData::update_rating($insertArray); //insert/update


                        //verify guest user if enabled
                        $ratingFormArray = CBRatingSystemData::get_ratingForm($insertArray['form_id']);



                        if ($rating_id) {
                            //getting the criteria rating result
                            $ratingAverage = self::viewPerCriteriaRatingResult($insertArray['form_id'], $insertArray['post_id'], $user_id);

                            $ratingsCount = $ratingAverage['ratingsCount'][$insertArray['form_id'] . '-' . $insertArray['post_id']];

                            if (!empty($ratingsCount)) {
                                $rating = array(
                                    'form_id'                     => $insertArray['form_id'],
                                    'post_id'                     => $insertArray['post_id'],
                                    'post_type'                   => $insertArray['post_type'],
                                    'per_post_rating_count'       => $ratingsCount,
                                    'per_post_rating_summary'     => number_format($ratingAverage['perPost'][$insertArray['post_id']], 2),
                                    'custom_user_rating_summary'  => maybe_serialize($ratingAverage['customUser']),
                                    'per_criteria_rating_summary' => maybe_serialize($ratingAverage['avgPerCriteria']),
                                );

                                foreach ($ratingAverage['avgPerCriteria'] as $cId => $criteria) {
                                    $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count($criteria['stars']);
                                    $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
                                }
                                if (!empty($ratingAverage['customUser'] ['perCriteria']['editor'])) {
                                    foreach ($ratingAverage['customUser'] ['perCriteria']['editor'] as $cId => $criteria) {
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count($ratingAverage['avgPerCriteria'][$cId]['stars']);
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
                                    }
                                } else {
                                    foreach ($ratingAverage['avgPerCriteria'] as $cId => $criteria) {
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count($ratingAverage['avgPerCriteria'][$cId]['stars']);
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = 0;
                                    }
                                }
                                $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $rating['per_post_rating_summary'];
                                if (!empty($ratingAverage['customUser'] ['perCriteria']['editor'])) {
                                    $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $ratingAverage['customUser']['perPost']['editor'];
                                } else {
                                    $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = 0;
                                }

                                //update rating summary
                                $rating_summary = CBRatingSystemData::update_rating_summary($rating);

                                //now do some other necessry things

                                //send guest email verification
                                $guest_verify = isset($ratingFormArray['email_verify_guest']) ? intval($ratingFormArray['email_verify_guest']) : 1;
                                if ($guest_verify && $insertArray['comment_status'] == 'unverified' && $insertArray['comment_hash'] != '' && $insertArray['user_email'] != '') {

                                    $cb_subject = apply_filters('cbratingsystem_guest_emailsubject', __('Verify Your email', 'cbratingsystem'));

                                    $cb_message = get_site_url() . '?cbratingemailverify=' . $insertArray['comment_hash'];
                                    $from       = get_option('admin_email');

                                    wp_mail($insertArray['user_email'], $cb_subject, $cb_message);

                                }

                                do_action('cbratingsystem_after_comment_posted', $rating_id, $insertArray); //custom hooks here


                                $editorCount = (isset($ratingAverage['customUser']['perPostRateCount']['editor']) ? (int)$ratingAverage['customUser']['perPostRateCount']['editor'] : 0);



                                //get the theme for doing frontend UI works
                                $theme_key                            = get_option('cbratingsystem_theme_key', 'basic');
                                $reviewOptions['theme']               = $theme_key;
                                $reviewOptions['comment_status']      = $insertArray['comment_status'];
                                $reviewOptions["hide_this_user_name"] = isset($returnedData["hide_this_user_name"]) ? $returnedData["hide_this_user_name"] : 0;

                                $lastcomment = (is_numeric($rating_id)) ? self::build_user_rating_review_single($reviewOptions, $ratingFormArray, $rating_id) : '';

                                //echo $lastcomment;
                                if ($rating_summary) {
                                    $encoded = json_encode(
                                        array(
                                            'img_path'           => CBRatingSystem::ratingIconUrl(),
                                            'hints'              => json_encode($cCriteria),
                                            'is_rated'           => 1,
                                            'ratingsCount'       => $ratingsCount,
                                            'editorRatingsCount' => $editorCount,
                                            'lastcomment'        => $lastcomment,
                                            'theme_key'          => $reviewOptions['theme'],
                                            'firstcomment'       => true, // false
                                            'comment_status'     => $insertArray['comment_status'],
                                            'edit_mode'          => $edit_mode,
                                            'old_id'             => $rating_logid
                                        )
                                    );
                                    echo $encoded;
                                }
                            }
                        }
                    } else {

                        //this user gave  rating before, show summary
                        $summary = CBRatingSystemData::get_ratings_summary(array('form_id' => array($insertArray['form_id']), 'post_id' => array($insertArray['post_id'])));



                        if (!empty($summary[0])) {
                            $summary = $summary[0];


                            foreach ($summary['per_criteria_rating_summary'] as $cId => $criteria) {
                                $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = count($criteria['stars']);
                                $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
                            }

                            if (!empty($ratingAverage['customUser'] ['perCriteria']['editor'])) {
                                foreach ($ratingAverage['customUser'] ['perCriteria']['editor'] as $cId => $criteria) {
                                    $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = isset($summary['avgPerCriteria'][$cId]['stars']) ? count($summary['avgPerCriteria'][$cId]['stars']) : 0;
                                    $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = $criteria['value'];
                                }
                            } else {
                                if (!empty($summary)) {
                                    foreach ($summary['per_criteria_rating_summary'] as $cId => $criteria) {
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-count'] = isset($summary['avgPerCriteria'][$cId]['stars']) ? count($summary['avgPerCriteria'][$cId]['stars']) : 0;
                                        $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-stars-' . $cId . '-value'] = 0;
                                    }
                                }
                            }


                            $cCriteria['readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $summary['per_post_rating_summary'];
                            if (!empty($ratingAverage['customUser'] ['perCriteria']['editor'])) {
                                $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = $summary['customUser']['perPost']['editor'];
                            } else {
                                $cCriteria['editor-readonly-criteria-label-' . $insertArray['form_id'] . '-post-' . $insertArray['post_id'] . '-avgvalue'] = 0;
                            }


                            $encoded = json_encode(
                                array(
                                    'img_path'     => CBRatingSystem::ratingIconUrl(),
                                    'hints'        => json_encode($cCriteria),
                                    'is_rated'     => 1,
                                    'ratingsCount' => $summary['per_post_rating_count'],
                                    'errorMessage' => __('You have already rated this.', 'cbratingsystem'),
                                    'edit_mode'    => $edit_mode,
                                    'old_id'             => $rating_logid
                                )
                            );

                            echo $encoded;
                        } else {
                            $encoded = json_encode(
                                array(
                                    'is_rated'     => 1,
                                    'edit_mode'    => $edit_mode,
                                    'old_id'             => $rating_logid,
                                    'errorMessage' => __('An error occurred while storing data. Please ensure that all data are resonable. If problem persist please contact the administrator.', 'cbratingsystem'),
                                )
                            );

                            echo $encoded;
                        }
                    }
                    //end you already submitted your rating.
                }
            }
        }

        wp_die();
    }

    /**
     * Get the single review via ajax
     *
     * @param array $reviewOptions
     * @param array $ratingFormArray
     * @param int $lastcommentid
     * @param bool $ajax
     *
     * @return array|string
     */
    public static function build_user_rating_review_single($reviewOptions = array(), $ratingFormArray = array(), $lastcommentid, $ajax = false)
    {
        global $wpdb;

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $date_time_format = $date_format . ' ' . $time_format;

        $firstLabel = '';

        $post_id = (!empty($reviewOptions['post_id']) ? $reviewOptions['post_id'] : get_the_ID());
        if (!empty($reviewOptions['form_id'])) {
            $ratingFormId = $reviewOptions['form_id'];
        } else {
            $defaultFormId = get_option('cbratingsystem_defaultratingForm');
            $ratingFormId  = apply_filters('rating_form_array', $defaultFormId);
        }
        if (is_string($reviewOptions['theme']) and !empty($reviewOptions['theme'])) {
            $theme_key = $reviewOptions['theme'];
        } else {
            $theme_key = 'basic';
        }

        $reviews = CBRatingSystemData::get_user_ratings_with_ratingForm_lastID($lastcommentid, true);
        $output  = $mainContent = '';
        if (!empty($reviews[0])) {
            if (!empty($reviews) and is_array($reviews)) {
                $jsArray      = array();
                $shownReviews = 0;

				//reviews loop
				//google schema http://schema.org/Rating
                foreach ($reviews as $reviewKey => $review) {


                    $mainContent .= '<div itemprop="review" itemscope itemtype="http://schema.org/Review" id="cbrating-' . $ratingFormId . '-review-' . $review->id . '" data-review-id="' . $review->id . '" data-post-id="' . $post_id . '" data-form-id="' . $ratingFormId . '" class="cbratingsinglerevbox reviews_wrapper_' . $theme_key . '_theme review_wrapper review_wrapper_post-' . $post_id . '_form-' . $ratingFormId . ' review_wrapper_post-' . $post_id . '_form-' . $ratingFormId . '_review-' . $review->id . '">';

					$mainContent .= '<span style="display: none;"  itemprop="name">'.get_the_title($post_id).'</span>';

                    $mainContent .= '    <div class="cbratingboxinner ' . $reviewOptions['comment_status'] . ' reviews_rating_' . $theme_key . '_theme review_rating review_rating_review-' . $review->id . '">';

                    if (!empty($review->rating) && is_array($review->rating)) {

						if (intval($review->user_id)  > 0) {

							$user_url = get_author_posts_url($review->user_id);
							$name     = get_the_author_meta('display_name', $review->user_id);
							$gravatar = '';

							//finally check the settings
							if ($ratingFormArray ['show_user_avatar_in_review'] == '1') {
								$gravatar = get_avatar($review->user_id, 36);
								$gravatar       = apply_filters('cbrating_single_review_user_avatar', $gravatar, $review->user_id, $ratingFormArray, $review);
							}


							$name           = apply_filters('cbrating_single_review_user_name', $name, $review->user_id, $ratingFormArray, $review);

							$user_html = '<span itemprop="author" itemscope itemtype="http://schema.org/Person" class="user_gravatar">' . $gravatar . '<span itemprop="name">' . $name . '</span>' . '</span>';

							if (!empty($user_url) && $ratingFormArray ['show_user_link_in_review'] == '1') {
								$user_url       = apply_filters('cbrating_single_review_user_link', $user_url, $review->user_id, $ratingFormArray, $review);
								$user_html = '<a target="_blank" href="' . $user_url . '">' . $user_html . '</a>';
							}

						} else {
							//guest part

							$gravatar = '';
							$name     = (!empty($review->user_name) ? $review->user_name : esc_html__('Anonymous', 'cbratingsystem'));

							if ($ratingFormArray ['show_user_avatar_in_review'] == '1') {
								$gravatar 	= get_avatar(0, 36, 'gravatar_default');
								$gravatar   = apply_filters('cbrating_single_review_user_avatar', $gravatar, 0, $ratingFormArray, $review);
							}


							$name           = apply_filters('cbrating_single_review_user_name', $name, $review->user_id, $ratingFormArray, $review);

							$user_html = '<span itemprop="author" itemscope itemtype="http://schema.org/Person" class="user_gravatar">' . $gravatar . '.<span itemprop="name">'.$name .'</span></span>';
						}//end user part



                        //$user_html = apply_filters('cbrating_edit_review_user_info', $user_html, $review->user_id, $ratingFormArray, $reviewOptions, $review);




                        $mainContent .= '  <div class="reviews_user_details_' . $theme_key . '_theme review_user_details">
                                                           <p class="cbrating_user_name">' . $user_html
                            . '<span class="user_rate_value" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" title="' . sprintf(esc_html__('Rated %s out of 5', 'cbratingsystem'), (($review->average / 100) * 5)) . '"><meta itemprop="worstRating" content = "1">
														         ( <span itemprop="ratingValue">' . (($review->average / 100) * 5) . '</span> ' . esc_html__('out of', 'cbratingsystem') . '  <span itemprop="bestRating">5</span> )
														    </span>'
                            . '</p>'
                            . '<span class="user_rate_time"><a title="' . cbratingsystem_date_display($review->created) . '" href="' . get_permalink($post_id) . '#cbrating-' . $ratingFormId . '-review-' . $review->id . '"><i class="user_rate_time_regular">' . cbratingsystem_date_display($review->created) . '</i><i class="user_rate_time_relative">' . CBRatingSystemFunctions :: codeboxr_time_elapsed_string($review->created) . '</i></a></span>
                                                        </div>
                                                        <div class="clear" style="clear:both;"></div> ';

                        $mainContent .= '    <div data-form-id="' . $ratingFormId . '" class="all-criteria-wrapper all_criteria_warpper_' . $theme_key . '_theme  all-criteria-wrapper-form-' . $ratingFormId . ' all-criteria-wrapper-form-' . $ratingFormId . $theme_key . '_theme">';


                        foreach ($review->rating as $criteriId => $value) {
                            if (is_numeric($criteriId)) {
                                $value                                                                                                            = (($value / 100) * $review->rating[$criteriId . '_starCount']);
                                $jsArray['review'][$review->id]['ratingForm']                                                                     = $ratingFormId;
                                $jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_value']       = $value;
                                $jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_count']       = $review->rating[$criteriId . '_starCount'];
                                $jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_redOnlyHint'] = $review->rating[$criteriId . '_stars'][$value - 1];


                                $jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_hints'] = $review->rating[$criteriId . '_stars'];



                                $mainContent .= '<div data-form-id="' . $ratingFormId . '" data-criteria-id="' . $criteriId . '" class="criteria_warpper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $criteriId . ' criteria-id-wrapper-' . $criteriId . '-form-' . $ratingFormId . ' ">
			                                                <div class="criteria_label_warpper_' . $theme_key . '_theme criteria-label-wrapper">
			                                                    <span class="criteria-label criteria-label-id-' . $criteriId . '" ><strong>' . $review->custom_criteria[$criteriId]['label']. '</strong></span>
			                                                </div>
			                                                <div  id="criteria-star-wrapper-' . $review->id . '"  data-form-id="' . $ratingFormId . '" data-criteria-id="' . $criteriId . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $criteriId . '-form-' . $ratingFormId . '"></div>
			                                                <div class="readonly_criteria_average_label_' . $theme_key . '_theme readonly-criteria-average-label criteria-average-label-form-' . $ratingFormId . '-label-' . $criteriId . '">
			                                                    <span class="starTitle">' . (sanitize_text_field($review->rating[$criteriId . '_stars'][($value - 1)])) . '</span>
			                                                </div>
		                                            </div>';

                            }
                        }//end loop

                        $mainContent .= '</div><div class="clear" style="clear:both;"></div>';

                        // Question Display part.
                        $mainContent .= '    <div data-form-id="' . $ratingFormId . '" class="question_wrapper_' . $theme_key . '_theme question-wrapper question-wrapper-form-' . $ratingFormId . '">';

                        if (!empty($review->question) && is_array($review->question)) {


                            foreach ($review->question as $questionId => $value) {

                                $single_question = $review->custom_question[$questionId];
                                $type            = $single_question['field']['type'];

                                if (is_array($value)) {

                                    $seperated = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']) : 0;
                                    $fieldArr  = $single_question['field'][$type];


                                    $valuesText = array();

                                    foreach ($value as $key => $val) {

                                        $valuesText[$review->id][$questionId][] = '<strong>' . stripcslashes($fieldArr[$key]['text']) . '</strong>';
                                    }

                                    if ((!empty($valuesText))) {
                                        $mainContent .= '
		                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
		                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
		                                                <span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? stripslashes($review->custom_question[$questionId]['title']): '') . '</span>
		                                                <span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
		                                                <span class="answer"><strong>' . (implode(', ', $valuesText[$review->id][$questionId])) . '</strong></span>
		                                            </div>
		                                        </div>
		                                        ';
                                    }
                                } else {


                                    //$single_question =  $review->custom_question[$questionId];
                                    $seperated = isset($single_question['field']['type']['seperated']) ? intval($single_question['field']['type']['seperated']) : 0;


                                    if ($seperated == 0) {
                                        if ($type == 'text') {
                                            $mainContent .= '
			                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? stripslashes($review->custom_question[$questionId]['title']) : '') . '</span>
			                                                <span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
			                                                <span class="answer"><strong>' . $value . '</strong></span>
			                                            </div>
			                                        </div>';
                                        } else {
                                            $fieldArr = $single_question['field'][$type];
                                            $mainContent .= '
			                                        <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question_id_wrapper question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
			                                            <div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
			                                                <span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? stripslashes($review->custom_question[$questionId]['title']): '') . '</span>
			                                                <span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
			                                                <span class="answer"><strong>' . (($value == 1) ? esc_html__('Yes', 'cbratingsystem') : esc_html__('No', 'cbratingsystem')) . '</strong></span>
			                                            </div>
			                                        </div>';
                                        }

                                    }
                                }
                                //end of else
                            }
                            //end of foreach
                        }
                        $mainContent .= '    </div>
                                        	<div class="clear" style="clear:both;"></div>';

                        // Comment Display part
                        if (!empty($review->comment) && is_string($review->comment)) {


                            $comment = $review->comment;



                            $comment_output = '<p class="comment" itemprop="description">' . stripslashes($review->comment) . '</p>';


                            if ($reviewOptions['comment_status'] != 'approved') {

                                if ($reviewOptions['comment_status'] == 'unverified') {
                                    //$review_status = 'Your comment is ' . $reviewOptions['comment_status'] . '[please check your mail to verify]';
                                    $review_status = sprintf(esc_html__('Your comment is %s [please check your mail to verify]', 'cbratingsystem'), $reviewOptions['comment_status']);
                                } else {
                                    $review_status = $reviewOptions['comment_status'];
                                }

                                $mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
		                                            	        <strong>' . esc_html__('Comment', 'cbratingsystem') . ': </strong> ' . $comment_output . ' (' . $review_status . ')' . '
		                                        	          </div>
		                                        	          <div class="clear" style="clear:both;"></div>
		                                     ';
                            } else {
                                $mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
		                                            	            <strong>' . esc_html__('Comment', 'cbratingsystem') . ':</strong> ' . $comment_output . '
		                                        	          </div>
		                                        	         <div class="clear" style="clear:both;"></div>
		                                     ';

                            }

                        }


                    }

                    $mainContent .= '</div>';
                    $mainContent .= '</div><div class="clear" style="clear:both;"></div>';
                    $shownReviews++;
                }
                // end foreach 
                $output .= $mainContent;
            }

            // end  if ( ! empty( $reviews ) and is_array( $reviews ) )
            $jsSettings = self::front_end_review_js_settings($reviews, $jsArray, $post_id, $ajax);
            $output .= '<script type="text/javascript">' . $jsSettings . '</script>';

            if ($ajax === true) {
                return array(

                    'html' => $mainContent . '<script type="text/javascript">' . $jsSettings . '</script>',

                );
            }
            $output = array($output, $review);

            return $output;

        }
        // end  empty( $reviews[0] 

    }

    /**
     * Front end js review
     *
     * @param      $reviews
     * @param      $jsArray
     * @param      $post_id
     * @param bool $ajax
     *
     * @return string
     */
    public static function front_end_review_js_settings($reviews, $jsArray, $post_id, $ajax = false)
    {
        $js = '';

        If (!empty($jsArray['review'])) {
            foreach ($jsArray['review'] as $review => $reviewArr) {
                $JSON['review_' . $review] = array(
                    'img_path'    => CBRatingSystem::ratingIconUrl(),
                    'options'     => json_encode($jsArray['review'][$review]['criteria']),
                    'cancel_hint' => esc_html__('Click to cancel given rating', 'cbratingsystem'),
                    'is_rated'    => 1,
                );
            }

            if ($ajax === true) {
                $js .= '
                    var reviewContent_post_' . $post_id . '_form_' . $reviewArr['ratingForm'] . '_ajax = ' . json_encode(
                        $JSON
                    ) . ';
                ';
            } else {
                $js .= '
                    var reviewContent_post_' . $post_id . '_form_' . $reviewArr['ratingForm'] . ' = ' . json_encode(
                        $JSON
                    ) . ';
                ';
            }
        }

        $js .= '
            var cbrpRatingFormReviewContent = ' . json_encode(
                array(
                    'failure_msg' => esc_html__('An error occurred while processing the data. Please ensure that all data are resonable. If problem persist please contact the administrator.', 'cbratingsystem'),
                )
            ) . ';
        ';

        return $js;
    }

    /**
     * @param $ratingFormArray
     * @param $post_id
     * @param int $user_id
     *
     * @return mixed
     */
    public static function viewPerCriteriaRatingResult($ratingFormArray, $post_id, $user_id = 0)
    {

        if (!empty($post_id)) {
            if (!is_array($ratingFormArray) && is_numeric($ratingFormArray)) {
                $ratingFormArray = CBRatingSystemData::get_ratingForm($ratingFormArray);
            }

            if (is_array($ratingFormArray)) {

                $data['form_id']                                                                 = $ratingFormArray['id'];
                $data['ratings']                                                                 = CBRatingSystemData::get_ratings($ratingFormArray['id'], $post_id);
                $data['avgRatingArray']['ratingsCount'][$ratingFormArray['id'] . '-' . $post_id] = count($data['ratings']);

                foreach ($data['ratings'] as $k => $rating) {
                    foreach ($rating['rating'] as $cId => $value) {
                        if (is_numeric($cId)) {
                            $data['ratingsValueArray'][$k]['criterias'][$cId]['value']          = $value;
                            $data['ratingsValueArray'][$k]['criterias'][$cId]['count']          = count($ratingFormArray['custom_criteria'][$cId]['stars']);
                            $data['ratingsValueArray'][$k]['criterias'][$cId]['criteria_array'] = $ratingFormArray['custom_criteria'][$cId]['stars'];
                        }
                    }
                    $data['ratingsValueArray'][$k]['user_id'] = $data['ratings'][$k]['user_id'];
                }
                $data['criteria'] = $ratingFormArray['custom_criteria'];


                $userIdToMatch = array(
                    'guest'      => array(0),
                    'registered' => -1,
                );


                $userWithCustomRole = new WP_User_Query(array('role' => $ratingFormArray['editor_group'][0], 'fields' => 'ID'));

                if (!empty($userWithCustomRole->total_users)) {
                    $userIds = $userWithCustomRole->results;
                    $userIdToMatch['editor'] = $userIds;
                }

                $data['userIdToMatch'] = $userIdToMatch;
                CBRatingSystemCalculation::allUserPerCriteriaAverageCalculation($data, $post_id);
                return $data['avgRatingArray'];
            }
        }
    }

    /**
     * Display Checkbox field
     *
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     *
     * @return string
     */
    public static function display_checkbox_field($questionId, $questionOption, $required = array(), $ratingFormArray = array(), $submit_data = '', $hidden = false)
    {

        $seperated = (isset($questionOption['field']['checkbox']['seperated'])) ? intval($questionOption['field']['checkbox']['seperated']) : 0;
        unset($questionOption['field']['checkbox']['seperated']);

        $output = '';

        if ($seperated == 1) {
            //multiple answer
            $checkboxCount = count($questionOption['field']['checkbox']);

            $output .= '
                <div data-q-id="' . $questionId . '" class="form_item_checkbox form_item_field_display form_item_checkbox_q_id-' . $questionId . ' ">
                    <label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes($questionOption['title']) . $required['required_text'] . '</label>
            ';

            for ($checkboxId = 0; $checkboxId < $checkboxCount; $checkboxId++) {
                if (!empty($questionOption['field']['checkbox'][$checkboxId]['text'])) {
                    $output .= '
                        <div class="add_left_margin">
                            <input  '.((is_array($submit_data) && isset($submit_data[$checkboxId]) && intval($submit_data[$checkboxId]) == 1 )? ' checked="checked" ':'').' data-form-id="' . $ratingFormArray['id'] . '" data-checkbox-field-text-id="' . $checkboxId . '" data-q-id="' . $questionId . '" type="checkbox" id="edit-custom-question-checkbox-field-text-' . $checkboxId . '-q-' . $questionId . '"
                                name="question[' . $ratingFormArray['id'] . '-' . $questionId . '-' . $checkboxId . ']"
                                value="' . ($checkboxId + 1) . '"
                                class="form-text ' . $required['required_class'] . ' custom-question-field-checkbox-q-id-' . $questionId . ' custom-question-field-checkbox-' . $checkboxId . '-label-text-q-' . $questionId . '">
                            <label class="question-field-label question-field-checkbox-label label-q-' . $questionId . '-checkbox-' . $checkboxId . ' option mouse_normal"
                                for="edit-custom-question-checkbox-field-text-' . $checkboxId . '-q-' . $questionId . '"
                                >' . stripslashes($questionOption['field']['checkbox'][$checkboxId]['text']) . '</label>

                        </div>
                    ';
                }
            }
            $output .= '    </div>';
        } else {
            //single answer
            $output .= '<label style="margin-right:20px;" for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes($questionOption['title']) . $required['required_text'] . '</label>';
            $output .= '<input '.((!is_array($submit_data) && ($submit_data != '') && intval($submit_data) == 1 )? ' checked="checked" ':'').' data-q-id="' . $questionId . '" id="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '" class="form-text ' . $required['required_class'] . ' " type="checkbox" name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']" value="1" />';
        }

        return $output;
    }

    /**
     * Display Radio Field
     *
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     *
     * @return string
     */
    public static function display_radio_field($questionId, $questionOption, $required = array(), $ratingFormArray = array(), $submit_data = '', $hidden = false)
    {

        $seperated = 1;
        unset($questionOption['field']['radio']['seperated']);
        $output = '';

        if ($seperated == 1) {
            $radioCount = count($questionOption['field']['radio']);

            $output .= '
                <div data-q-id="' . $questionId . '" class="form_item_radio form_item_field_display form_item_radio_q_id-' . $questionId . ' ">
                    <label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes($questionOption['title']) . $required['required_text'] . '</label>
            ';

            for ($radioId = 0; $radioId < $radioCount; $radioId++) {
                if (!empty($questionOption['field']['radio'][$radioId]['text'])) {
                    $output .= '
                        <div class="add_left_margin">
                            <input '.(($submit_data == ($radioId + 1))? ' checked="checked" ':'').' data-form-id="' . $ratingFormArray['id'] . '" data-radio-field-text-id="' . $radioId . '" data-q-id="' . $questionId . '" type="radio" id="edit-custom-question-radio-field-text-' . $radioId . '-q-' . $questionId . '"
                                name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']"
                                value="' . ($radioId + 1) . '"
                                class="form-text ' . $required['required_class'] . ' custom-question-field-radio-q-id-' . $questionId . ' custom-question-field-radio-' . $radioId . '-label-text-q-' . $questionId . '">
                            <label class="question-field-label question-field-radio-label label-q-' . $questionId . '-radio-' . $radioId . ' option mouse_normal"
                                for="edit-custom-question-radio-field-text-' . $radioId . '-q-' . $questionId . '"
                                >' . stripslashes($questionOption['field']['radio'][$radioId]['text']) . '</label>
                        </div>
                    ';
                }
            }
            $output .= '    </div>';
        }

        return $output;
    }

    /**
     * Displays Text field
     *
     * @param $questionId
     * @param $questionOption
     * @param array $required
     * @param array $ratingFormArray
     * @param bool $hidden
     *
     * @return string
     */
    public static function display_text_field($questionId, $questionOption, $required = array(), $ratingFormArray = array(), $submit_data = '', $hidden = false)
    {

        $output = '';
        $output .= '<label for="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '">' . stripslashes($questionOption['title']) . $required['required_text'] . '</label>';
        $output .= '<input '.(($required['required_status'])? 'required': '').' data-q-id="' . $questionId . '" id="question-form-' . $ratingFormArray['id'] . '-q-' . $questionId . '" class="' . $required['required_class'] . ' add_left_margin" type="text" name="question[' . $ratingFormArray['id'] . '-' . $questionId . ']" value="'.$submit_data.'"  />';

        return $output;
    }
}

/*
** todo::  need to move this function into a class, may be into a helper class. I don't understand why this function is kept standalone here
*/
function cbratingsystem_user_roles_front($useCase = 'admin')
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
                        'name' => esc_html__("Guest", 'cbratingsystem'),
                    ),
                ),
                'Registered' => get_editable_roles(),
            );
            break;

        case 'front':
            foreach (get_editable_roles() as $role => $roleInfo) {
                $userRoles[$role] = $roleInfo['name'];
            }
            $userRoles['guest'] = esc_html__("Guest", 'cbratingsystem');
            break;
    }

    return $userRoles;

}