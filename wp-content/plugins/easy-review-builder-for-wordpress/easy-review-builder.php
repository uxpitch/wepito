<?php
/*
Plugin Name: Easy Review Builder
Version: 0.8
Plugin URI: http://www.dyerware.com/main/products/easy-review-builder
Description: Creates a customizable star-based review summary table from a shortcode
Author: dyerware
Author URI: http://www.dyerware.com
*/
/*  Copyright © 2009, 2010, 2011, 2012, 2013  dyerware
    Support: support@dyerware.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
  
class wpEasyReview
{
    private $reviewNum = 0;
    
	// Database Settings
 	var $DEF_TITLE = "Review Score";
	var $DEF_RATINGMAX = 5;
	var $DEF_OVERALL = true;
	var $DEF_ICON = "star";
	var $DEF_SUMMARY = "Average score from all categories.";
	var $DEF_TABLECSS = "easyReviewWrapper";
	
	var $DEF_TITLEBACKCOLOR = "8A038A";
	var $DEF_BACKCOLOR = "FFE5FF";
	var $DEF_LINECOLOR = "8A038A";
	var $DEF_TITLETEXTCOLOR = "FFFFFF";
	var $DEF_CATTEXTCOLOR = "000000";
	var $DEF_BODYTEXTCOLOR = "0019BD";
	var $DEF_ENDBKGCOLOR = "FFB2FF";
	var $DEF_BKGALPHA = "1.0";
	var $DEF_CUSTOMCOLORS = false;
	
			
	var $op; 
    
	public function __construct()
    { 
       $jsDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) . 'js';       
       $this->init_options_map();
       $this->load_options();

	   if (is_admin()) 
	   {
           add_action('admin_head', array(&$this,'add_admin_files'));
        	add_action('admin_menu', array(&$this, 'add_admin_menu'));
	   }                    
    }

    function CTXID() 
    { 
        return get_class($this); 
    }
    
	function addCSS() 
	{
		echo '<link type="text/css" rel="stylesheet" href="' . plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) .'/easy-review-builder.css" />';
	
	}
	
	function add_admin_files() 
    {	
        $plgDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ); 
        
        	
    	if ( isset( $_GET['page'] ) && $_GET['page'] == 'easy-review-builder-for-wordpress/easy-review-builder.php' ) 
    	{
    	    echo "<link rel='stylesheet' media='screen' type='text/css' href='" . $plgDir . "/colorpicker/code/colorpicker.css' />\n";
    		echo "<script type='text/javascript' src='" . $plgDir . "/colorpicker/code/colorpicker.js'></script>\n";
  
	
        $cmt = '// <![CDATA[';
        $cmte = '// ]]>';
        echo '
<script type="text/javascript">
' . $cmt . '
	jQuery(document).ready(function($){       		
        jQuery(".dyerware-color").each(function(index, obj){
			$(obj).ColorPicker({
			 	onShow: function (colpkr) {
              		$(colpkr).fadeIn(200);
              		return false;
	            },
            	onHide: function (colpkr) {
            		$(colpkr).fadeOut(200);
            		return false;
            	},
            	onChange: function (hsb, hex, rgb) {
            		jQuery(obj).css("backgroundColor", "#" + hex);
            		jQuery(obj).css("color", (hsb.b < 50 || (hsb.s > 75 && hsb.b < 75)) ? "#fff" : "#000");
            		jQuery(obj).val(hex.toUpperCase()); 
            	},
        		onSubmit: function(hsb, hex, rgb, el) 
        		  { jQuery(obj).css("backgroundColor", "#" + hex);
        		    jQuery(obj).css("color", (hsb.b < 50 || (hsb.s > 75 && hsb.b < 75)) ? "#fff" : "#000");
        		    jQuery(el).val(hex.toUpperCase()); 
        		    jQuery(el).ColorPickerHide(); },
        		onBeforeShow: function () 
        		  { jQuery(this).ColorPickerSetColor( jQuery(this).attr("value") ); }
        		});
		}); 	     	
	});	
' . $cmte . '
</script>';	
    	}	       	
    }


 	function add_admin_menu() 
 	{
		$title = 'Easy Review Builder';
		add_options_page($title, $title, 10, __FILE__, array(&$this, 'handle_options'));
	}
	
	function init_options_map() 
	{
		$opnames = array(
			'DEF_TITLE', 'DEF_RATINGMAX', 'DEF_OVERALL', 'DEF_ICON', 'DEF_SUMMARY', 'DEF_TABLECSS',
			'DEF_BACKCOLOR','DEF_LINECOLOR', 'DEF_TITLETEXTCOLOR','DEF_CATTEXTCOLOR','DEF_BODYTEXTCOLOR',
			'DEF_ENDBKGCOLOR', 'DEF_CUSTOMCOLORS', 'DEF_TITLEBACKCOLOR','DEF_BKGALPHA'
		);
		$this->op = (object) array();
		foreach ($opnames as $name)
			$this->op->$name = &$this->$name;
	}
	
	function load_options() 
	{
		$context = $this->CTXID();
		$options = $this->op;
		$saved = get_option($context);
		if ($saved) foreach ( (array) $options as $key => $val ) 
		{
			if (!isset($saved->$key)) continue;
			$this->assign_to($options->$key, $saved->$key);
		}
		// Backward compatibility hack, to be removed in a future version
		//$this->migrateOptions($options, $context);
	}
		
	function handle_options() 
	{
		$actionURL = $_SERVER['REQUEST_URI'];
		$context = $this->CTXID();
		$options = $this->op;
		$updated = false;
		$status = '';
		if ( $_POST['action'] == 'update' ):
			check_admin_referer($context);
			if (isset($_POST['submit'])):
				foreach ($options as $key => $val):
					$bistate = is_bool($val);
					if ($bistate):
						$newval = isset($_POST[$key]);
					else:
						if ( !isset($_POST[$key]) ) continue;
						$newval = trim( $_POST[$key] );
					endif;
					if ( $newval == $val ) continue;
					$this->assign_to($options->$key, $newval);
					$updated = true; $status = 'updated';
				endforeach;
				if ($updated): update_option($context, $options); endif;
			elseif (isset($_POST['reset'])):
				delete_option($context);
				$updated = true; $status = 'reset';
			endif;
		endif;
		include 'easy-review-settings.php';
	}
	
	private function assign_to(&$var, $value) 
	{
		settype($value, gettype($var));
		$var = $value;
	}	
 
    private function translate_numerics(&$value, $key) 
    {
        if ($value == 'false') {
        	$value = false;
        } elseif ($value == 'true') {
            $value = true;
        }
    }        
            
	public function process_shortcode($atts) 
	{	
	    $haveIssue = FALSE;
	    $nearKey = "";
	    $nearValue = "";
	    
	    if ($atts)
	    {
    	    foreach ($atts as $key => $att)
    	    {
    	       $keyval = (int)$key;
    	       if ($keyval != 0 || strpos($key, "0") === 0)
    	       {
                    $haveIssue = TRUE;
                    $nearKey = $keyval;
                    $nearValue = $att;
                    break;
    	       }
    	    }
	    }
	    	
	    if ($haveIssue === TRUE)
	       return "<p><b>EASY REVIEW BUILDER SHORTCODE ERROR</b><lu><li>Check for misspelled parameters (case matters)</li><li>Check for new lines (all must reside on one long line)</li><li>Error near [" . $key . "], [" . $att . "]</li></lu><br/>For assistance, please visit <a>http://www.dyerware.com/main/products/easy-review-builder</a></p>";
	    
	             
        $chartConfig = shortcode_atts( array(
                'title' => $this->DEF_TITLE,
                'ratingmax' => $this->DEF_RATINGMAX,
                'overall' => ($this->DEF_OVERALL == true)?'true':'false',
                'icon' => $this->DEF_ICON,
                'summary' => $this->DEF_SUMMARY,
                'cat1title' => NULL,
                'cat2title' => NULL,
                'cat3title' => NULL,
                'cat4title' => NULL,
                'cat5title' => NULL,
                'cat6title' => NULL,
                'cat7title' => NULL,
                'cat8title' => NULL,
                'cat1detail' => 'Summarize why you chose this rating',
                'cat2detail' => 'Summarize why you chose this rating',
                'cat3detail' => 'Summarize why you chose this rating',
                'cat4detail' => 'Summarize why you chose this rating',
                'cat5detail' => 'Summarize why you chose this rating',
                'cat6detail' => 'Summarize why you chose this rating',
                'cat7detail' => 'Summarize why you chose this rating',
                'cat8detail' => 'Summarize why you chose this rating',
                'cat1rating' => 0,
                'cat2rating' => 0,
                'cat3rating' => 0,
                'cat4rating' => 0,
                'cat5rating' => 0,
                'cat6rating' => 0,
                'cat7rating' => 0,
                'cat8rating' => 0,
                'tablecss' => $this->DEF_TABLECSS,)
			    , $atts );

	    // Translate strings to numerics
	    array_walk($chartConfig, array($this, 'translate_numerics'));
	          
	    $this->reviewNum++;
		$reviewDiv = 'easyReviewDiv' . $this->reviewNum;
	
    	$ratingMax = (int)$chartConfig["ratingmax"];
    	if ($ratingMax > 15)
    	   $ratingMax = 15;

   	   $starFullImg = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) .'/icons/' .$chartConfig['icon'].'_full.png';
       $starHalfImg = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) .'/icons/' .$chartConfig['icon'].'_half.png';
       $starEmptyImg = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) .'/icons/' .$chartConfig['icon'].'_empty.png';
  	
 	
	  	if ($this->DEF_CUSTOMCOLORS == true)
	  	{
        if ($this->DEF_BKGALPHA >= 1.0)
        {
          $defTitleBkgColor = 'background-color:#' . $this->DEF_TITLEBACKCOLOR . ';background-image:none;';
          $defBkgColor = 'background-color:#' . $this->DEF_BACKCOLOR . ';background-image:none;';
          $defEndBkgColor = 'background-color:#' . $this->DEF_ENDBKGCOLOR . ';background-image:none;';
        }
        else
        {
          sscanf ($this->DEF_TITLEBACKCOLOR, "%02x%02x%02x", $r, $g, $b);
          $defTitleBkgColor = 'background-color:rgba(' . $r . ',' . $g . ',' . $b . ',' . $this->DEF_BKGALPHA . ');background-image:none;';
  
          sscanf ($this->DEF_BACKCOLOR, "%02x%02x%02x", $r, $g, $b);
          $defBkgColor = 'background-color:rgba(' . $r . ',' . $g . ',' . $b . ',' . $this->DEF_BKGALPHA . ');background-image:none;';
  
          sscanf ($this->DEF_ENDBKGCOLOR, "%02x%02x%02x", $r, $g, $b);
          $defEndBkgColor = 'background-color:rgba(' . $r . ',' . $g . ',' . $b . ',' . $this->DEF_BKGALPHA . ');background-image:none;';
        }

    

	  		$defLineColor = 'border-color:#' . $this->DEF_LINECOLOR;
	  		$defEndLineColor = 'border-top-color:#' . $this->DEF_LINECOLOR;
	  		
	  		$defTitleColor = 'color:#' . $this->DEF_TITLETEXTCOLOR;
	  		$defCatColor = 'color:#' . $this->DEF_CATTEXTCOLOR;
	  		$defBodyColor = 'color:#' . $this->DEF_BODYTEXTCOLOR;
	  	}
	  	else
	  	{
			$defLineColor = 'border-color:#DDD';	  	
		}
 

	   $output = "<div class='" . $chartConfig["tablecss"] . "' style='" . $defLineColor . "'> <table class='easyReviewTable'  border='0' style='text-align:center;' align='center' bgcolor='FFFFFF'>";
	
       // Optional title   post-footer postwrap
       if (strlen($chartConfig["title"]))
           $output .= "<tr><th class='easyReviewTitle' style='vertical-align:middle;font-size:120%;" 
           . $defTitleBkgColor . $defTitleColor . "' colspan='2'>" . $chartConfig["title"] . "</th></tr>";
           
        
       // For each valid entry
       $firstRow = TRUE;
       $average = 0;
       $numRows = 0;
       $numLiveRows = 0;
    		
	   for ($x = 0; $x < 8; $x++)
       {
      	   $keyTitle = "cat" . ($x+1) . "title";
      	   $keyDetail = "cat" . ($x+1) . "detail";
      	   $keyRating = "cat" . ($x+1) . "rating";
      	   $rating = (float)$chartConfig[$keyRating];
      	   $halfStar = FALSE;
      	   if ($rating < round($rating))
      	       $halfStar = TRUE;
      	   $ratingFloor = floor($rating);
      	   
      	   $rowStr = "";
      	   if (strlen($chartConfig[$keyTitle]))
      	   {
      	       if ($firstRow == FALSE)
      	         $rowStr .= "<tr><td colspan='2' class='easyReviewBlank' style='border-bottom:1px solid;" . $defLineColor . ";'></td></tr>";     
            
      	       $rowStr .= "<tr style='" . $defBkgColor ."''><th class='easyReviewRow' style='xwidth:100%;"
      	       		 . $defBkgColor . $defCatColor . ";'>".$chartConfig[$keyTitle]."</th>"	                      
      	               . "<td class='easyReviewRow' style='white-space:nowrap;float:right;" . $defBkgColor . "'>";
          	           
          	          
          	   if ($rating >= 0)
          	   { 
          	   	   $numLiveRows++;
	      	       for ($y = 0; $y < $ratingMax; $y++)
	      	       {
	      	          if ($y + 1 <= $ratingFloor)
	      	               $rowStr .= "<img alt='www.dyerware.com' class='easyReviewImage' src='" . $starFullImg . "'/>";
	                    else if ($y + 1 == $ratingFloor + 1 && $halfStar)
	                         $rowStr .= "<img alt='www.dyerware.com' class='easyReviewImage'  src='" . $starHalfImg . "'/>";
	                    else          
	                         $rowStr .= "<img alt='www.dyerware.com' class='easyReviewImage' src='" . $starEmptyImg . "'/>";
	      	       }
	      	       
	      	       $average +=  $rating;
          	   }
          	   
               $rowStr .= "</td></tr>";
       	       $rowStr .= "<tr><td colspan='2' class='easyReviewRow' style='"
       	       		. $defBkgColor.$defBodyColor."'>".$chartConfig[$keyDetail]."</td></tr>";     	       
      	       
      	                 	           
      	       if ($firstRow)
      	           $firstRow = FALSE;
      	           
      	       $numRows++;
       	   }
      	   
      	   $output .= $rowStr;
         }
	
	  $output .= "</table>";
	  	  
      // Add conclusion
      if ($firstRow == false && $chartConfig['overall'] == true)
       {
           $halfStar = false;
           
           if ($numLiveRows)
           {
           	$average = $average / $numLiveRows;
           }
           else
           {
           	$average = 0;
           }
    
           if ($average < round($average))
               $halfStar = TRUE;
           $average = floor($average);
      
           $output .= "<div class='easyReviewConclude' style='" . $defEndLineColor . "'><table class='easyReviewTable'  border='0' style='text-align:center;". $defEndBkgColor . "' frame='box' align='center' bgcolor='FFFFFF'>";
           $output .= "<tr><th class='easyReviewRow' style='xwidth:100%;" 
           		  . $defEndBkgColor . $defCatColor . "'>Overall</th>"
                   . "<td class='easyReviewRow' style='white-space:nowrap;float:right;" . $defEndBkgColor . "'>";	           
              
       	 $endStr = "";   
           for ($y = 0; $y < $ratingMax; $y++)
           {
               if ($y + 1 <= $average)
                $endStr .= "<img alt='www.dyerware.com' class='easyReviewImage' src='" . $starFullImg . "'/>";
               else if ($y + 1 == $average + 1 && $halfStar)
                $endStr .= "<img alt='www.dyerware.com' class='easyReviewImage' src='" . $starHalfImg . "'/>";
               else            
                $endStr .= "<img alt='www.dyerware.com' class='easyReviewImage' src='" . $starEmptyImg . "'/>";
           }
           
           $output .= $endStr . "</td></tr>";
           
           $output .= "<tr><td colspan='2' class='easyReviewEnd' style='"
           . $defEndBkgColor . $defBodyColor . "'>" . $chartConfig['summary'] . "</td></tr></table></div>";	
       }
       
    $output .= "</div>";
    return $output;  
   }
   
   function RGBtoHSB ($rgb)
   {
        sscanf ($rgb, "%02x%02x%02x", $r, $g, $b);
     
        $h = 0;
        $s = 0;
        
        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $delta = $max - $min;
        $b = $max;
        if ($max != 0) {
        	
        }
        $s = $max != 0 ? 255 * $delta / $max : 0;
        if ($s != 0) {
        	if ($r == $max) {
        		$h = ($g - $b) / $delta;
        	} else if ($g == $max) {
        		$h = 2 + ($b - $r) / $delta;
        	} else {
        		$h = 4 + ($r - $g) / $delta;
        	}
        } else {
        	$h = -1;
        }
        $h *= 60;
        if ($h < 0) {
        	$h += 360;
        }
        $s *= 100/255;
        $b *= 100/255;
 
        return array($h, $s, $b);
    }

}  

// Instantiate our class
$wpEasyReview = new wpEasyReview();

/**
 * Add filters and actions
 */

add_action('wp_head', array($wpEasyReview, 'addCSS'));
add_shortcode('easyreview',array($wpEasyReview, 'process_shortcode'));
?>
