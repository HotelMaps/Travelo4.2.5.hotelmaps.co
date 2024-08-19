$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    // vacancies manage(add/edit) page
    $('#accommodation_id').select2({
        placeholder: "Select an Accommodation",
        width: "250px"
    });
    $('#room_type_id').select2({
        placeholder: "Select a Room Type",
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

    $('#accommodation_id').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'acc_get_acc_room_list',
                'accommodation_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var room_type_id = $('#room_type_id').val();
                    $('#room_type_id').html(response);
                    $('#room_type_id').val(room_type_id);
                    $('#room_type_id').select2({
                        placeholder: "Select a Room Type",
                        width: "250px",
                    });
                }
            }
        });
    });

    $('#room_type_id').change(function(){
        if ( ! $('#accommodation_id').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'acc_get_room_acc_id',
                    'room_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#accommodation_id').val(response).change();
                    }
                }
            });
        }
    });

    // vacancies list page
    $('#accommodation_filter').select2({
        placeholder: "Filter by Accommodation",
        allowClear: true,
        width: "240px"
    });
    $('#room_type_filter').select2({
        placeholder: "Filter by Room Type",
        allowClear: true,
        width: "240px"
    });
    $('#date_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_from_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to_filter').datepicker({ dateFormat: "yy-mm-dd" });

    $('#accommodation_filter').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'acc_get_acc_room_list',
                'accommodation_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var room_type_id = $('#room_type_filter').val();
                    $('#room_type_filter').html(response);
                    $('#room_type_filter').val(room_type_id);
                    $('#room_type_filter').select2({
                        placeholder: "Filter by Room Type",
                        allowClear: true,
                        width: "240px",
                    });
                }
            }
        });
    });

    $('#room_type_filter').change(function(){
        if ( ! $('#accommodation_filter').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'acc_get_room_acc_id',
                    'room_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#accommodation_filter').val(response).change();
                    }
                }
            });
        }
    });

    $('#vacancy-filter').click(function(){
        var accommodationId = $('#accommodation_filter').val();
        var roomTypeId = $('#room_type_filter').val();
        var filter_date = $('#date_filter').val();
        var loc_url = 'edit.php?post_type=accommodation&page=vacancies';
        if (accommodationId) loc_url += '&accommodation_id=' + accommodationId;
        if (roomTypeId) loc_url += '&room_type_id=' + roomTypeId;
        if (filter_date) loc_url += '&date=' + filter_date;
        document.location = loc_url;
    });

    $('#booking-filter').click(function(){
        var accommodationId = $('#accommodation_filter').val();
        var roomTypeId = $('#room_type_filter').val();
        var dateFrom = $('#date_from_filter').val();
        var dateTo = $('#date_to_filter').val();
        var booking_no = $('#booking_no_filter').val();
        var status = $('#status_filter').val();
        var loc_url = 'edit.php?post_type=accommodation&page=bookings';
        if (accommodationId) loc_url += '&accommodation_id=' + accommodationId;
        if (roomTypeId) loc_url += '&room_type_id=' + roomTypeId;
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

        var clone_last = $(this).closest('table').find('.clone-field:last');
        var clone_obj = clone_last.clone();
        clone_obj.insertAfter( clone_last );
        var input_obj = clone_obj.find( 'input' );

        // Reset value
        input_obj.val( '' );

        // Get the field name, and increment
        input_obj.each( function(index) {
            var name = $(this).attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
            {
                return '[' + ( parseInt( p1 ) + 1 ) + ']';
            } );

            // Update the "name" attribute
            $(this).attr( 'name', name );
        });

        var select_obj = clone_obj.find( 'select' );
        select_obj.each( function(index) {
            var name = select_obj.attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
            {
                return '[' + ( parseInt( p1 ) + 1 ) + ']';
            } );
            // Update the "name" attribute
            $(this).attr( 'name', name );
            $(this).find("option:selected").prop("selected", false);
        });

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
function manage_vacancy_validateForm() {
    "use strict";

    if ( submitting == true ) 
        return false;

    if( '' == $('#accommodation_id').val() ) {
        alert('Please select an accommodation');
        return false;
    } else if( '' == $('#room_type_id').val() ) {
        alert('Please select a room type');
        return false;
    }

    submitting = true;

    return true;
}

function manage_booking_validateForm() {
    return manage_vacancy_validateForm(); //same functions with vacancy validation
}