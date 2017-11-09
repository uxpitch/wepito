<?php

class CBRatingSystemFrontReview extends CBRatingSystemFront
{

    /**
     * @param array $ratingFormArray
     * @param       $postid
     * @param int $page
     *
     * @return string
     */
    public static function rating_reviews($ratingFormArray = array(), $postid, $page = 1)
    {


        if (empty($ratingFormArray)) {
            $defaultFormId = get_option('cbratingsystem_defaultratingForm');
            $form_id       = apply_filters('rating_form_array', $defaultFormId);

            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
        }

        //note followed this simple example, it works
        if ($page <= 0) $page = 1;


        $perpage = (isset($ratingFormArray['review_limit']) && intval($ratingFormArray['review_limit']) > 0) ? intval($ratingFormArray['review_limit']) : 10;


        $theme_key = get_option('cbratingsystem_theme_key', 'basic');


        $reviewOptions['limit']['perpage'] = $perpage;
        $reviewOptions['limit']['page']    = $page;

        $reviewOptions['theme']   = $theme_key;
        $reviewOptions['post_id'] = $postid;


        $output = self::build_user_rating_review($reviewOptions, $ratingFormArray);

        return $output;
    }


    /**
     * rating_reviews_shorttag description
     *
     * @param  array $ratingFormArray
     * @param  integer $postid
     * @param  integer $page
     *
     * @return string
     */
    public static function rating_reviews_shorttag($ratingFormArray = array(), $postid, $page = 1)
    {
        //this function needs take care

        if (empty($ratingFormArray)) {

            $defaultFormId   = get_option('cbratingsystem_defaultratingForm');
            $form_id         = apply_filters('rating_form_array', $defaultFormId);
            $ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
        }

        if ($page <= 0) $page = 1;

		$perpage = (isset($ratingFormArray['review_limit']) && intval($ratingFormArray['review_limit']) > 0) ? intval($ratingFormArray['review_limit']) : 10;


		$reviewOptions['limit']['page']    = $page;
		$reviewOptions['limit']['perpage'] = $perpage;
		$reviewOptions['theme']            = $ratingFormArray['theme_key'];
		$reviewOptions['post_id']          = $postid;
		$reviewOptions['form_id']          = $ratingFormArray['id'];

        $output = self::build_user_rating_review($reviewOptions, $ratingFormArray);

        return $output;
    }

    /**
     * Ajax review listing
     */
    public static function cbReviewAjaxFunction()
    {
        if (isset($_POST['cbReviewData']) and !empty($_POST['cbReviewData'])) {
            $returnedData = $_POST['cbReviewData'];

            if (wp_verify_nonce($returnedData['nonce'], 'cb_ratingForm_front_review_nonce_field')) {

                $theme_key = get_option('cbratingsystem_theme_key', 'basic');

                $option['form_id'] = $returnedData['ratingFormID'];
                $option['post_id'] = $returnedData['postID'];
                $option['theme']   = $theme_key;

                //limit
                $option['limit']['page']    = $returnedData['page'] + 1; //go for next page, increase page value by 1
                $option['limit']['perpage'] = $returnedData['perpage'];


                $ratingFormArray = CBRatingSystemData::get_ratingForm($returnedData['ratingFormID']);
                $results         = self::build_user_rating_review($option, $ratingFormArray, true);
                $encoded         = json_encode($results);
                echo $encoded;

            }
        }

        die();
    }

    /**
     * @param $comment_status
     * @param $session
     * @param $ip
     *
     * @return bool
     */
    public static function check_cpmment_status($comment_status, $session, $ip)
    {
        global $current_user, $wpdb;

        $show_own_review = false;
        $comment_show    = false;
        $user_session    = '';
        $user_ip         = '';

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            $user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME];
            $user_ip      = CBRatingSystem::get_ipaddress();
        } elseif ($user_id > 0) {
            $user_session = 'user-' . $user_id; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();
        }
        if ($user_session == $session && $user_ip == $ip) {
            $show_own_review = true;
        }
        if ($show_own_review || $comment_status == 'approved') {
            $comment_show = true;
        } else {
            $comment_show = false;
        }

        return $comment_show;
    }

    /**
     * @param array $comment
     * @param       $session
     * @param       $ip
     *
     * @return bool
     */
    public static function check_permission($comment = array(), $session, $ip)
    {
        $ratingFormArray['comment_view_allowed_users'] = $comment;

        global $current_user, $wpdb;
        $user_id = get_current_user_id();

        $showreview = false;



        if ($user_id > 0) {
            $role               = $wpdb->prefix . 'capabilities';
            $current_user->role = array_keys($current_user->$role);
            $role               = $current_user->role[0];
        }

        $user_id = get_current_user_id();
        if ($user_id == 0 && in_array('guest', $ratingFormArray['comment_view_allowed_users'])) {
            $showreview = true;
        } else if ($user_id > 0 && in_array($role, $ratingFormArray['comment_view_allowed_users'])) {
            $showreview = true;
        }

        if ($user_id == 0) {
            $user_session = $_COOKIE[CB_RATINGSYSTEM_COOKIE_NAME]; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();
        } elseif ($user_id > 0) {
            $user_session = 'user-' . $user_id; //this is string
            $user_ip      = CBRatingSystem::get_ipaddress();
        }

        $show_own_review = false;
        //allowing user to show his own comment
        if ($user_session == $session && $user_ip == $ip) {
            $show_own_review = true;
        }

        if ($show_own_review || $showreview) {
            $show_review_final = true;
        } else {
            $show_review_final = false;
        }

        return $show_review_final;
    }

    /**
     * Returns Reviews Html Output
     *
     * @param  array $reviewOptions
     * @param  array $ratingFormArray
     * @param  boolean $ajax
     *
     * @return string
     */
    public static function build_user_rating_review($reviewOptions = array(), $ratingFormArray = array(), $ajax = false, $approvedonly = true)
    {

        global $wpdb;

        $postID = isset($reviewOptions['post_id']) ? $reviewOptions['post_id'] : get_the_ID();
        $postID = (int)$postID;

		if (!empty($reviewOptions['form_id'])) {
			$form_id = $reviewOptions['form_id'];
		} else {
			$defaultFormId = get_option('cbratingsystem_defaultratingForm');
			$form_id       = apply_filters('rating_form_array', $defaultFormId);
		}

		$form_id = (int)$form_id;


		if (!isset($reviewOptions['limit']['perpage'])) { // As we only need to get this ratingFormArray from DB to get the offset value.
			$ratingFormArray = CBRatingSystemData::get_ratingForm($form_id);
		}

		if (is_string($reviewOptions['theme']) and !empty($reviewOptions['theme'])) {
			$theme_key = $reviewOptions['theme'];
		} else {
			$theme_key = get_option('cbratingsystem_theme_key', 'basic');
		}

		$page = isset($reviewOptions['limit']['page']) ? $reviewOptions['limit']['page'] : 1;
		if ($page <= 0) $page = 1;
		$perpage = isset($reviewOptions['limit']['perpage']) ? $reviewOptions['limit']['perpage'] : $ratingFormArray['review_limit'];


		//let's confirm the limit array once again
		$reviewOptions['limit']['page']    = $page;
		$reviewOptions['limit']['perpage'] = $perpage;



		if ($approvedonly) {
			$totalLimit = $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM " . $wpdb->prefix . "cbratingsystem_user_ratings WHERE comment_status='approved' AND form_id='%d' AND post_id='%d'", $form_id, $postID));
		} else {
			$totalLimit = $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM " . $wpdb->prefix . "cbratingsystem_user_ratings WHERE form_id='%d' AND post_id='%d'", $form_id, $postID));

		}

		$reviews = array();

		if ($totalLimit > $perpage) {
			$showLoadMoreButton = true;
			$reviews            = CBRatingSystemData::get_user_ratings_with_ratingForm(array($form_id), array($postID), array(), '', 'created', 'DESC', $reviewOptions['limit'], true);
		} else {
			$showLoadMoreButton = false;
			$reviews            = CBRatingSystemData::get_user_ratings_with_ratingForm(array($form_id), array($postID), array(), '', 'created', 'DESC', array(), true);
		}

		$output       = $mainContent = '';
		$jsArray      = array();
		$shownReviews = 0;


		$review_output = '';

		if (!empty($reviews[0])) {

			$review_heading = '<h3 id="cbratingfrom_reviews_title-' . $form_id . '" class="cbratingfrom_reviews_title">' . sprintf(__("Reviews (%d)", 'cbratingsystem'), $totalLimit) . '</h3>';
			$review_output .= '<div id="reviews_container_' . $postID . '" data-post-id="' . $postID . '" data-form-id="' . $form_id . '" class="reviews_container reviews_container_' . $theme_key . '_theme  reviews_container_post-' . $postID . '_form-' . $form_id . ' ">';

			$review_output .= '<div data-post-id="' . $postID . '" data-form-id="' . $form_id . '" class="reviews_container_div_' . $theme_key . '_theme reviews_container_div reviews_container_div_post-' . $postID . '_form-' . $form_id . ' ">';



			if (!empty($reviews) && is_array($reviews)) {

				//reviews loop
				//google schema http://schema.org/Rating
				foreach ($reviews as $reviewKey => $review) {

					$review_post_id = $review->post_id;


					$comment_status = self::check_cpmment_status($review->comment_status, $review->user_session, $review->user_ip);

					$show_reviews_user = self::check_permission($ratingFormArray['comment_view_allowed_users'], $review->user_session, $review->user_ip);

					if ($show_reviews_user && $comment_status) {

						$mainContent .= '<div itemprop="review" itemscope itemtype="http://schema.org/Review" id="cbrating-' . $form_id . '-review-' . $review->id . '" data-review-id="' . $review->id . '" data-post-id="' . $review_post_id . '" data-form-id="' . $form_id . '" class="reviews_wrapper_' . $theme_key . '_theme review_wrapper review_wrapper_post-' . $review_post_id . '_form-' . $form_id . ' review_wrapper_post-' . $review_post_id . '_form-' . $form_id . '_review-' . $review->id . '">';

						$mainContent .= '<span style="display: none;"  itemprop="name">'.get_the_title($postID).'</span>';

						$mainContent .= '    <div class="reviews_rating_' . $theme_key . '_theme review_rating review_rating_review-' . $review->id . '">';

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




							$modified_review = (array)$review;

							//$user_html = apply_filters('cbrating_edit_review_user_info', $user_html, $review->user_id, $ratingFormArray, $modified_review, $review);

							$single_score_avg  	= (($review->average / 100) * 5);
							$single_score_full 	= 5;

							$mainContent .= ''; //ignore now

							$mainContent .= '    <div class="reviews_user_details_' . $theme_key . '_theme review_user_details">
													   <p class="cbrating_user_name">' . $user_html.'<span class="cbratingreview_listing" data-scoreavg="'.$single_score_avg.'" data-scorefull="'.$single_score_full.'"></span>'.'<span class="user_rate_value" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" title="' . sprintf(__('Rated %s out of 5', 'cbratingsystem'), (($review->average / 100) * 5)) . '"><meta itemprop="worstRating" content = "1">
															 ( <span itemprop="ratingValue">' . (($review->average / 100) * 5) . '</span> ' . __('out of', 'cbratingsystem') . '  <span itemprop="bestRating">5</span> )
														</span>'
                                                    . '</p>'
                                                    . '<span class="user_rate_time"><a title="' . cbratingsystem_date_display($review->created) . '" href="' . get_permalink($review_post_id) . '#cbrating-' . $form_id . '-review-' . $review->id . '"><i class="user_rate_time_regular">' . cbratingsystem_date_display($review->created) . '</i><i class="user_rate_time_relative">' . CBRatingSystemFunctions :: codeboxr_time_elapsed_string($review->created) . '</i></a></span>
                                                </div>
                                                <div class="clear" style="clear:both;"></div> ';

							$mainContent .= '    <div data-form-id="' . $form_id . '" class="all-criteria-wrapper all_criteria_warpper_' . $theme_key . '_theme  all-criteria-wrapper-form-' . $form_id . ' all-criteria-wrapper-form-' . $form_id . $theme_key . '_theme">';


							foreach ($review->rating as $criteriId => $value) {

								if (is_numeric($criteriId)) {
									$firstLabel = '';
									$value      = (($value / 100) * $review->rating[$criteriId . '_starCount']);

									$jsArray['review'][$review->id]['ratingForm']                                                                     = $form_id;
									$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_value']       = $value;
									$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_count']       = $review->rating[$criteriId . '_starCount'];
									$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_redOnlyHint'] = $review->rating[$criteriId . '_stars'][$value - 1];
									$jsArray['review'][$review->id]['criteria']['review_' . $review->id . '_criteria_' . $criteriId . '_hints']       = $review->rating[$criteriId . '_stars'];


									$mainContent .= '<div data-form-id="' . $form_id . '" data-criteria-id="' . $criteriId . '" class="criteria_warpper_' . $theme_key . '_theme criteria-wrapper criteria-id-wrapper-' . $criteriId . ' criteria-id-wrapper-' . $criteriId . '-form-' . $form_id . ' ">
															<div class="criteria_label_warpper_' . $theme_key . '_theme criteria-label-wrapper">
																<span class="criteria-label criteria-label-id-' . $criteriId . '" ><strong>' . esc_html($review->custom_criteria[$criteriId]['label']) . '</strong></span>
															</div>
															<div data-form-id="' . $form_id . '" data-criteria-id="' . $criteriId . '" class="criteria-star-wrapper criteria-star-wrapper-id-' . $firstLabel . ' criteria-star-wrapper-id-' . $criteriId . '-form-' . $form_id . '" id="criteria-star-wrapper-' . $review->id . '"></div>
															<div class="readonly-criteria-average-label readonly_criteria_average_label_' . $theme_key . '_theme  criteria-average-label-form-' . $form_id . '-label-' . $criteriId . '">
																<span class="starTitle">' . (sanitize_text_field($review->rating[$criteriId . '_stars'][($value - 1)])) . '</span>
															</div>
														 </div> ';

								}
							}//end loop

							$mainContent .= '</div><div class="clear" style="clear:both;"></div>';



							// Question Display part.
							$mainContent .= '<div data-form-id="' . $form_id . '" class="question_wrapper_' . $theme_key . '_theme question-wrapper question-wrapper-form-' . $form_id . '">';


							if (!empty($review->question) && is_array($review->question)) {

								foreach ($review->question as $questionId => $question) {
									$single_question = $review->custom_question[$questionId];
									$type            = $single_question['field']['type'];


									if (is_array($question)) {

										$fieldArr = $single_question['field'][$type];

										$separated = isset($fieldArr['seperated']) ? intval($fieldArr['seperated']) : 0;

										$valuesText = array();

										foreach ($question as $key => $val) {
											$valuesText[$review->id][$questionId][] = '<strong>' . stripcslashes($fieldArr[$key]['text']) . '</strong>';
										}


										if (!empty($valuesText)) {
											$mainContent .= '
											<div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
												<div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
													<span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? __(stripslashes($review->custom_question[$questionId]['title']), 'cbratingsystem') : '') . '</span>
													<span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
													<span class="answer"><strong>' . (implode(', ', $valuesText[$review->id][$questionId])) . '</strong></span>
												</div>
											</div>';
										}

									} else {

										$separated = isset($single_question['field'][$type]['seperated']) ? intval($single_question['field'][$type]['seperated']) : 0;


										//either checkbox single select or text area
										if ($separated == 0) {

											if ($type == 'text') {
												$mainContent .= '
												<div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
													<div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
														<span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? __(stripslashes($review->custom_question[$questionId]['title']), 'cbratingsystem') : '') . '</span>
														<span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
														<span class="answer"><strong>' . $question . '</strong></span>
													</div>
												</div>';

											} else {
												$mainContent .= '
												<div data-form-id="' . $form_id . '" data-q-id="' . $questionId . '" class="question_id_wrapper_' . $theme_key . '_theme question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $form_id . ' ">
													<div class="question_label_wrapper_' . $theme_key . '_theme question-label-wrapper">
														<span class="question-label question-label-id-' . $questionId . '" >' . (isset($review->custom_question[$questionId]) ? __(stripslashes($review->custom_question[$questionId]['title']), 'cbratingsystem') : '') . '</span>
														<span class="question-label-hiphen">' . (isset($review->custom_question[$questionId]) ? ' - ' : '') . '</span>
														<span class="answer"><strong>' . (($question == 1) ? __("Yes", 'cbratingsystem') : __("No", 'cbratingsystem')) . '</strong></span>
													</div>
												</div>';

											}

										}
									}
								}
								//end each single question loop
							}
							$mainContent .= '    </div>
									<div class="clear" style="clear:both;"></div>';


							///////////////////////////////////////////
							// Comment Display part
							if (!empty($review->comment) && is_string($review->comment)) {

								$comment = $review->comment;
								$comment_output = '<p itemprop="description" class="comment">' . stripslashes($comment) . '</p>';

								$mainContent .= '<div class="review_user_rating_comment_' . $theme_key . '_theme review_user_rating_comment">
												 <strong>' . esc_html__('Comment', 'cbratingsystem') . ': </strong> ' . $comment_output;
								if ($review->comment_status != 'approved') {

									$mainContent .= '<br> <strong>' . esc_html__('Comment Status', 'cbratingsystem') . ':</strong> ' . ucfirst($review->comment_status) . '

												</div>
												<div class="clear" style="clear:both;"></div>
								 ';
								} else {
									$mainContent .= '

											 </div>
											<div class="clear" style="clear:both;"></div>
								';
								}

							}
							else{
								$mainContent .= '<p itemprop="description" style="display: none;"></p>';
							}
						}

						$mainContent .= '</div>';
						$mainContent .= '</div>
					<div class="clear" style="clear:both;"></div>';

						$shownReviews++;
					}
					// end of if approved
				}
				//end for each review
				$review_output .= $mainContent;
			}

			$review_output .= '</div>';
			$review_output .= '</div>';

			if ($shownReviews > 0) {
				$output .= $review_heading;
			}
			$output .= $review_output;


			if ($showLoadMoreButton === true) {
				$output .= '<p class="cbratingload_more_button load_more_button load_more_button_' . $theme_key . '_theme  load_more_button_form-' . $form_id . '_post-' . $postID . '" >';                     $output .= '<a  href="#" data-form-id="' . $form_id . '" data-post-id="' . $postID . '" data-page="' . $page . '" data-perpage="' . $perpage . '"  clickable="true">' . __('Load More', 'cbratingsystem');
				$output .= '<span style="display:none;" class="cbrating_waiting_icon cbrating_waiting_icon_form-' . $form_id . '_post-' . $postID . '"><img alt="' . __("Loading", 'cbratingsystem') . '" src="' . CB_RATINGSYSTEM_PLUGIN_DIR_URL . 'images/ajax-loader.gif" /></span>';
				$output .= '<input type="hidden" id="cb_ratingForm_front_review_nonce_field" value="' . wp_create_nonce('cb_ratingForm_front_review_nonce_field') . '" />';
				$output .= '</a>';
				$output .= "</p>";
				$output .= '<div  class="ratingFormReviewStatus ratingFormReviewStatus_' . $theme_key . '_theme  ratingFormReviewStatus-review-form-' . $form_id . '"></div>';
			}
		}


		$jsSettings = self::front_end_review_js_settings($reviews, $jsArray, $postID, $ajax);
		$output .= '<script type="text/javascript">' . $jsSettings . '</script>';

		$totalpages = ceil($totalLimit / $perpage);



		if ($ajax === true) {
			return array(
				'html'       => $mainContent . '<script type="text/javascript">' . $jsSettings . '</script>',
				'page'       => $page,
				'perpage'    => $perpage,
				'isFinished' => ($page >= $totalpages) ? '1' : '0'
			);
		}

		return $output;
    }

	/**
	 * @param      $reviews
	 * @param      $jsArray
	 * @param      $postId
	 * @param bool $ajax
	 *
	 * @return string
	 */
    public static function front_end_review_js_settings($reviews, $jsArray, $postId, $ajax = false)
    {
        $js = '';


        If (!empty($jsArray['review'])) {
            foreach ($jsArray['review'] as $review => $reviewArr) {
                $JSON['review_' . $review] = array(
                    'img_path'    => CBRatingSystem::ratingIconUrl(),
                    'options'     => json_encode($jsArray['review'][$review]['criteria']),
                    'cancel_hint' => esc_html__("Cancel rating", 'cbratingsystem'),
                    'is_rated'    => 1,
                );
            }

            if ($ajax === true) {
                $js .= '
                    var reviewContent_post_' . $postId . '_form_' . $reviewArr['ratingForm'] . '_ajax = ' . json_encode(
                        $JSON
                    ) . ';
                ';
            } else {
                $js .= '
                    var reviewContent_post_' . $postId . '_form_' . $reviewArr['ratingForm'] . ' = ' . json_encode(
                        $JSON
                    ) . ';
                ';
            }
        }

        $js .= '
            var cbrpRatingFormReviewContent = ' . json_encode(
                array(
                    'failure_msg' => esc_html__('Failed to load, please refresh this page.', 'cbratingsystem'),
                    'success_msg' => esc_html__('All loaded, you are at end.', 'cbratingsystem')
                )
            ) . ';
        ';


        return $js;
    }
}