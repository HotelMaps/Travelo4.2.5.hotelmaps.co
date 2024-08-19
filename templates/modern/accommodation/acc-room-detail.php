<?php
/*
 * Accommodation Detail
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $acc_id, $room_type_id;

$city = trav_acc_get_city( $acc_id );
$country = trav_acc_get_country( $acc_id );

if ( ! empty( $acc_id ) ) : ?>

	<div class="single-content-section accommodation-details-info">
		<h3 class="subsection-title"><?php echo esc_html__( 'Accommodation Details', 'trav' ); ?></h3>

		<div class="detail-part-one">
			<div class="single-info">
				<span class="label"><?php echo esc_html__( 'Hotel Name', 'trav' ); ?>:</span>
				<span class="val"><?php echo get_the_title( $acc_id ); ?></span>
			</div>

			<?php
			$acc_meta = get_post_meta( $acc_id );
			$acc_detail_fields = array( 
				'star_rating'			=> array( 'label' => esc_html__( 'Rating Stars', 'trav' ), 'pre' => '', 'sur' => ' ' . esc_html__(  'star', 'trav' ) ),
				'charge_extra_people'	=> array( 'label' => esc_html__( 'Extra people', 'trav' ), 'pre' => '', 'sur' => '' ),
				'minimum_stay'			=> array( 'label' => esc_html__( 'Minimum Stay', 'trav' ), 'pre' => '', 'sur' => '' ),
				'security_deposit'		=> array( 'label' => esc_html__( 'Security Deposit', 'trav' ), 'pre' => '', 'sur' => ' ' . '%' ),
				'country'				=> array( 'label' => esc_html__( 'Country', 'trav' ), 'pre' => '', 'sur' => '' ),
				'city'					=> array( 'label' => esc_html__( 'City', 'trav' ), 'pre' => '', 'sur' => '' ),
				'address'				=> array( 'label' => esc_html__( 'Address', 'trav' ), 'pre' => '', 'sur' => '' ),
				'phone'					=> array( 'label' => esc_html__( 'Phone No', 'trav' ), 'pre' => '', 'sur' => '' ),				
			);

			foreach ( $acc_detail_fields as $field => $value ) {
				if ( empty( $$field ) ) {
					$$field = empty( $acc_meta["trav_accommodation_$field"] ) ? '' : $acc_meta["trav_accommodation_$field"][0];
				}
				if ( ! empty( $$field ) ) {
					$content = $value['pre'] . $$field . $value['sur'];
					?>
					<div class="single-info">
						<span class="label"><?php echo esc_html( $value['label'] ); ?>:</span>
						<span class="val"><?php echo esc_html( $content ); ?></span>
					</div>
					<?php
				}
			} 
			?>

		</div>

		<div class="detail-bottom-part">
			
			<?php if ( ! empty( $acc_meta["trav_accommodation_neighborhood"] ) ) : ?>
				<span class="label"><?php echo esc_html__( 'Neighborhood', 'trav' ); ?>:</span>
				<span class="text"><?php echo esc_html( $acc_meta["trav_accommodation_neighborhood"][0] ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $acc_meta["trav_accommodation_cancellation"] ) ) : ?>
				<span class="label"><?php echo esc_html__( 'Cancellation', 'trav' ); ?>:</span>
				<span class="text"><?php echo esc_html( $acc_meta["trav_accommodation_cancellation"][0] ); ?></span>
			<?php endif; ?>

		</div>
	</div>

	<?php
endif;