<?php
/**
 * Search Group Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_search_group]
function trav_shortcode_search_group( $atts, $content = null ) {
	
	$variables = array(
						'extra_class'	=> '',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-search-group-' . $id );

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}
	
	global $trav_options, $search_max_rooms, $search_max_adults, $search_max_kids, $def_currency, $search_max_passengers, $search_max_flighters;
	
	$all_features = array( 'acc', 'tour' );
	$enabled_features = array();
	foreach( $all_features as $feature ) {
		if ( empty( $trav_options['disable_' . $feature ] ) ) $enabled_features[] = $feature;
	}

	ob_start();
	?>

	<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="main-tab-form <?php echo esc_attr( $content_class ); ?>">
		<div class="form-tab-inner">
			<?php if ( count( $enabled_features ) > 1 ) : ?>
				<div class="nav nav-tabs">
					<?php if ( in_array( 'acc', $enabled_features ) ) : ?>
						<a data-toggle="tab" href="#hotel" class="nav-title <?php if ( $enabled_features[0] == 'acc' ) echo 'active show'; ?>">
							<span class="title-inner"><i class="travelo-hotel"></i> <?php echo esc_html__( 'Hotels', 'trav' ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( in_array( 'tour', $enabled_features ) ) : ?>
						<a data-toggle="tab" href="#tours" class="nav-title <?php if ( $enabled_features[0] == 'tour' ) echo 'active show' ?>">
							<span class="title-inner"><i class="travelo-tours"></i> <?php echo esc_html__( 'Tours', 'trav' ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( in_array( 'flight', $enabled_features ) ) : ?>
						<a data-toggle="tab" href="#flights" class="nav-title <?php if ( $enabled_features[0] == 'flight' ) echo 'active show' ?>">
							<span class="title-inner"><i class="travelo-flights"></i> <?php echo esc_html__( 'Flights', 'trav' ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( in_array( 'car', $enabled_features ) ) : ?>
						<a data-toggle="tab" href="#cars" class="nav-title <?php if ( $enabled_features[0] == 'car' ) echo 'active show' ?>">
							<span class="title-inner"><i class="travelo-cars"></i> <?php echo esc_html__( 'Cars', 'trav' ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( in_array( 'cruise', $enabled_features ) ) : ?>
						<a data-toggle="tab" href="#cruises" class="nav-title <?php if ( $enabled_features[0] == 'cruise' ) echo 'active show' ?>">
							<span class="title-inner"><i class="travelo-cruises"></i> <?php echo esc_html__( 'Cruises', 'trav' ); ?></span>
						</a>
					<?php endif; ?>
					
				</div>
				
			<?php endif; ?>
			
			<div class="tab-content">
				<?php if ( in_array( 'acc', $enabled_features ) ) : ?>
					<?php if ( count( $enabled_features ) > 1 ) : ?>
						<div id="hotel" class="tab-pane fade <?php if ( $enabled_features[0] == 'acc' ) echo ' show active' ?>">
					<?php endif; ?>
					<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="acc-searchform hero-search-form">
						<input type="hidden" name="post_type" value="accommodation">

						<div class="direction-wrap">
							<label class="form-label"><?php echo esc_html__( 'Where', 'trav' ); ?></label>

							<div class="field-section">
								<i class="fas fa-map-marker-alt"></i>
								<input type="text" class="form-control" name="s" placeholder="<?php echo esc_attr__( 'Direction', 'trav' ); ?>">
							</div>
						</div>

						<div class="check-in-out-wrap">
							<div class="label-section">
								<label class="form-label"><?php echo esc_html__( 'Check In', 'trav' ); ?></label>
								<label class="form-label"><?php echo esc_html__( 'Check Out', 'trav' ); ?></label>	
							</div>

							<div class="field-sections">
								<div class="field-section date-in">
									<i class="far fa-calendar-alt"></i>
									<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
								</div>

								<div class="field-section date-out">
									<i class="far fa-calendar-alt"></i>
									<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
								</div>

								<input type="text" name="datetimes" class="hidden-field" format="<?php echo trav_get_date_format('html'); ?>">
								<input type="hidden" name="date_from">
								<input type="hidden" name="date_to">
							</div>
						</div>

						<div class="guest-wrap">
							<label class="form-label"><?php echo esc_html__( 'Guests', 'trav' ); ?></label>

							<div class="field-section">
								<i class="fas fa-user"></i>
								<span class="guest-value form-control"><?php echo esc_html__( 'Who\'s going?', 'trav' ); ?></span>
							</div>

							<div class="guest-dropdown-info">
								<div class="guest-qty-section">
									<div class="room-qty qty-field">
										<div class="label-wrap">
											<span class="title"><?php echo esc_html__( 'Rooms', 'trav' ); ?></span>
											<span class="desc"><?php echo esc_html__( 'Min 1 Room', 'trav' ); ?></span>
										</div>

										<div class="count-wrap">
											<i class="fas fa-minus"></i>
											<input type="text" name="rooms" class="count-value" min="1" value="1">
											<i class="fas fa-plus"></i>
										</div>
									</div>

									<div class="adults-qty qty-field">
										<div class="label-wrap">
											<span class="title"><?php echo esc_html__( 'Adults', 'trav' ); ?></span>
											<span class="desc"><?php echo esc_html__( '17 Onward', 'trav' ); ?></span>
										</div>

										<div class="count-wrap">
											<i class="fas fa-minus"></i>
											<input type="text" name="adults" class="count-value" min="1" value="1">
											<i class="fas fa-plus"></i>
										</div>
									</div>

									<div class="children-qty qty-field children-age-field-container">
										<div class="label-wrap">
											<span class="title"><?php echo esc_html__( 'Children', 'trav' ); ?></span>
											<span class="desc"><?php echo esc_html__( 'Ages 2-17', 'trav' ); ?></span>
										</div>

										<div class="count-wrap">
											<i class="fas fa-minus"></i>
											<input type="text" name="kids" class="count-value" min="0" value="0">
											<i class="fas fa-plus"></i>
										</div>
										<input type="hidden" name="child_ages[]" value="2">
									</div>

								</div>
								<p class="guest-description"><?php echo esc_html__( '3 guests maximum. Infants don’t count toward the number of guests.', 'trav' ); ?></p>
							</div>
						</div>
						
						<div class="form-submit">
							<button type="submit" class="submit-btn"><?php echo esc_html__( 'Search', 'trav' ); ?></button>
						</div>
					</form>
					<?php if ( count( $enabled_features ) > 1 ) : ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( in_array( 'tour', $enabled_features ) ) : ?>
					<?php if ( count( $enabled_features ) > 1 ) : ?>
						<div class="tab-pane fade<?php if ( $enabled_features[0] == 'tour' ) echo ' active show' ?>" id="tours">
					<?php endif; ?>
					<form role="search" method="get" class="tour-searchform hero-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="hidden" name="post_type" value="tour">

						<div class="direction-wrap">
							<label class="form-label"><?php echo esc_html__( 'Where', 'trav' ); ?></label>

							<div class="field-section">
								<i class="fas fa-map-marker-alt"></i>
								<input type="text" class="form-control" name="s" placeholder="<?php echo esc_attr__( 'Direction', 'trav' ); ?>">
							</div>
						</div>

						<div class="check-in-out-wrap">
							<div class="label-section">
								<label class="form-label"><?php echo esc_html__( 'Check In', 'trav' ); ?></label>
								<label class="form-label"><?php echo esc_html__( 'Check Out', 'trav' ); ?></label>	
							</div>

							<div class="field-sections">
								<div class="field-section date-in">
									<i class="far fa-calendar-alt"></i>
									<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
								</div>

								<div class="field-section date-out">
									<i class="far fa-calendar-alt"></i>
									<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
								</div>

								<input type="text" name="datetimes" class="hidden-field" format="<?php echo trav_get_date_format('html'); ?>">
								<input type="hidden" name="date_from">
								<input type="hidden" name="date_to">
							</div>
						</div>

						<div class="category-wrap dropdown-control-wrap">
							<label class="form-label"><?php echo esc_html__( 'Category', 'trav' ); ?></label>

							<?php $trip_types = get_terms( 'tour_type', array( 'hide_empty' => 0 ) ); ?>

							<div class="field-section">
								<i class="fas fa-tree"></i>
								<div class="tour-type-category category-selection form-control">
									<select name="tour_types" class="full-width">
										<option value=""><?php _e( 'Trip Type', 'trav' ) ?></option>
										<?php foreach ( $trip_types as $trip_type ) : ?>
											<option value="<?php echo $trip_type->term_id ?>"><?php _e( $trip_type->name, 'trav' ) ?></option>
										<?php endforeach; ?>
									</select>
									<span class="dropdown-nav"><i class="fas fa-chevron-down"></i></span>
								</div>
							</div>
						</div>

						<div class="budget-wrap">
							<label class="form-label"><?php echo esc_html__( 'Budget', 'trav' ); ?></label>

							<div class="field-section">
								<i class="fas fa-wallet"></i>
								<input type="text" name="max_price" class="form-control" placeholder="<?php echo sprintf( esc_html__( 'Amount (%s)', 'trav'), strtoupper( $def_currency ) ); ?>">
							</div>
						</div>	

						<div class="form-submit">
							<button type="submit" class="submit-btn"><?php echo esc_html__( 'Search', 'trav' ); ?></button>
						</div>
							
					</form>
					<?php if ( count( $enabled_features ) > 1 ) : ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

			</div>
		
		</div>
	</div>
	
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_search_group', 'trav_shortcode_search_group' );

/**
 * WPBakery
 */
function trav_vc_shortcode_search_group() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Search Group', 'trav' ),
		'base'			=>	'trav_modern_search_group',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Show search group in your page.', 'trav' ),
		'params'		=>	array(
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'trav' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'trav' )
			)
		)
	) );
}

trav_vc_shortcode_search_group();