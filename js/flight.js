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
	/* Flight Search Page */
	// flight stop filter
	tjq("#flight-stops-filter .filters-option li").click(function(){
		var url_noflight_stop = tjq("#flight-stops-filter").data('url-noflight_stop').replace(/&amp;/g, '&');

		if (tjq(this).hasClass('all-stops')) {
			if (! tjq(this).hasClass('active')) {
				tjq(this).toggleClass('active');

				return false;
			} else {
				tjq("#flight-stops-filter .filters-option li").removeClass('active');
				tjq(this).addClass('active');
			}
		} else {
			if (tjq("#flight-stops-filter .filters-option li.active").length == 0) {
				tjq("#flight-stops-filter .filters-option li.all-stops").addClass('active');
			} else {
				tjq("#flight-stops-filter .filters-option li.all-stops").removeClass('active');
				tjq("#flight-stops-filter .filters-option li.active").each(function(index){
					url_noflight_stop += '&flight_stops[]=' + tjq(this).data('term-id');
				});
			}
		}

		if (url_noflight_stop.indexOf("?") < 0) { url_noflight_stop = url_noflight_stop.replace(/&/, '?'); }

		window.location.href = url_noflight_stop;
	});

	// flight type filter
	tjq("#flight-types-filter .filters-option li").click(function(){
		var url_noflight_type = tjq("#flight-types-filter").data('url-noflight_type').replace(/&amp;/g, '&');

		if (tjq(this).hasClass('all-types')) {
			if (! tjq(this).hasClass('active')) {
				tjq(this).toggleClass('active');

				return false;
			} else {
				tjq("#flight-types-filter .filters-option li").removeClass('active');
				tjq(this).addClass('active');
			}
		} else {
			if (tjq("#flight-types-filter .filters-option li.active").length == 0) {
				tjq("#flight-types-filter .filters-option li.all-types").addClass('active');
			} else {
				tjq("#flight-types-filter .filters-option li.all-types").removeClass('active');
				tjq("#flight-types-filter .filters-option li.active").each(function(index){
					url_noflight_type += '&flight_types[]=' + tjq(this).data('term-id');
				});
			}
		}

		if (url_noflight_type.indexOf("?") < 0) { url_noflight_type = url_noflight_type.replace(/&/, '?'); }

		window.location.href = url_noflight_type;
	});

	// air line filter
	tjq("#air-lines-filter .filters-option li").click(function(){
		var url_noair_line = tjq("#air-lines-filter").data('url-noair_line').replace(/&amp;/g, '&');

		if (tjq(this).hasClass('all-air_lines')) {
			if (! tjq(this).hasClass('active')) {
				tjq(this).toggleClass('active');

				return false;
			} else {
				tjq("#air-lines-filter .filters-option li").removeClass('active');
				tjq(this).addClass('active');
			}
		} else {
			if (tjq("#air-lines-filter .filters-option li.active").length == 0) {
				tjq("#air-lines-filter .filters-option li.all-air_lines").addClass('active');
			} else {
				tjq("#air-lines-filter .filters-option li.all-air_lines").removeClass('active');
				tjq("#air-lines-filter .filters-option li.active").each(function(index){
					url_noair_line += '&air_lines[]=' + tjq(this).data('term-id');
				});
			}
		}

		if (url_noair_line.indexOf("?") < 0) { url_noair_line = url_noair_line.replace(/&/, '?'); }

		window.location.href = url_noair_line;
	});

	tjq("select[name='trip_type']").on( 'change', function(){
		if (tjq(this).val() == 'round_trip') {
			tjq('.search-return').show();
		} else {
			tjq('.search-return').hide();
		}
	});

	tjq("select[name='trip_type']").trigger( 'change' );
});