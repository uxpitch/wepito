<div class="postbox">
    <h3>Plugin Info</h3>

    <div class="inside">
        <p>Name : <a href="http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress"
                     target="_blank">CBX Multi Criteria Rating & Review
                System <?php echo 'v' . CB_RATINGSYSTEM_PLUGIN_VERSION; ?></a></p>

        <p>Author : Codeboxr Team</p>
        <p>Plugin URL :
            <a href="http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress" target="_blank">codeboxr.com</a>
        </p>
        <p>Email : <a href="mailto:info@codeboxr.com" target="_blank">info@codeboxr.com</a></p>
        <p>Contact : <a href="http://codeboxr.com/contact-us" target="_blank">Contact Us</a></p>
    </div>
</div>
<div class="postbox">
    <h3>Help & Supports</h3>

    <div class="inside">
        <p>Support: <a href="http://codeboxr.com/contact-us" target="_blank">Contact Us</a></p>
        <p><i class="icon-envelope"></i> <a href="mailto:info@codeboxr.com">info@codeboxr.com</a></p>
    </div>
</div>
<div class="postbox">
    <h3><?php _e('Codeboxr Other Plugins', 'cbratingsystem'); ?></h3>

    <div class="inside">
        <?php
        include_once(ABSPATH . WPINC . '/feed.php');
        if (function_exists('fetch_feed')) {
            //$feed = fetch_feed('http://codeboxr.com/feed?post_type=product');
            $feed = fetch_feed('http://codeboxr.com/products/feed/?product_cat=wpplugins');

            if (!is_wp_error($feed)) : $feed->init();
                $feed->set_output_encoding('UTF-8'); // this is the encoding parameter, and can be left unchanged in almost every case
                $feed->handle_content_type(); // this double-checks the encoding type
                $feed->set_cache_duration(21600); // 21,600 seconds is six hours
                $limit = $feed->get_item_quantity(6); // fetches the 18 most recent RSS feed stories
                $items = $feed->get_items(0, $limit); // this sets the limit and array for parsing the feed

                $blocks = array_slice($items, 0, 6); // Items zero through six will be displayed here

                echo '<ul>';

                foreach ($blocks as $block) {
                    $url = $block->get_permalink();
                    echo '<li style="clear:both;  margin-bottom:5px;"><a target="_blank" href="' . $url . '">';
                    echo '<strong>' . $block->get_title() . '</strong></a></li>';
                }
                //end foreach

                echo '</ul>';


            endif;
        }
        ?>
    </div>
    <div class="postbox">
        <h3>Codeboxr on facebook</h3>

        <div class="inside">
            <iframe scrolling="no" frameborder="0" allowtransparency="true"
                    style="border:none; overflow:hidden; width:260px; height:258px;"
                    src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fcodeboxr&amp;width=260&amp;height=258&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=558248797526834"></iframe>
        </div>
    </div>