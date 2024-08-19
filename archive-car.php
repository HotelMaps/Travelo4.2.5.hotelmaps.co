<?php
/**
* Car Archive Template
 */
get_header();
global $current_view, $trav_options, $language_count, $search_max_passengers;

$order_array = array( 'ASC', 'DESC' );
$order_by_array = array(
		'name' => 'car_title',
		'price' => 'cast(price as unsigned)',
	);
$order_defaults = array(
		'name' => 'ASC',
		'price' => 'ASC',
	);

$s = isset($_REQUEST['s']) ? sanitize_text_field( $_REQUEST['s'] ) : '';
$passengers = isset($_REQUEST['passengers']) ? sanitize_text_field( $_REQUEST['passengers'] ) : 1;
$min_price = ( isset( $_REQUEST['min_price'] ) && is_numeric( $_REQUEST['min_price'] ) ) ? sanitize_text_field( $_REQUEST['min_price'] ) : 0;
$max_price = ( isset( $_REQUEST['max_price'] ) && ( is_numeric( $_REQUEST['max_price'] ) || ( $_REQUEST['max_price'] == 'no_max' ) ) ) ? sanitize_text_field( $_REQUEST['max_price'] ) : 'no_max';
$order_by = ( isset( $_REQUEST['order_by'] ) && array_key_exists( $_REQUEST['order_by'], $order_by_array ) ) ? sanitize_text_field( $_REQUEST['order_by'] ) : 'name';
$order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], $order_array ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';
$car_type = ( isset( $_REQUEST['car_types'] ) ) ? ( is_array( $_REQUEST['car_types'] ) ? $_REQUEST['car_types'] : array( $_REQUEST['car_types'] ) ):array();
$car_agent = ( isset( $_REQUEST['car_agents'] ) ) ? ( is_array( $_REQUEST['car_agents'] ) ? $_REQUEST['car_agents'] : array( $_REQUEST['car_agents'] ) ):array();
$preferences = ( isset( $_REQUEST['preferences'] ) && is_array( $_REQUEST['preferences'] ) ) ? $_REQUEST['preferences'] : array();
$current_view = isset( $_REQUEST['view'] ) ? sanitize_text_field( $_REQUEST['view'] ) : 'list';
$page = ( isset( $_REQUEST['page'] ) && ( is_numeric( $_REQUEST['page'] ) ) && ( $_REQUEST['page'] >= 1 ) ) ? sanitize_text_field( $_REQUEST['page'] ) : 1;
$per_page = ( isset( $trav_options['car_posts'] ) && is_numeric($trav_options['car_posts']) ) ? $trav_options['car_posts'] : 12;

if ( is_tax() ) {
	$queried_taxonomy = get_query_var( 'taxonomy' );
	$queried_term = get_query_var( 'term' );
	$queried_term_obj = get_term_by('slug', $queried_term, $queried_taxonomy);
	if ( $queried_term_obj ) {
		if ( ( $queried_taxonomy == 'car_type' ) && ( ! in_array( $queried_term_obj->term_id, $car_type ) ) ) $car_type[] = $queried_term_obj->term_id;
		if ( ( $queried_taxonomy == 'car_agent' ) && ( ! in_array( $queried_term_obj->term_id, $car_agent ) ) ) $car_agent[] = $queried_term_obj->term_id;
		if ( ( $queried_taxonomy == 'preference' ) && ( ! in_array( $queried_term_obj->term_id, $preferences ) ) ) $preferences[] = $queried_term_obj->term_id;
	}
}

$date_from = isset( $_REQUEST['date_from'] ) ? trav_sanitize_date( $_REQUEST['date_from'] ) : '';
$date_to = isset( $_REQUEST['date_to'] ) ? trav_sanitize_date( $_REQUEST['date_to'] ) : '';
if ( trav_strtotime( $date_from ) >= trav_strtotime( $date_to ) ) {
	$date_from = '';
	$date_to = '';
}

$results = trav_car_get_search_result( $s, $date_from, $date_to, $order_by_array[$order_by], $order, ( $page - 1 ) * $per_page, $per_page, $min_price, $max_price, $passengers, $car_type, $car_agent, $preferences );
$count = trav_car_get_search_result_count( $min_price, $max_price, $car_type, $car_agent, $preferences );

global $before_article, $after_article, $car_list;
$before_article = '';
$after_article = '';
$car_list = array();
foreach ( $results as $result ) {
	$car_list[] = $result->car_id;
} ?>
<section id="content">
	<div class="container">
		<div id="main">
			<div class="row">
				<div class="col-sm-4 col-md-3">
					<h4 class="search-results-title"><i class="soap-icon-search"></i><b><?php echo esc_html( $count ); ?></b> <?php _e( 'results found.', 'trav' ) ?></h4>
					<div class="toggle-container filters-container style1">
						<div class="panel arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#modify-search-panel" class=""><?php esc_html_e( 'Modify Search', 'trav' ); ?></a>
							</h4>
							<div id="modify-search-panel" class="panel-collapse collapse in">
								<div class="panel-content">
									<form role="search" method="get" class="car-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
										<input type="hidden" name="post_type" value="car">
										<input type="hidden" name="view" value="<?php echo esc_attr( $current_view ) ?>">
										<input type="hidden" name="order_by" value="<?php echo esc_attr( $order_by ) ?>">
										<input type="hidden" name="order" value="<?php echo esc_attr( $order ) ?>">
										<?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
											<input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ) ?>">
										<?php } ?>
										
										<div class="form-group">
											<label><?php _e( 'pickup from', 'trav') ?></label>
											<input type="text" name="s" class="input-text full-width" placeholder="<?php _e( 'city, district, or specific airpot', 'trav') ?>" value="<?php echo esc_attr( $s ); ?>" />
										</div>
										<div class="search-when" data-error-message1="<?php echo __( 'Your drop-off date is before your pick-up date. Have another look at your date and try again.' , 'trav') ?>" data-error-message2="<?php echo __( 'Please select current or future dates for check-in and check-out.' , 'trav') ?>">
											<div class="form-group">
												<label><?php _e( 'pick-up date', 'trav') ?></label>
												<div class="datepicker-wrap from-today">
													<input name="date_from" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $date_from ); ?>" />
												</div>
											</div>
											<div class="form-group">
												<label><?php _e( 'drop-off date', 'trav') ?></label>
												<div class="datepicker-wrap from-today">
													<input name="date_to" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $date_to ); ?>" />
												</div>
											</div>
										</div>
										<div class="form-group">
											<label><?php _e( 'Passengers','trav' ); ?></label>
											<div class="selector">
												<select name="passengers" class="full-width">
													<?php
														$passengers = ( isset( $_GET['passengers'] ) && is_numeric( (int) $_GET['passengers'] ) )?(int) $_GET['passengers']:1;
														for ( $i = 1; $i <= $search_max_passengers; $i++ ) {
															$selected = '';
															if ( $i == $passengers ) $selected = 'selected';
															echo '<option value="' . esc_attr( $i ) . '" ' . $selected . '>' . esc_html( $i ) . '</option>';
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
						<?php if ( $trav_options['car_enable_price_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#price-filter" class="collapsed"><?php _e( 'Price', 'trav' );?></a>
							</h4>
							<div id="price-filter" class="panel-collapse collapse">
								<div class="panel-content">
									<div id="price-range" data-slide-last-val="<?php echo esc_attr( ( ! empty($trav_options['car_price_filter_max']) && is_numeric($trav_options['car_price_filter_max']) ) ? $trav_options['car_price_filter_max'] :200 ) ?>" data-slide-step="<?php echo esc_attr( ( ! empty($trav_options['car_price_filter_step']) && is_numeric($trav_options['car_price_filter_step']) ) ? $trav_options['car_price_filter_step'] :50 ) ?>" data-def-currency="<?php echo esc_attr( trav_get_site_currency_symbol() );?>" data-min-price="<?php echo esc_attr( $min_price ); ?>" data-max-price="<?php echo esc_attr( $max_price ); ?>" data-url-noprice="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price', 'page' ) ) ); ?>"></div>
									<br />
									<span class="min-price-label pull-left"></span>
									<span class="max-price-label pull-right"></span>
									<div class="clearer"></div>
								</div><!-- end content -->
							</div>
						</div>
						<?php endif; ?>
						
						<?php if ( $trav_options['car_enable_car_type_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#car-type-filter" class="<?php echo empty( $car_type )?'collapsed':''?>"><?php _e( 'Car Type', 'trav' ) ?></a>
							</h4>
							<div id="car-type-filter" data-url-nocar_type="<?php echo esc_url( remove_query_arg( array( 'car_types', 'page' ) ) ); ?>" class="panel-collapse collapse filters-container <?php echo empty( $car_type )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$selected = ( $car_type == '' )?' active':'';
											echo '<li class="all-types' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '<small>(' . esc_html( $count ) . ')</small></a></li>';
											$all_car_types = get_terms( 'car_type', array('hide_empty' => 0) );
											foreach ( $all_car_types as $each_car_type ) {
												$selected = ( ( is_array( $car_type ) && in_array( $each_car_type->term_id, $car_type ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_car_type->term_id ) . '"><a href="#">' . esc_html( $each_car_type->name ) . '<small>(' . esc_html( trav_car_get_search_result_count( $min_price, $max_price, array( $each_car_type->term_id ), $car_agent, $preferences ) ) . ')</small></a></li>';
											}
										?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>
						
						<?php if ( $trav_options['car_enable_car_agent_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#car-agent-filter" class="<?php echo empty( $car_agent )?'collapsed':''?>"><?php _e( 'Car Agent', 'trav' ) ?></a>
							</h4>
							<div id="car-agent-filter" data-url-nocar_agent="<?php echo esc_url( remove_query_arg( array( 'car_agents', 'page' ) ) ); ?>" class="panel-collapse collapse filters-container <?php echo empty( $car_agent )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$selected = ( $car_agent == '' )?' active':'';
											echo '<li class="all-types' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '<small>(' . esc_html( $count ) . ')</small></a></li>';
											$all_car_agents = get_terms( 'car_agent', array('hide_empty' => 0) );
											foreach ( $all_car_agents as $each_car_agent ) {
												$selected = ( ( is_array( $car_agent ) && in_array( $each_car_agent->term_id, $car_agent ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_car_agent->term_id ) . '"><a href="#">' . esc_html( $each_car_agent->name ) . '<small>(' . esc_html( trav_car_get_search_result_count( $min_price, $max_price, $car_type, array( $each_car_agent->term_id ), $preferences ) ) . ')</small></a></li>';
											}
										?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>
						
						<?php if ( $trav_options['car_enable_preference_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#car-preferences-filter" class="<?php echo empty( $preferences )?'collapsed':''?>"><?php _e( 'Car Preferences', 'trav' ) ?></a>
							</h4>
							<div id="car-preferences-filter" data-url-nocar_preference="<?php echo esc_url( remove_query_arg( array( 'preferences', 'page' ) ) ); ?>" class="panel-collapse collapse filters-container <?php echo empty( $preferences )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$selected = ( $preferences == '' )?' active':'';
											$all_preferences = get_terms( 'preference', array('hide_empty' => 0) );
											foreach ( $all_preferences as $each_preference ) {
												$selected = ( ( is_array( $preferences ) && in_array( $each_preference->term_id, $preferences ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_preference->term_id ) . '"><a href="#">' . esc_html( $each_preference->name ) . '<small>(' . esc_html( trav_car_get_search_result_count( $min_price, $max_price, $car_type, $car_agent, array( $each_preference->term_id ) ) ) . ')</small></a></li>';
											}
										?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>
						
					</div>
				</div>
				<div class="col-sm-8 col-md-9">
					<div class="sort-by-section clearfix">
						<h4 class="sort-by-title block-sm"><?php _e( 'Sort results by:', 'trav' ); ?></h4>
						<ul class="sort-bar clearfix block-sm">
							<?php
								foreach( $order_by_array as $key => $value ) {
									$active = '';
									$def_order = $order_defaults[ $key ];
									if ( $key == $order_by ) {
										$active = ' active';
										$def_order = ( $order == 'ASC' )?'DESC':'ASC';
									}
									echo '<li class="sort-by-' . esc_attr( $key . $active ) . '"><a class="sort-by-container" href="' . esc_url( add_query_arg( array( 'order_by' => $key, 'order' => $def_order ) ) ) . '"><span>' . esc_html( __( $key, 'trav' ) ) . '</span></a></li>';
								}
							?>
						</ul>
						
						<ul class="swap-tiles clearfix block-sm">
							<?php
								$views = array( 'list' => __( 'List View', 'trav' ),
												'grid' => __( 'Grid View', 'trav' ),
												'block' => __( 'Block View', 'trav' )
											);
								$params = $_GET;
								foreach( $views as $view => $label ) {
									$active = ( $view == $current_view )?' active':'';
									echo '<li class="swap-' . esc_attr( $view . $active ) . '">';
									echo '<a href="' . esc_url( add_query_arg( array( 'view' => $view ) ) ) . '" title="' . esc_attr( $label ) . '"><i class="soap-icon-' . esc_attr( $view ) . '"></i></a>';
									echo '</li>';
								}
							?>
						</ul>
					</div>
					<?php if ( ! empty( $results ) ) { ?>
						<div class="car-list list-wrapper image-box">
							<?php if ( $current_view == 'block' ) {
								echo '<div class="row listing-style2 add-clearfix">';
								$before_article = '<div class="col-sms-6 col-sm-6 col-md-4">';
								$after_article = '</div>';
							} elseif ( $current_view == 'grid' ) {
								echo '<div class="row car listing-style1 add-clearfix">';
								$before_article = '<div class="col-sm-6 col-md-4">';
								$after_article = '</div>';
							} else {
								echo '<div class="listing-style3 car">';
								$before_article = '';
								$after_article = '';
							}
							trav_get_template( 'car-list.php', '/templates/car/'); ?>
							</div>
							<?php if ( ! empty( $trav_options['ajax_pagination'] ) ) { ?>
								<?php if ( count( $results ) >= $per_page ) { ?>
									<a href="<?php echo esc_url( add_query_arg( array( 'page' => ( $page + 1 ) ) ) ); ?>" class="uppercase full-width button btn-large btn-load-more-accs" data-view="<?php echo esc_attr( $current_view ); ?>" data-search-params="<?php echo esc_attr( http_build_query( $_GET, '', '&amp;' ) ) ?>"><?php echo __( 'load more listing', 'trav' ) ?></a>
								<?php } ?>
							<?php } else {
								unset( $_GET['page'] );
								$pagenum_link = strtok( $_SERVER["REQUEST_URI"], '?' ) . '%_%';
								$total = ceil( $count / $per_page );
								$args = array(
									'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
									'total' => $total,
									'format' => '?page=%#%',
									'current' => $page,
									'show_all' => false,
									'prev_next' => true,
									'prev_text' => __('Previous', 'trav'),
									'next_text' => __('Next', 'trav'),
									'end_size' => 1,
									'mid_size' => 2,
									'type' => 'list',
									'add_args' => $_GET,
								);
								echo paginate_links( $args );
							} ?>
						</div>
					<?php } else { ?>
						<div class="travelo-box"><?php _e( 'No available cars', 'trav' );?></div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>















<?php
get_footer();
?>