jQuery(document).ready(function ($) {

    //add clipboard
    //for shortcode copy to clipboard
    var clipboard = new Clipboard('.cbratingsystemshortcodetrigger');
    clipboard.on('success', function ( e ) {
        e.clearSelection();

    });

    //select all text on click of shortcode text
    $('.cbratingsystemshortcode').on("click", function () {

        var text = $(this).text();
        var $this = $(this);
        var $input = $('<input class="cbratingsystemshortcode-text" type="text">');
        $input.prop('value', text);
        $input.insertAfter($(this));
        $input.focus();
        $input.select();
        $this.hide();
        $input.focusout(function(){
            $this.show();
            $input.remove();
        });
    });
    //end adding clipboard js for copying


    var q_title = commentAjax.sampleqtitle;
    var click_note = commentAjax.clicktoedit;


    //adding tooltip
    $('.cbratingtooltipster').tooltipster({
        animation: 'fade',
        delay: 200,
        theme: 'tooltipster-light',
        touchDevices: true,
        trigger: 'hover',
        contentAsHTML: true

    });

    //form setting page
    // Switches option sections
    $('.ratingtabgroup').hide();
    var cbratingactivetab = '';

    if (cbratingactivetab != '' && $(cbratingactivetab).length) {
        //console.log('reset first one');
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(cbratingactivetab).fadeIn();
    } else {
        $('.ratingtabgroup:first').fadeIn();
    }


    $('.ratingtabgroup .collapsed').each(function () {
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function () {
                if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
            });
    });

    if (cbratingactivetab != '' && $(cbratingactivetab + '-tab').length) {
        $(cbratingactivetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }

    $('.nav-tab-wrapper a').click(function (evt) {
        evt.preventDefault();

        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        $('.ratingtabgroup').hide();
        $(clicked_group).fadeIn();
    });
    //end form setting page


    //question js starts

    //question, show x number of radio or checkbox for question

    $('.edit-custom-question-fields-wrapper').on('change', 'select.select_count', function () {
        // $(this).change(function () {


        var $this = $(this);
        var q_id = $this.attr('data-q-id');
        var val = $('.question_field_type_q_id-' + q_id).val();

        var selectInputVal = $this.val();

        $('.field_display_' + val + '_div_q_id-' + q_id).each(function () {

            //console.log($('.field_display_' + val + '_div_q_id-' + q_id)) ;

            $(this).hide();
        });

        for (var i = 0; selectInputVal > i; i++) {
            $('.field_display_' + val + '_div_q_id-' + q_id + '_field_id-' + i).show();
        }

    });


    //question type change between textbox, radio and checkbox
    $('.edit-custom-question-fields-wrapper').on('change', 'select.question_field_type', function () {

        //console.log('field type change');

        var fieldtype = $(this).val();  //variable name 'fieldtype' is better than 'val'
        var q_id = $(this).attr('data-q-id');
        //console.log($(this).next('.seperated_checkbox_div_q_id-' + q_id));

        //disable the 4th col all fields
        $('.form_item_text_q_id-' + q_id).addClass('disable_field');
        $('.form_item_checkbox_q_id-' + q_id).addClass('disable_field');
        $('.form_item_radio_q_id-' + q_id).addClass('disable_field');

        //except selected field type
        $('.form_item_' + fieldtype + '_q_id-' + q_id).removeClass('disable_field');

        //now disable seperated box if not checkbox
        //seperated_checkbox_div seperated_checkbox_div_q_id-0 seperated_field_checkbox_div_q_id-0 disable_field
        if (fieldtype == 'checkbox') {
            $('.seperated_field_checkbox_div_q_id-' + q_id).removeClass('disable_field');
        }
        else {
            $('.seperated_field_checkbox_div_q_id-' + q_id).addClass('disable_field');
        }

    });
    //ok


    //edit question title
    $('.edit-custom-question-fields-wrapper').on('click', 'label.question-label', function () {

        var q_id = $(this).attr('data-q-id');
        $(this).hide();
        //console.log($(this));
        $('input#edit-custom-question-text-q-' + q_id).show();
        $('input#edit-custom-question-text-q-' + q_id).focus();
    });

    //make the label editable for first column
    //ok

    /*
     * After completion of editing when user will click outside of the editing textbox,
     * hiding the field and showing the "Question Title Label".
     */

    $('.edit-custom-question-fields-wrapper').on('blur', 'input.edit-custom-question-text-q', function () {
        // console.log($(this));


        var q_id = $(this).attr('data-q-id');
        if ($(this).hasClass('error')) {
            $(this).removeClass('error');
        }

        var val = $(this).val(); //get the inline edit input field value

        //console.log(val);

        if (val.length != 0) {
            $(this).hide();

            $('#question-label-' + q_id).text(val); //show the edit text from input field back to label
            $('#question-label-' + q_id).show();

            //$('.question-label').hide();
            //$('.edit-custom-question-text-q').show();

        } else {
            $(this).addClass('error');
        }

    });


    //ok
    //question: Clicking on the "Question Field Title Label", allowing user/admin to edit the "Question Field Title".
    //$('.question-field-label').each(function (e) {

    //click to edit labels of multiple options for radio or checkbox
    $('.edit-custom-question-fields-wrapper').on('click', 'label.question-field-label', function () {
        var $this = $(this);

        //console.log('i am here');
        var q_id = $this.attr('data-q-id');
        var field_type = $('.question_field_type_q_id-' + q_id).val();
        var field_id = $this.attr('data-' + field_type + '-field-text-id');
        var labelEditable = $this.attr('labeleditable');

        //console.log(labelEditable);


        if ((field_type == 'checkbox') || (field_type == 'radio')) {
            //console.log(labelEditable);
            if (labelEditable == '1') {
                $this.hide();
                $('input#edit-custom-question-' + field_type + '-field-text-' + field_id + '-q-' + q_id).show().removeClass('disable_field').focus();
            }
        }

    }); //ok


    /*
     * After completion of editing when user will click outside of the editing textbox,
     * hiding the field and showing the "Question Title Label".
     */
    //ok
    //$('.edit_field_display_input_label').each(function () {

    $('.edit-custom-question-fields-wrapper').on('blur', 'input.edit_field_display_input_label', function () {

        //console.log('hellow');

        //console.log(jQuery(this).val());
        //$(this).blur(function () {

        var labelEditable = $(this).attr('labeleditable');
        if (labelEditable == '1') {

            var q_id = $(this).attr('data-q-id');
            var field_type = $('.question_field_type_q_id-' + q_id).val();//which type of input field is selected
            var field_id = $(this).attr('data-' + field_type + '-field-text-id');
            //console.log(field_id);
            //console.log('input#edit-custom-question-'+val+'-field-text-'+field_id+'-q-'+q_id);

            if ((field_type == 'checkbox') || (field_type == 'radio')) {

                if ($(this).hasClass('error')) {
                    $(this).removeClass('error');
                }
                var inputVal = $(this).val();
                //console.log(inputVal + ' (Click to edit)');
                if (inputVal.length != 0) {
                    $('label.label-q-' + q_id + '-' + field_type + '-' + field_id).text(inputVal);
                    $('label.label-q-' + q_id + '-' + field_type + '-' + field_id).show();
                    $(this).hide();
                    //jQuery('input#edit-custom-question-enable-q-'+q_id).attr('checked','checked');
                    //jQuery('div.form-item-custom-question-required-q-id-'+q_id).removeClass('disable_field');
                } else {
                    $(this).addClass('error');
                }
            }
        }
        // });
    }); //ok should also work for newly loaded content


    /*
     *  If "Seperated" is not selected then disable the edit option and show the main question.
     */

    $('.edit-custom-question-fields-wrapper').on('change', 'input.seperated_checkbox_input', function () {
        //$('.seperated_checkbox_input').each(function () {
        console.log('hi');
        //console.log($(this).val());


        var q_id = $(this).attr('data-q-id');
        var field_type = $('.question_field_type_q_id-' + q_id).val();

        //why field id is zero at first ?
        var field_id = 0;   // As the checkbox is only with the main question, So we don't need to show multiple checkbox

        //main question label
        var questionLabel = $('input#edit-custom-question-text-q-' + q_id).val().substring(0, 10);
        var seperated = $(this).val();
        var totalFieldCount = $('.select_' + field_type + '_count_q_id-' + q_id + ' option').length;
        var curentFieldVal = $('.select_' + field_type + '_count_q_id-' + q_id + '  :selected').val();


        //var curentFieldVal = ($('.select_' + field_type + '_count_q_id-' + q_id + '  :selected').val());


        if ($(this).val() == 0) {
            //single checkbox mode

            //if single checkbox is selected then hide the count box for checkbox
            $('.select_checkbox_count_div_q_id-' + q_id).hide();

            for (var j = 0; totalFieldCount > j; j++) {
                $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).hide();
                //jQuery('.field_display_'+val+'_div_q_id-'+q_id+'_field_id-'+j).addClass('disable_field');
            }
            $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0').show();
            $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0 label').show();
            //$('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-0 input#edit-custom-question-' + field_type + '-field-text-0-q-' + q_id).hide();

            $('label.label-q-' + q_id + '-' + field_type + '-0').attr('labeleditable', '0');
            $('label.label-q-' + q_id + '-' + field_type + '-0').attr('title', click_note);
            $('input#edit-custom-question-' + field_type + '-field-text-' + 0 + '-q-' + q_id).attr('labeleditable', '0');

            //$('input#edit-custom-question-' + field_type + '-field-text-' + j + '-q-' + q_id).attr('labeleditable', '1');


        } else if ($(this).val() == 1) {
            //multiple mode

            //if single checkbox is selected then hide the count box for checkbox
            $('.select_checkbox_count_div_q_id-' + q_id).show(); //show select box wrapper

            $('.select_checkbox_count_q_id-' + q_id).removeClass('disable_field'); //show select box

            //single checkbox fields class "field_display_checkbox_div field_display_checkbox_div_q_id-X field_display_checkbox_div_q_id-X_field_id-X"
            for (var j = 0; j < totalFieldCount; j++) {
                if (j < curentFieldVal) {
                    //enable the checkbox wrapper
                    $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).show();
                    $('.field_display_' + field_type + '_div_q_id-' + q_id + '_field_id-' + j).removeClass('disable_field');
                }


                //make the checkbox label editable and title tip
                //question-field-label question-field-checkbox-label label-q-0-checkbox-0 option mouse_normal
                $('label.label-q-' + q_id + '-' + field_type + '-' + j).attr('labeleditable', '1');
                $('label.label-q-' + q_id + '-' + field_type + '-' + j).attr('title', click_note);

                $('input#edit-custom-question-' + field_type + '-field-text-' + j + '-q-' + q_id).attr('labeleditable', '1');

                var seperatedQLabel = $('input#edit-custom-question-' + field_type + '-field-text-' + j + '-q-' + q_id).val();

                $('label.label-q-' + q_id + '-' + field_type + '-' + j).empty().text(seperatedQLabel);
            }

        }

    });

    //question end


    //criteria js actions

    //blur event label input, when user click outside of any label input event , focus lost for label input
    //this will also work on newly added inputs/ajax loaded labels
    $('.edit-custom-criteria-fields-wrapper').on('blur', 'input.edit-custom-criteria-label-text-star', function (e) {

        var $this = $(this);

        var star_id = $this.attr('data-star-id');
        var label_id = $this.attr('data-label-id');

        if ($this.hasClass('error')) {
            $this.removeClass('error');
        }

        //var star_id = $(this).attr('data-star-id');

        var val = $this.val();

        if (val.length != 0) {


            $this.hide();

            $('.label-' + label_id + '-option-star-' + star_id).text(val);
            $('.label-' + label_id + '-option-star-' + star_id).show();
            $this.attr('checked', 'checked');
        } else {
            $this.addClass('error');
        }

    }); //

    //click on label, render input field for that label
    //this will also work on newly added labels/ajax loaded labels
    $('.edit-custom-criteria-fields-wrapper').on('click', 'label.custom-criteria-single-label', function (e) {

        var $this = $(this);
        $this.hide();  //hide label

        var star_id = $this.attr('data-star-id');
        var label_id = $this.attr('data-label-id');



        $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).show();
        $('.edit-custom-criteria-label-text-' + label_id + '-star-' + star_id).focus();



    });


    //end //click on label, render input field for that label

    /******************** edited at 22-7 end here *****************/
    //custom criteria
    var starSelectionErrorMessage = 'Please select at least one.';


    $('.display_item').show();


    /*
     * Code for read more feature for comment section at admin.
     */

    $('.js_read_link').click(function () {
        var readMoreText = $(this).html();

        $('.read_more_span').toggleClass('disable_field');
        $(this).toggleClass('read_more');

        if (!$(this).hasClass('read_more')) {
            $(this).html(' ...Less');
        } else {
            $(this).html(' ...More');
        }
    });


    /*
     * Code for AJAXified Enable/Disable of the form at rating form listing page at admin.
     */

    $('.cbrp-form-listing .enable_disable_click').each(function () {
        var ratingFormId = $(this).attr('data-form-id');

        $(this).click(function () {
            var is_active = $(this).attr('data-form-status');
            var nonce = $('#cb_form_ajax_nonce_action_name').val();

            var cbRatingFormEnableData = {};
            cbRatingFormEnableData['ratingFormId'] = ratingFormId;
            cbRatingFormEnableData['is_active'] = is_active;
            cbRatingFormEnableData['nonce'] = nonce;

            //console.log(cbRatingFormEnableData);

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'cbAdminRatingFormListingAjaxFunction',
                    cbRatingFormEnableData: cbRatingFormEnableData
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    //console.log(data);
                    if (data) {
                        var parsed = $.parseJSON(data);

                        if (parsed) {
                            $('.enable_disable_click_form_' + ratingFormId).empty().text(parsed.text);
                            $('.enable_disable_click_form_' + ratingFormId).attr('data-form-status', parsed.is_active);
                            $('.enable_disable_click_form_' + ratingFormId).toggleClass('disable');
                            $('.enable_disable_click_form_' + ratingFormId).toggleClass('enable');

                            //invertText();
                        }
                    }

                },
                error: function (MLHttpRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                }
            });
        });

        $('.enable_disable_click_form_' + ratingFormId).hover(
            function () {
                var state = $(this).attr('data-form-status');

                $(this).text(ratingFormEnableDisableStateText[state + '_hover']);
            },
            function () {
                var state = $(this).attr('data-form-status');

                $(this).text(ratingFormEnableDisableStateText[state + '_normal']);
            }
        );
    });

});// end of dom ready