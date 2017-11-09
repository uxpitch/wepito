<?php

/**
 * Class CBRatingSystemData
 */
class CBRatingSystemData
{
    /**
     *
     */
    public static function install_table()
    {
        global $wpdb;

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        //need for database creation
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

        $table_name = self::get_ratingForm_settings_table_name();


        $sql = "CREATE TABLE IF NOT EXISTS $table_name  (
                        id mediumint(8) unsigned not null auto_increment,
                        name varchar(500) not null,
                        is_active tinyint(1) not null,
                        post_types VARCHAR( 1000 ) NOT NULL,
                        show_on_single INT( 1 ) NOT NULL DEFAULT  '1',
                        show_on_home INT( 1 ) NOT NULL DEFAULT  '1',
                        show_on_arcv INT( 1 ) NOT NULL DEFAULT  '1',
                        position varchar(100) not null,
                        enable_shorttag tinyint(1) not null,
                        logging_method varchar(100) not null,
                        allowed_users varchar(200) not null,
                        editor_group varchar(50) not null,
                        custom_criteria longtext not null,
                        enable_comment tinyint(1) not null,
                        comment_limit INT( 10 ) NOT NULL DEFAULT  '0',
                        enable_question INT( 1 ) NOT NULL,
                        custom_question longtext not null,
                        show_credit_to_codeboxr INT( 1 ) NOT NULL DEFAULT  '1',
                        extrafields	longtext NOT NULL  DEFAULT '',
                        PRIMARY KEY  (id)
            ) $charset_collate;";

        $wpdb->query($sql);
        dbDelta($sql); //we take upgrade.php to get this method


        $table_name = self::get_user_ratings_table_name();
        $sql        = "CREATE TABLE IF NOT EXISTS $table_name (
                      id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                      form_id int(10) NOT NULL,
                      post_id int(10) NOT NULL,
                      post_type varchar(15),
                      rating longtext NOT NULL,
                      question LONGTEXT NOT NULL,
                      comment LONGTEXT NOT NULL,
                      comment_status VARCHAR(50) NOT NULL,
                      comment_hash VARCHAR(32) NOT NULL,
                      comment_limit INT( 10 ) NOT NULL DEFAULT '0',
                      average INT( 5 ) NOT NULL COMMENT  'value * 100',
                      user_id int(10),
                      user_name varchar(100) DEFAULT NULL,
                      user_email varchar(100) DEFAULT NULL,
                      user_session VARCHAR( 100 ),
                      user_ip VARCHAR( 45 ) NOT NULL,
                      created int(20) NOT NULL,
                      allow_user_to_hide VARCHAR( 16 ) NOT NULL DEFAULT  'false',
                      PRIMARY KEY  (id)
            ) $charset_collate;";
        $wpdb->query($sql);
        dbDelta($sql);


        $table_name = self::get_user_ratings_summury_table_name();
        $sql        = "CREATE TABLE IF NOT EXISTS $table_name (
                        id int(10) NOT NULL AUTO_INCREMENT,
                        post_id int(10) NOT NULL,
                        post_type VARCHAR( 25 ) NOT NULL,
                        form_id int(10) NOT NULL,
                        per_post_rating_count int(100) NOT NULL DEFAULT '0',
                        per_post_rating_summary int(2) NOT NULL,
                        custom_user_rating_summary longtext DEFAULT '' COLLATE utf8_unicode_ci NOT NULL,
                        per_criteria_rating_summary longtext DEFAULT '' COLLATE utf8_unicode_ci NOT NULL,
                        PRIMARY KEY  (id)
                )$charset_collate;";
        $wpdb->query($sql);
        dbDelta($sql);

    }

    /**
     *
     *
     */
    public static function update_table()
    {
        global $wpdb;

        $version = CB_RATINGSYSTEM_PLUGIN_VERSION; //notice how can we take value from other class.the var is static in nature

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        $method = 'install_table';
        if (method_exists('CBRatingSystemData', $method)) //
        {
            self::$method();
        }


    }

    /**
     *  Update tables comparing version or sql table structure
     *
     */
    public static function  modify_tables()
    {
        global $wpdb;
        $setting_table = self::get_ratingForm_settings_table_name();

    }

    /**
     * Delete all tables created by this plugin
     *
     */
    public static function delete_tables()
    {
        //delete tables

        global $wpdb;
        $table_name   = array(); 
        $table_name[] = self::get_ratingForm_settings_table_name(); //look how to create an array
        $table_name[] = self::get_user_ratings_table_name();
        $table_name[] = self::get_user_ratings_summury_table_name();

        $sql = "DROP TABLE IF EXISTS " . implode(', ', $table_name);
        $val = $wpdb->query($sql);

        //require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        //dbDelta( $sql );

    }

    /**
     * Delete all options created by this plugin
     *
     */
    public static function delete_options()
    {
        //delete options
        delete_option("cbratingsystem_defaultratingForm");
        delete_option("cbratingsystem_theme_key");
        delete_option("cbratingsystem_theme_settings");
        delete_option("cbratingsystem_deleteonuninstall");
    }

    /**
     * Delete all meta keys created by this plugin
     *
     */
    public static function  delete_metakeys()
    {
        //delete meta keys
        $meta_keys['_cbrating_enable_ratingForm']  = 'enable_ratingForm';
        $meta_keys['_cbrating_listing_ratingForm'] = 'listing_ratingForm';

        foreach ($meta_keys as $meta_key) {
            delete_post_meta_by_key($meta_key);
        }
    }

    /**
     * @param bool $is_object
     * @param array $action_option
     *
     * @return array
     */
    public static function get_ratingForms($is_object = false, array $action_option = array())
    {
        global $wpdb;

        $form_default  = CBRatingSystem::form_default_fields();
        $form_question = CBRatingSystem::form_default_question();
        $form_criteria = CBRatingSystem::form_default_criteria();


        $table_name = self::get_ratingForm_settings_table_name();

        $action     = '';
        if (!empty($action_option) and is_array($action_option)) {
            $action = "WHERE";
            $action .= (isset($action_option['is_actives']) ? ' is_active=1 AND' : '');
            $action .= (!empty($action_option['post_type']) and is_string($action_option['post_type']) ? ' post_types LIKE  \'%' . $action_option['post_type'] . '%\'' : '');

            if ($action == 'WHERE') {
                $action = '';
            }
        }

        if (substr($action, -3) == 'AND') {
            $action = substr($action, 0, -3);
        }

        $sql = "SELECT * FROM $table_name $action ORDER BY id ASC";


        if (!$is_object) { //how i want rating forms like an array or an object
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);

            for ($i = 0; $i < $count; $i++) {


                $results[$i]["custom_criteria"] = maybe_unserialize($results[$i]["custom_criteria"]);


                $results[$i]["custom_question"] = maybe_unserialize($results[$i]["custom_question"]);

                $result      = $results[$i];
                $extrafields = maybe_unserialize($result['extrafields']);

                $extrafields = (array)$extrafields;


                $result = array_merge($result, $extrafields);

                foreach ($form_default as $key => $field) {
                    if ($field['type'] == 'multiselect') {
                        //$result[$key] = maybe_unserialize($result[$key]);

                        if(isset($result[$key])){
                            $result[$key] = maybe_unserialize($result[$key]); // warning for new field
                        }
                        else{
                            $result[$key] = $field['default']; // warning for new field
                        }
                    }


                }

                $results[$i] = $result;

            }
        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {


                $results[$i]->custom_criteria = maybe_unserialize($results[$i]->custom_criteria);
                $results[$i]->custom_question = maybe_unserialize($results[$i]->custom_question);

                $result      = $results[$i];
                $extrafields = maybe_unserialize($result->extrafields);
                $extrafields = (array)$extrafields;

                $result = array_merge((array)$result, $extrafields);

                foreach ($form_default as $key => $field) {
                    if ($field['type'] == 'multiselect') {

                        if(isset($result[$key])){
                            $result[$key] = maybe_unserialize($result[$key]); // warning for new field
                        }
                        else{
                            $result[$key] = $field['default']; // warning for new field
                        }

                    }
                }

                $results[$i] = (object)$result;

            }
        }

        return $results;
    }

    /**
     * Get Rating form data
     *
     * @param $id
     * @param bool $is_object
     *
     * @return array|object
     */
    public static function get_ratingForm($id, $is_object = false)
    {

        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();
        $sql        = $wpdb->prepare("SELECT * FROM $table_name WHERE id=%d", $id);


        //$postTypes          = CBRatingSystem::post_types();
        //$userRoles          = CBRatingSystem::user_roles();
        //$editorUserRoles    = CBRatingSystem::editor_user_roles();

        $form_default  = CBRatingSystem::form_default_fields();
        $form_question = CBRatingSystem::form_default_question();
        $form_criteria = CBRatingSystem::form_default_criteria();

        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }
            //now we are fixing for array


            $result = $results[0];

            $result["custom_criteria"] = maybe_unserialize($result["custom_criteria"]);
            $result["custom_question"] = maybe_unserialize($result["custom_question"]);

            $extrafields = maybe_unserialize($result['extrafields']);

            $extrafields = (array)$extrafields;


            $result = array_merge($result, $extrafields);
            foreach ($form_default as $key => $field) {
                if ($field['type'] == 'multiselect') {

                    if(isset($result[$key])){
                        $result[$key] = maybe_unserialize($result[$key]); // warning for new field
                    }
                    else{
                        $result[$key] = $field['default']; // warning for new field
                    }

                    //if (!isset($result[$key])) continue;
                    //$result[$key] = maybe_unserialize($result[$key]);
                }
            }


        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return new stdclass();
            }


            $result = $results[0];

            $result->custom_criteria = maybe_unserialize($result->custom_criteria);
            $result->custom_question = maybe_unserialize($result->custom_question);


            $extrafields = maybe_unserialize($result->extrafields);
            $extrafields = (array)$extrafields;

            $result = array_merge((array)$result, $extrafields);

            foreach ($form_default as $key => $field) {
                if ($field['type'] == 'multiselect') {
                    //$result[$key] = maybe_unserialize($result[$key]);
                    if(isset($result[$key])){
                        $result[$key] = maybe_unserialize($result[$key]); // warning for new field
                    }
                    else{
                        $result[$key] = $field['default']; // warning for new field
                    }
                }
            }

            $result = (object)$result;


        }

        return $result;
    }

    /**
     * @param $form_id
     * @param string $post_id
     * @param string $user_id
     * @param bool $is_object
     *
     * @return array
     */
    public static function get_ratings($form_id, $post_id = '', $user_id = '', $is_object = false)
    {

        global $wpdb;
        $table_name1   = self::get_user_ratings_table_name();
        $active_clause = (!empty($post_id)) ? ((is_array($user_id) ? " AND ur.post_id IN(%s)" : " AND ur.post_id=%d")) : "";
        $active_clause .= (!empty($user_id)) ? ((is_array($user_id) ? " AND ur.user_id IN(%s)" : " AND ur.user_id=%d")) : "";
        $post_id = ((is_array($post_id) ? implode(',', $post_id) : $post_id));
        $user_id = ((is_array($user_id) ? implode(',', $user_id) : $user_id));

        $sql = $wpdb->prepare("SELECT ur.* FROM $table_name1 ur WHERE ur.form_id=%d $active_clause", $form_id, $post_id, $user_id);

        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]["rating"]   = maybe_unserialize($results[$i]["rating"]);
                $results[$i]["question"] = maybe_unserialize($results[$i]["question"]);
            }
        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]->rating   = maybe_unserialize($results[$i]->rating);
                $results[$i]->question = maybe_unserialize($results[$i]->question);
            }
        }

        return $results;
    }

    /**
     * Ger user rating with rating form
     *
     * @param array $form_id
     * @param array $post_id
     * @param array $user_id
     * @param string $user_session
     * @param string $sort
     * @param string $sort_type
     * @param array $limit
     * @param bool $is_object
     * @param string $commentID
     * @param array $post_types
     * @param array $status
     * @param array $post_status
     *
     * @return array|null|object
     */
    public static function get_user_ratings_with_ratingForm(array $form_id = array(), array $post_id = array(), array $user_id = array(), $user_session = '', $sort = 'created', $sort_type = 'DESC', array $limit = array(), $is_object = false, $commentID = '', array $post_types = array(), array $status = array(), array $post_status = array(''))
    {
        global $wpdb;


        $table_rate_logs = self::get_user_ratings_table_name(); //user log
        $table_forms     = self::get_ratingForm_settings_table_name(); //form table
        $table_posts     = $wpdb->posts;

        $form_id     = array_filter($form_id);
        $post_id     = array_filter($post_id);
        $user_id     = array_filter($user_id);
        $post_types  = array_filter($post_types);
        $status      = array_filter($status);
        $post_status = array_filter($post_status);

        //when need one comment/rating
        $commentID = intval($commentID);

        $active_clause = (!empty($form_id) && is_array($form_id)) ? " AND ur.form_id IN ('" . implode(',', $form_id) . "')" : "";
        $active_clause .= (!empty($post_id) && is_array($post_id)) ? " AND ur.post_id IN ('" . implode(',', $post_id) . "')" : "";
        $active_clause .= (!empty($user_id) && is_array($user_id)) ? " AND ur.user_id IN ('" . implode(',', $user_id) . "')" : "";
        $active_clause .= (!empty($status) && is_array($status)) ? " AND ur.comment_status IN ('" . implode(',', $status) . "')" : "";
        $active_clause .= (!empty($post_types) && is_array($post_types)) ? " AND ur.post_type IN ('" . implode(',', $post_types) . "')" : "";
        $active_clause .= (!empty($post_status) && is_array($post_status)) ? " AND p.post_status IN ('" . implode(',', $post_status) . "')" : "";

        $active_clause .= (($user_session != '')) ? " AND ur.user_session='" . $user_session . "' " : "";

        $active_clause .= (($commentID > 0)) ? " AND ur.id='" . $commentID . "' " : "";


        $sortingOrder = '';


		if ($sort == 'created') {
			$sortingOrder = 'ORDER BY ur.created ' . $sort_type;
		} elseif ($sort == 'post_id') {
			$sortingOrder = 'ORDER BY ur.post_id ' . $sort_type;
		} elseif ($sort == 'post_title') {
			$sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
		} elseif ($sort == 'form_id') {
			$sortingOrder = 'ORDER BY ur.form_id ' . $sort_type;
		} elseif ($sort == 'post_type') {
			$sortingOrder = 'ORDER BY ur.post_type ' . $sort_type;
		} elseif ($sort == 'comment') {
			$sortingOrder = 'ORDER BY ur.comment_status ' . $sort_type;
		} elseif ($sort == 'user_id') {
			$sortingOrder = 'ORDER BY ur.user_id ' . $sort_type;
		} elseif ($sort === 'avg') {
			$sortingOrder = 'ORDER BY ur.average ' . $sort_type;
		}elseif ($sort === 'id') {
			$sortingOrder = 'ORDER BY ur.id ' . $sort_type;
		}




        $limitAction = '';


		$perpage = isset($limit['perpage']) ? intval($limit['perpage']) : 20;
		$page    = isset($limit['page']) ? intval($limit['page']) : 1;

		$start_point = ($page * $perpage) - $perpage;

		$limitAction = "LIMIT";
		$limitAction .= ' ' . $start_point . ',';
		$limitAction .= ' ' . $perpage;




        $sql = "SELECT rs.name, rs.custom_criteria, rs.custom_question, ur.*, p.post_title, p.post_type  FROM $table_rate_logs ur
                INNER JOIN  $table_forms rs
                INNER JOIN $table_posts p ON p.ID = ur.post_id
                WHERE rs.id = ur.form_id $active_clause $sortingOrder $limitAction";


        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]["rating"]          = maybe_unserialize($results[$i]["rating"]);
                $results[$i]["custom_criteria"] = maybe_unserialize($results[$i]["custom_criteria"]);
                $results[$i]["question"]        = maybe_unserialize($results[$i]["question"]);
                $results[$i]["custom_question"] = maybe_unserialize($results[$i]["custom_question"]);
            }
        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]->rating          = maybe_unserialize($results[$i]->rating);
                $results[$i]->custom_criteria = maybe_unserialize($results[$i]->custom_criteria);
                $results[$i]->question        = maybe_unserialize($results[$i]->question);
                $results[$i]->custom_question = maybe_unserialize($results[$i]->custom_question);
            }
        }

        return $results;
    }

    /**
     * Ger user rating with rating form total count
     *
     * @param array $form_id
     * @param array $post_id
     * @param array $user_id
     * @param string $user_session
     * @param string $sort
     * @param string $sort_type
     * @param array $limit
     * @param bool $is_object
     *
     *
     * @return array
     */
    public static function get_user_ratings_with_ratingForm_total(array $form_id = array(), array $post_id = array(), array $user_id = array(), $user_session = '', $sort = 'created', $sort_type = 'DESC', $commentID = '', array $post_types = array(), array $status = array(), array $post_status = array())
    {
        global $wpdb;


        $table_rate_logs = self::get_user_ratings_table_name(); //user log
        $table_forms     = self::get_ratingForm_settings_table_name(); //form table
        $table_posts     = $wpdb->posts;

        $form_id        = array_filter($form_id);
        $post_id        = array_filter($post_id);
        $user_id        = array_filter($user_id);
        $post_types     = array_filter($post_types);
        $status         = array_filter($status);
        $post_status    = array_filter($post_status);

        //when need one comment/rating
        $commentID = intval($commentID);

        $active_clause = (!empty($form_id) && is_array($form_id)) ? " AND ur.form_id IN ('" . implode(',', $form_id) . "')" : "";
        $active_clause .= (!empty($post_id) && is_array($post_id)) ? " AND ur.post_id IN ('" . implode(',', $post_id) . "')" : "";
        $active_clause .= (!empty($user_id) && is_array($user_id)) ? " AND ur.user_id IN ('" . implode(',', $user_id) . "')" : "";
        $active_clause .= (!empty($status) && is_array($status)) ? " AND ur.comment_status IN ('" . implode(',', $status) . "')" : "";
        $active_clause .= (!empty($post_types) && is_array($post_types)) ? " AND ur.post_type IN ('" . implode(',', $post_types) . "')" : "";
        $active_clause .= (!empty($post_status) && is_array($post_status)) ? " AND p.post_status IN ('" . implode(',', $post_status) . "')" : "";



        $active_clause .= (($user_session != '')) ? " AND ur.user_session='" . $user_session . "' " : "";

        $active_clause .= (($commentID > 0)) ? " AND ur.id='" . $commentID . "' " : "";


        $sortingOrder = '';

        if (!empty($sort) and !empty($sort_type)) {
            if ($sort == 'created') {
                $sortingOrder = 'ORDER BY ur.created ' . $sort_type;
            } elseif ($sort == 'post_id') {
                $sortingOrder = 'ORDER BY ur.post_id ' . $sort_type;
            } elseif ($sort == 'post_title') {
                $sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
            } elseif ($sort == 'form_id') {
                $sortingOrder = 'ORDER BY ur.form_id ' . $sort_type;
            } elseif ($sort == 'post_type') {
                $sortingOrder = 'ORDER BY ur.post_type ' . $sort_type;
            } elseif ($sort == 'comment') {
                $sortingOrder = 'ORDER BY ur.comment_status ' . $sort_type;
            } elseif ($sort == 'user_id') {
                $sortingOrder = 'ORDER BY ur.user_id ' . $sort_type;
            } elseif ($sort === 'avg') {
                $sortingOrder = 'ORDER BY ur.average ' . $sort_type;
            }elseif ($sort === 'id') {
                $sortingOrder = 'ORDER BY ur.id ' . $sort_type;
            }
        }


        $limitAction = '';




        $sql = "SELECT COUNT(*)  FROM $table_rate_logs ur
                INNER JOIN  $table_forms rs
                INNER JOIN $table_posts p ON p.ID = ur.post_id
                WHERE rs.id = ur.form_id $active_clause $sortingOrder $limitAction";

        $count = $wpdb->get_var($sql);
        return $count;
    }



    /*
     * Getting the last review/rating using last review/rating id
     *
     * @param array  $form_id
     * @param array  $post_id
     * @param array  $user_id
     * @param string $user_session
     * @param string $sort
     * @param string $sort_type
     * @param array  $limit
     *
     * return array|object
     *
     */
    public static function get_user_ratings_with_ratingForm_lastID($lastCommentID, $is_object = false)
    {
        global $wpdb;


        $table_rate_logs = self::get_user_ratings_table_name(); //user log
        $table_forms     = self::get_ratingForm_settings_table_name(); //form table
        $table_posts     = $wpdb->posts;

        $sql = $wpdb->prepare(
            "SELECT ur.*, p.post_title, p.post_type, rs.name, rs.custom_criteria, rs.custom_question FROM $table_rate_logs ur
                INNER JOIN $table_forms rs
                INNER JOIN $table_posts p ON p.ID = ur.post_id
                WHERE rs.id = ur.form_id AND ur.id=%d ", $lastCommentID
        );


        if ($is_object) {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return new object();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]->rating          = maybe_unserialize($results[$i]->rating);
                $results[$i]->custom_criteria = maybe_unserialize($results[$i]->custom_criteria);
                $results[$i]->question        = maybe_unserialize($results[$i]->question);
                $results[$i]->custom_question = maybe_unserialize($results[$i]->custom_question);
            }

        } else {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]["rating"]          = maybe_unserialize($results[$i]["rating"]);
                $results[$i]["custom_criteria"] = maybe_unserialize($results[$i]["custom_criteria"]);
                $results[$i]["question"]        = maybe_unserialize($results[$i]["question"]);
                $results[$i]["custom_question"] = maybe_unserialize($results[$i]["custom_question"]);
            }
        }

        return $results;
    }

    /**
     * Get Avg Rating Summary data
     *
     * @param array $whereOptions
     * @param string $sort
     * @param string $sort_type
     * @param bool|false $is_object
     * @param int $limit
     * @return array|null|object
     */
    public static function get_ratings_summary(array $whereOptions = array(), $sort = 'form_id', $sort_type = 'ASC', $is_object = false, $perpage = 10, $page = 1)
    {
        global $wpdb;

        // 1 = post rating date 0 = post creation date

        $summary_table = self::get_user_ratings_summury_table_name();
        $post_table    = $wpdb->posts;
        $form_table    = self::get_ratingForm_settings_table_name();

        $active_clause = '';
        $sortingOrder  = '';

        $userRoleSQL = '';

        if (!empty($whereOptions)) {
            $active_clause = (!empty($whereOptions['form_id']) && is_array($whereOptions['form_id'])) ? " AND rs.form_id IN ('" . implode(',', $whereOptions['form_id']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_id']) && is_array($whereOptions['post_id'])) ? " AND rs.post_id IN ('" . implode(',', $whereOptions['post_id']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_type']) && is_array($whereOptions['post_type'])) ? " AND rs.post_type IN ('" . implode(',', $whereOptions['post_type']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_date']) && !is_array($whereOptions['post_date'])) ? " AND p.post_date > '{$whereOptions['post_date']}'" : "";
        }



        if ($sort == 'post_id') {
            $sortingOrder = 'ORDER BY p.ID ' . $sort_type;
        } elseif ($sort == 'post_title') {
            $sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
        } elseif ($sort == 'form_id') {
            $sortingOrder = 'ORDER BY rs.form_id ' . $sort_type;
        } elseif ($sort === 'avg') {
            $sortingOrder = 'ORDER BY rs.per_post_rating_summary ' . $sort_type;
        }
        elseif ($sort === 'per_post_rating_count') {
            $sortingOrder = 'ORDER BY rs.per_post_rating_count ' . $sort_type;
        }



        $start_point = ($page * $perpage) - $perpage;
        $limitAction = "LIMIT";
        $limitAction .= ' ' . $start_point . ',';
        $limitAction .= ' ' . $perpage;



        //$form_id = '';
        //$post_id = '';

        //on wpdb error was from here
        $sql =
            "SELECT rs.*, p.post_title, p.post_type, r.name FROM $summary_table rs
             INNER JOIN $post_table p
             INNER JOIN $form_table r
             $userRoleSQL
             WHERE p.ID=rs.post_id AND r.id=rs.form_id $active_clause $sortingOrder $limitAction";




        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);


            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]["per_criteria_rating_summary"] = maybe_unserialize($results[$i]["per_criteria_rating_summary"]);
                $results[$i]["custom_user_rating_summary"]  = maybe_unserialize($results[$i]["custom_user_rating_summary"]);
            }
        } else {
            $results = $wpdb->get_results($sql, OBJECT);


            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]->per_criteria_rating_summary = maybe_unserialize($results[$i]->per_criteria_rating_summary);
                $results[$i]->custom_user_rating_summary  = maybe_unserialize($results[$i]->custom_user_rating_summary);

            }
        }


        return $results;
    }

    /**
     * Get Avg Rating Summary data
     *
     * @param array $whereOptions
     * @param string $sort
     * @param string $sort_type
     * @param bool|false $is_object
     * @param int $limit
     * @return array|null|object
     */
    public static function get_ratings_summary_total(array $whereOptions = array(), $sort = 'form_id', $sort_type = 'ASC')
    {
        global $wpdb;

        // 1 = post rating date 0 = post creation date
        //$interval_type = isset($whereOptions['daytype']) ? intval($whereOptions['daytype']): 1;

        $summary_table = self::get_user_ratings_summury_table_name();
        $post_table    = $wpdb->posts;
        $form_table    = self::get_ratingForm_settings_table_name();

        $active_clause = '';
        $sortingOrder  = '';
        //$limit          = '';
        $userRoleSQL = '';

        if (!empty($whereOptions)) {
            $active_clause = (!empty($whereOptions['form_id']) && is_array($whereOptions['form_id'])) ? " AND rs.form_id IN ('" . implode(',', $whereOptions['form_id']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_id']) && is_array($whereOptions['post_id'])) ? " AND rs.post_id IN ('" . implode(',', $whereOptions['post_id']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_type']) && is_array($whereOptions['post_type'])) ? " AND rs.post_type IN ('" . implode(',', $whereOptions['post_type']) . "')" : "";
            $active_clause .= (!empty($whereOptions['post_date']) && !is_array($whereOptions['post_date'])) ? " AND p.post_date > '{$whereOptions['post_date']}'" : "";
        }



        if ($sort == 'post_id') {
            $sortingOrder = 'ORDER BY p.ID ' . $sort_type;
        } elseif ($sort == 'post_title') {
            $sortingOrder = 'ORDER BY p.post_title ' . $sort_type;
        } elseif ($sort == 'form_id') {
            $sortingOrder = 'ORDER BY rs.form_id ' . $sort_type;
        } elseif ($sort === 'avg') {
            $sortingOrder = 'ORDER BY rs.per_post_rating_summary ' . $sort_type;
        }
        elseif ($sort === 'per_post_rating_count') {
            $sortingOrder = 'ORDER BY rs.per_post_rating_count ' . $sort_type;
        }







        //$form_id = '';
        //$post_id = '';

        //on wpdb error was from here
        $sql =
            "SELECT COUNT(*) FROM $summary_table rs
             INNER JOIN $post_table p
             INNER JOIN $form_table r
             $userRoleSQL
             WHERE p.ID=rs.post_id AND r.id=rs.form_id $active_clause $sortingOrder";



        $count = $wpdb->get_var($sql);
        return $count;
    }

    /**
     * @param bool $is_object
     *
     * @return array
     */
    public static function get_ratings_summary_with_ratingForms($is_object = false)
    {
        global $wpdb;

        $summary_table = self::get_user_ratings_summury_table_name();
        $form_table    = self::get_ratingForm_settings_table_name();

        $sql = "SELECT SUM(rs.per_post_rating_count) AS count, rs.*, r.* FROM $form_table r
            LEFT JOIN $summary_table rs ON r.id=rs.form_id
            GROUP BY r.id
            ORDER BY rs.per_post_rating_count DESC";

        //echo '<pre>'; print_r($sql); echo '</pre>'; //die();

        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);
            for ($i = 0; $i < $count; $i++) {
                $results[$i]["per_criteria_rating_summary"] = maybe_unserialize($results[$i]["per_criteria_rating_summary"]);
                $results[$i]["rating"]                      = maybe_unserialize($results[$i]["rating"]);
                $results[$i]["custom_criteria"]             = maybe_unserialize($results[$i]["custom_criteria"]);
                $results[$i]["question"]                    = maybe_unserialize($results[$i]["question"]);
                $results[$i]["custom_question"]             = maybe_unserialize($results[$i]["custom_question"]);
            }
        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return array();
            }

            $count = sizeof($results);


            for ($i = 0; $i < $count; $i++) {

                $results[$i]->per_criteria_rating_summary = maybe_unserialize($results[$i]->per_criteria_rating_summary);

                if (property_exists($results[$i], 'rating')) {
                    $results[$i]->rating = maybe_unserialize($results[$i]->rating);
                }
                if (property_exists($results[$i], 'question')) {
                    $results[$i]->question = maybe_unserialize($results[$i]->question);
                }

                $results[$i]->custom_criteria = maybe_unserialize($results[$i]->custom_criteria);

                $results[$i]->custom_question = maybe_unserialize($results[$i]->custom_question);
            }
        }

        return $results;
    }

    /**
     * @param $ratingForm
     *
     * @return bool
     */
    public static function update_ratingForm($ratingForm)
    {
        global $wpdb;

        if (!empty($ratingForm)) {
            $table_name = self::get_ratingForm_settings_table_name();

            $id = $ratingForm['id'];
            unset($ratingForm['id']);

            $fieldTypes = self::check_array_element_value_type($ratingForm);

            if ($id == 0) {
                $rating_forms = self:: get_ratingForms();
                if (is_array($rating_forms) && count($rating_forms) >= 1) {
                    $_can_add_cbratingform = apply_filters('cbraing_add_more_forms', false);
                } else {
                    $_can_add_cbratingform = true;
                }
                if ($_can_add_cbratingform) {
                    $success = $wpdb->insert($table_name, $ratingForm, $fieldTypes);
                    $id      = $wpdb->insert_id;
                } else {
                    $success = false;
                    add_filter('cbrating_error', array('CBRatingSystemData', 'cbrating_no_more_forms_error'));
                }

            } else {

                $success = $wpdb->update($table_name, $ratingForm, array("id" => $id));

            }
        }

        return ($success !== false) ? $id : false;
    }

    // edit error msg
    public static function cbrating_no_more_forms_error($error)
    {
        return __('No more forms for free version', 'cbratingsystem');
    }

    /**
     * @param $rating
     *
     * @return insertid|bool
     */
    public static function update_rating($rating)
    {
        global $wpdb;

        if (!empty($rating)) {
            $table_name = self::get_user_ratings_table_name();

            $fieldTypes = self::check_array_element_value_type($rating);

            $success = $wpdb->insert($table_name, $rating, $fieldTypes);
        }

        return ($success) ? $wpdb->insert_id : false;
    }

    /**
     * @param $rating
     *
     * @return bool
     */
    public static function update_rating_comment($rating)
    {
        global $wpdb;

        if (!empty($rating)) {
            $table_name = self::get_user_ratings_table_name();

            $fieldTypes = self::check_array_element_value_type($rating);

            $success = $wpdb->update($table_name, $rating, array('id' => $rating['id'], 'post_id' => $rating['post_id'], 'form_id' => $rating['form_id']), $fieldTypes, array('%d', '%d'));
        }

        return ($success) ? $wpdb->insert_id : false;
    }



    /**
     * Update rating summary for each post
     *
     * @param $rating
     *
     * @return bool
     */
    public static function update_rating_summary($rating)
    {
        global $wpdb;

        if (!empty($rating)) {
            $table_name = self::get_user_ratings_summury_table_name();
            $fieldTypes = self::check_array_element_value_type($rating);

            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM $table_name WHERE post_id=%d AND form_id=%d", $rating['post_id'], $rating['form_id']));
            //echo '<pre>Count:'; print_r($count); echo '</pre>'; die();
            if ($count > 0) {
                $success = $wpdb->update($table_name, $rating, array('post_id' => $rating['post_id'], 'form_id' => $rating['form_id']), $fieldTypes, array('%d', '%d'));
                update_post_meta($rating['post_id'], 'cbrating' . $rating['form_id'], $rating['per_post_rating_summary']);
            } else {
                $success = $wpdb->insert($table_name, $rating, $fieldTypes);
                add_post_meta($rating['post_id'], 'cbrating' . $rating['form_id'], $rating['per_post_rating_summary'], true);
            }
        }
        return ($success) ? true : false;
    }



    /**
     * @param array $id
     */
    public static function delete_ratingForm(array $id)
    {

        global $wpdb;
        $table_name = self::get_ratingForm_settings_table_name();
        $sql = "DELETE FROM $table_name WHERE id IN (" . implode(',', $id) . ")";
        $wpdb->query($sql);
    }

    /**
     * @param array $ids
     */
    public static function delete_user_rating(array $ids)
    {

        global $wpdb;
        $table_name1 = self::get_user_ratings_summury_table_name();
        $table_name  = self::get_user_ratings_table_name();

        foreach ($ids as $id) {
            $sql     = $wpdb->prepare("SELECT post_id ,form_id FROM $table_name1 WHERE id=%d ", $id);
            $results = $wpdb->get_results($sql, ARRAY_A);
            $sql     = $wpdb->prepare("DELETE FROM $table_name WHERE post_id=%d AND form_id=%d", $results[0]['post_id'], $results[0]['form_id']);
            $results = $wpdb->get_results($sql, ARRAY_A);

        }
        // return 0;
    }


    /**
     * Delete forms by id(s), as well delete all avg and user log for this form
     *
     * @param array $form_ids
     */
    public static function delete_ratingForm_with_all_ratings(array $form_ids)
    {

        global $wpdb;

        $form_table    = self::get_ratingForm_settings_table_name();
        $summary_table = CBRatingSystemData::get_user_ratings_summury_table_name();
        $userlog_table = CBRatingSystemData::get_user_ratings_table_name();

        foreach ($form_ids as $form_id) {
            $sql     = $wpdb->prepare("SELECT id, post_id ,form_id FROM $summary_table WHERE form_id=%d ", $form_id);
            $results = $wpdb->get_results($sql);

            foreach ($results as $result) {
                $sql_log     = $wpdb->prepare("SELECT id, form_id, post_id, user_id FROM $userlog_table WHERE post_id=%d AND form_id=%d ", $result->post_id, $result->form_id);
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
                delete_post_meta($result->post_id, 'cbrating' . $result->form_id);

                //delete this avg log
                $sql = $wpdb->prepare("DELETE FROM $summary_table WHERE id=%d", $result->id);
                $wpdb->query($sql);
            }

            //lastly delete the form from the form setting table
            $sql            = $wpdb->prepare("DELETE FROM $form_table WHERE id = %d", $form_id);
            $return_value_1 = $wpdb->query($sql);
        }

        return 1;
    }

    /**
     * @param array $form_id
     * @param array $post_id
     *
     * @return mixed
     */
    public static function delete_ratingSummary(array $form_id = array(), array $post_id = array())
    {
        global $wpdb;
        $table_name = self::get_user_ratings_summury_table_name();

        $action = (!empty($form_id) || !empty($post_id)) ? 'WHERE' : '';
        $action .= (!empty($form_id)) ? ' form_id IN (\'' . implode(',', $form_id) . '\') AND' : '';
        $action .= (!empty($post_id)) ? ' post_id IN (' . implode(',', $post_id) . ') AND' : '';

        if (substr($action, -3) == 'AND') {
            $action = substr($action, 0, -3);
        }

        $return = $wpdb->query("DELETE FROM $table_name $action");

        return $return;
    }

    /**
     * @param array $id
     * @param array $post_id
     * @param array $form_id
     * @param array $ip
     *
     * @return mixed
     */

    public static function delete_ratings(array $id, array $post_id = array(), array $form_id = array(), array $ip = array())
    {

        global $wpdb;
        $table_name = self::get_user_ratings_table_name();

        $action = (!empty($id) || !empty($post_id) || !empty($form_id) || !empty($ip)) ? 'WHERE' : '';
        $action .= (!empty($id)) ? ' id IN (' . implode(',', $id) . ') AND' : '';
        $action .= (!empty($post_id)) ? ' post_id IN (\'' . implode(',', $post_id) . '\') AND' : '';
        $action .= (!empty($form_id)) ? ' form_id IN (\'' . implode(',', $form_id) . '\') AND' : '';
        $action .= (!empty($ip)) ? ' user_ip IN (\'' . implode(',', $ip) . '\') AND' : '';

        if (substr($action, -3) == 'AND') {

            $action = substr($action, 0, -3);
        }

        $sql = "DELETE FROM $table_name $action";


        $return = $wpdb->query($sql);


        return $return;
    }

    /**
     * @param array $post_id
     * @param array $form_id
     */
    public static function delete_ratings_log(array $post_id = array(), array $form_id = array())
    {
        self::delete_ratings(array(), $post_id, $form_id, array());
        self::delete_ratingSummary($form_id, $post_id);

    }



    /**
     * @param $array
     *
     * @return array
     */
    public static function check_array_element_value_type($array)
    {
        $ret = array();

        if (!empty($array)) {
            foreach ($array as $val) {
                $ret[] = self::check_value_type($val);
            }
        }

        return $ret;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function check_value_type($string)
    {
        $t   = gettype($string);
        $ret = '';

        switch ($t) {
            case 'string' :
                $ret = '\'%s\'';
                break;

            case 'integer':
                //$ret = '\'%d\'';
                $ret = '%d';
                break;
        }

        return $ret;
    }

    /**
     * @return string
     */
    public static function get_ratingForm_settings_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_ratingform_settings";
    }

    /**
     * @return string
     */
    public static function get_user_ratings_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_user_ratings";
    }

    /**
     * @return string
     */
    public static function get_user_ratings_summury_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . "cbratingsystem_ratings_summary";
    }// get top rated post function added 27-11-14
    /*
   *
   */
    /**
     * @param array $whereOptions
     * @param bool $is_object
     * @param string $limit
     *
     * @return array|mixed
     * get top rated post for widget and shortcode
     */
    public static function get_top_rated_post(array $whereOptions = array(), $is_object = false, $limit = '')
    {
        global $wpdb;


        //$interval_type = $whereOptions['interval_type'];

        $firstorder = $whereOptions['firstorder'];

        if ($firstorder == 'post_count') {
            $secondorder = 'rating';
        } else if ($firstorder == 'rating') {
            $secondorder = 'post_count';
        }


        $active_clause = '';

        if ($whereOptions ['post_filter'] == 'post_id') {
            if ($whereOptions ['post_id'] != '' && $whereOptions ['post_id'] != '0') {
                $active_clause .= 'AND post.ID IN (' . ($whereOptions['post_id']) . ') ';

            }
        } else if ($whereOptions ['post_filter'] == 'post_type') {

            if ($whereOptions ['post_type'] != '' && $whereOptions ['post_type'] != '0') {
                $active_clause .= 'AND post.post_type ="' . $whereOptions['post_type'] . '"';
            }
        }

        if ($whereOptions ['user_id'] != '') {
            if ($whereOptions ['user_id'] != '' && $whereOptions ['user_id'] != '0') {
                $active_clause .= 'AND post.post_author IN (' . ($whereOptions['user_id']) . ') ';

            }
        }

        if ($whereOptions ['form_id'] != '' && $whereOptions ['form_id'] != '0') {
            $active_clause .= 'AND summary.form_id ="' . (int)$whereOptions ['form_id'] . '"';
        }

        if (array_key_exists('post_date', $whereOptions)) {
            $active_clause .= 'AND post.post_date >"' . $whereOptions['post_date'] . '"';
        }

        if ($whereOptions ['order'] != '') {
            $order_by = $whereOptions ['order'];
        } else {
            $order_by = 'DESC';
        }

        if ($limit != '') {
            $limit = (int)(preg_replace("/[^0-9]/", "", $limit));
            //var_dump($limit);
            $limit = 'LIMIT ' . $limit;
        }

        $posttable    = $wpdb->prefix . "posts";
        $summarytable = self::get_user_ratings_summury_table_name();
        $usertable    = $wpdb->prefix . "users";
        $formtable    = self::get_ratingForm_settings_table_name();



        $sql = "SELECT SUM(summary.per_post_rating_summary)/count(summary.post_id) as rating, count(summary.post_id) as post_count,post.post_author  FROM $posttable as post  LEFT JOIN $summarytable as summary ON summary.post_id = post.ID  WHERE  post.post_status = 'publish' $active_clause GROUP BY post.post_author ORDER BY $firstorder $order_by ,$secondorder  $order_by $limit";


        if (!$is_object) {
            $results = $wpdb->get_results($sql, ARRAY_A);

            if (empty($results)) {
                return array();
            }

        } else {
            $results = $wpdb->get_results($sql, OBJECT);

            if (empty($results)) {
                return array();
            }
        }

        return $results;
    }

    public static function getReviewsCountByStatus($form_id = 0, $post_id = 0, $user_id = 0, $post_type = ''){

        global $wpdb;


        $table_rate_logs = self::get_user_ratings_table_name(); //user log

        $where_sql = '';
        if($form_id != 0){
            $where_sql .= $wpdb->prepare('form_id=%d', $form_id);
        }

        if($post_id != 0){
            if($where_sql != '') $where_sql .= ' AND ';
            $where_sql .= $wpdb->prepare('post_id=%d', $post_id);
        }

        if($user_id != 0){
            if($where_sql != '') $where_sql .= ' AND ';
            $where_sql .= $wpdb->prepare('user_id=%d', $user_id);
        }

        if($post_type !=  ''){
            if($where_sql != '') $where_sql .= ' AND ';
            $where_sql .= $wpdb->prepare('post_type=%s', $post_type);
        }

        if($where_sql == ''){
            $where_sql = '1';
        }

        $sql_select = "SELECT comment_status, COUNT(*) as reviews_counts FROM $table_rate_logs  WHERE   $where_sql GROUP BY comment_status";

        $results = $wpdb->get_results( "$sql_select", 'ARRAY_A' );

        $total = 0;

        $data = array(
            'all' => $total
        );


        if($results != null){
            foreach($results  as $result){

                $total += intval($result['reviews_counts']);

                $data[$result['comment_status']] = $result['reviews_counts'];
            }
            $data['all'] = $total;
        }

        return $data;

    }

}// end of class