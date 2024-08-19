/*
 * Title:   Travelo | Responsive Wordpress Booking Template - Javascript file for Single Car
 * Author:  http://themeforest.net/user/soaptheme
 */

"use strict";
var booking_data = '';
var price_arr = {};
var flag_searched = false;
var tjq = jQuery.noConflict();

tjq(document).ready(function() {
    
    init_calendar();

    //set default panel
    tjq('#car-main-content ul.tabs li:first-child a').tab('show');
    
    // add car to wishlist
    tjq("body").on('click', '.btn-add-wishlist', function(e) {
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'car_add_to_wishlist',
                'car_id' : car_data.car_id
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

    // remove car from wishlist
    tjq("body").on('click', '.btn-remove-wishlist', function(e) {
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'car_add_to_wishlist',
                'car_id' : car_data.car_id,
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

    //book now action
    tjq("#booking-form").submit(function(e) {
        e.preventDefault();
        var date_from_obj = tjq(this).find('input[name="date_from"]');
        var date_to_obj = tjq(this).find('input[name="date_to"]');
        var time_from_obj = tjq(this).find('input[name="time_from"]');
        var time_to_obj = tjq(this).find('input[name="time_to"]');
        var location_from_obj = tjq(this).find('input[name="location_from"]');
        var location_to_obj = tjq(this).find('input[name="location_to"]');
        //form validation
        var date_from  = date_from_obj.val();
        if (! date_from) {
            trav_field_validation_error([date_from_obj], car_data.msg_wrong_date_2, tjq('#booking-form .alert-error'));
            return false;
        }
        var date_to  = date_to_obj.val();
        if (! date_to) {
            trav_field_validation_error([date_to_obj], car_data.msg_wrong_date_3, tjq('#booking-form .alert-error'));
            return false;
        }
        var time_from = time_from_obj.val();
        if (! time_from) {
            trav_field_validation_error([time_from_obj], car_data.msg_wrong_date_4, tjq('#booking-form .alert-error'));
            return false;
        }
        var time_to = time_to_obj.val();
        if (! time_to) {
            trav_field_validation_error([time_to_obj], car_data.msg_wrong_date_5, tjq('#booking-form .alert-error'));
            return false;
        }
        var location_from = location_from_obj.val();
        if (! location_from) {
            trav_field_validation_error([location_from_obj], car_data.msg_wrong_date_6, tjq('#booking-form .alert-error'));
            return false;
        }
        var location_to = location_to_obj.val();
        if (! location_to) {
            trav_field_validation_error([location_to_obj], car_data.msg_wrong_date_7, tjq('#booking-form .alert-error'));
            return false;
        }

        var one_day=1000*60*60*24;
        var date_from_date = date_from_obj.datepicker("getDate");
        var date_to_date = date_to_obj.datepicker("getDate");
        var today = new Date();

        today.setDate(today.getDate() - 1);
        if (date_from_date < today) {
            trav_field_validation_error([tjq('input[name="date_from"]')], car_data.msg_wrong_date_9, tjq('#booking-form .alert-error'));
            return false;
        }

        date_from_date = date_from_date.getTime();
        date_to_date = date_to_date.getTime();

        if (date_from_date > date_to_date) {
            trav_field_validation_error([date_from_obj, date_to_obj], car_data.msg_wrong_date_8, tjq('#booking-form .alert-error'));
            return false;
        }

        booking_data = tjq("#booking-form").serialize();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: booking_data,
            success: function(response) {
                if ( response.success == 1 ) {
                    if ( car_data.booking_url ) {
                        booking_data = tjq("#booking-form").serialize();
                        var form = tjq('<form method="get" action="' + car_data.booking_url + '"></form>');
                        form.append('<input type="hidden" name="booking_data" value="' + booking_data + '">');
                        tjq("body").append(form);
                        form.submit();
                    } else {
                        alert(car_data.msg_no_booking_page);
                    }
                    return false;                    
                } else {
                    trav_field_validation_error([tjq('input[name="date_from"]')], response.result, tjq('#booking-form .alert-error'));
                }
            }
        });        

        return false;
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
                'action': 'car_get_month_car_numbers',
                'car_id' : car_data.car_id,
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