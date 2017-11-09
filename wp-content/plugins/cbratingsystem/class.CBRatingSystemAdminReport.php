<?php

require_once(ABSPATH . 'wp-admin/includes/template.php');
if (class_exists('cbratingsystemaddonfunctions')) {
    require_once(WP_PLUGIN_DIR . '/cbratingsystemaddon/includes/cbratingsystemaddonfunctions.php');
}

/**
 * Class CBRatingSystemAdminReport
 */
class CBRatingSystemAdminReport extends CBRatingSystemAdmin
{
    /**
     * cbCommentEditAjaxFunction
     */
    //public static $cb_user_comment_log_data = array();


    /**
     * cbCommentAjaxFunction
     */
    public static function cbCommentAjaxFunction()
    {
        global $wpdb;

        $userlog_table = CBRatingSystemData::get_user_ratings_table_name();
        $summary_table = CBRatingSystemData::get_user_ratings_summury_table_name();

        //cbratingsystemcomment
        if (isset($_POST['cbRatingData']) && !empty($_POST['cbRatingData'])) {


            $returnedData = $_POST['cbRatingData'];
            $insertArray  = array();

            //verify nonce
            check_ajax_referer('cbratingsystemcomment-' . $returnedData['id'], 'nonce');

            $insertArray['id'] = $comment_id = (int)$returnedData['id'];

            $sql     = $wpdb->prepare("SELECT post_id ,form_id, user_id, comment_status FROM $userlog_table WHERE id=%d ", $comment_id);
            $results = $wpdb->get_results($sql, ARRAY_A);

            $user_id                       = $results[0]['user_id']; //user who rated
            $old_status                    = $results[0]['comment_status'];
            $insertArray['form_id']        = $form_id = (int)$results[0]['form_id'];
            $insertArray['post_id']        = $post_id = (int)$results[0]['post_id'];
            $insertArray['comment_status'] = $returnedData['comment_status'];


            $trigger_array = array(
                'comment_id' => $comment_id,
                'form_id'    => $form_id,
                'post_id'    => $post_id,
                'user_id'    => $user_id,
                'old_status' => $old_status
            );


            //any comment transition type other than delete
            if ($insertArray['comment_status'] != 'delete') {
                //check if really comment status change
                if ($old_status != $insertArray['comment_status']) {
                    //let 3rd party process
                    do_action('cbratingsystem_before_comment_status_change', $trigger_array, $insertArray);

                    $return = CBRatingSystemData::update_rating_comment($insertArray);

                    //let 3rd party process
                    $trigger_array['return'] = $return; //add return value for any extra care
                    do_action('cbratingsystem_after_comment_status_change', $trigger_array, $insertArray);
                }

            } else {
                //delete action

                //help 3rd party plugins to do extra
                do_action('cbratingsystem_before_comment_deleted', $comment_id, $form_id, $post_id, $user_id);

                //$return = CBRatingSystemData::delete_ratings($id, $post_id, $form_id);
                //$sql = "DELETE FROM $userlog_table WHERE id IN (" . implode(',', array($returnedData['id'])) . ")";
                //$wpdb->query($sql);

                //now delete the user log
                //$sql         = $wpdb->prepare("DELETE FROM $userlog_table WHERE id=%d", $comment_id;
                $sql = $wpdb->prepare("DELETE FROM $userlog_table WHERE id=%d", $comment_id);
                $wpdb->query($sql);

                //help 3rd party plugins to do extra, not user it will have any use though
                do_action('cbratingsystem_after_comment_deleted', $comment_id, $form_id, $post_id, $user_id);


                $ratingAverage = CBRatingSystemFront::viewPerCriteriaRatingResult($form_id, $post_id);

                if (isset($ratingAverage['avgPerRating'])) {

                    //at least on another user rating exists for this post and form id
                    //$perPostAverageRating = isset($ratingAverage['perPost'][$postId])? $ratingAverage['perPost'][$postId] : '';
                    $perPostAverageRating = $ratingAverage['perPost'][$post_id];
                    $postType             = get_post_type($post_id);

                    $ratingsCount = $ratingAverage['ratingsCount'][$form_id . '-' . $post_id];

                    $rating = array(
                        'form_id'                     => $form_id,
                        'post_id'                     => $post_id,
                        'post_type'                   => $postType,
                        'per_post_rating_count'       => $ratingsCount,
                        'per_post_rating_summary'     => $perPostAverageRating,
                        'per_criteria_rating_summary' => maybe_serialize($ratingAverage['avgPerCriteria']),
                    );

                    $success = CBRatingSystemData::update_rating_summary($rating);
                } else {
                    //there no other user rating for this post id and form id, so delete the avg log entry for this matching

                    $sql = $wpdb->prepare("DELETE FROM $summary_table WHERE form_id=%d AND post_id=%d", $form_id, $post_id);
                    $wpdb->query($sql);

                    //we also delete all meta keys created for this post and form id
                    delete_post_meta($post_id, 'cbrating' . $form_id);

                }
            }


            $cb_return_data = __('Saved', 'cbratingsystem');
            echo json_encode($cb_return_data);
            die();

        }

    }

    /**
     * Rating Single Log listing
     */
    public static function logReportPageOutput()
    {

        $path = admin_url('admin.php?page=rating_reports');
        ?>
        <div class="wrap columns-2">
            <div class="icon32 icon32_cbrp_admin icon32-cbrp-user-ratings" id="icon32-cbrp-user-ratings"><br></div>
            <h1><?php _e('CBX Rating & Review: User Rating Logs', 'cbratingsystem') ?></h1>
            <div class="metabox-holder has-right-sidebar" id="poststuff">
                <div class="messages"></div>
                <div id="post-body" class="post-body">
                    <?php
                    $user_log = new Cbratinguserlog(array());
                    $user_log->prepare_items();
                    ?>
                    <div class="cbratinguserlog">
                        <?php $user_log->views(); ?>
                        <form id="user-filter" method="get">

                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                            <?php
                            $user_log->display(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>




    <?php
    }

}

require_once(ABSPATH . 'wp-admin/includes/template.php');

if (!class_exists('WP_List_Table')) {

    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class Cbratinguserlog
 *
 */
class Cbratinguserlog extends CBRatingUserLog_List_Table
{

    //public $cb_user_comment_log_data = array();

    //public $cb_rating_avg_data_ = array();

    /**
     * @param array $args
     */
    public function __construct($args = array())
    {

        parent::__construct(array(

            'singular' => 'wdcheckbox', //singular name of the listed records
            'plural'   => 'wdcheckboxs',
            'ajax'     => false,
            'screen'   => isset($args['screen']) ? $args['screen'] : null,

        ));



    }

    /**
     * prepare_items
     */
    public function prepare_items()
    {

        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $user   = get_current_user_id();
        $screen = get_current_screen();

        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();


        $option_name = $screen->get_option( 'per_page', 'option' ); //the core class name is WP_Screen


        $per_page = intval( get_user_meta( $user, $option_name, true ) );


        if ( $per_page == 0 ) {
            $per_page = intval( $screen->get_option( 'per_page', 'default' ) );
        }



        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->process_bulk_action();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $logdata = array();



        $order      = (isset($_GET['order']) && $_GET['order'] != '') ? $_GET['order'] : 'desc';
        $orderby    = (isset($_GET['orderby']) && $_GET['orderby'] != '') ? $_GET['orderby'] : 'created';


        $postID     = ((!empty($_GET['post']) && (is_numeric($_GET['post']))) ? (integer)($_GET['post']) : '');
        $formID     = ((!empty($_GET['form']) && (is_numeric($_GET['form']))) ? (integer)($_GET['form']) : '');
        $userID     = ((!empty($_GET['user']) && (is_numeric($_GET['user']))) ? (integer)($_GET['user']) : '');
        $postType   = ((!empty($_GET['type']) && (is_string($_GET['type']))) ? esc_attr($_GET['type']) : '');
        $status     = ((!empty($_GET['comment_status']) && (is_string($_GET['comment_status']))) ? esc_attr($_GET['comment_status']) : '');







        $commentID = ((!empty($_GET['rating_id']) && (is_numeric($_GET['rating_id']))) ? (integer)($_GET['rating_id']) : '');

        $limit = array(
            'perpage'   => $per_page,
            'page'      => $current_page
        );

        $data       = CBRatingSystemData::get_user_ratings_with_ratingForm(array($formID), array($postID), array($userID), '', $orderby, $order, $limit, true, $commentID, array($postType), array($status));
        $data_total = CBRatingSystemData::get_user_ratings_with_ratingForm_total(array($formID), array($postID), array($userID), '', $orderby, $order, $commentID, array($postType), array($status));


        if ($data) :

            foreach ($data as $rows) :

                $log_id_hidden = $rows->form_id . '-' . $rows->post_id . '-' . $rows->id; // (form_id)-(post_id)-(log_id)
                $log_id = $rows->id;

                $user_type = ($rows->user_id > 0) ? sprintf(__('User%s', 'cbratingsystem'), " (ID: $rows->user_id)") : __('Guest', 'cbratingsystem');
                $user_name = (($rows->user_id > 0) ? get_the_author_meta('display_name', $rows->user_id) : (!empty($rows->user_name) ? $rows->user_name : 'Anonymous'));
                $user_email = (($rows->user_id > 0) ? get_the_author_meta('email', $rows->user_id) : (!empty($rows->user_email) ? $rows->user_email : '--'));
                $log_post_id = $rows->post_id;
                $log_post_title = $rows->post_title;
                $log_post_type = $rows->post_type;
                $log_form_id = $rows->form_id;
                $log_average = ($rows->average > 0) ? '<strong>' . (($rows->average / 100) * 5) . ' / 5</strong>' : '-';

                if (!empty($rows->rating)) {

                    $log_criteria_rating = '<ul>';
                    foreach ($rows->rating as $cId => $value) {

                        if (is_numeric($cId)) {

                            $log_criteria_rating .= '<li>' . $rows->custom_criteria[$cId]['label'] . ': <strong>' . number_format(($value / 100) * count($rows->custom_criteria[$cId]['stars']), 2) . '/' . count($rows->custom_criteria[$cId]['stars']) . '</strong>';
                        }
                    }
                    $log_criteria_rating .= '</ul>';
                } else {
                    $log_criteria_rating = '-';
                }

                $log_q_a = '';
                $valuesText = array();

                if (!empty($rows->question) and is_array($rows->question)) {
                    $log_q_a .= '<ul>';

                    foreach ($rows->question as $questionId => $value) {

                        $ratingFormId = $rows->form_id;
                        $type = $rows->custom_question[$questionId]['field']['type'];

                        if (array_key_exists($type, $rows->custom_question[$questionId]['field']))
                            $fieldArr = $rows->custom_question[$questionId]['field'][$type];
                        else $fieldArr = array();


                        if (array_key_exists('seperated', $fieldArr))
                            $seperated = $fieldArr['seperated'];
                        else $seperated = 0;

                        if (is_array($value)) {
                            foreach ($value as $key => $val) {
                                $valuesText[$rows->form_id][$questionId][] = '<strong>' . __(stripcslashes($fieldArr[$key]['text']), 'cbratingsystem') . '</strong>';
                            }

                            if ((!empty($valuesText))) {
                                $log_q_a .= '
                                                                        <li>
                                                                            <div data-q-id="' . $questionId . '" class="question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
                                                                                <div class="question-label-wrapper">
                                                                                    <span class="question-label question-label-id-' . $questionId . '" >' . (isset($rows->custom_question[$questionId]) ? __(stripslashes($rows->custom_question[$questionId]['title']), 'cbratingsystem') : '') . '</span>
                                                                                    <span class="question-label-hiphen">' . (isset($rows->custom_question[$questionId]) ? ' - ' : '') . '</span>
                                                                                    <span class="answer"><strong>' . (implode(', ', $valuesText[$rows->form_id][$questionId])) . '</strong></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    ';
                            }
                        } else {
                            if ($seperated == 0) {

                                $log_q_a .= '
                                                                        <li>
                                                                            <div data-form-id="' . $ratingFormId . '" data-q-id="' . $questionId . '" class="question-id-wrapper-' . $questionId . ' question-id-wrapper-' . $questionId . '-form-' . $ratingFormId . ' ">
                                                                                <div class="question-label-wrapper">
                                                                                    <span class="question-label question-label-id-' . $questionId . '" >' . (isset($rows->custom_question[$questionId]) ? __(stripslashes($rows->custom_question[$questionId]['title']), 'cbratingsystem') : '') . '</span>
                                                                                    <span class="question-label-hiphen">' . (isset($rows->custom_question[$questionId]) ? ' - ' : '') . '</span>
                                                                                    <span class="answer"><strong>' . (($value == 1) ? __('Yes', 'cbratingsystem') : __($value, 'cbratingsystem')) . '</strong></span>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    ';
                            }
                        }
                    }

                    $log_q_a .= '</ul>';
                }


                $log_comment = ($rows->comment) ? stripslashes($rows->comment) : '-';
                $log_comment_status = $rows->comment_status;

                //$log_date    = cbratingsystem_date_display($rows->created);
                $log_date = $rows->created;


                $log_host_ip = $rows->user_ip;


                $comment_status_list = array('delete', 'unapproved', 'approved', 'spam');
                $comment_status = $log_comment_status;
                array_push($logdata,
                            array(
                                'ID'                => $log_id,
                                'id'                => $log_id,
                                'user_name'         => $user_name,
                                'user_id'           => $rows->user_id,
                                'userinfo'          => $user_email,
                                'post_id'           => $log_post_id,
                                'post_type'         => $log_post_type,
                                'post_title'        => $log_post_title,
                                'form_id'           => $log_form_id,
                                'avgrating'         => $log_average,
                                'criteriarating'    => $log_criteria_rating,
                                'qa'                => $log_q_a,
                                'comment'           => $log_comment,
                                'commentstatus'     => $comment_status,
                                'created'           => $log_date,
                                'ip'                => $log_host_ip
                            )
                );
            endforeach;
        endif;

        //$data = CBRatingSystemAdminReport::$cb_user_comment_log_data;


        //usort($data, $this->usort_reorder);
        //usort($data, array($this, 'usort_reorder') );



        $total_items = $data_total;

        //$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $logdata;

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
        ));

    } // end of function prepare_items

    /**
     * @param $item
     * @param $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    { // this columns takes the valus from emample data with keys and echo it


        switch ($column_name) {
            case 'id':
            case 'post_id':
            case 'post_type':
            case 'form_id':
            case 'user_id':
            case 'avgrating':
            case 'criteriarating':
            case 'qa':
            case 'userinfo':
            case 'comment':
            case 'created':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_userinfo($item)
    {

        $output = '';
        $output .= '<p id="user-log-' . $item['form_id'] . '-' . $item['post_id'] . '-' . $item['userinfo'] . '" ><strong>' . __('User Name', 'cbratingsystem') . ' : </strong>' . $item['user_name'] . '</p>';
        $output .= '<p><strong>' . __('User Email', 'cbratingsystem') . ' : </strong><a href="' . get_edit_user_link() . '">' . $item['userinfo'] . '</a></p>';
        $output .= '<p><strong>' . __('User IP', 'cbratingsystem') . ' : </strong>' . $item['ip'] . '</p>';

        return sprintf($output);
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_id($item)
    {
        $actions = array(
            'edit'   => sprintf('<a href="?">' . __('Edit', 'cbratingsystem') . '</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete' => sprintf('<a href="?">' . __('Delete', 'cbratingsystem') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );

        return sprintf('<span class = "user-rating-log-%1$s" style="">%1$s </span>',

            $item['id'],
            $item['ID'],
            $this->row_actions($actions)
        );
    }

    /**
     * Post column
     *
     * @param $item
     *
     * @return string
     */
    public function column_post_id($item)
    {

        $post_id_url = add_query_arg('post', $item['post_id']);


        //$output = '<a  href="' . admin_url('admin.php?page=rating_reports&post=' . $item['post_id']) . '" target="_blank" >' . $item['post_id'] . '</a><br/>';


        $output = '<a  href="' . $post_id_url . '" target="_blank" >' . $item['post_id'] . '</a><br/>';

        $output .= '<a target="_blank" href="' . get_permalink($item['post_id']) . '"><span class="dashicons dashicons-welcome-view-site"></span></a> <a target="_blank" href="' . get_edit_post_link($item['post_id']) . '"><span class="dashicons dashicons-edit"></span></a>';

        return $output;
    }

    /**
     * Post column
     *
     * @param $item
     *
     * @return string
     */
    public function column_post_type($item)
    {
        $post_type_url = add_query_arg('type', $item['post_type']);

        //$output = '<a  href="' . admin_url('admin.php?page=rating_reports&type=' . $item['post_type']) . '" target="_blank" >' . $item['post_type'] . '</a>';


        $output = '<a  href="' . $post_type_url . '" target="_blank" >' . $item['post_type'] . '</a>';

        return $output;
    }

    /**
     * Formid column
     *
     * @param $item
     *
     * @return string
     */
    public function column_form_id($item)
    {

        $form_url = add_query_arg('form', $item['form_id']);

        /*
        $output = '<a  href="' . admin_url('admin.php?page=rating_reports&form=' . $item['form_id']) . '" target="_blank" >' . $item['form_id'] . '</a>';
        */
        $output = '<a  href="' . $form_url . '" target="_blank" >' . $item['form_id'] . '</a>';

        return $output;
    }

    /**
     * User id column
     *
     * @param $item
     *
     * @return string
     */
    public function column_user_id($item)
    {


        /*
        $output = ($item['user_id'] == 0) ? __('Guest', 'cbratingsystem') : '<a  href="' . admin_url('admin.php?page=rating_reports&user=' . $item['user_id']) . '" target="_blank" >' . $item['user_id'] . '</a>';
        */

        $user_url = add_query_arg('user', $item['user_id']);

        $output = ($item['user_id'] == 0) ? __('Guest', 'cbratingsystem') : '<a  href="' . $user_url . '" target="_blank" >' . $item['user_id'] . '</a>';

        return $output;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_cb($item)
    {

        return sprintf(
            '<input class="cbwdchkbox"  type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id'] //The value of the checkbox should be the record's id
        );
    }

    /**
     * Comment Column
     *
     * @param $item
     *
     * @return string
     */
    public function column_created($item)
    {
        return cbratingsystem_date_display($item['created']);

    }

    /**
     * Comment Column
     *
     * @param $item
     *
     * @return string
     */
    public function column_comment($item)
    {

        //$nonce = wp_create_nonce( 'my-action_'.$post->ID );

        $output = '';
        //$output_date         = ' <div class ="cbratingdate"><p><strong>On: </strong>' . $item['date'] . '</p></div>';
        $output_date         = ' ';
        $output_click        = apply_filters('cbratingsystem_commentedit_title', '(Edit Option in premium version)');
        $cb_comment_box      = apply_filters('cbratingsystem_commentedit_class', 'cbratingcomment_noedit');
        $cb_comment_edit_box = apply_filters('cbratingsystem_commenteditbox_class', 'cbratingcomment_editbox_noedit');


        $output .= '<div class ="cbratingcomment_container cbratingcomment_container_' . $item['id'] . '">
                <div class="' . $cb_comment_box . ' cbratingcomment_' . $item['id'] . '" data-id = "' . $item['id'] . '" title ="' . $output_click . '">
                    ' . $item['comment'] . '
                </div>
                <textarea style="display:none;" class="' . $cb_comment_edit_box . ' cbratingcomment_edit_' . $item['id'] . '" data-form-id ="' . $item['form_id'] . '" data-post-id = "' . $item['post_id'] . '"  data-id = "' . $item['id'] . '"></textarea>
            </div>';

        //endif;

        $log_comment_status         = '';
        $comment_status_list        = array('delete', 'unapproved', 'approved', 'spam');
        $comment_status             = $item['commentstatus'];
        $log_id                     = $item['id'];
        $log_form_id                = $item['form_id'];
        $log_post_id                = $item['post_id'];
        $log_comment_status_wrapper = '';


        $log_comment_status_wrapper = apply_filters('cbrating_comment_status_mod', $log_comment_status_wrapper, $comment_status, $comment_status_list, $log_id, $log_post_id, $log_form_id);

        $output = $output_date . '<div class ="cbratingdash_comment_wrapper">' . $output . $log_comment_status_wrapper . '</div>';

        return sprintf($output);

    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'                => '<input type="checkbox"  />', //Render a checkbox instead of text
            'id'                => __('ID: ', 'cbratingsystem'),
            'post_id'           => __('Post Id: ', 'cbratingsystem'),
            'post_type'         => __('Post Type: ', 'cbratingsystem'),
            'form_id'           => __('Form Id: ', 'cbratingsystem'),
            'user_id'           => __('User Id: ', 'cbratingsystem'),
            'avgrating'         => __('Average Rating: ', 'cbratingsystem'),
            'criteriarating'    => __('Criteria Rating: ', 'cbratingsystem'),
            'qa'                => __('Q/A: ', 'cbratingsystem'),
            'userinfo'          => __('User Info: ', 'cbratingsystem'),
            'comment'           => __('Comment: ', 'cbratingsystem'),
            'created'           => __('Created: ', 'cbratingsystem'),


        );

        return $columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {

        $bulk_actions = apply_filters('cbratingsystem_comment_status_bulk_action', array(

            'delete' => __('Delete', 'cbratingsystem')

        ));

        return $bulk_actions;

    }

    /**
     * process_bulk_action
     */
    public function process_bulk_action()
    {


        $action = $this->current_action();

        switch ($action) {
            //delete user logs/delete comment/delete rating logs
            case 'delete':

                if (!empty($_GET['wdcheckbox'])) {
                    global $wpdb;
                    $avgid   = $_GET['wdcheckbox'];

                    $formIds = array();
                    $postIds = array();

                    $userlog_table = CBRatingSystemData::get_user_ratings_table_name();
                    $summary_table = CBRatingSystemData::get_user_ratings_summury_table_name();




                    foreach ($avgid as $id) {

                        $id = (int)$id;

                        $sql     = $wpdb->prepare("SELECT post_id ,form_id, user_id FROM $userlog_table WHERE id=%d ", $id);
                        $results = $wpdb->get_results($sql, ARRAY_A);

                        array_push($formIds, $results[0]['form_id']);
                        array_push($postIds, $results[0]['post_id']);

                        //help 3rd party plugins to do extra
                        do_action('cbratingsystem_before_comment_deleted', $id, $results[0]['form_id'], $results[0]['post_id'], $results[0]['user_id']);

                        //now delete the user log
                        $sql = $wpdb->prepare("DELETE FROM $userlog_table WHERE id=%d", $id);
                        $wpdb->query($sql);

                        //help 3rd party plugins to do extra, not user it will have any use though
                        do_action('cbratingsystem_after_comment_deleted', $id, $results[0]['form_id'], $results[0]['post_id'], $results[0]['user_id']);


                    }


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


                $redirect_url = admin_url('admin.php?page=rating_reports');
                CBRatingSystem::redirect($redirect_url);

                break;

            case 'approve':

                if (class_exists('cbratingsystemaddonfunctions')) {

                    if (!empty($_GET['wdcheckbox'])) {

                        $avgid           = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'approved';
                        cbratingsystemaddonfunctions::cbratingsystem_comment_statuschange($avgid, $cbsommentstatus);

                    }
                    // end of if get

                }
                // end of if class exists
                $redirect_url = admin_url('admin.php?page=rating_reports');
                CBRatingSystem::redirect($redirect_url);

                break;
            case 'spam':

                if (class_exists('cbratingsystemaddonfunctions')) {


                    if (!empty($_GET['wdcheckbox'])) {
                        $avgid           = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'spam';
                        cbratingsystemaddonfunctions::cbratingsystem_comment_statuschange($avgid, $cbsommentstatus);

                    }
                    // end of if get

                }
                // end of if class exists
                $redirect_url = admin_url('admin.php?page=rating_reports');
                CBRatingSystem::redirect($redirect_url);


                break;
            case 'unapprove':

                if (class_exists('cbratingsystemaddonfunctions')) {


                    if (!empty($_GET['wdcheckbox'])) {
                        $avgid           = $_GET['wdcheckbox'];
                        $cbsommentstatus = 'unapproved';
                        cbratingsystemaddonfunctions::cbratingsystem_comment_statuschange($avgid, $cbsommentstatus);

                    }
                    // end of if get

                }
                // end of if class exists
                $redirect_url = admin_url('admin.php?page=rating_reports');
                CBRatingSystem::redirect($redirect_url);

                break;

            default:

                // do nothing or something else
                //$redirect_url = admin_url('admin.php?page=rating_reports');
                //CBRatingSystem::redirect($redirect_url);

                return;
                break;
        }

        return;
    }

    /**
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'                => array('id', false),
            'post_id'           => array('post_id', false),
            'post_type'         => array('post_type', false),
            'form_id'           => array('form_id', false),
            'user_id'           => array('user_id', false),
            'created'           => array('created', false),
            'comment'           => array('comment', false),

        );

        return $sortable_columns;
    }

}//end of class

class CBRatingUserLog_List_Table extends  WP_List_Table{
    /**
     * Generates content for a single row of the table
     *
     * @since 3.1.0
     * @access public
     *
     * @param object $item The current item
     */

    public function single_row( $item ) {

        $row_class = 'cb-tr-status';
        $row_class = apply_filters('cbratingsystem_userlog_row_status', $row_class, $item);

        echo '<tr id="cb-tr-status_'.$item['id'].'" class="'.$row_class.'">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }


    function get_views(){
        $views = array();

        $current = ( isset($_REQUEST['comment_status']) ? $_REQUEST['comment_status'] : 'all');


        $form_id    = isset($_GET['form'])? intval($_GET['form']): 0;
        $post_id    = isset($_GET['post'])? intval($_GET['post']): 0;
        $post_type  = isset($_GET['type'])? esc_attr($_GET['type']): '';
        $user_id    = isset($_GET['user'])? intval($_GET['user']): 0;



        $data       = CBRatingSystemData::getReviewsCountByStatus($form_id, $post_id, $user_id, $post_type);



        $status_arr  = array(
            'all'           => __('Total (%d)', 'cbratingsystem'),
        );

        $status_arr = apply_filters('cbratingsystem_status_count_arr', $status_arr);



        foreach($status_arr as $status_key => $status_str){
            if(!isset($data[$status_key])){
                $data[$status_key] = 0;
            }

            if($status_key == 'all'){
                //All link
                $class      = ($current == 'all' ? ' class="current"' :'');
                $all_url    = remove_query_arg('comment_status');
                $views['all'] = "<a href='{$all_url }' {$class} >".sprintf(__('All (%d)', 'cbratingsystem'), $data['all'])."</a>";
            }
            else{
                //any other link but all
                $unapproved_url    = add_query_arg('comment_status',$status_key);
                $class      = ($current == $status_key ? ' class="current"' :'');
                $views[$status_key] = "<a href='{$unapproved_url}' {$class} >".sprintf($status_arr[$status_key], $data[$status_key])."</a>";
            }

        }

        



/*
        //approved link
        $approved_url    = add_query_arg('comment_status','approved');
        $class      = ($current == 'approved' ? ' class="current"' :'');
        $views['approved'] = "<a href='{$approved_url}' {$class} >".sprintf(__('Approved (%d)','cbratingsystem'), $data['approved'])."</a>";

        //unpublished link
        $unapproved_url    = add_query_arg('comment_status','unapproved');
        $class      = ($current == 'unapproved' ? ' class="current"' :'');
        $views['unapproved'] = "<a href='{$unapproved_url}' {$class} >".sprintf(__('Unapproved (%s)','cbratingsystem'), $data['unapproved'])."</a>";


        //spam  link
        $spam_url    = add_query_arg('comment_status','spam');
        $class      = ($current == 'spam' ? ' class="current"' :'');
        $views['spam'] = "<a href='{$spam_url}' {$class} >".sprintf(__('Spam (%d)','cbratingsystem'), $data['spam'])."</a>";

        //verified link
        $verified_url    = add_query_arg('comment_status','verified');
        $class      = ($current == 'verified' ? ' class="current"' :'');
        $views['verified'] = "<a href='{$verified_url}' {$class} >".sprintf(__('Verified (%d)','cbxpollproaddon (%d)'), $data['verified'])."</a>";

        //unverified  link
        $unverified_url    = add_query_arg('comment_status','unverified');
        $class      = ($current == 'unverified' ? ' class="current"' :'');
        $views['unverified'] = "<a href='{$unverified_url}' {$class} >".sprintf(__('Unverified (%d)','cbxpollproaddon (%d)'), $data['unverified'])."</a>";*/

        return $views;
    }
}
