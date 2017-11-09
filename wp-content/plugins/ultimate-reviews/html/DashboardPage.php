<!-- Upgrade to pro link box -->
<!-- TOP BOX-->

<?php global $wpdb;

//start review box
	if (isset($_POST['hide_urp_review_box_hidden'])) {update_option('EWD_URP_Hide_Dash_Review_Ask', $_POST['hide_urp_review_box_hidden']);}
	$hideReview = get_option('EWD_URP_Hide_Dash_Review_Ask');
	$Ask_Review_Date = get_option('EWD_URP_Ask_Review_Date');
	if ($Ask_Review_Date == "") {$Ask_Review_Date = time() - 3600*24;}

	$Install_Time = get_option("EWD_URP_Install_Time");
//end review box
?>

<div id="fade" class="ewd-urp-dark_overlay"></div>
<div id="ewd-dashboard-top" class="metabox-holder">

<!-- Upgrade to pro link box -->
<?php if ($URP_Full_Version != "Yes" or get_option("EWD_URP_Trial_Happening") == "Yes") { ?>
<div id="ewd-urp-dashboard-top-upgrade">
	<div id="ewd-urp-dashboard-top-upgrade-left">
		<div id="ewd-dashboard-pro" class="postbox upcp-pro upcp-postbox-collapsible" >
			<div class="handlediv" title="Click to toggle"></div><h3 class='hndle ewd-dashboard-h3'><span><?php _e("UPGRADE TO FULL VERSION", 'ultimate-reviews') ?></span></h3>
			<div class="inside">
				<h3><?php _e("What you get by upgrading:", 'ultimate-reviews') ?></h3>
				<div class="clear"></div>
				<ul>
					<li><span>"ultimate-review-search" shortcode, to let users search reviews by keyword, and "reviews-summary" shortcode.</span></li>
					<li><span>Infinite review scroll, so more reviews are loaded as visitors reach the bottom of a page of reviews.</span></li>
					<li><span>Control who reviews by requiring email confirmation or login.</span></li>
					<li><span>WooCommerce integration, to let you create more detailed reviews for your products.</span></li>
					<li><span>Two extra review formats, admin notifications when a review is received, and dozens of styling and labeling options!</span></li>
					<li><span>Access to e-mail support.</span></li>
				</ul>
				<div class="clear"></div>
				<a class="purchaseButton" href="http://www.etoilewebdesign.com/plugins/ultimate-reviews/" target="_blank">
					Click here to purchase the full version
				</a>
				<div class="clear"></div>
				<div class="full-version-form-div">
					<form action="edit.php?post_type=urp_review" method="post">
						<div class="form-field form-required">
							<!-- <label for="Catalogue_Name"><?php _e("Product Key", 'ultimate-reviews') ?></label> -->
							<input name="Key" type="text" value="" size="40" placeholder="<?php _e('Enter product key or free trial code here', 'ultimate-reviews'); ?>" />
						</div>
						<input type="submit" name="EWD_URP_Upgrade_To_Full" value="<?php _e('UPGRADE', 'ultimate-reviews'); ?>">
					</form>
				</div>
			</div>
		</div>
	</div> <!-- ewd-urp-dashboard-top-upgrade-left -->
	<?php if (get_option("EWD_URP_Trial_Happening") != "No") { ?>
		<div id="ewd-urp-dashboard-top-upgrade-right">
			<div id="ewd-dashboard-pro" class="postbox upcp-pro upcp-postbox-collapsible" >
				<div class="handlediv" title="Click to expand"></div>
				<h3 class="hndle ewd-dashboard-h3">&nbsp;</h3>
				<div class="inside">
					<div class="topPart">
						<?php
						if(!get_option("EWD_URP_Trial_Happening")){
							_e("Want to try out the premium features first?", 'ultimate-reviews');
						}
						else{
							_e("Your free trial is currently active", 'ultimate-reviews');
						}
						?>
					</div>
					<div class="clear"></div>
					<div class="bottomPart">
						<?php if(!get_option("EWD_URP_Trial_Happening")){ ?>
							Use code<br /><span class="freeTrialText">&nbsp;EWD Trial&nbsp;</span><br />for a free 7-day trial!
						<?php }
						else{ ?>
							Your trial expires at <span class="freeTrialText"><?php echo date("Y-m-d H:i:s", get_option("EWD_URP_Trial_Expiry_Time")); ?> GMT</span>. <a href="http://www.etoilewebdesign.com/plugins/ultimate-reviews/" class="freeTrialPurchaseLink" target="_blank">Upgrade here</a> before then to retain any premium changes made!
						<?php } ?>
					</div> <!-- bottomPart -->
				</div> <!-- inside -->
			</div> <!-- postbox -->
		</div> <!-- ewd-urp-dashboard-top-upgrade-right -->
	<?php } ?>
</div> <!-- ewd-urp-dashboard-top-upgrade -->
<?php } ?>


<?php if (get_option("EWD_URP_Update_Flag") == "Yes" or get_option("EWD_URP_Install_Flag") == "Yes") {?>
	<div id="side-sortables" class="metabox-holder ">
		<div id="EWD_URP_pro" class="postbox " >
			<div class="handlediv" title="Click to toggle"></div>
			<h3 class='hndle'><span><?php _e("Thank You!", 'ultimate-reviews') ?></span></h3>
		 	<div class="inside">
				<?php  if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Reviews plugin.", 'ultimate-reviews'); ?><br> <a href='https://www.youtube.com/channel/UCZPuaoetCJB1vZOmpnMxJNw'><?php _e("Subscribe to our YouTube channel ", 'ultimate-reviews'); ?></a> <?php _e("for tutorial videos on this and our other plugins!", 'ultimate-reviews');?> </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 1.3.24!", 'ultimate-reviews'); ?><br> <a href='https://wordpress.org/support/view/plugin-reviews/ultimate-reviews?filter=5'><?php _e("Please rate our plugin", 'ultimate-reviews'); ?></a> <?php _e("if you find Ultimate Reviews useful!", 'ultimate-reviews');?> </li></ul><?php } ?>

				<?php /* if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Product Catalogue Plugin.", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?> </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 2.2.9!", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?> </li></ul><?php } */ ?>

				<?php /* if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Product Catalogue Plugin.", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?>  </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 3.0.16!", 'ultimate-reviews'); ?><br> <a href='http://wordpress.org/support/view/plugin-reviews/ultimate-product-catalogue'><?php _e("Please rate our plugin", 'ultimate-reviews'); ?></a> <?php _e("if you find the Ultimate Product Catalogue Plugin useful!", 'ultimate-reviews');?> </li></ul><?php } */ ?>

				<?php /* if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Product Catalogue Plugin.", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?>  </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 3.4.8!", 'ultimate-reviews'); ?><br> <a href='http://wordpress.org/plugins/order-tracking/'><?php _e("Try out order tracking plugin ", 'ultimate-reviews'); ?></a> <?php _e("if you ship orders and find the Ultimate Product Catalogue Plugin useful!", 'ultimate-reviews');?> </li></ul><?php } */ ?>

				<?php /* if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Product Catalogue Plugin.", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?>  </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 2.3.9!", 'ultimate-reviews'); ?><br> <a href='http://wordpress.org/support/topic/error-hunt'><?php _e("Please let us know about any small display/functionality errors. ", 'ultimate-reviews'); ?></a> <?php _e("We've noticed a couple, and would like to eliminate as many as possible.", 'ultimate-reviews');?> </li></ul><?php } */ ?>

				<?php /* if (get_option("EWD_URP_Install_Flag") == "Yes") { ?><ul><li><?php _e("Thanks for installing the Ultimate Product Catalogue Plugin.", 'ultimate-reviews'); ?><br> <a href='https://www.youtube.com/channel/UCZPuaoetCJB1vZOmpnMxJNw'><?php _e("Check out our YouTube channel ", 'ultimate-reviews'); ?></a> <?php _e("for tutorial videos on this and our other plugins!", 'ultimate-reviews');?> </li></ul>
				<?php } elseif ($Full_Version == "Yes") { ?><ul><li><?php _e("Thanks for upgrading to version 3.5.0!", 'ultimate-reviews'); ?><br> <a href='http://www.facebook.com/EtoileWebDesign'><?php _e("Follow us on Facebook", 'ultimate-reviews'); ?></a> <?php _e("to suggest new features or hear about upcoming ones!", 'ultimate-reviews');?> </li></ul>
				<?php } else { ?><ul><li><?php _e("Thanks for upgrading to version 3.4!", 'ultimate-reviews'); ?><br> <?php _e("Love the plugin but don't need the premium version? Help us speed up product support and development by donating. Thanks for using the plugin!", 'ultimate-reviews');?>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="AQLMJFJ62GEFJ">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
						</li></ul>
				<?php } */ ?>

			</div>
		</div>
	</div>
	<?php
	EWD_URP_Get_EWD_Blog();
	EWD_URP_Get_Changelog();
	update_option('EWD_URP_Update_Flag', "No");
	update_option('EWD_URP_Install_Flag', "No");
}
?>


	<div id="ewd-dashboard-box-orders" class="ewd-urp-dashboard-box" >
	  	<div class="ewd-dashboard-box-icon"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/urp-buttonsicons-full-06.png"/>
	  	</div>
		<div class="ewd-dashboard-box-value-and-field-container">
		  <div class="ewd-dashboard-box-value"><span class="displaying-num"><?php echo $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type='urp_review' AND post_status='publish'"); ?></span>
		  </div>
		  <div class="ewd-dashboard-box-field">reviews
		  </div>
		</div>
	</div>
	<div id="ewd-dashboard-box-links" class="ewd-urp-dashboard-box" >
	  	<div class="ewd-dashboard-box-icon"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/urp-buttonsicons-05.png"/>
	  	</div>
		<div class="ewd-dashboard-box-value-and-field-container">
		  <div class="ewd-dashboard-box-value ewd-font-20"><?php echo $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='urp_review' ORDER BY post_date DESC"); ?>
		  </div>
		  <div class="ewd-dashboard-box-field">Last Review Posted
		  </div>
		</div>
	</div>
	<div id="ewd-dashboard-box-views" class="ewd-urp-dashboard-box" >
	  	<div class="ewd-dashboard-box-icon"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/urp-buttonsicons-03.png"/>
	  	</div>
		<div class="ewd-dashboard-box-value-and-field-container">
		  <div class="ewd-dashboard-box-value"><?php echo $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key='urp_view_count'"); ?>
		  </div>
		  <div class="ewd-dashboard-box-field">Views
		  </div>
		</div>
	</div>

	<div id="ewd-dashboard-box-support" class="ewd-urp-dashboard-box" >
		<div class="ewd-dashboard-box-icon"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/urp-buttonsicons-04.png"/>
	  	</div>
		<div class="ewd-dashboard-box-value-and-field-container">
		  	<div class="ewd-dashboard-box-support-value">
			<form id="form1" runat="server">
			<a href="javascript:void(0)" onclick="document.getElementById('light').style.display='block';document.getElementById('fade').style.display='block'">Click here for support</a>
		  		</div>
			</div>
		</div>
	<div id="light" class="ewd-urp-bright_content">
            <asp:Label ID="lbltext" runat="server" Text="Hey there!"></asp:Label>
            <a href="javascript:void(0)" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">Close</a>
		</br>
		<h2>Need help?</h2>
		<p>You may find the information you need with our support tools.</p>
		<a href="https://www.youtube.com/playlist?list=PLEndQUuhlvSpw3HQakJHj4G0F0Gyc-CtU"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/support_icons_urp-01.png" /></a>
		<a href="https://www.youtube.com/playlist?list=PLEndQUuhlvSpw3HQakJHj4G0F0Gyc-CtU"><h4>Youtube Tutorials</h4></a>
		<p>Our tutorials show you the basics of setting up your plugin, to the more specific utilization of our features.</p>
		<div class="ewd-urp-clear"></div>
		<a href="https://wordpress.org/support/plugin/ultimate-reviews"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/support_icons_urp-03.png"/></a>
		<a href="https://wordpress.org/support/plugin/ultimate-reviews"><h4>WordPress Forum</h4></a>
		<p>We make sure to answer your questions within a 24hrs frame during our business days. Search within our threads to find your answers. If it has not been addressed, please create a new thread!</p>
		<div class="ewd-urp-clear"></div>
		<a href="http://www.etoilewebdesign.com/plugins/ultimate-reviews/documentation-ultimate-reviews/"><img src="<?php echo plugins_url(); ?>/ultimate-reviews/images/support_icons_urp-02.png"/></a>
		<a href="http://www.etoilewebdesign.com/plugins/ultimate-reviews/documentation-ultimate-reviews/"><h4>Documentation</h4></a>
		<p>Most information concerning the installation, the shortcodes and the features are found within our documentation page.</p>
        </div>
	</form>

<!--END TOP BOX-->
</div>



<!--Middle box-->
<div class="ewd-dashboard-middle">
<div id="col-full">
<h3 class="ewd-urp-dashboard-h3">Reviews Summary</h3>
<div>
	<table class='ewd-urp-overview-table wp-list-table widefat fixed striped posts'>
		<thead>
			<tr>
				<th><?php _e("Title", 'EWD_ABCO'); ?></th>
				<th><?php _e("Views", 'EWD_ABCO'); ?></th>
				<th><?php _e("Review Rating", 'EWD_ABCO'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$args = array(
					'post_type' => 'urp_review',
					'orderby' => 'meta_value_num',
					'meta_key' => 'urp_view_count'
				);

				$Dashboard_Reviews_Query = new WP_Query($args);
				$Dashboard_Reviews = $Dashboard_Reviews_Query->get_posts();

				if (sizeOf($Dashboard_Reviews) == 0) {echo "<tr><td colspan='3'>" . __("No reviews to display yet. Create a review and then view it for it to be displayed here.", 'ultimate-reviews') . "</td></tr>";}
				else {
					foreach ($Dashboard_Reviews as $Dashboard_Review) { ?>
						<tr>
							<td><a href='post.php?post=<?php echo $Dashboard_Review->ID;?>&action=edit'><?php echo $Dashboard_Review->post_title; ?></a></td>
							<td><?php echo get_post_meta($Dashboard_Review->ID, 'urp_view_count', true); ?></td>
							<td><?php echo get_post_meta($Dashboard_Review->ID, 'EWD_URP_Overall_Score', true); ?></td>
						</tr>
					<?php }
				}
			?>
		</tbody>
	</table>
</div>
<br class="clear" />
</div>
</div>

<?php if($hideReview != 'Yes'){ ?>
<div id='ewd-urp-dashboard-leave-review' class='ewd-urp-leave-review postbox upcp-postbox-collapsible'>
	<h3 class='hndle ewd-urp-dashboard-h3'>Leave a Review <span></span></h3>
	<div class='ewd-dashboard-content'>
		<div class="ewd-dashboard-leave-review-text">
			If you enjoy this plugin and have a minute, please consider leaving a 5-star review. Thank you!
		</div>
		<a href="https://wordpress.org/support/plugin/ultimate-reviews/reviews/" class="ewd-dashboard-leave-review-link" target="_blank">Leave a Review!</a>
		<div class="clear"></div>
	</div>
	<form action="admin.php?page=EWD-URP-Options" method="post">
		<input type="hidden" name="hide_urp_review_box_hidden" value="Yes">
		<input type="submit" name="hide_urp_review_box_submit" class="ewd-dashboard-leave-review-dismiss" value="I've already left a review">
	</form>
</div>
<div class="clear"></div>
<?php } ?>

<?php if ($Ask_Review_Date < time() and $Install_Time < time() - 3600*24*4) { ?>
<div id='ewd-urp-review-ask-overlay'></div>
<div class='ewd-urp-review-ask-popup'>
	<div class='ewd-urp-review-ask-title'><?php _e('Thank You!', 'ultimate-reviews'); ?></div>
	<div class='ewd-urp-review-ask-content'>
		<p><?php _e('We wanted to thank the users of our plugins for all of their great reviews recently.', 'ultimate-reviews'); ?></p>
		<p><?php _e('Your positive feedback and constructive suggestions on how to improve our plugins make coming in to work every day worth it for us.', 'ultimate-reviews'); ?></p>
		<p><strong><?php _e("Haven't had a chance to leave a review yet? You can do so at:", 'ultimate-reviews'); ?></strong></p>
		<a href='https://wordpress.org/support/plugin/ultimate-reviews/reviews/' target="_blank" class='ewd-urp-review-ask-content-link'>Leave a Review!</a>
	</div>
	<div class='ewd-urp-review-ask-footer-links'>
		<div class='ewd-urp-hide-review-ask' id="ewd-urp-hide-review-ask-week" data-askreviewdelay='7'><?php _e('Ask me in a week', 'ultimate-reviews'); ?></div>
		<div class='ewd-urp-hide-review-ask' id="ewd-urp-hide-review-ask-never" data-askreviewdelay='2000'><?php _e('Never ask me again', 'ultimate-reviews'); ?></div>
	</div>
</div>
<?php } ?>

<!-- END MIDDLE BOX -->

<!-- FOOTER BOX -->
<!-- A list of the products in the catalogue -->
<div class="ewd-dashboard-footer">
<div id='ewd-dashboard-updates' class='ewd-urp-updates postbox upcp-postbox-collapsible'>
<h3 class='hndle ewd-urp-dashboard-h3' id='ewd-recent-changes'><?php _e("Recent Changes", 'UPCP'); ?> <i class="fa fa-cog" aria-hidden="true"></i></h3>
<div class='ewd-dashboard-content' ><?php echo get_option('EWD_URP_Changelog_Content'); ?></div>
</div>

<div id='ewd-dashboard-blog' class='ewd-urp-blog postbox upcp-postbox-collapsible'>
<h3 class='hndle ewd-urp-dashboard-h3'>News <i class="fa fa-rss" aria-hidden="true"></i></h3>
<div class='ewd-dashboard-content'><?php echo get_option('EWD_URP_Blog_Content'); ?></div>
</div>

<div id="ewd-dashboard-plugins" class='ewd-urp-plugins postbox upcp-postbox-collapsible' >
	<h3 class='hndle ewd-urp-dashboard-h3'><span><?php _e("Goes great with:", 'UPCP') ?></span><i class="fa fa-plug" aria-hidden="true"></i></h3>
	<div class="inside">
		<div class="ewd-dashboard-plugin-icons">
			<div style="width:50%">
				<a target='_blank' href='https://wordpress.org/plugins/ultimate-product-catalogue/'><img style="width:100%" src='<?php echo plugins_url(); ?>/ultimate-reviews/images/UPCP_Icons-07-300x300.png'/></a>
			</div>
			<div>
				<h3>Product Catalog</h3> <p>Enables you to display your business's products in a clean and efficient manner.</p>
			</div>

		</div>
		<div class="ewd-dashboard-plugin-icons">
			<div style="width:50%">
				<a target='_blank' href='https://wordpress.org/plugins/ultimate-faqs/'><img style="width:100%" src='<?php echo plugins_url(); ?>/ultimate-reviews/images/UFAQ_Related_Sales_Icon.png'/></a>
			</div>
			<div>
				<h3>Ultimate FAQS</h3><p>An easy-to-use FAQ plugin that lets you create, order and publicize FAQs, insert 3 styles of FAQs on a page.</p>
			</div>

		</div>
	</div>
</div>
</div>
</div>


<?php
function EWD_URP_Get_EWD_Blog() {
	$Blog_URL = EWD_URP_CD_PLUGIN_PATH . 'Blog.html';
	$Blog = file_get_contents($Blog_URL);

	update_option('EWD_URP_Blog_Content', $Blog);
}

function EWD_URP_Get_Changelog() {
	$Readme_URL = EWD_URP_CD_PLUGIN_PATH . 'readme.txt';
	$Readme = file_get_contents($Readme_URL);

	$Changes_Start = strpos($Readme, "== Changelog ==") + 15;
	$Changes_Section = substr($Readme, $Changes_Start);

	$Changes_Text = substr($Changes_Section, 0, strposX($Changes_Section, "=", 5));

	$Changes_Text = str_replace("= ", "<h3>", $Changes_Text);
	$Changes_Text = str_replace(" =", "</h3>", $Changes_Text);
	$Changes_Text = str_replace("- ", "<br />- ", $Changes_Text);

	update_option('EWD_URP_Changelog_Content', $Changes_Text);
}

function strposX($haystack, $needle, $number){
    if($number == '1'){
        return strpos($haystack, $needle);
    }elseif($number > '1'){
        return strpos($haystack, $needle, strposX($haystack, $needle, $number - 1) + strlen($needle));
    }else{
        return error_log('Error: Value for parameter $number is out of range');
    }
}

?>
