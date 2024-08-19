/*
 * Title:   Travelo | Responsive Wordpress Booking Template - Javascript file for Single Tour
 * Author:  http://themeforest.net/user/soaptheme
 */

"use strict";
var tjq = jQuery.noConflict();
var booking_data;

tjq(document).ready(function() {

    //check accommodation room availability
    tjq("#check_availability_form").submit(function(e) {
        e.preventDefault();
        var date_from_obj = tjq(this).find('input[name="date_from"]');
        var date_to_obj = tjq(this).find('input[name="date_to"]');
        //form validation
        var date_from  = date_from_obj.val();
        if (! date_from) {
            trav_field_validation_error([date_from_obj], tour_data.msg_wrong_date_1, tjq('#check_availability_form .alert-error'));
            return false;
        }
        var date_to  = date_to_obj.val();
        if (! date_to) {
            trav_field_validation_error([date_to_obj], tour_data.msg_wrong_date_2, tjq('#check_availability_form .alert-error'));
            return false;
        }

        var one_day=1000*60*60*24;
        var date_from_date = date_from_obj.datepicker("getDate");
        var date_to_date = date_to_obj.datepicker("getDate");
        var today = new Date();
        today.setDate(today.getDate() - 1);
        if (date_from_date < today) {
            trav_field_validation_error([tjq('input[name="date_from"]')], tour_data.msg_wrong_date_3, tjq('#check_availability_form .alert-error'));
            return false;
        }
        date_from_date = date_from_date.getTime();
        date_to_date = date_to_date.getTime();
        if (date_from_date >= date_to_date) {
            trav_field_validation_error([date_from_obj,date_to_obj], tour_data.msg_wrong_date_4, tjq('#check_availability_form .alert-error'));
            return false;
        }

        booking_data = tjq("#check_availability_form").serialize();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: booking_data,
            success: function(response){
                if (response.success == 1) {
                    tjq('#schedule-list').html(response.result);
                    changeTraveloElementUI();
                    tjq('#schedule-list button').css('visibility', 'visible');
                } else {
                    alert(response.result);
                }
            }
        });
        return false;
    });

    // book now action
    tjq('#schedule-list').on('submit', '.tour-booking-form', function(e) {
        var action = tjq(this).attr('action');
        if ( typeof action == 'undefined' ) {
            alert(tour_data.msg_no_booking_page);
            return false;
        }
    });

    tjq('#schedule-list').on('change', '.tour-date-select, select[name="adults"], select[name="kids"]', function(){
        tour_recalculate_tour_price( tjq(this) );
    });

    // load more button click action on search result page
/*    tjq("body").on('click', '.btn-add-wishlist', function(e) {
        e.preventDefault();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'acc_add_to_wishlist',
                'accommodation_id' : tour_data.acc_id
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
                'action' : 'acc_add_to_wishlist',
                'accommodation_id' : tour_data.acc_id,
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
    });*/

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

function trav_get_price_field( price ) {
    var decimals = tour_data.cf_data.decimal_prec;
    var dec_point = tour_data.cf_data.dec_point;
    var thousands_sep = tour_data.cf_data.thousands_sep;
    var cs_pos = tour_data.cf_data.cs_pos;
    var currency_symbol = tour_data.currency_symbol;
    var formated_price = number_format( price * tour_data.exchange_rate, decimals, dec_point, thousands_sep );
    if ( cs_pos == 'after' ) {
        return formated_price + currency_symbol;
    } else {
        return currency_symbol + formated_price;
    }
}

function tour_recalculate_tour_price( obj ) {
    var booking_form = obj.closest('.intro.table-wrapper');
    var price=0, child_price=0, max_seat=0, adults=1, kids=0;

    if ( booking_form.find('.tour-date-select').length ) {
        price = booking_form.find('.tour-date-select option:selected').data('price');
        child_price = booking_form.find('.tour-date-select option:selected').data('child-price');
        max_seat = booking_form.find('.tour-date-select option:selected').data('max-seat');
    } else if ( booking_form.find('.price-data').length ){
        price = booking_form.find('.price-data').data('price');
        child_price = booking_form.find('.price-data').data('child-price');
        max_seat = booking_form.find('.price-data').data('max-seat');
    }

    if ( booking_form.find('select[name="adults"]').length ) {
        adults = parseInt( booking_form.find('select[name="adults"]').val() );
    }
    if ( booking_form.find('select[name="kids"]').length ) {
        kids = parseInt( booking_form.find('select[name="kids"]').val() );
    }

    if ( max_seat <= 0 ) {
        booking_form.find('.sold-out').removeClass('no-display');
        booking_form.find('.btn-book-now').addClass('no-display');
        booking_form.find('.exceed-persons').addClass('no-display');
    } else if ( max_seat < adults + kids ) {
        booking_form.find('.exceed-persons').removeClass('no-display');
        booking_form.find('.sold-out').addClass('no-display');
        booking_form.find('.btn-book-now').addClass('no-display');
    } else {
        booking_form.find('.btn-book-now').removeClass('no-display');
        booking_form.find('.sold-out').addClass('no-display');
        booking_form.find('.exceed-persons').addClass('no-display');
    }

    var total_price = price * adults + child_price * kids;
    booking_form.find('.adult-price').html(trav_get_price_field(price));
    booking_form.find('.child-price').html(trav_get_price_field(child_price));
    booking_form.find('.total-price').html(trav_get_price_field(total_price));
    booking_form.find('.available-seats').html(max_seat);
}