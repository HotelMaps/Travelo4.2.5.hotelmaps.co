$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    // schedules manage(add/edit) page
    $('#cruise_id').select2({
        placeholder: "Select a Cruise",
        width: "250px"
    });
    $('#cabin_type_id').select2({
        placeholder: "Select a Cabin Type",
        width: "250px"
    });
    $('#date_from').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to').datepicker({ dateFormat: "yy-mm-dd" });
    $('#child_cost_yn').change(function() {
        $('.child_cost').toggle(this.checked);
    });
    if ($('#child_cost_yn').attr('checked')) {
        $('.child_cost').show();
    }

    $('#cruise_id').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'cruise_get_cruise_cabin_list',
                'cruise_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var cabin_type_id = $('#cabin_type_id').val();
                    $('#cabin_type_id').html(response);
                    $('#cabin_type_id').val(cabin_type_id);
                    $('#cabin_type_id').select2({
                        placeholder: "Select a Cabin Type",
                        width: "250px",
                    });
                }
            }
        });
    });

    $('#cabin_type_id').change(function(){
        if ( ! $('#cruise_id').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'cruise_get_cabin_cruise_id',
                    'cabin_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#cruise_id').val(response).change();
                    }
                }
            });
        }
    });

    // schedules list page
    $('#cruise_filter').select2({
        placeholder: "Filter by Cruise",
        allowClear: true,
        width: "240px"
    });
    $('#cabin_type_filter').select2({
        placeholder: "Filter by Cabin Type",
        allowClear: true,
        width: "240px"
    });
    $('#date_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_from_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to_filter').datepicker({ dateFormat: "yy-mm-dd" });

    $('#cruise_filter').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'cruise_get_cruise_cabin_list',
                'cruise_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var cabin_type_id = $('#cabin_type_filter').val();
                    $('#cabin_type_filter').html(response);
                    $('#cabin_type_filter').val(cabin_type_id);
                    $('#cabin_type_filter').select2({
                        placeholder: "Filter by Cabin Type",
                        allowClear: true,
                        width: "240px",
                    });
                }
            }
        });
    });

    $('#cabin_type_filter').change(function(){
        if ( ! $('#cruise_filter').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'cruise_get_cabin_cruise_id',
                    'cabin_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#cruise_filter').val(response).change();
                    }
                }
            });
        }
    });

    $('#vacancy-filter').click(function(){
        var cruiseId = $('#cruise_filter').val();
        var cabinTypeId = $('#cabin_type_filter').val();
        var filter_date = $('#date_filter').val();
        var loc_url = 'edit.php?post_type=cruise&page=cruise_vacancies';
        if (cruiseId) loc_url += '&cruise_id=' + cruiseId;
        if (cabinTypeId) loc_url += '&cabin_type_id=' + cabinTypeId;
        if (filter_date) loc_url += '&date=' + filter_date;
        document.location = loc_url;
    });

    $('#schedule-filter').click(function(){
        var cruiseId = $('#cruise_filter').val();
        var filter_date = $('#date_filter').val();
        var loc_url = 'edit.php?post_type=cruise&page=cruise_schedules';
        if (cruiseId) loc_url += '&cruise_id=' + cruiseId;
        if (filter_date) loc_url += '&date=' + filter_date;
        document.location = loc_url;
    });

    $('#booking-filter').click(function(){
        var cruiseId = $('#cruise_filter').val();
        var cabinTypeId = $('#cabin_type_filter').val();
        var dateFrom = $('#date_from_filter').val();
        var dateTo = $('#date_to_filter').val();
        var booking_no = $('#booking_no_filter').val();
        var status = $('#status_filter').val();
        var loc_url = 'edit.php?post_type=cruise&page=cruise_bookings';
        if (cruiseId) loc_url += '&cruise_id=' + cruiseId;
        if (cabinTypeId) loc_url += '&cabin_type_id=' + cabinTypeId;
        if (dateFrom) loc_url += '&date_from=' + dateFrom;
        if (dateTo) loc_url += '&date_to=' + dateTo;
        if (booking_no) loc_url += '&booking_no=' + booking_no;
        if (status) loc_url += '&status=' + status;
        document.location = loc_url;
    });

    $('.row-actions .delete a').click(function(){
        var r = confirm("It will be deleted permanetly. Do you want to delete it?");
        if(r == false) {
            return false;
        }
    });

    toggle_remove_buttons();

    // Add more clones
    $( '.add-clone' ).on( 'click', function(e){
        e.stopPropagation();
        var clone_last = $( '.clone-field:last' );
        var clone_obj = clone_last.clone();
        clone_obj.insertAfter( clone_last );
        var input_obj = clone_obj.find( 'input' );

        // Reset value
        input_obj.val( '' );

        // Get the field name, and increment
        var name = input_obj.attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
        {
            return '[' + ( parseInt( p1 ) + 1 ) + ']';
        } );

        // Update the "name" attribute
        input_obj.attr( 'name', name );

        toggle_remove_buttons();
        return false;
    } );

    // Remove clones
    $( 'body' ).on( 'click', '.remove-clone', function(){
        // Remove clone only if there're 2 or more of them
        if ( $('.clone-field').length <= 1 ) return false;

        $(this).closest('.clone-field').remove();
        toggle_remove_buttons();
        return false;
    });

    function toggle_remove_buttons(){
        var button = $( '.clone-field .remove-clone' );
        button.length < 2 ? button.hide() : button.show();
    }
});

var submitting = false;
function manage_schedule_validateForm() {
    "use strict";
    if ( submitting == true ) return false;
    if( '' == $('#cruise_id').val()){
        alert('Please select a cruise');
        return false;
    } else if( '' == $('#cabin_type_id').val()){
        alert('Please select a cabin type');
        return false;
    }
    submitting = true;
    return true;
}

function manage_booking_validateForm() {
    return manage_schedule_validateForm(); //same functions with schedule validation
}