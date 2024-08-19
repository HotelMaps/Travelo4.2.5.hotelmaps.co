<?php 
if ( ! function_exists( 'trav_modern_get_acc_grid_single' ) ) {
	function trav_modern_get_acc_grid_single( $acc_id ) {
		global $login_url;
		$avg_price = get_post_meta( $acc_id, 'trav_accommodation_avg_price', true );
		$review = get_post_meta( $acc_id, 'review', true );
		$review = ( ! empty( $review ) ) ? round( $review, 1 ) : 0;
		$brief = get_post_meta( $acc_id, 'trav_accommodation_brief', true );
		if ( empty( $brief ) ) {
			$brief = apply_filters( 'the_content', get_post_field( 'post_content', $acc_id ) );
			$brief = wp_trim_words( $brief, 20, '' );
		}

		$gallery_imgs = get_post_meta( $acc_id, 'trav_gallery_imgs' );
		if ( empty( $gallery_imgs ) ) {
			$gallery_imgs = array();
		}

		$featured_img_id = get_post_thumbnail_id( $acc_id );
	    if ( $featured_img_id ) {
	        array_unshift( $gallery_imgs, $featured_img_id );
	    }
	    
		$city = trav_acc_get_city( $acc_id );
		$country = trav_acc_get_country( $acc_id );
		$review_count = trav_modern_get_review_count( $acc_id );

		ob_start();
		?>
			<div class="single-travel-item-wrap">
				<div class="single-travel-item">
					<?php if ( is_user_logged_in() ) {
						$user_id = get_current_user_id();
						$wishlist = get_user_meta( $user_id, 'wishlist', true );
						if ( empty( $wishlist ) ) $wishlist = array();
						if ( ! in_array( trav_acc_org_id( $acc_id ), $wishlist) ) { ?>
							<a class="wishlist-ribbon btn-add-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
						<?php } else { ?>
							<a class="wishlist-ribbon active btn-remove-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
						<?php } ?>
					<?php } else { ?>
						<a href="<?php echo $login_url; ?>" class="wishlist-ribbon btn-add-wishlist"><i class="fas fa-heart"></i></a>
					<?php } ?>

					<div class="featured-imgs">
						<?php foreach ( $gallery_imgs as $gallery_img ) {
							echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
						} ?>					
					</div>

					<div class="package-item-info">
						<h3 class="package-item-name"><?php echo get_the_title( $acc_id ); ?></h3>
						<p class="package-item-direction"><?php echo esc_html( $city ); ?>, <?php echo esc_html( $country ); ?></p>
						<div class="review-rating">
							<div class="five-stars-container">
								<span class="five-stars" style="width: <?php echo $review * 20; ?>%;"></span>
							</div>
							<span class="review-nums"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'reviews', 'trav' ); ?></span>
						</div>
						<p class="description"><?php echo esc_html( $brief ); ?></p>
						<div class="price-field"><?php echo trav_get_price_field( $avg_price ); ?> <span>/ <?php echo esc_html__( 'Pre Night', 'trav' ); ?></span></div>
						<a href="<?php echo get_permalink( $acc_id ); ?>" class="view-detail border-btn-third"><?php echo esc_html__( 'See Details', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></a>
					</div>
				</div>
			</div>
		<?php
		$result = ob_get_clean();

		return $result;
	}
}

if ( ! function_exists( 'trav_modern_ajax_acc_get_available_rooms' ) ) {
	function trav_modern_ajax_acc_get_available_rooms() {
		//validation and initiate variables
		$result_json = array( 
			'success'   => 0, 
			'result'    => '' 
		);

		if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['accommodation_id'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'post-' . sanitize_text_field( $_POST['accommodation_id'] ) ) ) {
			$result_json['success'] = 0;
			$result_json['result'] = __( 'Sorry, your nonce did not verify.', 'trav' );

			wp_send_json( $result_json );
		}

		$rooms = ( isset( $_POST['rooms'] ) && is_numeric( $_POST['rooms'] ) ) ? sanitize_text_field( $_POST['rooms'] ) : 1;
		$adults = ( isset( $_POST['adults'] ) && is_numeric( $_POST['adults'] ) ) ? sanitize_text_field( $_POST['adults'] ) : 1;
		$kids = ( isset( $_POST['kids'] ) && is_numeric( $_POST['kids'] ) ) ? sanitize_text_field( $_POST['kids'] ) : 0;
		$child_ages = isset( $_POST['child_ages'] ) ? $_POST['child_ages'] : '';

		if ( isset( $_POST['accommodation_id'] ) && isset( $_POST['date_from'] ) && trav_strtotime( $_POST['date_from'] ) && isset( $_POST['date_to'] ) && trav_strtotime( $_POST['date_to'] ) && ( ( time()-(60*60*24) ) < trav_strtotime( $_POST['date_from'] ) ) ) {
			$acc_id = (int) $_POST['accommodation_id'];
			$except_booking_no = 0; 
			$pin_code = 0;

			if ( isset( $_POST['edit_booking_no'] ) ) {
				$except_booking_no = sanitize_text_field( $_POST['edit_booking_no'] );
			}

			if ( isset( $_POST['pin_code'] ) ) {
				$pin_code = sanitize_text_field( $_POST['pin_code'] );
			}

			$return_value = trav_acc_get_available_rooms( $acc_id, $_POST['date_from'], $_POST['date_to'], $rooms, $adults, $kids, $child_ages, $except_booking_no, $pin_code );

			if ( ! empty ( $return_value ) && is_array( $return_value ) ) {

				$number_of_days = count( $return_value['check_dates'] );
				
				ob_start();
				$available_room_type_ids = $return_value['bookable_room_type_ids'];
				?>
				<h3 class="section-inner-title"><?php echo esc_html__( 'Available Rooms', 'trav' ); ?></h3>

				<?php
				if ( empty( $available_room_type_ids ) ) {
					?>
					
					<div class="description-part">
						<p class="description"><?php echo esc_html__( 'No room found on your desired dates.', 'trav' ); ?></p>
						<a href="#check_availability_form" class="search-edit-btn"><i class="far fa-edit"></i> <?php echo esc_html__( 'Change Details', 'trav' );?></a>
					</div>
					
					<?php					
				} else {
					?>
					<div class="description-part">
						<p class="description"><?php echo count( $available_room_type_ids ); ?> <?php echo esc_html( _n( 'room found on your desired dates.', 'rooms found on your desired dates.', count( $available_room_type_ids ), 'trav' ) ); ?></p>
						<a href="#check_availability_form" class="search-edit-btn"><i class="far fa-edit"></i> <?php echo esc_html__( 'Change Details', 'trav' );?></a>
					</div>
					<div class="available-travel-package-wrap">
						<?php
						foreach ( $available_room_type_ids as $room_type_id ) {
							$room_price = 0;
							foreach ( $return_value['check_dates'] as $check_date ) {
								$room_price += (float) $return_value['prices'][ $room_type_id ][ $check_date ]['total'];
							}
							
							trav_modern_acc_get_room_detail_html( $room_type_id, 'available', $room_price, $number_of_days, $rooms );
						}
						?>
					</div>
					<?php
				}

				$output = ob_get_contents();
				ob_end_clean();

				$result_json['success'] = 1;
				$result_json['result'] = $output;
			} else {
				$result_json['success'] = 1;
				$result_json['result'] = $return_value;
			}
		} else {
			$result_json['success'] = 0;
			$result_json['result'] = __( 'Invalid input data', 'trav' );
		}

		wp_send_json( $result_json );
	}
}

if ( ! function_exists( 'trav_modern_acc_get_room_detail_html' ) ) {
	function trav_modern_acc_get_room_detail_html( $room_type_id, $type = 'all', $room_price = 0, $number_of_days = 0, $rooms = 0) { // available type - all,available,not_available,not_match
		$room_type_id = trav_room_clang_id( $room_type_id );

		$gallery_imgs = get_post_meta( $room_type_id, 'trav_gallery_imgs' );
		$max_adults = get_post_meta( $room_type_id, 'trav_room_max_adults', true );
		$max_kids = get_post_meta( $room_type_id, 'trav_room_max_kids', true );		
		$facilities = wp_get_post_terms( $room_type_id, 'amenity' );
		$facility_names = array();
		foreach ( $facilities as $facility ) {
			$facility_names[] = $facility->name;
		}
		?>
		<div class="single-travel-item-wrap">
			<div class="single-travel-item detail-list-view-room">
				<div class="featured-imgs">
					<?php foreach ( $gallery_imgs as $gallery_img ) {
						echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
					} ?>
				</div>

				<div class="package-item-info">
					<h3 class="package-item-name"><?php echo get_the_title( $room_type_id ); ?></h3>
					<div class="guest-max-nums">
						<span class="guest-val"><?php echo esc_html__( 'Max Guest', 'trav' ); ?>: <span class="val"><?php echo esc_html( $max_adults ); ?></span></span>
						<span class="kids-val"><?php echo esc_html__( 'Max Kids', 'trav' );?>: <span class="val"><?php echo esc_html( $max_kids ); ?></span></span>
					</div>
					<p class="description"><?php echo implode( ', ', $facility_names ); ?></p>
					
					<div class="info-bottom-part">
						<div class="price-field"><?php echo trav_get_price_field( $room_price ); ?> <span>/ <?php echo esc_html( $number_of_days ); ?> <?php echo esc_html( _n( 'Night', 'Nights', $number_of_days, 'trav' ) ); ?></span></div>
						<button class="view-detail border-btn-third btn-book-now" data-room-type-id="<?php echo esc_attr( $room_type_id ); ?>"><?php echo esc_html__( 'Book Room', 'trav' ); ?></button>
					</div>
				</div>
			</div>
		</div>		
		<?php
	}
}

/*
 * get post review html from post_id
 */
if ( ! function_exists( 'trav_modern_get_review_html' ) ) {
	function trav_modern_get_review_html( $post_id, $start_num=0, $per_page=10 ) {
		$reviews = trav_get_reviews( $post_id, $start_num, $per_page );

		if ( ! empty( $reviews ) ) {

			foreach ( $reviews as $review ) {
				$default = "";
				$photo = trav_get_avatar( array( 'id' => $review['user_id'], 'email' => $review['reviewer_email'], 'size' => 74 ) );
				?>

				<li class="comment">
					<div class="comment-body">
						<div class="head-part">
							<?php echo wp_kses_post( $photo ) ?>
							<div class="comment-info">
								<span class="name"><?php echo esc_html( $review['reviewer_name'] );?></span>

								<div class="title-date">
									<span class="date"><?php echo date( "M, d, Y",trav_strtotime( $review['date'] ) );?></span>
									<h4 class="comment-title"><?php echo esc_html( stripslashes( $review['review_title'] ) ) ?></h4>
								</div>
							</div>
						</div>
						<p class="comment-txt"><?php echo esc_html( stripslashes( $review['review_text'] ) ) ?></p>
					</div>
				</li>

				<?php
			}
		}
		
		return count( $reviews );
	}
}

if ( ! function_exists( 'trav_modern_get_write_review_form_html' ) ) {
	function trav_modern_get_write_review_form_html( $acc_id ) {

		global $wpdb;

		$booking_data = '';
		$review_data ='';
		$rating_detail = '';
		$average_rating = 0;

		if ( is_user_logged_in() ) {
			$booking_data = $wpdb->get_row( sprintf( 'SELECT * FROM ' . TRAV_ACCOMMODATION_BOOKINGS_TABLE . ' WHERE accommodation_id=%d AND user_id=%d AND date_to<%s ORDER BY date_to DESC', $acc_id, get_current_user_id(), date("Y-m-d") ), ARRAY_A );
			if ( ! empty( $booking_data ) ) {
				$review_data = $wpdb->get_row( sprintf( 'SELECT * FROM ' . TRAV_REVIEWS_TABLE . ' WHERE booking_no=%d AND pin_code=%d', $booking_data['booking_no'], $booking_data['pin_code'] ), ARRAY_A );
				if ( is_array( $review_data ) && isset( $review_data['review_rating_detail'] ) ) {
					$rating_detail = unserialize( $review_data['review_rating_detail'] );
					$average_rating = array_sum( $rating_detail ) / count( $rating_detail );
				}
			}
		}

		$review_factors = array(
									'cln' => esc_html__( 'Cleanliness', 'trav' ),
									'cft' => esc_html__( 'Comfort', 'trav' ),
									'loc' => esc_html__( 'Location', 'trav' ),
									'fac' => esc_html__( 'Facilities', 'trav' ),
									'stf' => esc_html__( 'Staff', 'trav' ),
									'vfm' => esc_html__( 'Value for money', 'trav' ),
								);
		$i = 0;
		$review_detail = array( 0, 0, 0, 0, 0, 0 );
		if ( ! empty( $acc_meta['review_detail'] ) ) {
			$review_detail = is_array( $acc_meta['review_detail'] ) ? $acc_meta['review_detail'] : unserialize( $acc_meta['review_detail'] );
		}
		
		ob_start();
		?>
		<div class="write-review-form" >
			<div class="alert alert-error" style="display: none;"><span class="message"></span><span class="close"></span></div>
			<form id="review-form" method="post" class="review-form-inner review-form">
				<?php wp_nonce_field( 'post-' . $acc_id, '_wpnonce', false ); ?>
				<input type="hidden" name="review_rating" value="<?php echo esc_attr( $average_rating ) ?>">
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $acc_id ); ?>">
				<input type="hidden" name="action" value="acc_submit_review">

				<div class="form-title-part">
					<h3 class="section-inner-title"><?php echo esc_html__( 'Write a Review', 'trav' ); ?></h3>
					<p class="rating-txt"><?php echo esc_html__( 'Overall Rating', 'trav' ); ?>: <span class="ribbon">Disappointed</span></p>
				</div>

				<div class="individual-rating-part">
					<?php
						$i = 0;
						foreach ( $review_factors as $factor => $label ) {
							?>
							<div class="special-rating">
								<span class="title"><?php echo esc_html( $label ); ?></span>

								<p class="stars <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) ) ? 'selected' : ''; ?>">
									<span>
										<a class="star-1 <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) && $rating_detail[ $i ] == 1 ) ? 'active' : ''; ?>" href="#">1</a>
										<a class="star-2 <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) && $rating_detail[ $i ] == 2 ) ? 'active' : ''; ?>" href="#">2</a>
										<a class="star-3 <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) && $rating_detail[ $i ] == 3 ) ? 'active' : ''; ?>" href="#">3</a>
										<a class="star-4 <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) && $rating_detail[ $i ] == 4 ) ? 'active' : ''; ?>" href="#">4</a>
										<a class="star-5 <?php echo ( is_array( $rating_detail ) && isset( $rating_detail[ $i ] ) && $rating_detail[ $i ] == 5 ) ? 'active' : ''; ?>" href="#">5</a>
									</span>
									<input type="hidden" class="rating_detail_hidden validation-field" name="review_rating_detail[]" value="<?php echo ( is_array($rating_detail) && isset( $rating_detail[ $i ] ) ? esc_attr( $rating_detail[ $i ] ) : '' ); ?>">
								</p>
							</div>
							<?php
							$i++;
						}
					?>
				</div>

				<div class="main-form-inner">
					<div class="form-group">
						<label for="booking-no"><?php echo esc_html__( 'Booking No', 'trav' ); ?> <span class="required">*</span></label>
						<input type="text" name="booking_no" class="form-control validation-field" value="<?php if ( is_array( $booking_data ) && isset( $booking_data['booking_no'] ) ) echo esc_attr( $booking_data['booking_no'] ); ?>" data-error-message="<?php _e( 'Enter your booking number', 'trav' ); ?>" placeholder="<?php echo esc_attr__( 'Booking Number', 'trav' ); ?>">
					</div>

					<div class="form-group">
						<label for="pin-code"><?php echo esc_html__( 'Pin Code', 'trav' ); ?> <span class="required">*</span></label>
						<input type="text" name="pin_code" class="form-control validation-field" value="<?php if ( is_array( $booking_data ) && isset( $booking_data['pin_code'] ) ) echo esc_attr( $booking_data['pin_code'] ); ?>" data-error-message="<?php _e( 'Enter your pin code', 'trav' ); ?>" placeholder="<?php echo esc_attr__( 'Your pin code', 'trav' ); ?>">
					</div>

					<div class="form-group">
						<label for="review-title"><?php echo esc_html__( 'Review Title', 'trav' ); ?></label>
						<input type="text" name="review_title" class="form-control validation-field" value="<?php if ( is_array( $review_data ) && isset( $review_data['review_title'] ) ) echo esc_attr( $review_data['review_title'] ); ?>" data-error-message="<?php _e( 'Enter a review title', 'trav' ); ?>" placeholder="<?php echo esc_attr__( 'Your review title', 'trav' ); ?>">
					</div>

					<div class="form-group textarea-group">
						<label for="comment"><?php echo esc_html__( 'Your Comment', 'trav' ); ?></label>
						<textarea rows="10" class="validation-field" name="review_text" data-error-message="<?php _e( 'Enter your review', 'trav' ); ?>" placeholder="<?php echo esc_attr__( 'Your review', 'trav' ); ?>"><?php if ( is_array( $review_data ) && isset( $review_data['review_text'] ) ) echo esc_textarea( $review_data['review_text'] ); ?></textarea>
					</div>

					<div class="form-submit">
						<label for="submit"><?php echo esc_html__( 'Submit', 'trav' ); ?></label>
						<button type="submit" class="submit-btn review-form-submit"><?php echo esc_html__( 'Submit', 'trav' ); ?></button>
					</div>
				</div>
			</form>
		</div>
		<?php
		$result = ob_get_clean();
		return $result;
	}
}