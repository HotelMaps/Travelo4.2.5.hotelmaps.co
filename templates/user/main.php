<?php
 if ( ! is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}

do_action( 'trav_before_dashboard' );
?>

<?php if ( trav_get_current_style() == 'modern' ) : ?>
	<div class="tab-container user-dashboard-wrap">
		<div class="nav nav-tabs">
			<a data-toggle="tab" href="#dashboard" class="nav-title active show">
				<span class="title-inner"><i class="soap-icon-anchor circle"></i> <?php echo __( 'Dashboard', 'trav' ) ?></span>
			</a>
			<a data-toggle="tab" href="#profile" class="nav-title">
				<span class="title-inner"><i class="soap-icon-user circle"></i><?php echo __( 'Profile', 'trav' ) ?></span>
			</a>
			<a data-toggle="tab" href="#booking" class="nav-title">
				<span class="title-inner"><i class="soap-icon-businessbag circle"></i><?php echo __( 'Booking', 'trav' ) ?></span>
			</a>
			<a data-toggle="tab" href="#wishlist" class="nav-title">
				<span class="title-inner"><i class="soap-icon-wishlist circle"></i><?php echo __( 'Wishlist', 'trav' ) ?></span>
			</a>
			<a data-toggle="tab" href="#settings" class="nav-title">
				<span class="title-inner"><i class="soap-icon-settings circle"></i><?php echo __( 'Settings', 'trav' ) ?></span>
			</a>
		</div>

		<div class="tab-content">
			<div id="dashboard" class="tab-pane fade show active">
				<?php trav_get_template( 'dashboard.php', '/templates/user' ); ?>
			</div>
			<div id="profile" class="tab-pane fade">
				<?php trav_get_template( 'profile.php', '/templates/user' ); ?>
			</div>
			<div id="booking" class="tab-pane fade">
				<?php trav_get_template( 'booking-history.php', '/templates/user' ); ?>
			</div>
			<div id="wishlist" class="tab-pane fade">
				<?php trav_get_template( 'wishlist.php', '/templates/user' ); ?>
			</div>
			<div id="settings" class="tab-pane fade">
				<?php trav_get_template( 'account.php', '/templates/user' ); ?>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="tab-container full-width-style arrow-left dashboard">
		<ul class="tabs">
			<li class="active"><a data-toggle="tab" href="#dashboard"><i class="soap-icon-anchor circle"></i><?php echo __( 'Dashboard', 'trav' ) ?></a></li>
			<li class=""><a data-toggle="tab" href="#profile"><i class="soap-icon-user circle"></i><?php echo __( 'Profile', 'trav' ) ?></a></li>
			<li class=""><a data-toggle="tab" href="#booking"><i class="soap-icon-businessbag circle"></i><?php echo __( 'Booking', 'trav' ) ?></a></li>
			<li class=""><a data-toggle="tab" href="#wishlist"><i class="soap-icon-wishlist circle"></i><?php echo __( 'Wishlist', 'trav' ) ?></a></li>
			<li class=""><a data-toggle="tab" href="#settings"><i class="soap-icon-settings circle"></i><?php echo __( 'Settings', 'trav' ) ?></a></li>
		</ul>
		<div class="tab-content">
			<div id="dashboard" class="tab-pane fade in active">
				<?php trav_get_template( 'dashboard.php', '/templates/user' ); ?>
			</div>
			<div id="profile" class="tab-pane fade">
				<?php trav_get_template( 'profile.php', '/templates/user' ); ?>
			</div>
			<div id="booking" class="tab-pane fade">
				<?php trav_get_template( 'booking-history.php', '/templates/user' ); ?>
			</div>
			<div id="wishlist" class="tab-pane fade">
				<?php trav_get_template( 'wishlist.php', '/templates/user' ); ?>
			</div>
			<div id="settings" class="tab-pane fade">
				<?php trav_get_template( 'account.php', '/templates/user' ); ?>
			</div>
		</div>
	</div>
<?php endif; ?>