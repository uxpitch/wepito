=== CBX Multi Criteria Rating & Review ===
Contributors: manchumahara,codeboxr
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NWVPKSXP6TCDS
Tags: widget,shortcodes,rating,comment,reviews, multi criteria,ratingsystem, woocommerce
Requires at least: 3.0
Tested up to: 4.7.4
Stable tag: 3.9.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multi Criteria Rating & Review System for wordpress

== Description ==

 CBX Rating System for wordpress is a versatile and complete rating solution for your wordpress site .
 It helps you to get rating of your articles with additional customs question and comment option.

= Features: =

*   Unlimited rating forms. You can add,edit ,delete rating forms ,view all forms listing with it's review count ,select one form as default , change default form any time
*   While adding rating forms you will find huge options like form position ,shortcode enable /diaable , show/hide , users who can rate , posts where to show ,users who can view rating reviews and many more
*   Ip and cookie checking system to restrict repeated rating
*   You have 3 custom criteria with 5 custom users .Stars are selectable (show/hide), stars names and criteria names are editable
*   Comment box ,with required options and charecter limit
*   3 custom question of type textbox/radio/checkbox . Can show,hide questions , make it required and edit checkbox or radio numbers
*   User based and form based summary table with delete bulk actions
*   Form intigretion with short code , with meta box under post ,with direct function call in any where or in post loop , find result summary with direct function call
*   Top rated posts widget
*   Language file support and easily customizable


See more details and usages guide here [CBX Multi-Criteria Flexible Rating System for WordPress](http://codeboxr.com/product/multi-criteria-flexible-rating-system-for-wordpress)

For details documentation check here  [CBX Rating & Review Documentation](http://codeboxr.com/cbx-rating-review-documentation/)


= Pro Version: =

We have pro addon(s)  which enables more premium features beside the free core version(Core version will be always free).

**Pro Version Features**

*  Theme & Custom Style for rating presentation
*  Unlimited criteria
*  Unlimited reasons/stars in each criteria
*  Guest email verify
*  Custom WooCommerce Top Rated Product Widgets
*  Custom Easy Digital Downloads Top Rated Downloads Widgets
*  Admin alert for new rating
*  Buddypress posting integration
*  myCred integration
*  Custom Rating Icons
*  Rating Edit optoin for specific user group



= Plugin Backend =
[youtube http://www.youtube.com/watch?v=Xa6M2uJnKVw]

== Installation ==

Please note: if you are updating to 3.3 from any other version you need to reset the rating tables. Go to Rating System from
left menu, then tools -> click reset all. It will give a fresh rating system that works. We needed to change the database tables for this plugin and changed a log
and couldn't keep the previous data to move for any better future of this plugin.

How to install the plugin and get it working.


1. Upload `cbratingsystem` folder  to the `/wp-content/plugins/` directory or as you define the wp-content directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Rating System-> Add rating Form  to create a  rating form
4. Explore the setting and create form , set the position ,edit criteria names ,star names and questions
5. View backend pages to view all ratng logs , forms listing
6. Read faqs and detail from our site to get the shortcodes and function

== Frequently Asked Questions ==

= Is it possible to restrict any user group from rating ? =

yes, there is setting in every form to set which user groups can rate .

= If i call direct function in post loop then how to hide default form   ? =

Select default form position setting s  as none ( positions are left /right /none)

= Can I show a different(not default ) form  in a post ? =

yes ,set the form with meta box under post .



== Screenshots ==

1. Plugin listing in plugin manager
2. Rating System menu after activate the plugin
3. Rating form listing with 'add new form button'
4. Rating Form edit view-1
5. Rating Form edit view-2
6. Rating Form edit view-3(permissions)
7. Rating Form edit view-3(Criteria)
8. Rating Form edit view-4(Enable disable comments)
9. Post meta box for enable disable rating for any specific posts
10. Frontend Rating Form-1
11. Frontend Rating Result
12. Frontend Rating Reviews/Comments
13. Frontend Rating Form-2
14. Backend Rating Logs
15. Backend Average Rating
16. Theme Manager for Rating System
17. Rating System Tools to control tables and options values



== Changelog ==
= 3.9.3 =
* [Bug fix] Half star now works for any readonly version of rating

= 3.9.2 =
* [Bug fix] IP Field now compatible with IPv6

= 3.9.1 =
* [Bug fix] Critical JS Bug fix from previous release !


= 3.9.0 =
* [Bug fix] Google rich snippet fix
* Other code improvement/revamp


= 3.8.0 =
* Latest Review Shortcode
* Latest Review Widget
* Latest Review Custom Function
* Meta rich Snippet support

= 3.7.9 =
* [Fix] Star count fix if no vote and user doesn't have permission to vote
= 3.7.8 =
* [Fix] Language string missing fix
= 3.7.7 =
* [Improvement] Add translation for two hard coded string
* [New] Translation added as pot file.

= 3.7.6 =
* [Improvement] Moderated logs  now have better color indicator
* [New] New review status count by status type before user log table
* [New] Click to copy shortcode
* [New] Visual shortcode insert from the editor
* [Fixed] language folder renamed to 'languages' from 'language' to follow standard



= 3.7.5 =
* [Improvement] Added Screen option to set custom pagination number in backend user rating logs and avg rating logs
* [Fixed] backend user rating logs and avg rating logs sorting in proper way

= 3.7.4 =
* [Fixed] User log listing and avg log listing in admin dashboard now has proper pagination and data rendering.

= 3.7.3 =
* [Fixed] New review first time appended  or edited review was not showing rating icons properly after 3.7.2 updates

= 3.7.2 =
* [Fixed] New review first time appended perfectly(bug fix in js)
* [Fixed] Edit review at first delete if review is listed and then add(refresh)


= 3.7.1 =
* [Fixed] Duplicate content issue in archive mode
* [New] Rating Edit feature(coupled coded in core and addon)
* [Improvement] Core refactor for rating form show
* [Improvement] CSS tweak for admin and frontend
* [Fixed] Delete single log used to delete all avg, which is fixed now, sql bug

= 3.7.0 =
* [Fixed] Time ago method revamp for better translation support
* Other minor improvement

= 3.6.0 =
* [Bug Fix] Style was broken if review disabled from any form setting. Thanks @	Alex Stadler for noticed the reason why style brokes sometimes

= 3.5.2 =
* Solve guest user verify not working
* Removed feature which user groups rating will be moderated as it was never implemented but was added in form setting
* Moved buddypress posting code from core to pro addon which is a pro feature (code clean up)
* default sorting for rating log now working, it's desc by created date
* Admin alert implemented in pro addon

= 3.5.1 =
* Dates now display as per site timezone setting
* Added missing translations

= 3.5.0 =
* Guest email js verifiy now supports unicode and more better regex
* Guest user now can not vote usin any email that is used for any registered user
* JS Improved: Rating readonly version had width fixed to 100px and now it's removed for better style
* If you are using pro addon then download updates
* Top Rated Woo Products , Top Rated Edd Downloads  -  Two new widget is added to pro addon

= 3.4.7 =
* Extra theme css is now moved to addon plugin as this feature actually works from addon, more decoupling :)
* Switch toolbar is now more responsive and rating criteria title doesn't override in small screen

= 3.4.6 =
* Non logged in no vote mode avg rating shows criteria that is not chosen -- now fixed

= 3.4.5 =
* Minor bug fixes
* Fresh new documentation
* Meta key for avg rating for any form id for better custom query to sort by meta key
= 3.4.4
* Guest user email verify issue fixed
* Rating icon mouse hover hint added for already rated
= 3.4.2
* Backend js added as per the menu or screen
* Fixed question edit issues
= 3.4.0
* php5 style widget constructor as per wordpress 4.3 requirement
* Fix "Top Rated Posts" widget, form was not saving properly.
= 3.3.7 =
* Google Rating Schema or Rich Snippet Added
* Bug fix for avg rating when form id is not default(we are sorry, it should not be)
= 3.3.5 =
* While submitting review if comment char limit crossed the allowed size then it was showing comment cut wrong
* No more read more link in ajax comment preview
* Added stripslashes for user rating logs in admin
= 3.3.4 =
* \' type character is taken care in comment
= 3.3.3 =
* Fixed php warning for woocommerce tab replace
= 3.3.2 =
* Review shown login improved for who is allowed and if shown own review
* Ajax security updated
* Bug fix for question display in backend and frontend , store answer based on it on ajax and normal display
= 3.3.1 =
* bug fix for  extra field not array
= 3.3.0 =
* 50% revamp of code, please go to tools of this plugin and reset
= 3.2.26 =
* Bug fix
= 3.2.24 =
* Maintenance Release

