/**
 * Review Rate Display
 *
 * @param $
 * @param ratingFormID
 * @param postID
 * @param review_id
 * @param reviewRatingForm
 */
function cbxRatingDisplay($, ratingFormID, postID, review_id, reviewRatingForm) {

    var option      = $.parseJSON(reviewRatingForm.options);


    $('.review_rating_review-' + review_id + ' .criteria-wrapper .criteria-star-wrapper').each(function () {
        var $this       = $(this);
        var criteria_id = $this.attr('data-criteria-id');

        var value       = option['review_' + review_id + '_criteria_' + criteria_id + '_value'];
        var count       = option['review_' + review_id + '_criteria_' + criteria_id + '_count'];
        var hints       = option['review_' + review_id + '_criteria_' + criteria_id + '_hints'];


        $this.raty({
            path: reviewRatingForm.img_path,
            readOnly: true,
            half: true,
            noRatedMsg: cbratingsystem.noRatedMsg,
            number: count,
            score: Math.round(value),
            hints: hints,
            width: false
        });
    });
}//end funciton cbxRatingDisplay

/**
 * Review Rating Display
 *
 * @param ajax boolean
 * @param $ jquery cursor
 */
function cbxreviewRatingDisplay(ajax, $) {

    $('.reviews_container .review_wrapper').each(function () {

        var ratingFormID = $(this).attr('data-form-id');
        var postID       = $(this).attr('data-post-id');
        var reviewID     = $(this).attr('data-review-id');

        if (ajax === false) {
            varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID;
        } else {
            varReviewRatingFormName = 'reviewContent_post_' + postID + '_form_' + ratingFormID + '_ajax';
        }

        if (window[varReviewRatingFormName]['review_' + reviewID] !== undefined) {
            cbxRatingDisplay($, ratingFormID, postID, reviewID, window[varReviewRatingFormName]['review_' + reviewID]);
        }

    });
}//end cbxreviewRatingDisplay


jQuery(document).ready(function ($) {


    cbxreviewRatingDisplay(false, $);






    //Review load more button clicks
    $('.cbratingload_more_button a').click(function (e) {
        e.preventDefault();

        var $this = $(this);


        var ratingFormID = $this.attr('data-form-id');
        var postID       = $this.attr('data-post-id');
        var page         = $this.attr('data-page');
        var perpage      = $this.attr('data-perpage');


        //cb_ratingForm_front_review_nonce_field  nonce field id
        var nonce        = $this.find('#cb_ratingForm_front_review_nonce_field').val();
        var cbReviewData = {};


        $this.find('.cbrating_waiting_icon').show();
        $this.addClass('disabled_cbrp_button');

        $('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').empty();
        $('.cbrp-content-wprapper-form-' + ratingFormID + '-post-' + postID + ' .ratingFormStatus').removeClass('error_message');

        cbReviewData['ratingFormID'] = ratingFormID;
        cbReviewData['postID']       = postID;
        cbReviewData['page']         = page;
        cbReviewData['perpage']      = perpage;
        cbReviewData['nonce']        = nonce;

        $.ajax({
            type: 'POST',
            url: cbratingsystem.ajaxurl,
            data: {
                action: 'cbReviewAjaxFunction',
                cbReviewData: cbReviewData
            },
            success: function (data, textStatus, XMLHttpRequest) {

                if (data) {
                    try {

                        $this.find('.cbrating_waiting_icon').hide();
                        $this.removeClass('disabled_cbrp_button');
                        var appendableData = $.parseJSON(data);

                        $this.attr('data-page', appendableData.page);
                        $this.attr('data-perpage', appendableData.perpage);


                        $('.reviews_container_div_post-' + postID + '_form-' + ratingFormID).append(appendableData.html);

                        cbxreviewRatingDisplay(true, $);

                        if (appendableData.isFinished == '1') {
                            $this.replaceWith(cbrpRatingFormReviewContent.success_msg);
                        }

                    } catch (e) {
                        $this.replaceWith(cbrpRatingFormReviewContent.failure_msg);
                    }
                }

            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                $this.replaceWith(cbrpRatingFormReviewContent.failure_msg);
            }
        });
    });




});