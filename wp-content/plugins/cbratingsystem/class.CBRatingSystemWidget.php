<?php

/**
 * Class CBRatingSystemWidget
 */
class CBRatingSystemWidget extends WP_Widget
{

    function __construct()
    {
        parent::__construct('cbrp_top_rated', esc_html__('CBX Rating: Top Rated Posts', 'cbratingsystem'), array('description' => esc_html__('A widget to display top rated posts, pages or custom post types.', 'cbratingsystem')));
    }

    /**
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance)
    {
        global $wpdb;

        extract($args);

        $widget_id = $args['widget_id'];


        //need to make the html id unique for showing the rate display
        $widget_id_fresh = str_replace('_', '', $widget_id);
        $widget_id_fresh = str_replace('-', '', $widget_id_fresh);


        CBRatingSystem::load_scripts_and_styles();

        $whrOptn = array();


        $type    = $instance['type'];
        $date    = $instance['day'];
        $limit   = $instance['limit']; //limit
        $form_id = $instance['form_id'];
        $order   = $instance['order'];

        $whrOptn['order'] = $order;


        if ($date != 0) {

            $date                 = self::get_calculated_date($date);
            $whrOptn['post_date'] = $date;
        }

        $whrOptn['post_type'][] = $type;
        $whrOptn['form_id'][]   = $form_id; //added from v3.2.20


        $data = CBRatingSystemData::get_ratings_summary($whrOptn, 'avg', $order, true, $limit);

        $title = empty($options['widget_title']) ? esc_html__('Top Rated Posts', 'cbratingsystem') : $instance['widget_title'];
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        echo $before_title . $title . $after_title;
        ?>
        <ul class="cbrp-top-rated-wpanel">
            <?php if (!empty($data)) : ?>
                <?php foreach ($data as $newdata) : ?>
                    <li>
                        <script type="text/javascript">
                            jQuery(document).ready(function ($) {
                                $('#cbrp-top-rated<?php echo $widget_id_fresh.$newdata->post_id.'-'.$form_id; ?>').raty({
                                    half: true,
                                    path: '<?php echo CBRatingSystem::ratingIconUrl(); ?>',
                                    score: <?php echo ( ($newdata->per_post_rating_summary/100) * 5); ?>,
                                    readOnly: true,
                                    hintList: ['', '', '', '', ''],
                                    width: false
                                });
                            });
                        </script>
                        <span class="cbratingreview_listing_widget" id="cbrp-top-rated<?php echo $widget_id_fresh . $newdata->post_id . '-' . $form_id; ?>"
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
        echo $after_widget;
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    function form($instance)
    {

        $default = CBRatingSystem::get_default_ratingFormId();

        $instance = wp_parse_args((array)$instance, array('title' => esc_html__('Top Rated Posts', 'cbratingsystem'), 'order' => 'DESC', 'day' => 7, 'limit' => 10, 'type' => 'post', 'form_id' => $default));


        if ($instance) {
            $title = esc_attr($instance['title']);
        } else {
            $title = esc_html__('Top Rated Posts', 'cbratingsystem');
        }


        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e("Title", 'cbratingsystem') ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" type="text"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('day'); ?>"><?php _e("Display Last", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('day'); ?>" name="<?php echo $this->get_field_name('day'); ?>"
                    class="widefat" style="width:50%">
                <?php
                $no_of_days = array(1 => esc_html__('Last 24 hours', 'cbratingsystem'), 7 => esc_html__('Last 7 Days', 'cbratingsystem'), 15 => esc_html__('Last 15 Days', 'cbratingsystem'), 30 => esc_html__('Last 30 days', 'cbratingsystem'), 0 => esc_html__('All Time', 'cbratingsystem'));

                foreach ($no_of_days as $days_count => $day_label) {
                    echo "<option value='$days_count'";

                    selected($instance['day'], $days_count);

                    echo ">$day_label</option>";
                }
                ?>
            </select>
        </p>
        <p><?php _e('Note: Based on Post creation date', 'cbratingsystem'); ?></p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e("Count:", 'cbratingsystem') ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" type="text"
                   name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e("Post Type", 'cbratingsystem') ?>:</label>
            <select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>"
                    class="widefat" style="width: 55%">
                <?php

                foreach (CBRatingSystem::post_types() as $argType => $postTypes) {
                    echo '<optgroup label="' . $postTypes['label'] . '">';
                    foreach ($postTypes['types'] as $type => $typeLabel) {
                        echo "<option value='$type'";
                        selected($instance['type'], $type);
                        echo ">" . ucfirst($typeLabel) . "</option>";
                    }
                    echo '</optgroup>';
                }

                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e("Order", 'cbratingsystem') ?>:</label>
            <select id="<?php echo $this->get_field_id('order'); ?>"
                    name="<?php echo $this->get_field_name('order'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('ASC' => __('Ascending', 'cbratingsystem'), 'DESC' => __('Descending', 'cbratingsystem'));

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";
                    selected($instance['order'], $key);
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>


        <?php
        $action = array(
            'is_active' => true,
            'post_type' => $instance['type']
        );

        $ratingForms = CBRatingSystemData::get_ratingForms(true, $action);
        $ratingFormToShow = intval($instance['form_id']);


        ?>

        <p>
            <label for="<?php echo $this->get_field_id('form_id'); ?>"><?php _e("Form", 'cbratingsystem') ?>:</label>
            <select id="<?php echo $this->get_field_id('form_id'); ?>"
                    name="<?php echo $this->get_field_name('form_id'); ?>" class="widefat" style="width: 55%">

                <?php

                if (!empty($ratingForms)) {
                    foreach ($ratingForms as $ratingForm) {

                        if ($default == $ratingForm->id) {
                            $txt = ' (' . esc_html__('Default Form', 'cbratingsystem') . ')';
                        } else {
                            $txt = '';
                        }

                        if ($ratingFormToShow == $ratingForm->id) {
                            echo '<option selected value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        } else {
                            echo '<option value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        }
                    }
                }


                ?>
            </select>
        </p>
    <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    function update($new_instance, $old_instance)
    {
        $instance            = $old_instance;
        $instance['title']   = sanitize_text_field($new_instance['title']);
        $instance['day']     = intval($new_instance['day']);
        $instance['limit']   = intval($new_instance['limit']);
        $instance['type']    = $new_instance['type'];
        $instance['form_id'] = intval($new_instance['form_id']);
        $instance['order']   = $new_instance['order'];

        return $instance;
    }

    /**
     * @param $date
     *
     * @return bool|string
     */
    function get_calculated_date($date)
    {
        if (is_numeric($date)) {
            return date('Y-m-d H:i:s', strtotime("-$date days"));
        }
    }
}


/*Codeboxr Rating System Top Rated User*/

/**
 * Class CBRatingSystemUserWidget
 */
class CBRatingSystemUserWidget extends WP_Widget
{

    function __construct()
    {
        parent::__construct('cbrp_top_rated_user', esc_html__('CBX Rating: Top Rated User', 'cbratingsystem'), array('description' => esc_html__('A widget to display top rated user.', 'cbratingsystem')));
    }

    /**
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance)
    {

        global $wpdb;

        extract($args);

        $widget_id = $args['widget_id'];

        $widget_id_fresh = str_replace('_', '', $widget_id);
        $widget_id_fresh = str_replace('-', '', $widget_id_fresh);


        CBRatingSystem::load_scripts_and_styles();


        $posttype    = $instance['type'];
        $date        = $instance['day'];
        $limit       = $instance['limit'];
        $form_id     = $instance['form_id'];
        $user_id     = $instance['user_id'];
        $post_id     = $instance['post_id'];
        $post_filter = $instance['post_filter'];
        $order       = $instance['order'];
        $firstorder  = $instance['firstorder'];

        if ($date != 0) {

            $date                 = self::get_calculated_date($date);
            $whrOptn['post_date'] = $date;
        }

        $whrOptn['post_type']   = $posttype;
        $whrOptn['form_id']     = $form_id; //added from v3.2.20
        $whrOptn['user_id']     = $user_id;
        $whrOptn['form_id']     = $form_id; //added from v3.2.20
        $whrOptn['post_id']     = $post_id;
        $whrOptn['post_filter'] = $post_filter; //added from v3.2.20
        $whrOptn['order']       = $order; //added from v3.2.20
        $whrOptn['firstorder']  = $firstorder;

        $data = CBRatingSystemData::get_top_rated_post($whrOptn, false, $limit);


        $title = empty($options['widget_title']) ? esc_html__('Top Rated Posts', 'cbratingsystem') : $instance['widget_title'];
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        echo $before_title . $title . $after_title;
        ?>


        <ul class="cbrp-top-rated-wpanel" style="">
            <?php if (!empty($data)) : ?>
                <?php foreach ($data as $newdata) : ?>
                    <li>
                        <?php
                        $author_info = get_userdata((int)$newdata['post_author']);
                        ?>
                        <a href="<?php echo get_author_posts_url((int)$newdata['post_author']); ?>"><?php echo $author_info->display_name; ?></a> <?php echo $newdata['post_count']; ?> <?php esc_html_e('Posts', 'cbratingsystem'); ?>
                        <?php
                        $rating = (($newdata['rating'] / 100) * 5);
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function ($) {
                                $('#cbrp-top-rated-wg-<?php echo $widget_id_fresh.$newdata['post_author'].'_'.$widget_id; ?>').raty({
                                    half: true,
                                    path: '<?php echo CBRatingSystem::ratingIconUrl() ; ?>',
                                    score: <?php echo number_format($rating, 2, '.', ''); ?>,
                                    readOnly: true,
                                    hintList: ['', '', '', '', ''],
                                    width: false
                                });
                            });
                        </script>
                        <?php echo '<strong>' . number_format($rating, 2, '.', '') . '/5</strong>'; ?>

                        <div
                            id="cbrp-top-rated-wg-<?php echo $widget_id_fresh . $newdata['post_author'] . '_' . $widget_id ?>"
                            style=""></div>

                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><?php esc_html_e('No Results found', 'cbratingsystem'); ?></li>
            <?php endif; ?>
        </ul>
        <?php
        echo $after_widget;
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    function form($instance)
    {

        if ($instance) {
            $title = esc_attr($instance['title']);
        } else {
            $title = esc_html__('Top Rated Users', 'cbratingsystem');
        }

        if (array_key_exists('day', $instance)) {
            $timelimit = $instance['day'];
        } else {
            $timelimit = '0';
        }
        if (array_key_exists('user_id', $instance)) {
            $user_id = $instance['user_id'];
        } else {
            $user_id = '';
        }
        if (array_key_exists('post_id', $instance)) {
            $post_id = $instance['post_id'];
        } else {
            $post_id = '';
        }

        if (array_key_exists('limit', $instance)) {
            $limit = $instance['limit'];
        } else {
            $limit = '10';
        }
        if (array_key_exists('type', $instance)) {
            $posttype = $instance['type'];
        } else {
            $posttype = '0';
        }
        if (array_key_exists('post_filter', $instance)) {
            $post_filter = $instance['post_filter'];
        } else {
            $post_filter = 'post_type';
        }
        if (array_key_exists('form_id', $instance)) {
            $form_id = $instance['form_id'];
        } else {
            $form_id = 'form_id';
        }

        if (array_key_exists('order', $instance)) {
            $order = $instance['order'];
        } else {
            $order = 'DESC';
        }
        if (array_key_exists('firstorder', $instance)) {
            $firstorder = $instance['firstorder'];
        } else {
            $firstorder = 'post_count';
        }
        ?>


        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e("Title", 'cbratingsystem') ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" type="text"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>"/>
        </p>
        <!--user limit  -->
        <p>
            <label
                for="<?php echo $this->get_field_id('user_id'); ?>"><?php esc_html_e("User Ids ('Blank or comma separate ids ) ", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" type="text"
                   name="<?php echo $this->get_field_name('user_id'); ?>" value="<?php echo $user_id; ?>"/>
        </p>
        <!--time limit  -->
        <p>
            <label for="<?php echo $this->get_field_id('day'); ?>"><?php esc_html_e("Display Last", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('day'); ?>"
                    name="<?php echo $this->get_field_name('day'); ?>" class="widefat" style="width:50%">
                <?php
                //$no_of_days = array( 1 => '24 hours', 7 => 'Week', 15 => '15 Days', 30 => 'Month', 0 => 'All' );
                $no_of_days = array(1 => __('Last 24 hours', 'cbratingsystem'), 7 => __('Last 7 Days', 'cbratingsystem'), 15 => __('Last 15 Days', 'cbratingsystem'), 30 => __('Last 30 days', 'cbratingsystem'), 0 => __('All Time', 'cbratingsystem'));

                foreach ($no_of_days as $day => $day_label) {
                    echo "<option value='$day'";
                    if ($timelimit == $day) {
                        echo "selected='selected'";
                    }
                    echo ">$day_label</option>";
                }
                ?>
            </select>
        </p>
        <p><?php esc_html_e('Note: Based on Post creation date', 'cbratingsystem'); ?></p>
        <!--result limit  -->
        <p>
            <label
                for="<?php echo $this->get_field_id('limit'); ?>"><?php esc_html_e('No. To Display', 'cbratingsystem') ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" type="text"
                   name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit; ?>"/>
        </p>
        <!--post id -->
        <p>
            <label
                for="<?php echo $this->get_field_id('post_id'); ?>"><?php esc_html_e("Post Ids ('Blank or comma separate ids ) ", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('post_id'); ?>" type="text"
                   name="<?php echo $this->get_field_name('post_id'); ?>" value="<?php echo $post_id; ?>"/>
        </p>
        <!--post type id -->
        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php esc_html_e("Post Type", 'cbratingsystem') ?>
            </label>
            <select id="<?php echo $this->get_field_id('type'); ?>"
                    name="<?php echo $this->get_field_name('type'); ?>" class="widefat" style="width: 55%">
                <?php
                echo "<option value='0'";
                if ($posttype == 0) {
                    echo "selected='selected'";
                }
                echo ">" . ucfirst('All') . "</option>";


                foreach (CBRatingSystem::post_types() as $argType => $postTypes) {
                    echo '<optgroup label="' . $postTypes['label'] . '">';
                    foreach ($postTypes['types'] as $type => $typeLabel) {
                        echo "<option value='$type'";
                        if ($posttype == $type) {
                            echo "selected='selected'";
                        }
                        echo ">" . ucfirst($typeLabel) . "</option>";
                    }
                    echo '</optgroup>';
                }

                ?>
            </select>
        </p>
        <!-- post type filter -->
        <p>
            <label for="<?php echo $this->get_field_id('post_filter'); ?>"><?php esc_html_e("Post Filter", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('post_filter'); ?>"
                    name="<?php echo $this->get_field_name('post_filter'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('post_type' => 'Post Type', 'post_id' => 'Post ID');

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    if ($post_filter == $key) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>"><?php esc_html_e("Order", 'cbratingsystem') ?>:</label>
            <select id="<?php echo $this->get_field_id('order'); ?>"
                    name="<?php echo $this->get_field_name('order'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('ASC' => 'Ascending', 'DESC' => 'Descending');

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    if ($order == $key) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id('firstorder'); ?>"><?php esc_html_e("First Sort By", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('firstorder'); ?>"
                    name="<?php echo $this->get_field_name('firstorder'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('rating' => 'Rating', 'post_count' => 'User post Number');

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";
                    if ($firstorder == $key) {
                        echo "selected='selected'";
                    }
                    echo "> $label </option>";
                }


                ?>
            </select>
        </p>


        <!--form id -->
        <p>
            <label for="<?php echo $this->get_field_id('form_id'); ?>"><?php esc_html_e("Form", 'cbratingsystem') ?>:</label>
            <select id="<?php echo $this->get_field_id('form_id'); ?>"
                    name="<?php echo $this->get_field_name('form_id'); ?>" class="widefat" style="width: 55%">
                <?php
                $action = array(
                    'is_active' => true,

                );

                $ratingForms = CBRatingSystemData::get_ratingForms(true, $action);
                $ratingFormToShow = intval($form_id);

                $default = CBRatingSystem::get_default_ratingFormId();

                if (!empty($ratingForms)) {

                    echo "<option value='0'";
                    if ($ratingFormToShow == 0) {
                        echo "selected='selected'";
                    }
                    echo ">" . ucfirst('All') . "</option>";

                    foreach ($ratingForms as $ratingForm) {

                        if ($default == $ratingForm->id) {
                            $txt = ' (' . __('Default Form', 'cbratingsystem') . ')';
                        } else {
                            $txt = '';
                        }

                        if ($ratingFormToShow == $ratingForm->id) {
                            echo '<option selected value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        } else {
                            echo '<option value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        }
                    }
                }


                ?>
            </select>
        </p>
    <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    function update($new_instance, $old_instance)
    {

        $instance                = $old_instance;
        $instance['title']       = strip_tags($new_instance['title']);
        $instance['day']         = strip_tags($new_instance['day']);
        $instance['limit']       = strip_tags($new_instance['limit']);
        $instance['type']        = strip_tags($new_instance['type']);
        $instance['form_id']     = strip_tags($new_instance['form_id']);
        $instance['user_id']     = strip_tags($new_instance['user_id']);
        $instance['post_id']     = strip_tags($new_instance['post_id']);
        $instance['post_filter'] = strip_tags($new_instance['post_filter']);
        $instance['order']       = strip_tags($new_instance['order']);
        $instance['firstorder']  = strip_tags($new_instance['firstorder']);

        return $instance;
    }

    /**
     * @param $date
     *
     * @return bool|string
     */
    function get_calculated_date($date)
    {

        if (is_numeric($date)) {
            return date('Y-m-d H:i:s', strtotime("-$date days"));
        }
    }
}

// end of user widget class


/**
 * Class CBRatingSystemWidget
 */
class CBRatingSystemLatestReviewWidget extends WP_Widget
{

    function __construct()
    {
        parent::__construct('cbrp_latest_review', esc_html__('CBX Rating: Latest Reviews', 'cbratingsystem'), array('description' => esc_html__('A widget to display latest reviews based on different criteria.', 'cbratingsystem')));
    }

    /**
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance)
    {
        global $wpdb;

        extract($args);

        $widget_id = $args['widget_id'];


        //need to make the html id unique for showing the rate display
        $widget_id_fresh = str_replace('_', '', $widget_id);
        $widget_id_fresh = str_replace('-', '', $widget_id_fresh);


        $title = empty($options['widget_title']) ? esc_html__('Latest Reviews', 'cbratingsystem') : $instance['widget_title'];
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        echo $before_title . $title . $after_title;

        echo cbratingGetLatestReviews($instance);

        echo $after_widget;
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    function form($instance)
    {

        $default = CBRatingSystem::get_default_ratingFormId();

        $instance = wp_parse_args((array)$instance, array(
                'title'         => esc_html__('Latest Reviews', 'cbratingsystem'),
                'form_id'       => $default,
                'post_id'       => '',
                'user_id'       => '',
                'post_type'     => '',
                'order'         => 'DESC',
                'order_by'      => 'created',
                'limit'         => 10,
                'show_comment'  => 1, //show review comment
                'show_criteria' => 0, //show criteria base rating
                'show_post'     => 1, //show post heading with link
            )
        );


        if ($instance) {
            $title = esc_attr($instance['title']);
        } else {
            $title = esc_html__('Latest Reviews', 'cbratingsystem');
        }


        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e("Title", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" type="text"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_html($title); ?>"/>
        </p>




        <p>
            <label
                for="<?php echo $this->get_field_id('post_type'); ?>"><?php esc_html_e("Post Type", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('post_type'); ?>"
                    name="<?php echo $this->get_field_name('post_type'); ?>"
                    class="widefat" style="width: 55%">
                <?php

                foreach (CBRatingSystem::post_types() as $argType => $postTypes) {
                    echo '<optgroup label="' . $postTypes['label'] . '">';
                    foreach ($postTypes['types'] as $type => $typeLabel) {
                        echo "<option value='$type'";
                        selected($instance['post_type'], $type);
                        echo ">" . ucfirst($typeLabel) . "</option>";
                    }
                    echo '</optgroup>';
                }

                ?>
            </select>
        </p>
        <?php
        $action = array(
            'is_active' => true,
            'post_type' => $instance['post_type']
        );

        $ratingForms = CBRatingSystemData::get_ratingForms(true, $action);


        $ratingFormToShow = intval($instance['form_id']);


        ?>

        <p>
            <label for="<?php echo $this->get_field_id('form_id'); ?>"><?php esc_html_e("Form", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('form_id'); ?>"
                    name="<?php echo $this->get_field_name('form_id'); ?>" class="widefat" style="width: 55%">

                <?php

                if (!empty($ratingForms)) {
                    foreach ($ratingForms as $ratingForm) {

                        if ($default == $ratingForm->id) {
                            $txt = ' (' . esc_html__('Default Form', 'cbratingsystem') . ')';
                        } else {
                            $txt = '';
                        }

                        if ($ratingFormToShow == $ratingForm->id) {
                            echo '<option selected value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        } else {
                            echo '<option value="' . $ratingForm->id . '">' . $ratingForm->name . $txt . '</option>';
                        }
                    }
                }


                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_id'); ?>"><?php esc_html_e("Post ID", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('post_id'); ?>" type="text"
                   name="<?php echo $this->get_field_name('post_id'); ?>" value="<?php echo $instance['post_id']; ?>"/>
        </p>
        <p>Note: For multiple post id use comma,</p>
        <p>
            <label for="<?php echo $this->get_field_id('user_id'); ?>"><?php esc_html_e("User ID", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" type="text"
                   name="<?php echo $this->get_field_name('user_id'); ?>" value="<?php echo $instance['user_id']; ?>"/>
        </p>
        <p>Note: For multiple user id use comma,</p>
        <p>
            <label
                for="<?php echo $this->get_field_id('order_by'); ?>"><?php esc_html_e("Order By", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('order_by'); ?>"
                    name="<?php echo $this->get_field_name('order_by'); ?>"
                    class="widefat" style="width:50%">
                <?php

                $order_bys = array(
                    'created'    => esc_html__('Review Created Date', 'cbratingsystem'),
                    'post_id'    => esc_html__('Post ID', 'cbratingsystem'),
                    'post_title' => esc_html__('Post Title', 'cbratingsystem'),
                    'form_id'    => esc_html__('Form ID', 'cbratingsystem'),
                    'post_type'  => esc_html__('Post Type', 'cbratingsystem'),
                    'user_id'    => esc_html__('User ID', 'cbratingsystem'),
                    'avg'        => esc_html__('Average Score', 'cbratingsystem'),
                    'id'         => esc_html__('Review ID', 'cbratingsystem')
                );

                foreach ($order_bys as $index => $label) {
                    echo "<option value='$index'";

                    selected($instance['order_by'], $index);

                    echo ">$label</option>";
                }
                ?>
            </select>
        </p>
        <!-- order by  type filter -->
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>"><?php esc_html_e("Order", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('order'); ?>"
                    name="<?php echo $this->get_field_name('order'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('ASC' => esc_html__('Ascending', 'cbratingsystem'), 'DESC' => esc_html__('Descending', 'cbratingsystem'));

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    selected($instance['order'], $key);
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php esc_html_e("Limit", 'cbratingsystem') ?>
                :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" type="text"
                   name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('show_comment'); ?>"><?php esc_html_e("Show Comment", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('show_comment'); ?>"
                    name="<?php echo $this->get_field_name('show_comment'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('1' => esc_html__('Yes', 'cbratingsystem'), '0' => esc_html__('No', 'cbratingsystem'));

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    selected($instance['show_comment'], $key);
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('show_criteria'); ?>"><?php esc_html_e("Show Criteria Rating", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('show_criteria'); ?>"
                    name="<?php echo $this->get_field_name('show_criteria'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('1' => esc_html__('Yes', 'cbratingsystem'), '0' => esc_html__('No', 'cbratingsystem'));

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    selected($instance['show_criteria'], $key);
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('show_post'); ?>"><?php esc_html_e("Show Post Title", 'cbratingsystem') ?>
                :</label>
            <select id="<?php echo $this->get_field_id('show_post'); ?>"
                    name="<?php echo $this->get_field_name('show_post'); ?>" class="widefat" style="width:50%">
                <?php
                $no_of_filter = array('1' => esc_html__('Yes', 'cbratingsystem'), '0' => esc_html__('No', 'cbratingsystem'));

                foreach ($no_of_filter as $key => $label) {

                    echo "<option value = '$key'";

                    selected($instance['show_post'], $key);
                    echo "> $label </option>";
                }
                ?>
            </select>
        </p>
    <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    function update($new_instance, $old_instance)
    {
        $instance              = $old_instance;
        $instance['title']     = sanitize_text_field($new_instance['title']);
        $instance['form_id']   = $new_instance['form_id'];
        $instance['post_id']   = $new_instance['post_id'];
        $instance['user_id']   = $new_instance['user_id'];
        $instance['post_type'] = $new_instance['post_type'];
        $instance['limit']     = intval($new_instance['limit']);
        $instance['order']     = $new_instance['order'];
        $instance['order_by']  = $new_instance['order_by'];

        $instance['show_comment']  = $new_instance['show_comment'];
        $instance['show_criteria'] = $new_instance['show_criteria'];
        $instance['show_post']     = $new_instance['show_post'];

        return $instance;
    }
}
