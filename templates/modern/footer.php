<?php
// Mobile Nav Setting
$menu_locations = get_nav_menu_locations();
$mobile_location = 'header-menu';

if ( isset( $menu_locations['mobile-side-menu'] ) && $menu_locations['mobile-side-menu'] != 0 ) {
	$mobile_location = 'mobile-side-menu';
}
?>
	<footer class="main-footer">
		<div class="footer-inner-wrap container">
			<div class="row">	
				<div class="footer-widget-1 col-lg-4 col-md-6">
					<?php dynamic_sidebar( 'sidebar-footer-1' );?>
				</div>

				<div class="footer-widget-2 col-lg-2 col-md-3">
					<?php dynamic_sidebar( 'sidebar-footer-2' );?>
				</div>

				<div class="footer-widget-3 col-lg-2 col-md-3">
					<?php dynamic_sidebar( 'sidebar-footer-3' );?>
				</div>

				<div class="footer-widget-4 col-lg-2 col-md-3">
					<?php dynamic_sidebar( 'sidebar-footer-4' );?>
				</div>

				<div class="footer-widget-5 col-lg-2 col-md-3">
					<?php dynamic_sidebar( 'sidebar-footer-5' );?>
				</div>
			</div>
		</div>
	</footer>

	<div class="opacity-overlay opacity-ajax-overlay"><i class="fas fa-spinner fa-spin spinner"></i></div>

	<div class="mobile-nav">
		<div class="close-btn">
			<a href="#" class="close-btn-link">
				<span></span>
				<span></span>
			</a>
		</div>

		<div class="mobile-navigation-content">
			<?php
			wp_nav_menu( array(
				'theme_location' => $mobile_location,
				'container' => false,
				'menu_class' => 'travelo-mobile-nav',
				'walker'=>new Trav_Walker_Nav_Menu
			) );
			?>
		</div>
	</div>
</div>