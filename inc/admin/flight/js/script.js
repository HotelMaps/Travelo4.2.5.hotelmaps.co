$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    $('#flight_id').select2({
        placeholder: "Select a Flight",
        allowClear: true,
        width: "250px"
    });
    $('#air_line').select2({
        placeholder: "Select a Air Line",
        allowClear: true,
        width: "250px",
    });
    $('#location_from').select2({
        placeholder: "Select a Departure Location",
        allowClear: true,
        width: "250px",
    });
    $('#location_to').select2({
        placeholder: "Select a Arrival Location",
        allowClear: true,
        width: "250px",
    });
    $('#flight_date').datepicker({ dateFormat: "yy-mm-dd" });
    
    $('#return_flight_id').select2({
        placeholder: "Select a Flight",
        allowClear: true,
        width: "250px"
    });
    $('#return_air_line').select2({
        placeholder: "Select a Air Line",
        allowClear: true,
        width: "250px",
    });
    $('#return_location_from').select2({
        placeholder: "Select a Departure Location",
        allowClear: true,
        width: "250px",
    });
    $('#return_location_to').select2({
        placeholder: "Select a Arrival Location",
        allowClear: true,
        width: "250px",
    });
    $('#return_flight_date').datepicker({ dateFormat: "yy-mm-dd" });

    // booking manage(add/edit) page
    if ( $('.trav_booking_manage_table').length ) {
        //
    }

    // booking list page
    if ( $('#flight-bookings-filter').length ) {
        $('#booking-filter').click(function(){
            var flightId = $('#flight_id').val();
            var air_line = $('#air_line').val();
            var location_from = $('#location_from').val();
            var location_to = $('#location_to').val();            
            var flightDate = $('#flight_date').val();
            var booking_no = $('#booking_no').val();
            var status = $('#status').val();
            var loc_url = 'edit.php?post_type=flight&page=flight_bookings';
            if (flightId) loc_url += '&flight_id=' + flightId;
            if (air_line) loc_url += '&air_line=' + air_line;
            if (location_from) loc_url += '&location_from=' + location_from;
            if (location_to) loc_url += '&location_to=' + location_to;
            if (flightDate) loc_url += '&flight_date=' + flightDate;
            if (booking_no) loc_url += '&booking_no=' + booking_no;
            if (status) loc_url += '&status=' + status;
            document.location = loc_url;
        });
    }

    $('.row-actions .delete a').click(function(){
        var r = confirm("It will be deleted permanetly. Do you want to delete it?");
        if(r == false) {
            return false;
        }
    });


});

var submitting = false;
function manage_schedule_validateForm() {
    "use strict";
    if ( submitting == true ) return false;
    if( '' == $('#flight_id').val()){
        alert('Please select a flight');
        return false;
    }
    submitting = true;
    return true;
}

function manage_booking_validateForm() {
    return manage_schedule_validateForm(); //same functions with schedule validation
}