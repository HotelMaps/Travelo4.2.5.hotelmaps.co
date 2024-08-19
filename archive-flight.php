<?php
/**
* Flight Archive Template
 */
get_header();

global $trav_options, $search_max_flighters, $language_count;

$location_from = isset($_REQUEST['location_from']) ? sanitize_text_field( $_REQUEST['location_from'] ) : '';
$location_to = isset($_REQUEST['location_to']) ? sanitize_text_field( $_REQUEST['location_to'] ) : '';
$adults = ( isset( $_REQUEST['adults'] ) && is_numeric( $_REQUEST['adults'] ) ) ? sanitize_text_field( $_REQUEST['adults'] ) : 1;
$min_price = ( isset( $_REQUEST['min_price'] ) && is_numeric( $_REQUEST['min_price'] ) ) ? sanitize_text_field( $_REQUEST['min_price'] ) : 0;
$max_price = ( isset( $_REQUEST['max_price'] ) && ( is_numeric( $_REQUEST['max_price'] ) || ( $_REQUEST['max_price'] == 'no_max' ) ) ) ? sanitize_text_field( $_REQUEST['max_price'] ) : 'no_max';
$flight_types = ( isset( $_REQUEST['flight_types'] ) ) ? ( is_array( $_REQUEST['flight_types'] ) ? $_REQUEST['flight_types'] : array( $_REQUEST['flight_types'] ) ):array();
$air_lines = ( isset( $_REQUEST['air_lines'] ) ) ? ( is_array( $_REQUEST['air_lines'] ) ? $_REQUEST['air_lines'] : array( $_REQUEST['air_lines'] ) ):array();
$flight_stops = ( isset( $_REQUEST['flight_stops'] ) ) ? ( is_array( $_REQUEST['flight_stops'] ) ? $_REQUEST['flight_stops'] : array( $_REQUEST['flight_stops'] ) ):array();

$flight_date = isset( $_REQUEST['flight_date'] ) ? trav_sanitize_date( $_REQUEST['flight_date'] ) : '';
$return_date = isset( $_REQUEST['return_date'] ) ? trav_sanitize_date( $_REQUEST['return_date'] ) : '';
$trip_type = isset( $_REQUEST['trip_type'] ) ? sanitize_text_field( $_REQUEST['trip_type'] ) : 'one_way';

$results = trav_flight_get_search_result( $location_from, $location_to, $flight_date, $adults, $min_price, $max_price, $flight_types, $air_lines, $flight_stops );

$flight_list = array();
$return_list = array();

if ( ! empty( $results ) ) {
    $count = count( $results );
    foreach ( $results as $result ) {
        $flight_list[] = $result['flight_id'];
    } 

    if ( $trip_type == 'round_trip' ) {
        $return_results = trav_flight_get_search_result( $location_to, $location_from, $return_date, $adults, $min_price, $max_price, $flight_types, $air_lines, $flight_stops );

        if ( ! empty( $return_results ) ) {
        foreach ( $return_results as $result ) {
            $return_list[] = $result['flight_id'];
        }

        $count = count( $results ) * count( $return_results );
        } else {
            $count = 0;
        }            
    }

} else {
    $count = 0;
}


global $before_article, $after_article, $flight_list, $return_list;

$before_article = '';
$after_article = '';

?>

<section id="content">
    <div class="container">
        <div id="main">
            <div class="row">
                <div class="col-sm-4 col-md-3">
                    <h4 class="search-results-title">
                        <i class="soap-icon-search"></i><b><?php echo esc_html( $count ); ?></b> <?php _e( 'results found.', 'trav' ) ?>
                    </h4>
                    <div class="toggle-container style1 filters-container">
                        <div class="panel arrow-right">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#modify-search-panel" class=""><?php _e( 'Modify Search', 'trav' ) ?></a>
                            </h4>
                            <div id="modify-search-panel" class="panel-collapse collapse in">
                                <div class="panel-content">
                                    <form role="search" method="get" class="flight-searchform" action="<?php echo esc_url( get_post_type_archive_link( 'flight' ) ); ?>">
                                        <?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
                                            <input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ) ?>">
                                        <?php } ?>
                                        <div class="form-group">
                                            <label><?php _e( 'From','trav' ); ?></label>
                                            <div class="selector">
                                                <select name="location_from" class="full-width">
                                                    <?php
                                                        $flight_locations = get_terms( 'flight_location', array( 'hide_empty' => false, ) );
                                                        foreach ( $flight_locations as $flight_location ) {
                                                            echo '<option value="' . esc_attr( $flight_location->term_id ) . '" ' . selected( $flight_location->term_id, $location_from ) . ' >' . esc_html( $flight_location->name ) . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e( 'To','trav' ); ?></label>
                                            <div class="selector">
                                                <select name="location_to" class="full-width">
                                                    <?php
                                                        foreach ( $flight_locations as $flight_location ) {
                                                            echo '<option value="' . esc_attr( $flight_location->term_id ) . '" ' . selected( $flight_location->term_id, $location_to ) . ' >' . esc_html( $flight_location->name ) . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label><?php _e( 'Trip Type','trav' ); ?></label>
                                            <div class="selector">
                                                <select name="trip_type" class="full-width">
                                                    <option value="one_way" <?php selected( $trip_type, 'one_way' ); ?> ><?php echo esc_html__( 'ONE-WAY', 'trav' ); ?></option>
                                                    <option value="round_trip" <?php selected( $trip_type, 'round_trip' ); ?> ><?php echo esc_html__( 'ROUND-TRIP', 'trav' ); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <?php /* <div class="form-check form-check-inline trip-type-wrapper form-group">
                                            <label class="btn btn-primary">
                                                <input type="radio" name="trip_type" class="form-check-input" value="one_way" <?php checked( $trip_type, 'one_way' ); ?> />
                                                <?php echo esc_html__( 'ONE-WAY', 'trav' ); ?>
                                            </label>
                                            <label class="btn btn-default">
                                                <input type="radio" name="trip_type" class="" value="round_trip" <?php checked( $trip_type, 'round_trip' ); ?> />
                                                <?php echo esc_html__( 'ROUND-TRIP', 'trav' ); ?>
                                            </label>
                                        </div> */ ?>

                                        <div class="search-when" >
                                            <div class="form-group">
                                                <label><?php _e( 'Strat When','trav' ); ?></label>
                                                <div class="datepicker-wrap from-today">
                                                    <input name="flight_date" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $flight_date ); ?>" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="search-return" >
                                            <div class="form-group">
                                                <label><?php _e( 'Return When','trav' ); ?></label>
                                                <div class="datepicker-wrap from-today">
                                                    <input name="return_date" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $return_date ); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label><?php _e( 'Adults','trav' ); ?></label>
                                            <div class="selector">
                                                <select name="adults" class="full-width">
                                                    <?php
                                                        for ( $i = 1; $i <= $search_max_flighters; $i++ ) {
                                                            $selected = '';
                                                            if ( $i == $adults ) $selected = 'selected';
                                                            echo '<option value="' . esc_attr( $i ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <br />
                                        <button class="btn-medium icon-check uppercase full-width"><?php _e( 'search again', 'trav' ) ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php if ( $trav_options['flight_enable_price_filter'] ) : ?>
                        <div class="panel style1 arrow-right">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#price-filter" class="collapsed"><?php _e( 'Price', 'trav' );?></a>
                            </h4>
                            <div id="price-filter" class="panel-collapse collapse">
                                <div class="panel-content">
                                    <div id="price-range" data-slide-last-val="<?php echo esc_attr( ( ! empty($trav_options['flight_price_filter_max']) && is_numeric($trav_options['flight_price_filter_max']) ) ? $trav_options['flight_price_filter_max'] :200 ) ?>" data-slide-step="<?php echo esc_attr( ( ! empty($trav_options['flight_price_filter_step']) && is_numeric($trav_options['flight_price_filter_step']) ) ? $trav_options['flight_price_filter_step'] :50 ) ?>" data-def-currency="<?php echo esc_attr( trav_get_site_currency_symbol() );?>" data-min-price="<?php echo esc_attr( $min_price ); ?>" data-max-price="<?php echo esc_attr( $max_price ); ?>" data-url-noprice="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price' ) ) ); ?>"></div>
                                    <br />
                                    <span class="min-price-label pull-left"></span>
                                    <span class="max-price-label pull-right"></span>
                                    <div class="clearer"></div>
                                </div><!-- end content -->
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ( $trav_options['flight_enable_flight_stop_filter'] ) : ?>
                        <div class="panel style1 arrow-right">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#flight-stops-filter" class="<?php echo empty( $flight_stops )?'collapsed':''?>"><?php echo esc_html__( 'Flight Stops', 'trav' ); ?></a>
                            </h4>
                            <div id="flight-stops-filter" data-url-noflight_stop="<?php echo esc_url( remove_query_arg( array( 'flight_stops' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $flight_stops )?'':'in'?>">
                                <div class="panel-content">
                                    <ul class="check-square filters-option">
                                        <?php
                                            $selected = empty( $flight_stops )?' active':'';
                                            echo '<li class="all-stops' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '</a></li>';
                                            $all_flight_stops = get_terms( 'flight_stop', array( 'hide_empty' => 0 ) );
                                            foreach ( $all_flight_stops as $each_flight_stop ) {
                                                $selected = ( ( is_array( $flight_stops ) && in_array( $each_flight_stop->term_id, $flight_stops ) ) )?' class="active"':'';
                                                echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_flight_stop->term_id ) . '"><a href="#">' . esc_html( $each_flight_stop->name ) . '</a></li>';
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ( $trav_options['flight_enable_flight_type_filter'] ) : ?>
                        <div class="panel style1 arrow-right">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#flight-types-filter" class="<?php echo empty( $flight_types )?'collapsed':''?>"><?php _e( 'Flight Type', 'trav' ) ?></a>
                            </h4>
                            <div id="flight-types-filter" data-url-noflight_type="<?php echo esc_url( remove_query_arg( array( 'flight_types' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $flight_types )?'':'in'?>">
                                <div class="panel-content">
                                    <ul class="check-square filters-option">
                                        <?php
                                            $selected = empty( $flight_types)?' active':'';
                                            echo '<li class="all-types' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '</a></li>';
                                            $all_flight_types = get_terms( 'flight_type', array('hide_empty' => 0) );
                                            foreach ( $all_flight_types as $each_flight_type ) {
                                                $selected = ( ( is_array( $flight_types ) && in_array( $each_flight_type->term_id, $flight_types ) ) )?' class="active"':'';
                                                echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_flight_type->term_id ) . '"><a href="#">' . esc_html( $each_flight_type->name ) . '</a></li>';
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ( $trav_options['flight_enable_air_line_filter'] ) : ?>
                        <div class="panel style1 arrow-right">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#air-lines-filter" class="<?php echo empty( $air_lines )?'collapsed':''?>"><?php _e( 'Air Line', 'trav' ) ?></a>
                            </h4>
                            <div id="air-lines-filter" data-url-noair_line="<?php echo esc_url( remove_query_arg( array( 'air_lines' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $air_lines )?'':'in'?>">
                                <div class="panel-content">
                                    <ul class="check-square filters-option">
                                        <?php
                                            $selected = empty( $air_lines)?' active':'';
                                            echo '<li class="all-air_lines' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '</a></li>';
                                            $all_air_lines = get_terms( 'air_line', array('hide_empty' => 0) );
                                            foreach ( $all_air_lines as $each_air_line ) {
                                                $selected = ( ( is_array( $air_lines ) && in_array( $each_air_line->term_id, $air_lines ) ) )?' class="active"':'';
                                                echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_air_line->term_id ) . '"><a href="#">' . esc_html( $each_air_line->name ) . '</a></li>';
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if ( $trip_type == 'one_way' ) : ?>
                    <div class="col-sm-8 col-md-9">
                        <?php if ( ! empty( $results ) ) { ?>
                            <div class="flight-list listing-style3 flight">
                                <?php trav_get_template( 'flight-list.php', '/templates/flight/'); ?>
                            </div>
                        <?php } else { ?>
                            <div class="travelo-box">
                                <p><?php _e( 'No available flights', 'trav' );?></p>
                                <p><?php _e( 'Please confirm your search fields such as From, To, When, etc in sidebar.', 'trav' );?></p>
                            </div>
                        <?php } ?>
                    </div>
                <?php else : ?>
                    
                    <div class="col-sm-8 col-md-9">
                        <?php if ( ! empty( $count ) ) { ?>
                            <div class="flight-list listing-style3 flight">
                                <?php trav_get_template( 'round-flight-list.php', '/templates/flight/'); ?>
                            </div>
                        <?php } else { ?>
                            <div class="travelo-box">
                                <p><?php _e( 'No available flights', 'trav' );?></p>
                                <p><?php _e( 'Please confirm your search fields such as From, To, When, etc in sidebar.', 'trav' );?></p>
                            </div>
                        <?php } ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php

get_footer();