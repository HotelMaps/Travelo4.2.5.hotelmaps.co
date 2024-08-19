$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    // vacancies manage(add/edit) page
    $('#car_id').select2({
        placeholder: "Select a Car",
        width: "250px"
    });
    $('#date_from').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to').datepicker({ dateFormat: "yy-mm-dd" });
    
    $('#date_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_from_filter').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to_filter').datepicker({ dateFormat: "yy-mm-dd" });

    $('#car_filter').select2({
        placeholder: "Filter by Car",
        allowClear: true,
        width: "240px"
    });

    $('#booking-filter').click(function(){
        var car_Id = $('#car_filter').val();
        var dateFrom = $('#date_from_filter').val();
        var dateTo = $('#date_to_filter').val();
        var booking_no = $('#booking_no_filter').val();
        var status = $('#status_filter').val();
        var loc_url = 'edit.php?post_type=car&page=car_bookings';
        if (car_Id) loc_url += '&car_id=' + car_Id;
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
function manage_car_validateForm() {
    "use strict";
    if ( submitting == true ) return false;
    if( '' == $('#car_id').val()){
        alert('Please select a car');
        return false;
    }
    submitting = true;
    return true;
}

function manage_booking_validateForm() {
    return manage_car_validateForm(); //same functions with car validation
}