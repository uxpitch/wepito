<?php

require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(CB_RATINGSYSTEM_PATH . '/class.CBRatingSystemAdmin.php');
require_once(CB_RATINGSYSTEM_PATH . '/data.php');

/**
 * Class CBratinglogreportoutput for User Rating average logs
 */
class CBratinglogreportoutput extends CBRatingSystemAdmin
{

    public static $cb_avg_comment_log_data = array();

    /**
     * averageReportPageOutput
     */
    public static function averageReportPageOutput()
    {



        ?>
        <div class="wrap columns-2">
            <div class="icon32 icon32_cbrp_admin icon32-cbrp-rating-avg" id="icon32-cbrp-rating-avg"><br></div>
            <h1><?php esc_html_e('CBX Rating & Review: Average Rating Logs', 'cbratingsystem') ?></h1>

            <div class="metabox-holder has-right-sidebar" id="poststuff">
                <div id="message"
            </div>
            <div id="post-body" class="post-body">
                <div id="stuff-box">



                    <?php
                    $list_table = new Cbratingavglog();
                    $list_table->prepare_items();
                    ?>
                    <form id="cbrating_avg_table" method="get">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <?php $list_table->display(); ?>
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
 * Class Cbratingavglog
 */
class Cbratingavglog extends WP_List_Table
{


    ///public $cb_rating_avg_data_ = array();

    /**
     *
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'wdcheckbox', //singular name of the listed records
            'plural'   => 'wdcheckboxs', //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ));

    }

    /**
     * @param $item
     * @param $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    { // this columns takes the valus from emample data with keys and echo it
        switch ($column_name) {
            case 'post_id':
            case 'post_title':
            case 'form_id':
            case 'avgrating':
            case 'criteria_rating':
            case 'per_post_rating_count':
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
    public function column_cb($item)
    {
        return sprintf(
            '<input class="cbwdchkbox-%4$s"  type="checkbox" name="%1$s[]" value="%4$s" />',
            /*$1%s*/
            $this->_args['singular'],
            /*$5%s*/
            $item['id'],
            /*$4%s*/
            $item['id'], //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id'],
            /*$3%s*/
            $item['id'] //The value of the checkbox should be the record's id
        );
    }

    public function column_post_id($item)
    {
        $output = '<a title="'.__('Click to view all user ratings for this post','cbratingsystem').'"  href="' . admin_url('admin.php?page=rating_reports&post=' . $item['post_id']) . '&form='.$item['form_id'].'" target="_blank" >' . $item['post_id'] . '</a><br/>';
       // $output .= '<a target="_blank" href="' . get_permalink($item['post_id']) . '"><span class="dashicons dashicons-welcome-view-site"></span></a> <a target="_blank" href="' . get_edit_post_link($item['post_id']) . '"><span class="dashicons dashicons-edit"></span></a>';

        return $output;
    }

    public function column_post_title($item)
    {
        //$output = '<a  href="' . admin_url('admin.php?page=rating_reports&post=' . $item['post_id']) . '" target="_blank" >' . $item['post_id'] . '</a><br/>';
        $output = '<a title="'.__('View this post in front end','cbratingsystem').'" target="_blank" href="' . get_permalink($item['post_id']) . '">'.$item['post_title'].'</a> <a title="'.__('Click to edit this post','cbratingsystem').'" target="_blank" href="' . get_edit_post_link($item['post_id']) . '"><span class="dashicons dashicons-edit"></span></a>';

        return $output;
    }

    public function column_form_id($item)
    {
        //$output = '<a  href="' . admin_url('admin.php?page=rating_reports&post=' . $item['post_id']) . '" target="_blank" >' . $item['post_id'] . '</a><br/>';
        $output = '<a title="'.__('Click to view form setting','cbratingsystem').'" target="_blank" href="' . admin_url('admin.php?page=ratingformedit&id=' . $item['form_id']) . '"><span class="dashicons dashicons-edit"></span>'.$item['form_name'].' - '.$item['form_id'].'</a>';
        $output .= ' (<a title="'.__('Click to view all user ratings by this form','cbratingsystem').'" target="_blank" href="' . admin_url('admin.php?page=rating_reports&form=' . $item['form_id']) . '"><span class="dashicons dashicons-star-filled"></span></a>)';

        return $output;
    }

    public function column_avg($item)
    {
        $output = $item['avgrating'];


        return $output;
    }

    /**
     * @return array
     */
    public function get_columns()
    {

        $columns = array(
            'cb'                        => '<input type="checkbox"  />', //Render a checkbox instead of text
            'post_id'                   => __('Post', 'cbratingsystem'),
            'post_title'                => __('Post Title', 'cbratingsystem'),
            'form_id'                   => __('Form Id', 'cbratingsystem'),
            //'avgrating'                 => __('Average Rating', 'cbratingsystem'),
            'avg'                       => __('Average Rating', 'cbratingsystem'),
            'criteria_rating'           => __('Criteria Rating', 'cbratingsystem'),
            'per_post_rating_count'                       => __('Rating Count', 'cbratingsystem'),
        );

        return $columns;
    }

    /**
     * @return array
     */
    function get_bulk_actions()
    {

        $actions = array(
            'delete' => __('Delete', 'cbratingsystem')
        );

        return $actions;
    }

    /**
     * Delete Summary and more
     *
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action()
    {

        //delete summary
        if ('delete' === $this->current_action()) {

            if (!empty($_GET['wdcheckbox'])) {

                $avgid = $_GET['wdcheckbox'];


                global $wpdb;
                $summary_table = CBRatingSystemData::get_user_ratings_summury_table_name();
                $userlog_table = CBRatingSystemData::get_user_ratings_table_name();

                foreach ($avgid as $id) {
                    $sql     = $wpdb->prepare("SELECT post_id ,form_id FROM $summary_table WHERE id=%d ", $id);
                    $results = $wpdb->get_results($sql, ARRAY_A);


                    $sql_log     = $wpdb->prepare("SELECT id, form_id, post_id, user_id FROM $userlog_table WHERE post_id=%d AND form_id=%d ", $results[0]['post_id'], $results[0]['form_id']);
                    $results_log = $wpdb->get_results($sql_log, ARRAY_A);

                    foreach ($results_log as $log) {
                        //help 3rd party plugins to do extra
                        do_action('cbratingsystem_before_comment_deleted', $log['id'], $log['form_id'], $log['post_id'], $log['user_id']);


                        $sql        = $wpdb->prepare("DELETE FROM $userlog_table WHERE id=%d", $log['id']);
                        $sql_return = $wpdb->query($sql);


                        //help 3rd party plugins to do extra, not user it will have any use though
                        do_action('cbratingsystem_after_comment_deleted', $log['id'], $log['form_id'], $log['post_id'], $log['user_id']);
                    }


                    //we also delete all meta keys created for this post and form id
                    delete_post_meta($results[0]['post_id'], 'cbrating' . $results[0]['form_id']);

                    $sql = $wpdb->prepare("DELETE FROM $summary_table WHERE id=%d", $id);
                    $wpdb->query($sql);

                }

            }
            //redirect to avg rating listing page

            $redirect_url = admin_url('admin.php?page=rating_avg_reports');
            CBRatingSystem::redirect($redirect_url);

        }


    }

    /**
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(

            'post_id'          => array('post_id', false),
            'post_title'       => array('post_title', false),
            'form_id'          => array('form_id', false),
            'avg'              => array('avg', false),
            //'criteria_rating' => array('criteria_rating', false),
            'per_post_rating_count'              => array('per_post_rating_count', false)
        );

        return $sortable_columns;
    }



    function usort_reorder($a, $b) {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'post_id'; //If no sort, default to title
        $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

        //$result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order

        //return ($order === 'desc') ? $result : -$result; //Send final sort direction to usort

        if ($a[$orderby] == $b[$orderby]) {
            return 0;
        }
        return ($a[$orderby] > $b[$orderby]) ? -1 : 1;

    }

    /**
     *
     */
    public function prepare_items()
    {


        global $wpdb;

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




        $path        = admin_url('admin.php?page=rating_avg_reports');

        $order        = (isset($_GET['order']) && $_GET['order'] != '') ? $_GET['order'] : 'desc';
        $orderby      = (isset($_GET['orderby']) && $_GET['orderby'] != '') ? $_GET['orderby']: 'post_id';


        $summaryData = CBRatingSystemData::get_ratings_summary(array(), $orderby, $order, true, $per_page, $current_page);
        $total_count = CBRatingSystemData::get_ratings_summary_total(array(), $orderby, $order);

        $data = array();

        if ($summaryData) : ?>
            <?php foreach ($summaryData as $rowId => $rows) :
                if (!empty($rows->per_post_rating_count) && (!empty($rows->per_post_rating_summary))) :

                    $log_average    = array();
                    $log_post_id    = $rows->post_id;
                    $log_post_title = $rows->post_title;
                    $log_form_id    = $rows->form_id;
                    $form_name      = $rows->name;

                    $summary_val = (number_format((($rows->per_post_rating_summary / 100) * 5), 2));
                    $log_average = ($rows->per_post_rating_summary > 0) ? sprintf(__('<strong>%s out of 5</strong>', 'cbratingsystem'), $summary_val) : '-';


                    $userRoleLabels = CBRatingSystem::user_role_label();

                    $log_ratingCount = $rows->per_post_rating_count;
                    $log_id          = $rows->id;

                    $user_log_table = CBRatingSystemData::get_user_ratings_table_name();

                    $sql     = $wpdb->prepare("SELECT id FROM $user_log_table WHERE post_id=%d AND form_id=%d ", $log_post_id, $log_form_id);
                    $results = $wpdb->get_results($sql, ARRAY_A);
                    $results = (maybe_unserialize($results[0]['id']));

                    if (!empty($rows->per_criteria_rating_summary)) {
                        $log_criteria_rating = '<ul>';
                        foreach ($rows->per_criteria_rating_summary as $cId => $criteria) {
                            $summary_val   = (number_format(($criteria['value'] / 100) * count($criteria['stars']), 2));
                            $summary_label = sprintf(__('<strong>%s out of %s</strong>', 'cbratingsystem'), $summary_val, count($criteria['stars']));
                            $log_criteria_rating .= '<li>' . $criteria['label'] . ' : ' . $summary_label;
                        }
                        $log_criteria_rating .= '</ul>';

                    } else {
                        $log_criteria_rating = '-';
                    }

                    array_push($data,
                        array(
                            'id_user_table'             => $results,
                            'id'                        => $log_id,
                            'per_post_rating_count'     => $log_ratingCount,
                            'criteria_rating'           => $log_criteria_rating,
                            'post_id'                   => $log_post_id,
                            'post_title'                => $log_post_title,
                            'form_id'                   => $log_form_id,
                            'avgrating'                 => $log_average,
                            'avg'                       => $summary_val,
                            'form_name'                 => $form_name
                        )
                    );

                    ?>
                    <?php
                else : //echo "<td colspan='7' align='center'>No Results Found</td>";
                endif;



            endforeach;

        endif;


        /**
         * First, lets decide how many records per page to show
         */

        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        //$data = CBratinglogreportoutput::$cb_avg_comment_log_data;



        //usort($data, array($this, 'usort_reorder') );



        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = $total_count;
        //$total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        //$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
        ));
    } // end of function prepare_items
    /**
     *
     */
    public function cb_create_table()
    {

        self:: prepare_items();
        self::display();
    }


}// end of class
