$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    $('#tour_id').select2({
        placeholder: "Select a Tour",
        allowClear: true,
        width: "250px"
    });
    $('#schedule_type').select2({
        placeholder: "Select a Schedule Type",
        allowClear: true,
        width: "250px",
    });
    $('#tour_date').datepicker({ dateFormat: "yy-mm-dd" });
    $('#tour_id').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'tour_get_schedule_type',
                'tour_id' : $(this).val()
            },
            success: function(response){
                $('#schedule_type').html(response);
                $('#schedule_type').select2({
                    placeholder: "Select a Schedule Type",
                    allowClear: true,
                    width: "250px",
                });
                if ( response ) {
                    $('.schedule_type_wrapper').show();
                } else {
                    $('.schedule_type_wrapper').hide();
                }
            }
        });
    });
    // schedule manage(add/edit) page
    if ( $('.trav_tour_schedule_manage_table').length ) {
        $('#date_to').datepicker({ dateFormat: "yy-mm-dd" });
        $('#is_daily').change(function() {
            $('.end_date').toggle(this.checked);
            if ($('#is_daily').attr('checked')) {
                $('.start_date th').html('Start Date');
            } else {
                $('.start_date th').html('Tour Date');
            }
        });
        if ($('#is_daily').attr('checked')) {
            $('.start_date th').html('Start Date');
            $('.end_date').show();
        }
        $('#per_person_yn').change(function() {
            $('.child_price').toggle(this.checked);
            if ($('#per_person_yn').attr('checked')) {
                $('.price th').html('Price Per Adult');
            } else {
                $('.price th').html('Price');
            }
        });
        if ($('#per_person_yn').attr('checked')) {
            $('.price th').html('Price Per Adult');
            $('.child_price').show();
        }
    }

    // schedule list page
    if ( $('#tour-schedules-filter').length ) {
        $('#schedule-filter').click(function(){
            var tourId = $('#tour_id').val();
            var scheduleType = $('#schedule_type').val();
            var filter_date = $('#tour_date').val();
            var loc_url = 'edit.php?post_type=tour&page=schedules';
            if (tourId) loc_url += '&tour_id=' + tourId;
            if (scheduleType) loc_url += '&st_id=' + scheduleType;
            if (filter_date) loc_url += '&date=' + filter_date;
            document.location = loc_url;
        });
    }

    // booking manage(add/edit) page
    if ( $('.trav_booking_manage_table').length ) {
        //
    }

    // booking list page
    if ( $('#tour-bookings-filter').length ) {
        $('#booking-filter').click(function(){
            var tourId = $('#tour_id').val();
            var scheduleType = $('#schedule_type').val();
            var tourDate = $('#tour_date').val();
            var booking_no = $('#booking_no').val();
            var status = $('#status').val();
            var loc_url = 'edit.php?post_type=tour&page=tour_bookings';
            if (tourId) loc_url += '&tour_id=' + tourId;
            if (scheduleType) loc_url += '&st_id=' + scheduleType;
            if (tourDate) loc_url += '&tour_date=' + tourDate;
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
    if( '' == $('#tour_id').val()){
        alert('Please select a tour');
        return false;
    }
    submitting = true;
    return true;
}

function manage_booking_validateForm() {
    return manage_schedule_validateForm(); //same functions with schedule validation
}