<?php
/**
* Cruise Archive Template
 */

get_header();
global $current_view, $trav_options, $search_max_cabins, $search_max_adults, $search_max_kids, $language_count;

$order_array = array( 'ASC', 'DESC' );
$order_by_array = array(
		'name' => 'cruise_title',
		'price' => 'cast(avg_price as unsigned)',
		'rating' => 'review'
	);
$order_defaults = array(
		'name' => 'ASC',
		'price' => 'ASC',
		'rating' => 'DESC'
	);

$s = isset($_REQUEST['s']) ? sanitize_text_field( $_REQUEST['s'] ) : '';
$cabins = ( isset( $_REQUEST['cabins'] ) && is_numeric( $_REQUEST['cabins'] ) ) ? sanitize_text_field( $_REQUEST['cabins'] ) : 1;
$adults = ( isset( $_REQUEST['adults'] ) && is_numeric( $_REQUEST['adults'] ) ) ? sanitize_text_field( $_REQUEST['adults'] ) : 1;
$kids = ( isset( $_REQUEST['kids'] ) && is_numeric( $_REQUEST['kids'] ) ) ? sanitize_text_field( $_REQUEST['kids'] ) : 0;
$min_price = ( isset( $_REQUEST['min_price'] ) && is_numeric( $_REQUEST['min_price'] ) ) ? sanitize_text_field( $_REQUEST['min_price'] ) : 0;
$max_price = ( isset( $_REQUEST['max_price'] ) && ( is_numeric( $_REQUEST['max_price'] ) || ( $_REQUEST['max_price'] == 'no_max' ) ) ) ? sanitize_text_field( $_REQUEST['max_price'] ) : 'no_max';
$rating = ( isset( $_REQUEST['rating'] ) && is_numeric( $_REQUEST['rating'] ) ) ? sanitize_text_field( $_REQUEST['rating'] ) : 0;
$duration = ( isset( $_REQUEST['duration'] ) && ( is_numeric( $_REQUEST['duration'] ) || ( $_REQUEST['duration'] == 'no_max' ) ) ) ? sanitize_text_field( $_REQUEST['duration'] ) : 'no_max';
$order_by = ( isset( $_REQUEST['order_by'] ) && array_key_exists( $_REQUEST['order_by'], $order_by_array ) ) ? sanitize_text_field( $_REQUEST['order_by'] ) : 'name';
$order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], $order_array ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';
$cruise_type = ( isset( $_REQUEST['cruise_types'] ) ) ? ( is_array( $_REQUEST['cruise_types'] ) ? $_REQUEST['cruise_types'] : array( $_REQUEST['cruise_types'] ) ):array();
$amenities = ( isset( $_REQUEST['amenities'] ) && is_array( $_REQUEST['amenities'] ) ) ? $_REQUEST['amenities'] : array();
$cruise_line = ( isset( $_REQUEST['cruise_lines'] ) && is_array( $_REQUEST['cruise_lines'] ) ) ? $_REQUEST['cruise_lines'] : array();
$current_view = isset( $_REQUEST['view'] ) ? sanitize_text_field( $_REQUEST['view'] ) : 'list';
$page = ( isset( $_REQUEST['page'] ) && ( is_numeric( $_REQUEST['page'] ) ) && ( $_REQUEST['page'] >= 1 ) ) ? sanitize_text_field( $_REQUEST['page'] ) : 1;
$per_page = ( isset( $trav_options['cruise_posts'] ) && is_numeric($trav_options['cruise_posts']) ) ? $trav_options['cruise_posts'] : 12;
if ( is_tax() ) {
	$queried_taxonomy = get_query_var( 'taxonomy' );
	$queried_term = get_query_var( 'term' );
	$queried_term_obj = get_term_by('slug', $queried_term, $queried_taxonomy);
	if ( $queried_term_obj ) {
		if ( ( $queried_taxonomy == 'cruise_type' ) && ( ! in_array( $queried_term_obj->term_id, $cruise_type ) ) ) $cruise_type[] = $queried_term_obj->term_id;
		if ( ( $queried_taxonomy == 'amenity' ) && ( ! in_array( $queried_term_obj->term_id, $amenities ) ) ) $amenities[] = $queried_term_obj->term_id;
		if ( ( $queried_taxonomy == 'cruise_line' ) && ( ! in_array( $queried_term_obj->term_id, $cruise_line ) ) ) $cruise_line[] = $queried_term_obj->term_id;
	}
}

$date_from = isset( $_REQUEST['date_from'] ) ? trav_sanitize_date( $_REQUEST['date_from'] ) : '';
$date_to = isset( $_REQUEST['date_to'] ) ? trav_sanitize_date( $_REQUEST['date_to'] ) : '';

if ( ! empty( $date_from ) && ! empty( $date_to ) && trav_strtotime( $date_from ) >= trav_strtotime( $date_to ) ) {
	$date_from = '';
	$date_to = '';
}

$results = trav_cruise_get_search_result( $s, $date_from, $date_to, $cabins, $adults, $kids, $order_by_array[$order_by], $order, ( $page - 1 ) * $per_page, $per_page, $min_price, $max_price, $rating, $cruise_type, $amenities, $cruise_line, $duration );
$count = trav_cruise_get_search_result_count( $min_price, $max_price, $rating, $cruise_type, $amenities, $cruise_line, $duration );

global $before_article, $after_article, $cruise_list;
$before_article = '';
$after_article = '';
$cruise_list = array();
foreach ( $results as $result ) {
	$cruise_list[] = $result->cruise_id;
} ?>

<section id="content">
	<div class="container">
		<div id="main">
			<div class="row">
				<div class="col-sm-4 col-md-3">
					<h4 class="search-results-title"><i class="soap-icon-search"></i><b><?php echo esc_html( $count ); ?></b> <?php _e( 'results found.', 'trav' ) ?></h4>
					<div class="toggle-container style1 filters-container">
						<div class="panel arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#modify-search-panel" class=""><?php _e( 'Modify Search', 'trav' ) ?></a>
							</h4>
							<div id="modify-search-panel" class="panel-collapse collapse in">
								<div class="panel-content">
									<form role="search" method="get" class="cruise-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
										<input type="hidden" name="post_type" value="cruise">
										<input type="hidden" name="view" value="<?php echo esc_attr( $current_view ) ?>">
										<input type="hidden" name="order_by" value="<?php echo esc_attr( $order_by ) ?>">
										<input type="hidden" name="order" value="<?php echo esc_attr( $order ) ?>">
										<?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
											<input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ) ?>">
										<?php } ?>
										<div class="form-group">
											<label><?php _e( 'Your Destination','trav' ); ?></label>
											<input type="text" name="s" class="input-text full-width" placeholder="<?php _e( 'enter a destination or cruise name', 'trav') ?>" value="<?php echo esc_attr( $s ); ?>" />
										</div>
										<div class="search-when" data-error-message1="<?php echo __( 'Your date to is before your date from. Have another look at your date and try again.' , 'trav') ?>" data-error-message2="<?php echo __( 'Please select current or future dates for date from and date to.' , 'trav') ?>">
											<div class="form-group">
												<label><?php _e( 'DATE FROM','trav' ); ?></label>
												<div class="datepicker-wrap from-today">
													<input name="date_from" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $date_from ); ?>" />
												</div>
											</div>
											<div class="form-group">
												<label><?php _e( 'DATE TO','trav' ); ?></label>
												<div class="datepicker-wrap from-today">
													<input name="date_to" type="text" class="input-text full-width" placeholder="<?php echo trav_get_date_format('html'); ?>" value="<?php echo esc_attr( $date_to ); ?>" />
												</div>
											</div>
										</div>
										<br />
										<button class="btn-medium icon-check uppercase full-width"><?php _e( 'search again', 'trav' ) ?></button>
									</form>
								</div>
							</div>
						</div>

						<?php if ( $trav_options['cruise_enable_price_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#price-filter" class="collapsed"><?php _e( 'Price (avg/night)', 'trav' );?></a>
							</h4>
							<div id="price-filter" class="panel-collapse collapse">
								<div class="panel-content">
									<div id="price-range" data-slide-last-val="<?php echo esc_attr( ( ! empty($trav_options['cruise_price_filter_max']) && is_numeric($trav_options['cruise_price_filter_max']) ) ? $trav_options['cruise_price_filter_max'] :200 ) ?>" data-slide-step="<?php echo esc_attr( ( ! empty($trav_options['cruise_price_filter_step']) && is_numeric($trav_options['cruise_price_filter_step']) ) ? $trav_options['cruise_price_filter_step'] :50 ) ?>" data-def-currency="<?php echo esc_attr( trav_get_site_currency_symbol() );?>" data-min-price="<?php echo esc_attr( $min_price ); ?>" data-max-price="<?php echo esc_attr( $max_price ); ?>" data-url-noprice="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price', 'page' ) ) ); ?>"></div>
									<br />
									<span class="min-price-label pull-left"></span>
									<span class="max-price-label pull-right"></span>
									<div class="clearer"></div>
								</div><!-- end content -->
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $trav_options['cruise_enable_review_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#rating-filter" class="<?php echo ( $rating == 0 )?'collapsed':''?>"><?php _e( 'User Rating', 'trav' );?></a>
							</h4>
							<div id="rating-filter" class="panel-collapse collapse filters-container <?php echo ( $rating == 0 )?'':'in'?>">
								<div class="panel-content">
									<div id="rating" class="five-stars-container editable-rating" data-rating="<?php echo esc_attr( $rating );?>" data-url-norating="<?php echo esc_url( remove_query_arg( array( 'rating', 'page' ) ) ); ?>" data-label-norating="<?php _e( 'All Ratings', 'trav' );?>" data-label-rating="<?php _e( 'and Above', 'trav' );?>" data-label-fullrating="<?php _e( '5 Ratings', 'trav' );?>"></div>
									<span><?php _e( 'All', 'trav' );?></span>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $trav_options['cruise_enable_cruise_type_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#cruise-type-filter" class="<?php echo empty( $cruise_type )?'collapsed':''?>"><?php _e( 'Cruise Type', 'trav' ) ?></a>
							</h4>
							<div id="cruise-type-filter" data-url-nocruise_type="<?php echo esc_url( remove_query_arg( array( 'cruise_types', 'page' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $cruise_type )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$selected = ( $cruise_type == '' )?' active':'';
											echo '<li class="all-types' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '<small>(' . esc_html( $count ) . ')</small></a></li>';
											$all_cruise_types = get_terms( 'cruise_type', array('hide_empty' => 0) );
											foreach ( $all_cruise_types as $each_cruise_type ) {
												$selected = ( ( is_array( $cruise_type ) && in_array( $each_cruise_type->term_id, $cruise_type ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_cruise_type->term_id ) . '"><a href="#">' . esc_html( $each_cruise_type->name ) . '<small>(' . esc_html( trav_cruise_get_search_result_count( $min_price, $max_price, $rating, array( $each_cruise_type->term_id ), $amenities, $cruise_line ) ) . ')</small></a></li>';
											}
										?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $trav_options['cruise_enable_cruise_line_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#cruise-line-filter" class="<?php echo empty( $cruise_line )?'collapsed':''?>"><?php _e( 'Cruise Line', 'trav' ) ?></a>
							</h4>
							<div id="cruise-line-filter" data-url-nocruise_line="<?php echo esc_url( remove_query_arg( array( 'cruise_lines', 'page' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $cruise_line )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$selected = ( $cruise_line == '' )?' active':'';
											echo '<li class="all-lines' . esc_attr( $selected ) . '"><a href="#">' . __( 'All', 'trav' ) . '<small>(' . esc_html( $count ) . ')</small></a></li>';
											$all_cruise_lines = get_terms( 'cruise_line', array('hide_empty' => 0) );
											foreach ( $all_cruise_lines as $each_cruise_line ) {
												$selected = ( ( is_array( $cruise_line ) && in_array( $each_cruise_line->term_id, $cruise_line ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_cruise_line->term_id ) . '"><a href="#">' . esc_html( $each_cruise_line->name ) . '<small>(' . esc_html( trav_cruise_get_search_result_count( $min_price, $max_price, $rating, $cruise_type, $amenities, array( $each_cruise_line->term_id ) ) ) . ')</small></a></li>';
											}
										?>
									</ul>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( $trav_options['cruise_enable_amenity_filter'] ) : ?>
						<div class="panel style1 arrow-right">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#amenities-filter" class="<?php echo empty( $amenities )?'collapsed':''?>"><?php _e( 'Amenities', 'trav' ) ?></a>
							</h4>
							<div id="amenities-filter" data-url-noamenities="<?php echo esc_url( remove_query_arg( array( 'amenities', 'page' ) ) ); ?>" class="panel-collapse collapse <?php echo empty( $amenities )?'':'in'?>">
								<div class="panel-content">
									<ul class="check-square filters-option">
										<?php
											$args = array(
													'orderby'           => 'count', 
													'order'             => 'DESC',
													'hide_empty' => 0
												);

											$all_amenities = get_terms( 'amenity', $args );
											foreach ($all_amenities as $each_amenity) {
												$selected = ( ( is_array( $amenities ) && in_array( $each_amenity->term_id, $amenities ) ) )?' class="active"':'';
												echo '<li' . $selected . ' data-term-id="' . esc_attr( $each_amenity->term_id ) . '"><a href="#">' . esc_html( $each_amenity->name ) . '<small>(' . esc_html( trav_cruise_get_search_result_count( $min_price, $max_price, $rating, $cruise_type, array( $each_amenity->term_id ), $cruise_line ) ) . ')</small></a></li>';
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
					<div class="sort-by-section clearfix box">
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
						<div class="cruise-list list-wrapper">
							<?php if ( $current_view == 'block' ) {
								echo '<div class="row image-box listing-style2 add-clearfix">';
								$before_article = '<div class="col-sms-6 col-sm-6 col-md-4">';
								$after_article = '</div>';
							} elseif ( $current_view == 'grid' ) {
								echo '<div class="row image-box cruise listing-style1 add-clearfix">';
								$before_article = '<div class="col-sm-6 col-md-4">';
								$after_article = '</div>';
							} else {
								echo '<div class="image-box listing-style3 cruise">';
								$before_article = '';
								$after_article = '';
							}
							trav_get_template( 'cruise-list.php', '/templates/cruise/'); ?>
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
						<div class="travelo-box"><?php _e( 'No available cruises', 'trav' );?></div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
?>