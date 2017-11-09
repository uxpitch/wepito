/* Used to show and hide the admin tabs for URP */
function ShowTab(TabName) {
		jQuery(".OptionTab").each(function() {
				jQuery(this).addClass("HiddenTab");
				jQuery(this).removeClass("ActiveTab");
		});
		jQuery("#"+TabName).removeClass("HiddenTab");
		jQuery("#"+TabName).addClass("ActiveTab");
		
		jQuery(".nav-tab").each(function() {
				jQuery(this).removeClass("nav-tab-active");
		});
		jQuery("#"+TabName+"_Menu").addClass("nav-tab-active");
}


function ShowOptionTab(TabName) {
	jQuery(".urp-option-set").each(function() {
		jQuery(this).addClass("urp-hidden");
	});
	jQuery("#"+TabName).removeClass("urp-hidden");

	jQuery(".options-subnav-tab").each(function() {
		jQuery(this).removeClass("options-subnav-tab-active");
	});
	jQuery("#"+TabName+"_Menu").addClass("options-subnav-tab-active");
}

jQuery(document).ready(function() {
	SetCategoryDeleteHandlers();

	jQuery('.ewd-urp-add-review-category').on('click', function(event) {
		var ID = jQuery(this).data('nextid');

		var HTML = "<tr id='ewd-urp-review-category-row-" + ID + "'>";
		HTML += "<td><input type='hidden' name='Review_Category_Order_" + ID + "' value='" + ID + "'><a class='ewd-urp-delete-review-category' data-reviewid='" + ID + "'>Delete</a></td>";
		HTML += "<td><input type='text' name='Review_Category_" + ID + "_Name'></td>";
		HTML += "<td><select name='Review_Category_" + ID + "_Required'>";
		HTML += "<option value='Yes'>Yes</option>";
		HTML += "<option value='No'>No</option>";
		HTML += "</select></td>";
		HTML += "<td><select name='Review_Category_" + ID + "_Explanation'>";
		HTML += "<option value='Yes'>Yes</option>";
		HTML += "<option value='No'>No</option>";
		HTML += "</select></td>";
		HTML += "<td><select class='ewd-urp-field-type-select' name='Review_Category_" + ID + "_Type' data-elementid='" + ID + "''>";
		HTML += "<option value='ReviewItem'>Review Line</option>";
		HTML += "<option value='text'>Text Box</option>";
		HTML += "<option value='textarea'>Text Area</option>";
		HTML += "<option value='Dropdown'>Dropdown</option>";
		HTML += "<option value='Checkbox'>Checkbox</option>";
		HTML += "<option value='Radio'>Radio</option>";
		HTML += "<option value='Date'>Date</option>";
		HTML += "<option value='DateTime'>Date/Time</option>";
		HTML += "</select></td>";
		HTML += "<td><select name='Review_Category_" + ID + "_Filterable'>";
		HTML += "<option value='No'>No</option>";
		HTML += "<option value='Yes'>Yes</option>";
		HTML += "</select></td>";
		HTML += "<td><input type='text' name='Review_Category_" + ID + "_Options' disabled></td>";
		HTML += "</tr>";

		//jQuery('table > tr#ewd-uasp-add-reminder').before(HTML);
		jQuery('#ewd-urp-review-categories-table tr:last').before(HTML);

		ID++;
		jQuery(this).data('nextid', ID); //updates but doesn't show in DOM

		SetCategoryDeleteHandlers();
		SetFieldTypeHandler();

		event.preventDefault();
	});
});

function SetFieldTypeHandler() {
	jQuery('.ewd-urp-field-type-select').on('change', function() {
		var ID = jQuery(this).data('elementid');
		if (jQuery(this).val() == "Dropdown" || jQuery(this).val() == "Checkbox" || jQuery(this).val() == "Radio") {
			jQuery('input[name="Review_Category_' + ID + '_Options"]').prop("disabled", false);
		}
		else {jQuery('input[name="Review_Category_' + ID + '_Options"]').prop("disabled", true);}

		if (jQuery(this).val() == "ReviewItem") {
			jQuery('input[name="Review_Category_' + ID + '_Filterable"]').prop("disabled", true);
		}
		else {jQuery('input[name="Review_Category_' + ID + '_Filterable"]').prop("disabled", false);}
	});
}

function SetCategoryDeleteHandlers() {
	jQuery('.ewd-urp-delete-review-category').on('click', function(event) {
		var ID = jQuery(this).data('reviewid');
		var tr = jQuery('#ewd-urp-review-category-row-'+ID);

		tr.fadeOut(400, function(){
            tr.remove();
        });

		event.preventDefault();
	});
}

jQuery(document).ready(function() {
	SetProductDeleteHandlers();

	jQuery('.ewd-urp-add-product-list-item').on('click', function(event) {
		var ID = jQuery(this).data('nextid');

		var HTML = "<tr id='ewd-urp-product-list-item-" + ID + "'>";
		HTML += "<td><a class='ewd-urp-delete-product-list-item' data-productid='" + ID + "'>Delete</a></td>";
		HTML += "<td><input type='text' name='Product_List_" + ID + "_Name'></td>";
		HTML += "</tr>";

		//jQuery('table > tr#ewd-uasp-add-reminder').before(HTML);
		jQuery('#ewd-urp-product-list-table tr:last').before(HTML);

		ID++;
		jQuery(this).data('nextid', ID); //updates but doesn't show in DOM

		SetProductDeleteHandlers();

		event.preventDefault();
	});
});

function SetProductDeleteHandlers() {
	jQuery('.ewd-urp-delete-product-list-item').on('click', function(event) {
		var ID = jQuery(this).data('productid');
		var tr = jQuery('#ewd-urp-product-list-item-'+ID);

		tr.fadeOut(400, function(){
            tr.remove();
        });

		event.preventDefault();
	});
}

jQuery(document).ready(function() {
	SetReminderDeleteHandlers();

	jQuery('.ewd-urp-add-reminder-item').on('click', function(event) {
		var Counter = jQuery(this).data('nextcounter');
		var Max_ID = jQuery(this).data('maxid');

		var HTML = "<tr id='ewd-urp-reminder-row-" + Counter + "'>";
		HTML += "<td><input type='hidden' name='Reminder_" + Counter + "_ID' value='" + Max_ID + "' /><a class='ewd-urp-delete-reminder-item' data-reminderid='" + Counter + "'>Delete</a></td>";
		HTML += "<td><select name='Reminder_" + Counter + "_Email_To_Send' >";
		jQuery(urp_messages).each(function(index, el) {
			HTML += "<option value=" + el.ID + ">" + el.Name + "</option>";
		});
		HTML += "</select></td>";
		HTML += "<td><select name='Reminder_" + Counter + "_Reminder_Interval' >";
		HTML += "<option value='0'>Immediate</option>";
		HTML += "<option value='1'>1</option>";
		HTML += "<option value='2'>2</option>";
		HTML += "<option value='3'>3</option>";
		HTML += "<option value='4'>4</option>";
		HTML += "<option value='5'>5</option>";
		HTML += "<option value='6'>6</option>";
		HTML += "<option value='7'>7</option>";
		HTML += "<option value='8'>8</option>";
		HTML += "<option value='9'>9</option>";
		HTML += "<option value='10'>10</option>";
		HTML += "<option value='11'>11</option>";
		HTML += "<option value='12'>12</option>";
		HTML += "<option value='13'>13</option>";
		HTML += "<option value='14'>14</option>";
		HTML += "<option value='15'>15</option>";
		HTML += "<option value='16'>16</option>";
		HTML += "<option value='17'>17</option>";
		HTML += "<option value='18'>18</option>";
		HTML += "<option value='19'>19</option>";
		HTML += "<option value='20'>20</option>";
		HTML += "<option value='21'>21</option>";
		HTML += "<option value='22'>22</option>";
		HTML += "<option value='23'>23</option>";
		HTML += "</select></td>";
		HTML += "<td><select name='Reminder_" + Counter + "_Reminder_Unit' >";
		HTML += "<option value='Hours'> Hour(s)</option>";
		HTML += "<option value='Days'>Day(s)</option>";
		HTML += "<option value='Weeks'>Week(s)</option>";
		HTML += "</select></td>";
		HTML += "<td><select name='Reminder_" + Counter + "_Status_Trigger' >";
		jQuery(urp_wc_statuses).each(function(index, el) {
			HTML += "<option value=" + el.key + ">" + el.value+ "</option>";
		});
		HTML += "</select></td>";
		HTML += "</tr>";

		//jQuery('table > tr#ewd-uasp-add-reminder').before(HTML);
		jQuery('#ewd-urp-reminders-table tr:last').before(HTML);

		Counter++;
		Max_ID++;
		jQuery(this).data('nextcounter', Counter); //updates but doesn't show in DOM
		jQuery(this).data('maxid', Max_ID);

		SetReminderDeleteHandlers();

		event.preventDefault();
	});
});

function SetReminderDeleteHandlers() {
	jQuery('.ewd-urp-delete-reminder-item').on('click', function(event) {
		var Counter = jQuery(this).data('remindercounter');
		var tr = jQuery('#ewd-urp-reminder-row-'+Counter);

		tr.fadeOut(400, function(){
            tr.remove();
        });

		event.preventDefault();
	});
}

jQuery(document).ready(function() {
	SetMessageDeleteHandlers();

	jQuery('.ewd-urp-add-email').on('click', function(event) {
		var Counter = jQuery(this).data('nextcounter');
		var Max_ID = jQuery(this).data('maxid');

		var HTML = "<tr id='ewd-urp-email-message-" + Counter + "'>";
		HTML += "<td><input type='hidden' name='Email_Message_" + Counter + "_ID' value='" + Max_ID + "' /><a class='ewd-urp-delete-message' data-messagecounter='" + Counter + "'>Delete</a></td>";
		HTML += "<td><input type='text' name='Email_Message_" + Counter + "_Name'></td>";
		HTML += "<td><textarea name='Email_Message_" + Counter + "_Body'></textarea></td>";
		HTML += "</tr>";

		//jQuery('table > tr#ewd-uasp-add-reminder').before(HTML);
		jQuery('#ewd-urp-email-messages-table tr:last').before(HTML);

		Counter++;
		Max_ID++;
		jQuery(this).data('nextcounter', Counter); //updates but doesn't show in DOM
		jQuery(this).data('maxid', Max_ID); //updates but doesn't show in DOM

		SetMessageDeleteHandlers();

		event.preventDefault();
	});
});

function SetMessageDeleteHandlers() {
	jQuery('.ewd-urp-delete-message').on('click', function(event) {
		var ID = jQuery(this).data('messagecounter');
		var tr = jQuery('#ewd-urp-email-message-'+ID);

		tr.fadeOut(400, function(){
            tr.remove();
        });

		event.preventDefault();
	});
}

jQuery(document).ready(function() {
	jQuery('#ewd-urp-wordpress-login-option').on('change', {optionType: "wordpress"}, Update_Options);
	jQuery('#ewd-urp-feup-login-option').on('change', {optionType: "feup"}, Update_Options);
	jQuery('#ewd-urp-facebook-login-option').on('change', {optionType: "facebook"}, Update_Options);
	jQuery('#ewd-urp-twitter-login-option').on('change', {optionType: "twitter"}, Update_Options);
	
	Update_Options();
	EWD_URP_WooCommerce_Review_Type_Options();
});

jQuery(function() {
    ReorderAdminTablesList();
    EWD_URP_Send_Test_Email();
});


function ReorderAdminTablesList() {
    jQuery("#ewd-urp-product-list-table > tbody").sortable({
    	stop: function( event, ui ) {saveProductOrderClick(); }
    }).disableSelection();
    jQuery("#ewd-urp-review-categories-table > tbody").sortable({
    	stop: function( event, ui ) {saveReviewCategoryOrderClick(); }
    }).disableSelection();
}

function saveProductOrderClick() {
    // ----- Retrieve the li items inside our sortable list
    var items = jQuery("#ewd-urp-product-list-table tbody tr");

    var productIDs = [items.size()];
    var index = 0;

    // ----- Iterate through each li, extracting the ID embedded as an attribute
    items.each( function(intIndex) {
        jQuery(this).children().each(function() {
        	if (jQuery(this).html().substring(0,6) == "<input") {
        		jQuery(this).children().each(function() {
        			jQuery(this).attr('name', 'Product_List_'+intIndex+'_Name');
        		});
        	}
        });
    });
}

function saveReviewCategoryOrderClick() {
	// ----- Retrieve the li items inside our sortable list
    var items = jQuery("#ewd-urp-review-categories-table tbody tr");

    var productIDs = [items.size()];
    var index = 0;

    // ----- Iterate through each li, extracting the ID embedded as an attribute
    items.each( function(intIndex) {
        jQuery(this).children().each(function() {
        	if (jQuery(this).html().substring(0,49) == '<input type="hidden" name="Review_Category_Order_' || jQuery(this).html().substring(0,35) == '<input name="Review_Category_Order_') {
        		jQuery(this).children().each(function() {
        			if (jQuery(this).is("input")) {jQuery(this).val(intIndex);}
        		});
        	}
        });
    });
}

function Update_Options(params) {
	if (params === undefined || params.data.optionType == "wordpress") {
		if (jQuery('#ewd-urp-wordpress-login-option').is(':checked')) {
			jQuery('.ewd-urp-wordpress-login-option').removeClass('ewd-urp-hidden');
		}
		else {
			jQuery('.ewd-urp-wordpress-login-option').addClass('ewd-urp-hidden');
		}
	}
	if (params === undefined || params.data.optionType == "feup") {
		if (jQuery('#ewd-urp-feup-login-option').is(':checked')) {
			jQuery('.ewd-urp-feup-login-option').removeClass('ewd-urp-hidden');
		}
		else {
			jQuery('.ewd-urp-feup-login-option').addClass('ewd-urp-hidden');
		}
	}
	if (params === undefined || params.data.optionType == "facebook") {
		if (jQuery('#ewd-urp-facebook-login-option').is(':checked')) {
			jQuery('.ewd-urp-facebook-login-option').removeClass('ewd-urp-hidden');
		}
		else {
			jQuery('.ewd-urp-facebook-login-option').addClass('ewd-urp-hidden');
		}
	}
	if (params === undefined || params.data.optionType == "twitter") {
		if (jQuery('#ewd-urp-twitter-login-option').is(':checked')) {
			jQuery('.ewd-urp-twitter-login-option').removeClass('ewd-urp-hidden');
		}
		else {
			jQuery('.ewd-urp-twitter-login-option').addClass('ewd-urp-hidden');
		}
	}
}

function EWD_URP_WooCommerce_Review_Type_Options() {
	jQuery('input[name="woocommerce_review_types[]"]').on('click', function() {
		if (jQuery(this).is('[type="radio"]')) {jQuery('input[name="woocommerce_review_types[]"][type="checkbox"]').prop('checked', false);}
		if (jQuery(this).is('[type="checkbox"]')) {jQuery('input[name="woocommerce_review_types[]"][type="radio"]').prop('checked', false);}
	});
}

function EWD_URP_Send_Test_Email() {
	jQuery('.ewd-urp-send-test-email').on('click', function() {
		jQuery('.ewd-urp-test-email-response').remove();

		var Email_Address = jQuery('.ewd-urp-test-email-address').val();
		var Email_To_Send = jQuery('.ewd-urp-email-selector').val();

		if (Email_Address == "" || Email_To_Send == "") {
			jQuery('.ewd-urp-send-test-email').after('<div class="ewd-urp-test-email-response">Error: Select an email and enter an email address before sending.</div>');
		}

		var data = 'Email_Address=' + Email_Address + '&Email_To_Send=' + Email_To_Send + '&action=urp_send_test_email';
        jQuery.post(ajaxurl, data, function(response) {
        	jQuery('.ewd-urp-send-test-email').after(response);
        });
	});
}

jQuery(document).ready(function() {
    jQuery('.ewd-urp-hide-review-ask').on('click', function() {
        var Ask_Review_Date = jQuery(this).data('askreviewdelay');

        jQuery('.ewd-urp-review-ask-popup, #ewd-urp-review-ask-overlay').addClass('ewd-urp-hidden');

        var data = 'Ask_Review_Date=' + Ask_Review_Date + '&action=ewd_urp_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});
    });
    jQuery('#ewd-urp-review-ask-overlay').on('click', function() {
    	jQuery('.ewd-urp-review-ask-popup, #ewd-urp-review-ask-overlay').addClass('ewd-urp-hidden');
    })
});

jQuery(document).ready(function() {
	jQuery('.urp-spectrum').spectrum({
		showInput: true,
		showInitial: true,
		preferredFormat: "hex",
		allowEmpty: true
	});

	jQuery('.urp-spectrum').css('display', 'inline');

	jQuery('.urp-spectrum').on('change', function() {
		if (jQuery(this).val() != "") {
			jQuery(this).css('background', jQuery(this).val());
			var rgb = EWD_URP_hexToRgb(jQuery(this).val());
			var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
			if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
			else {jQuery(this).css('color', '#000000');}
		}
		else {
			jQuery(this).css('background', 'none');
		}
	});

	jQuery('.urp-spectrum').each(function() {
		if (jQuery(this).val() != "") {
			jQuery(this).css('background', jQuery(this).val());
			var rgb = EWD_URP_hexToRgb(jQuery(this).val());
			var Brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
			if (Brightness < 100) {jQuery(this).css('color', '#ffffff');}
			else {jQuery(this).css('color', '#000000');}
		}
	});
});

function EWD_URP_hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}