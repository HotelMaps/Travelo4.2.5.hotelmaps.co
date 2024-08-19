/*
 * Title:   Travelo | Responsive Wordpress Booking Template - Javascript file for Single Accommodation
 * Author:  http://themeforest.net/user/soaptheme
 */

"use strict";
var price_arr = {};
var booking_data = '';
var flag_searched = false;
var tjq = jQuery.noConflict();
init_map();

tjq(document).ready(function() {

    init_calendar();
    init_write_review();

    booking_data = tjq("#check_availability_form").serialize();
    if (tjq('input[name="pre_searched"]').val() == 1) {
        flag_searched = true;
    }

    //set default panel
    tjq('#hotel-main-content ul.tabs li:first-child a').tab('show');

    //check accommodation room availability
    tjq("#check_availability_form").on( 'submit', function(e) {
        e.preventDefault();
        var date_from_obj = tjq(this).find('input[name="date_from"]');
        var date_to_obj = tjq(this).find('input[name="date_to"]');
        //form validation
        var date_from  = date_from_obj.val();
        if (! date_from) {
            trav_field_validation_error([date_from_obj], acc_data.msg_wrong_date_2, tjq('#check_availability_form .alert-error'));
            return false;
        }
        var date_to  = date_to_obj.val();
        if (! date_to) {
            trav_field_validation_error([date_to_obj], acc_data.msg_wrong_date_3, tjq('#check_availability_form .alert-error'));
            return false;
        }

        var one_day=1000*60*60*24;
        var date_from_date = date_from_obj.datepicker("getDate");
        var date_to_date = date_to_obj.datepicker("getDate");
        var today = new Date();
        today.setDate(today.getDate() - 1);
        if (date_from_date < today) {
            trav_field_validation_error([tjq('input[name="date_from"]')], acc_data.msg_wrong_date_6, tjq('#check_availability_form .alert-error'));
            return false;
        }
        date_from_date = date_from_date.getTime();
        date_to_date = date_to_date.getTime();

        if (date_from_date + one_day * acc_data.minimum_stay - date_to_date > 0) {
            var msg = acc_data.msg_wrong_date_5;
            if (date_from_date >= date_to_date) { msg = acc_data.msg_wrong_date_4; }
            trav_field_validation_error([date_from_obj,date_to_obj], msg, tjq('#check_availability_form .alert-error'));
            return false;
        }

        booking_data = tjq("#check_availability_form").serialize();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: booking_data,
            success: function(response){
                if (response.success == 1) {
                    tjq('#hotel-features .room-list').html(response.result);
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
        tjq('#hotel-features .tabs a[href="#hotel-write-review"]').tab('show');
    });

    //show price button click action
    tjq('.room-list').on('click', '.btn-show-price', function(e) {
        e.preventDefault();
        trav_show_modal(0, acc_data.msg_wrong_date_1);
        return false;
    });

    // book now action
    tjq('.room-list').on('click', '.btn-book-now', function(e) {
        e.preventDefault();
        if (acc_data.booking_url) {
            var room_type_id = tjq(this).data('room-type-id');
            tjq('input[name="action"]').remove();
            booking_data = tjq("#check_availability_form").serialize();
            var form = tjq('<form method="get" action="' + acc_data.booking_url + '"></form>');
            if ( acc_data.lang ) {
                form.append('<input type="hidden" name="lang" value="' + acc_data.lang + '">');
            }
            form.append('<input type="hidden" name="booking_data" value="' + escape(booking_data + '&room_type_id=' + room_type_id) + '">');
            
            tjq("body").append(form);
            form.submit();
        } else {
            alert(acc_data.msg_no_booking_page);
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
                'action' : 'acc_add_to_wishlist',
                'accommodation_id' : acc_data.acc_id
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
                'accommodation_id' : acc_data.acc_id,
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
                'action': 'acc_get_more_reviews',
                'accommodation_id' : acc_data.acc_id,
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
            tjq('#hotel-write-review .alert').removeClass('alert-success');
            tjq('#hotel-write-review .alert').addClass('alert-error');
            var msg = "Please provide ratings for every category greater than 1 star.";
            trav_field_validation_error([tjq('.overall-rating .detailed-rating')], msg, tjq('#hotel-write-review .alert'));
            return false;
        }

        tjq('#review-form .validation-field').each(function() {
            if (! tjq(this).val()) {
                var msg = tjq(this).data('error-message');
                trav_field_validation_error([tjq(this)], msg, tjq('#hotel-write-review .alert-error'));
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
                    tjq('#hotel-write-review .alert').addClass('alert-success');
                    tjq('#hotel-write-review .alert').removeClass('alert-error');
                    trav_show_modal(1, response.title, response.result);
                    /*var msg = 'Thank you! Your review has been submitted successfully.';
                    trav_field_validation_error([], msg, tjq('#hotel-write-review .alert'));
                    tjq('.submit-review').hide();*/
                } else {
                    tjq('#hotel-write-review .alert').removeClass('alert-success');
                    tjq('#hotel-write-review .alert').addClass('alert-error');
                    trav_show_modal(0, response.result, '');
                    /*var msg = response.result;
                    trav_field_validation_error([], msg, tjq('#hotel-write-review .alert'));*/
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

    tjq("#select-month, #select-room-type").change(function() {
        var selected_year_month = tjq("#select-month").val();
        var year = parseInt(selected_year_month.split("-")[0]);
        var month = parseInt(selected_year_month.split("-")[1]);
        var selected_room_type = tjq("#select-room-type").val();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            //dataType: "json",
            data: 
            {
                'action': 'acc_get_month_vacancies',
                'accommodation_id' : acc_data.acc_id,
                'year' : year,
                'month' : month,
                'room_type' : selected_room_type
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

//init map
function init_map() {
    var map = null;
    var panorama = null;
    if (acc_data && acc_data.location) {

        var loc = acc_data.location.split(",");
        var fenway = new google.maps.LatLng(loc[0], loc[1]);
        var mapOptions = {
            center: fenway,
            zoom: 12
        };
        var panoramaOptions = {
            position: fenway,
            pov: {
                heading: 34,
                pitch: 10
            }
        };
        google.maps.event.addDomListener(window, 'load', map_initialize);

    }

    function map_initialize() {
        tjq("#map-tab").height(tjq("#hotel-main-content").width() * 0.6);
        map = new google.maps.Map(document.getElementById('map-tab'), mapOptions);
        var marker = new google.maps.Marker({
            position: fenway,
            map: map,
        });
        panorama = new google.maps.StreetViewPanorama(document.getElementById('steet-view-tab'), panoramaOptions);
        map.setStreetView(panorama);
    }

    tjq('a[href="#map-tab"]').on('shown.bs.tab', function (e) {
        if ((typeof(panorama) === 'undefined') || (panorama == null)) {
            init_map();
            map_initialize();
        }
        var center = panorama.getPosition();
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
    });
    tjq('a[href="#steet-view-tab"]').on('shown.bs.tab', function (e) {
        if ((typeof(panorama) === 'undefined') || (panorama == null)) {
            init_map();
            map_initialize();
        }
        fenway = panorama.getPosition();
        panoramaOptions.position = fenway;
        panorama = new google.maps.StreetViewPanorama(document.getElementById('steet-view-tab'), panoramaOptions);
        map.setStreetView(panorama);
    });
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

                var review_marks = Object.keys(acc_data.review_labels);
                review_marks.sort(function(a, b){return b-a});
                tjq.each(review_marks, function(index, review_mark) {
                    if (review_mark < total / (review_values.length)) {
                        tjq('.star-rating .status').html(acc_data.review_labels[review_mark]);
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