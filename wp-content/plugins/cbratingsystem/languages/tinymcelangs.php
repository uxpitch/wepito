<?php
/**
 * Created by PhpStorm.
 * User: fast user
 * Date: 2/16/2015
 * Time: 6:31 PM
 */


// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );



function cbratingsystem_tinymce_plugin_translation() {

    $ratingForms    = CBRatingSystemData::get_ratings_summary_with_ratingForms(true);
    $ratingForms_arr    = array();
    foreach ($ratingForms as $ratingForm) {
        $ratingForms_arr[] = array('value' => $ratingForm->id, 'text' => $ratingForm->name);
    }

    $themes = array(
        'basic' => __("Basic Theme", 'cbratingsystem')
    );

    $themes = apply_filters('cbratingsystem_theme_options', $themes);
    $themes_arr = array();
    foreach($themes as $theme_key => $theme_title){
        $themes_arr[] = array('value' => $theme_key, 'text' => $theme_title);
    }

    $strings = array(
        'title'                 => __( 'CBX Rating System Shortcode', 'cbratingsystem' ),
        'form_id_label'         => __( 'Choose Form', 'cbratingsystem' ),
        'form_id_tooltip'       => __( 'Select form', 'cbratingsystem' ),
        'theme_key_label'       => __( 'Theme', 'cbratingsystem' ),
        'theme_key_tooltip'     => __( 'Choose Theme', 'cbratingsystem' ),
        'showreview_label'      => __( 'Show Review', 'cbratingsystem' ),
        'showreview_tooltip'    => __( 'Show Reviews', 'cbratingsystem' ),
        'showreview_yes'        => __( 'Yes', 'cbratingsystem' ),
        'showreview_no'         => __( 'No', 'cbratingsystem' ),
        'forms'                 => json_encode( $ratingForms_arr ),
        'themes'                => json_encode( $themes_arr ),
    );



    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.cbratingsystem", ' . json_encode( $strings ) . ");\n";
    return $translated;
}

$strings = cbratingsystem_tinymce_plugin_translation( );