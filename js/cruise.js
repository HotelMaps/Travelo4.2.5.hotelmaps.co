/*
 * Title:   Travelo | Responsive Wordpress Booking Template - Javascript file for Single Cruise
 * Author:  http://themeforest.net/user/soaptheme
 */

"use strict";
var price_arr = {};
var booking_data = '';
var flag_searched = false;
var tjq = jQuery.noConflict();
//init_map();

tjq(document).ready(function() {

    init_calendar();
    init_write_review();

    booking_data = tjq("#check_availability_form").serialize();
    if (tjq('input[name="pre_searched"]').val() == 1) {
        flag_searched = true;
    }

    //set default panel
    tjq('#cruise-main-content ul.tabs li:first-child a').tab('show');

    //check cruise cabin availability
    tjq("#check_availability_form").submit(function(e) {
        e.preventDefault();
        var date_from_obj = tjq(this).find('select[name="date_from"]');

        //form validation
        var date_from = date_from_obj.val();
        if (! date_from) {
            trav_field_validation_error([date_from_obj], cruise_data.msg_wrong_date_2, tjq('#check_availability_form .alert-error'));
            return false;
        }
        
        var date_from_date = new Date(date_from);
        var today = new Date();
        today.setDate(today.getDate() - 1);
        if (date_from_date < today) {
            trav_field_validation_error([tjq('input[name="date_from"]')], cruise_data.msg_wrong_date_3, tjq('#check_availability_form .alert-error'));
            return false;
        }
        date_from_date = date_from_date.getTime();

        booking_data = tjq("#check_availability_form").serialize();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: booking_data,
            success: function(response){
                if (response.success == 1) {
                    tjq('#cruise-features .room-list').html(response.result);
                } else {
                    alert(response.result);
                }
                flag_searched = true;
            }
        });
        return false;
    });

    //write a review button action on reviews tab
    tjq(".goto-writereview-pane").click(function(e) {
        e.preventDefault();
        tjq('#cruise-features .tabs a[href="#cruise-write-review"]').tab('show');
    });

    //show price button click action
    tjq('.room-list').on('click', '.btn-show-price', function(e) {
        e.preventDefault();
        trav_show_modal(0, cruise_data.msg_wrong_date_1);
        return false;
    });

    // book now action
    tjq('.room-list').on('click', '.btn-book-now', function(e) {
        e.preventDefault();
        if (cruise_data.booking_url) {
            var cabin_type_id = tjq(this).data('cabin-type-id');
            tjq('input[name="action"]').remove();
            booking_data = tjq("#check_availability_form").serialize();
            var form = tjq('<form method="get" action="' + cruise_data.booking_url + '"></form>');
            form.append('<input type="hidden" name="booking_data" value="' + escape(booking_data + '&cabin_type_id=' + cabin_type_id) + '">');
            if ( cruise_data.lang ) {
                form.append('<input type="hidden" name="lang" value="' + cruise_data.lang + '">');
            }
            tjq("body").append(form);
            form.submit();
        } else {
            alert(cruise_data.msg_no_booking_page);
        }
        return false;
    });

    // load more button click action on search result page
    tjq("body").on('click', '.btn-add-wishlist', function(e) {
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'cruise_add_to_wishlist',
                'cruise_id' : cruise_data.cruise_id
            },
            success: function(response){
                if (response.success == 1) {
                    tjq('.btn-add-wishlist').text(tjq('.btn-add-wishlist').data('label-remove'));
                    tjq('.btn-add-wishlist').addClass('btn-remove-wishlist');
                    tjq('.btn-add-wishlist').removeClass('btn-add-wishlist');
                } else {
                    alert(response.result);
                }
            }
        });
        return false;
    });

    // load more button click action on search result page
    tjq("body").on('click', '.btn-remove-wishlist', function(e) {
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'cruise_add_to_wishlist',
                'cruise_id' : cruise_data.cruise_id,
                'remove' : 1
            },
            success: function(response){
                if (response.success == 1) {
                    tjq('.btn-remove-wishlist').text(tjq('.btn-remove-wishlist').data('label-add'));
                    tjq('.btn-remove-wishlist').addClass('btn-add-wishlist');
                    tjq('.btn-remove-wishlist').removeClass('btn-remove-wishlist');
                } else {
                    alert(response.result);
                }
            }
        });
        return false;
    });

    //reviews ajax loading
    tjq('.more-review').click(function() {

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'cruise_get_more_reviews',
                'cruise_id' : cruise_data.cruise_id,
                'last_no' : tjq('.guest-review').length
            },
            success: function(response){
                if (response == '') {
                    tjq('.more-review').remove();
                } else {
                    tjq('.guest-reviews').append(response);
                }
            }
        });
        return false;
    });

    //write review trip type button
    tjq(".sort-trip a").click(function(e) {
        e.preventDefault();
        tjq(this).parent().parent().children().removeClass("active");
        tjq(this).parent().addClass("active");
        tjq('input[name="trip_type"]').val(tjq(".sort-trip a").index(this));
    });

    tjq('#review-form').validate({
        rules: {
            review_rating_detail: { required: true },
            pin_code: { required: true},
            booking_no: { required: true},
            review_title: { required: true},
            review_text: { required: true},
        }
    });

    tjq('#review-form').submit(function() {
        //form validation
        var review_flag = true;
        tjq('.rating_detail_hidden').each(function() {
            if (! tjq(this).val() || (tjq(this).val() == 0)) {
                review_flag = false;
                return false;
            }
        });

        if (! review_flag) {
            tjq('#cruise-write-review .alert').removeClass('alert-success');
            tjq('#cruise-write-review .alert').addClass('alert-error');
            var msg = "Please provide ratings for every category greater than 1 star.";
            trav_field_validation_error([tjq('.overall-rating .detailed-rating')], msg, tjq('#cruise-write-review .alert'));
            return false;
        }

        tjq('#review-form .validation-field').each(function() {
            if (! tjq(this).val()) {
                var msg = tjq(this).data('error-message');
                trav_field_validation_error([tjq(this)], msg, tjq('#cruise-write-review .alert-error'));
                review_flag = false;
                return false;
            }
        });

        if (! review_flag) {
            return false;
        }

        var ajax_data = tjq("#review-form").serialize();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: ajax_data,
            success: function(response){
                if (response.success == 1) {
                    tjq('#cruise-write-review .alert').addClass('alert-success');
                    tjq('#cruise-write-review .alert').removeClass('alert-error');
                    trav_show_modal(1, response.title, response.result);
                    /*var msg = 'Thank you! Your review has been submitted successfully.';
                    trav_field_validation_error([], msg, tjq('#cruise-write-review .alert'));
                    tjq('.submit-review').hide();*/
                } else {
                    tjq('#cruise-write-review .alert').removeClass('alert-success');
                    tjq('#cruise-write-review .alert').addClass('alert-error');
                    trav_show_modal(0, response.result, '');
                    /*var msg = response.result;
                    trav_field_validation_error([], msg, tjq('#cruise-write-review .alert'));*/
                }
            }
        });

        return false;
    });

    tjq('#check_availability_form').on('change', 'input, select', function() {
        tjq('#check_availability_form .alert-error').hide();
        if (flag_searched) {
            tjq("#check_availability_form").submit();
        }
    });

    tjq("#date_from").change(function() {
        tjq('input[name="duration"]').val( tjq(this).find(":selected").data('cruise-duration') );
    });

});

var error_timer;
//check field validation
function trav_field_validation_error(objs, msg, alert_field) {
    for (var i = 0; i < objs.length; ++i) {
        objs[i].closest('.validation-field').addClass('error-field');
    }

    alert_field.find('.message').html(msg);
    alert_field.fadeIn(300);
    clearTimeout(error_timer);
    error_timer = setTimeout(function() {
        alert_field.fadeOut(300);
        tjq('.validation-field').removeClass('error-field');
    }, 5000);
}

//init calendar
function init_calendar() {
    // calendar panel
    var cal = new Calendar();
    var current_date = new Date();
    var current_year_month = (1900 + current_date.getYear()) + "-" + (current_date.getMonth() + 1);
    tjq("#select-month").find("[value='" + current_year_month + "']").prop("selected", "selected");

    tjq("#select-month").change(function() {
        var selected_year_month = tjq("#select-month").val();
        var year = parseInt(selected_year_month.split("-")[0]);
        var month = parseInt(selected_year_month.split("-")[1]);
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            //dataType: "json",
            data: 
            {
                'action': 'cruise_get_month_schedules',
                'cruise_id' : cruise_data.cruise_id,
                'year' : year,
                'month' : month
            },
            success: function(response){
                if (response.success == 1) {
                    var unavailable_days = [];
                    jQuery.each(response.result, function(i, val) {
                        if(val<=0) { unavailable_days.push(parseInt(i)); }
                    });
                    cal.generateHTML(month - 1, year, unavailable_days, price_arr);
                    tjq(".calendar").html(cal.getHTML());
                } else {
                    alert(response.result);
                }
            }
        });
    });

    tjq("#select-month").trigger('change');
}

//init write a review tab
var review_values = [];
function init_write_review(){
    tjq(".editable-rating.five-stars-container").each(function() {

        var _index = tjq(".editable-rating.five-stars-container").index(this);
        var review_value = tjq(this).data("original-stars");
        if (typeof review_value == "undefined") {
            review_value = 0;
        } else {
            review_value = parseInt(review_value);
        }
        review_values[_index] = review_value;
        tjq(this).slider({
            range: "min",
            value: review_value,
            min: 0,
            max: 5,
            slide: function(event, ui) {
                var i = tjq(".editable-rating.five-stars-container").index(this);
                review_values[i] = ui.value;
                var total = 0;
                tjq.each(review_values,function() {
                    total += this;
                });
                var _width = total / (review_values.length) / 5 * 100;
                tjq('.star-rating .five-stars').width(_width.toString() + '%');

                var review_marks = Object.keys(cruise_data.review_labels);
                review_marks.sort(function(a, b){return b-a});
                tjq.each(review_marks, function(index, review_mark) {
                    if (review_mark < total / (review_values.length)) {
                        tjq('.star-rating .status').html(cruise_data.review_labels[review_mark]);
                        tjq('.star-rating .status').fadeIn(300);
                        return false;
                    }
                });

                tjq('input[name="review_rating"]').val(total / (review_values.length));

                tjq('.rating_detail_hidden').eq(i).val(ui.value);

            }
        });
    });
}