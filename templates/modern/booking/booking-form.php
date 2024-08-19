<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$user_info = trav_get_current_user_info();
$_countries = trav_get_all_countries();
global $trav_options;

do_action( 'trav_booking_form_before' ); 
?>
<div class="personal-form-wrapper">
	<div class="form-group">
		<label for="first-name"><?php echo esc_html__( 'First Name', 'trav' ); ?> <span class="required">*</span></label>
		<input type="text" name="first_name" class="form-control" placeholder="<?php echo esc_attr__( 'Your first name', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="last-name"><?php echo esc_html__( 'Last Name', 'trav' ); ?> <span class="required">*</span></label>
		<input type="text" name="last_name" class="form-control" placeholder="<?php echo esc_attr__( 'Your last name', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="email"><?php echo esc_html__( 'E-mail', 'trav' ); ?> <span class="required">*</span></label>
		<input type="email" name="email" class="form-control" placeholder="<?php echo esc_attr__( 'Your email address', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="email"><?php echo esc_html__( 'Verify E-mail Address', 'trav' ); ?> <span class="required">*</span></label>
		<input type="email" name="email2" class="form-control" placeholder="<?php echo esc_attr__( 'Your email address', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="country"><?php echo esc_html__( 'Country Code', 'trav' ); ?></label>
		<select name="country_code" class="form-control">
			<?php foreach ( $_countries as $_country ) { ?>
				<option value="<?php echo esc_attr( $_country['d_code'] ) ?>" <?php selected( $user_info['country_code'], $_country['name'] . ' (' . $_country['d_code'] . ')' ); ?>><?php echo esc_html( $_country['name'] . ' (' . $_country['d_code'] . ')' ); ?></option>
			<?php } ?>
		</select>
	</div>

	<div class="form-group">
		<label for="phone"><?php echo esc_html__( 'Phone', 'trav' ); ?> <span class="required">*</span></label>
		<input type="text" name="phone" class="form-control" placeholder="<?php echo esc_attr__( 'Your phone number', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="address"><?php echo esc_html__( 'Address', 'trav' ); ?></label>
		<input type="text" name="address" class="form-control" placeholder="<?php echo esc_attr__( 'Your complete address', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="city"><?php echo esc_html__( 'City', 'trav' ); ?></label>
		<input type="text" name="city" class="form-control" placeholder="<?php echo esc_attr__( 'Your city', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="zip-code"><?php echo esc_html__( 'Zip Code', 'trav' ); ?></label>
		<input type="text" name="zip" class="form-control" placeholder="<?php echo esc_attr__( 'Your zip code', 'trav' ); ?>">
	</div>

	<div class="form-group">
		<label for="country"><?php echo esc_html__( 'Country', 'trav' ); ?></label>
		<select name="country" class="form-control">
			<?php foreach ( $_countries as $_country ) { ?>
				<option value="<?php echo esc_attr( $_country['name'] ) ?>" <?php selected( $user_info['country_code'], $_country['name'] ); ?>><?php echo esc_html( $_country['name'] ); ?></option>
			<?php } ?>
		</select>
	</div>

	<div class="form-group textarea-group">
		<label for="comment"><?php echo esc_html__( 'Special Requirements', 'trav' ); ?></label>
		<textarea rows="10" name="special_requirements" placeholder="<?php echo esc_attr__( 'Your special requirements', 'trav' ); ?>"></textarea>
	</div>

</div>

<?php do_action( 'trav_booking_form_after' ); ?>

<div class="form-group form-submit">
	<button type="submit" class="submit-btn"><?php echo esc_html__( 'Confirm Booking', 'trav' ); ?></button>
</div>

