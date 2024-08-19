<?php
/*
 * Flight List
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $flight_list, $return_list, $trav_options;

$flight_date = isset( $_REQUEST['flight_date'] ) ? trav_sanitize_date( $_REQUEST['flight_date'] ) : '';
$return_flight_date = isset( $_REQUEST['return_date'] ) ? trav_sanitize_date( $_REQUEST['return_date'] ) : '';
$adults = ( isset( $_REQUEST['adults'] ) && is_numeric( $_REQUEST['adults'] ) ) ? sanitize_text_field( $_REQUEST['adults'] ) : 1;
$trip_type = isset( $_REQUEST['trip_type'] ) ? sanitize_text_field( $_REQUEST['trip_type'] ) : 'one_way';

foreach( $flight_list as $flight_id ) {
    $flight_id = trav_flight_clang_id( $flight_id );
    $price = get_post_meta( $flight_id, 'trav_flight_price', true );
    $departure_time = get_post_meta( $flight_id, 'trav_flight_departure_time', true );
    $arrival_time = get_post_meta( $flight_id, 'trav_flight_arrival_time', true );
    $duration = get_post_meta( $flight_id, 'trav_flight_duration', true );    
    
    $air_line = wp_get_post_terms( $flight_id, 'air_line', array( "fields" => "all" ) );
    if ( ! empty( $air_line ) ) {
        $air_line = $air_line[0];
        $air_line->img = get_tax_meta( $air_line->term_id, 'air_line_image', true );
    }

    $flight_stop = wp_get_post_terms( $flight_id, 'flight_stop', array( "fields" => "all" ) );
    if ( ! empty( $flight_stop ) ) {
        $flight_stop = $flight_stop[0];
    }

    $flight_type = wp_get_post_terms( $flight_id, 'flight_type', array( "fields" => "all" ) );
    if ( ! empty( $flight_type ) ) {
        $flight_type = $flight_type[0];
    }

    foreach( $return_list as $return_flight_id ) {

        $return_flight_id = trav_flight_clang_id( $return_flight_id );
        $return_price = get_post_meta( $return_flight_id, 'trav_flight_price', true );
        $return_departure_time = get_post_meta( $return_flight_id, 'trav_flight_departure_time', true );
        $return_arrival_time = get_post_meta( $return_flight_id, 'trav_flight_arrival_time', true );
        $return_duration = get_post_meta( $return_flight_id, 'trav_flight_duration', true );
       
        $return_air_line = wp_get_post_terms( $return_flight_id, 'air_line', array( "fields" => "all" ) );
        if ( ! empty( $return_air_line ) ) {
            $return_air_line = $return_air_line[0];
            $return_air_line->img = get_tax_meta( $return_air_line->term_id, 'air_line_image', true );
        }

        $return_flight_stop = wp_get_post_terms( $return_flight_id, 'flight_stop', array( "fields" => "all" ) );
        if ( ! empty( $return_flight_stop ) ) {
            $return_flight_stop = $return_flight_stop[0];
        }

        $return_flight_type = wp_get_post_terms( $return_flight_id, 'flight_type', array( "fields" => "all" ) );
        if ( ! empty( $return_flight_type ) ) {
            $return_flight_type = $return_flight_type[0];
        }

        $query_args = array(
            'flight_date' => $flight_date,
            'flight_id' => $flight_id,
            'return_flight_date' => $return_flight_date,
            'return_flight_id' => $return_flight_id,
            'adults' => $adults,
            'trip_type' => $trip_type,
        );
        if ( ! empty( $trav_options['flight_booking_page'] ) ) {
            $url = esc_url( add_query_arg( $query_args, trav_get_permalink_clang( $trav_options['flight_booking_page'] ) ) );
        } else {
            $url = "#";
        }

        ?>

        <article class="box">
            <div class="round-flight">
                <figure class="col-xs-3 col-sm-2">
                    <?php if ( ! empty( $air_line->img ) ) : ?>
                        <span><img width="94" height="90" alt="" src="<?php echo $air_line->img['src']; ?>"></span>
                    <?php endif; ?>
                </figure>
                <div class="details col-xs-9 col-sm-10">
                    <div class="details-wrapper">
                        <div class="first-row">
                            <div>
                                <h4 class="box-title"><?php echo esc_html( get_the_title( $flight_id ) ); ?><small><?php echo ( ! empty( $air_line ) ) ? esc_html( $air_line->name ) : ''; ?></small></h4>
                                <a class="button btn-mini stop"><?php echo ( ! empty( $flight_stop ) ) ? esc_html( $flight_stop->name ) : ''; ?></a>
                                <a class="button btn-mini type"><?php echo ( ! empty( $flight_type ) ) ? esc_html( $flight_type->name ) : ''; ?></a>
                            </div>
                            <div class="hidden-price">
                                <span class="price"><small><?php echo esc_html( $adults ); ?> <?php echo esc_html__( 'PERSON(S)', 'trav' ); ?></small><?php echo esc_html( trav_get_price_field( floatval( $price + $return_price ) * $adults ) ); ?></span>
                            </div>
                        </div>
                        <div class="second-row">
                            <div class="time">
                                <div class="take-off col-sm-4">
                                    <div class="icon"><i class="soap-icon-plane-right yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'Take off', 'trav' ); ?></span><br /><?php echo date( 'D M d, Y h:i A', trav_strtotime( $flight_date . ' ' . $departure_time ) ); ?>
                                    </div>
                                </div>
                                <div class="landing col-sm-4">
                                    <div class="icon"><i class="soap-icon-plane-right yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'landing', 'trav' ); ?></span><br /><?php echo date( 'D M d, Y h:i A', trav_strtotime( $flight_date . ' ' . $departure_time  ) + trav_strtotime( $flight_date . ' ' . $duration ) - trav_strtotime( $flight_date ) ); ?>
                                    </div>
                                </div>
                                <div class="total-time col-sm-4">
                                    <div class="icon"><i class="soap-icon-clock yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'total time', 'trav' ); ?></span><br /><?php echo date( 'G', trav_strtotime( $duration ) ) . esc_html__( 'HOURS'); ?> <?php echo ( date( 'i', trav_strtotime( $duration ) ) != '00' ) ? date( 'i', trav_strtotime( $duration ) ) . esc_html__( 'MINUTES', 'trav' ) : ''; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="action">
                                <span class="price"><small><?php echo esc_html( $adults ); ?> <?php echo esc_html__( 'PERSON(S)', 'trav' ); ?></small><?php echo esc_html( trav_get_price_field( floatval( $price + $return_price ) * $adults ) ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="round-flight">
                <figure class="col-xs-3 col-sm-2">
                    <?php if ( ! empty( $return_air_line->img ) ) : ?>
                        <span><img width="94" height="90" alt="" src="<?php echo $return_air_line->img['src']; ?>"></span>
                    <?php endif; ?>
                </figure>
                <div class="details col-xs-9 col-sm-10">
                    <div class="details-wrapper">
                        <div class="first-row">
                            <div>
                                <h4 class="box-title"><?php echo esc_html( get_the_title( $return_flight_id ) ); ?><small><?php echo ( ! empty( $return_air_line ) ) ? esc_html( $return_air_line->name ) : ''; ?></small></h4>
                                <a class="button btn-mini stop"><?php echo ( ! empty( $return_flight_stop ) ) ? esc_html( $return_flight_stop->name ) : ''; ?></a>
                                <a class="button btn-mini type"><?php echo ( ! empty( $return_flight_type ) ) ? esc_html( $return_flight_type->name ) : ''; ?></a>
                                <div class="amenities">
                                    
                                </div>
                            </div>
                            <div class="hidden-price">
                                <span class="price"><small><?php echo esc_html( $adults ); ?> <?php echo esc_html__( 'PERSON(S)', 'trav' ); ?></small><?php echo esc_html( trav_get_price_field( floatval( $price + $return_price ) * $adults ) ); ?></span>
                            </div>
                        </div>
                        <div class="second-row">
                            <div class="time">
                                <div class="take-off col-sm-4">
                                    <div class="icon"><i class="soap-icon-plane-right yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'Take off', 'trav' ); ?></span><br /><?php echo date( 'D M d, Y h:i A', trav_strtotime( $return_flight_date . ' ' . $return_departure_time ) ); ?>
                                    </div>
                                </div>
                                <div class="landing col-sm-4">
                                    <div class="icon"><i class="soap-icon-plane-right yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'landing', 'trav' ); ?></span><br /><?php echo date( 'D M d, Y h:i A', trav_strtotime( $return_flight_date . ' ' . $return_departure_time  ) + trav_strtotime( $return_flight_date . ' ' . $return_duration ) - trav_strtotime( $return_flight_date ) ); ?>
                                    </div>
                                </div>
                                <div class="total-time col-sm-4">
                                    <div class="icon"><i class="soap-icon-clock yellow-color"></i></div>
                                    <div>
                                        <span class="skin-color"><?php echo esc_html__( 'total time', 'trav' ); ?></span><br /><?php echo date( 'G', trav_strtotime( $return_duration ) ) . esc_html__( 'HOURS'); ?> <?php echo ( date( 'i', trav_strtotime( $return_duration ) ) != '00' ) ? date( 'i', trav_strtotime( $return_duration ) ) . esc_html__( 'MINUTES', 'trav' ) : ''; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="action">
                                <a href="<?php echo esc_url( $url ); ?>" class="button btn-small full-width"><?php echo esc_html__( 'BOOK NOW', 'trav' ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <?php 
    }
    
}