<?php

	global $logo_url, $my_account_page, $login_url, $signup_url, $language_count;

	$class_name = 'page-content-wrapper'; 

	if ( trav_get_opt( 'enable_gap', false ) == false ) {
		$class_name .= ' full-width-content';
	}
?>
	<div id="page-wrapper" class="<?php echo esc_attr( $class_name ); ?>">
		<?php
		trav_get_template( trav_get_opt( 'modern_header_style', 'header8' ) . '.php', '/templates/modern/headers/' );
		?>

		<?php if ( trav_get_opt( 'sticky_header_setting' ) ) : ?>
			<div class="travelo-sticky-header sticky-header">
				<div class="site-header-wrapper">				
					<div class="header-container">
						<div class="site-logo">
							<?php
							$sticky_logo = trav_get_opt( 'sticky_logo' );
							if ( ! empty( $sticky_logo['url'] ) ) {
					            $sticky_logo_url = $sticky_logo['url'];
					        } else {
					            $sticky_logo_url = TRAV_IMAGE_URL . '/logo.png';
					        }
							?>
							<a href="<?php echo home_url(); ?>" rel="home"><img src="<?php echo esc_url( $sticky_logo_url ); ?>" alt="<?php bloginfo('name'); ?>"></a>
						</div>

						<div class="navigation-section">
							<div class="main-navigation-menu">
								<?php
									if ( has_nav_menu( 'header-menu' ) ) {
										wp_nav_menu( array(
															'theme_location'	=> 'header-menu',
															'container'			=> false,
															'menu_class' => 'travelo-main-navigation',
															'walker'=>new Trav_Walker_Nav_Menu
													) ); 
									} else { ?>
										
										<ul id="main-navigation" class="travelo-main-navigation">
											<li class="menu-item item-level-0"><a href="<?php echo esc_url( home_url() ); ?>"><?php _e('Home', "trav"); ?></a></li>
											<li class="menu-item item-level-0"><a href="<?php echo esc_url( admin_url('nav-menus.php') ); ?>"><?php _e('Configure', "trav"); ?></a></li>
										</ul>
							
								<?php } ?>
							</div>
							
							<div class="multi-lang-cur-setting">
								<?php if ( trav_is_multi_currency() && trav_get_opt( 'header_currency_switch' ) ) : ?>
									<div class="multi-currency dropdown">
										<a class="currency-link dropdown-toggle" href="#" id="multi-curr-Dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html( trav_get_user_currency() ); ?></a>
										
										<div class="dropdown-menu animate fadeIn" aria-labelledby="multi-curr-Dropdown">
											<?php
											$all_currencies = trav_get_all_available_currencies();
											$available_currencies = trav_get_opt( 'site_currencies' );
											if ( ! empty( $all_currencies ) ) {
												foreach ( array_filter( $available_currencies ) as $key => $content) {
													if ( isset( $all_currencies[$key] ) ) {
														$params = $_GET;
														$params['selected_currency'] = $key;

														$paramString = http_build_query($params, '', '&amp;');
														echo '<a href="' . esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) . '?' . $paramString ) . '" class="dropdown-item" title="' . esc_attr( $all_currencies[$key] ) . '">' . esc_html( strtoupper( $key ) ) . '</a>';
													}
												}
											}
											?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( trav_get_opt( 'header_lang_switch' ) ) : ?>
									<?php if ( $language_count > 1 ) :
									$languages = icl_get_languages('skip_missing=1'); ?>
								
									<div class="multi-language dropdown">
										<a class="language-link dropdown-toggle" href="#" id="multi-lang-Dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo ICL_LANGUAGE_NAME ; ?></a>
										
										<div class="dropdown-menu animate fadeIn" aria-labelledby="multi-lang-Dropdown">
											<?php foreach ( $languages as $l ) : ?>
												<a class="dropdown-item" href="<?php echo $l['url']; ?>" title="<?php echo $l['translated_name']; ?>"><?php echo $l['translated_name']; ?></a>
											<?php endforeach; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="multi-language dropdown">
										<a class="language-link dropdown-toggle" href="#" id="multi-lang-Dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">English</a>
										
										<div class="dropdown-menu animate fadeIn" aria-labelledby="multi-lang-Dropdown">
											<a class="dropdown-item" href="#" title="English">English</a>
											<a class="dropdown-item" href="#" title="English">Dutch</a>
											<a class="dropdown-item" href="#" title="English">German</a>
											<a class="dropdown-item" href="#" title="English">French</a>
										</div>
									</div>
								<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

						<div class="right-section">
							<div class="sign-in-up-wrap">
								<?php if ( is_user_logged_in() ) { ?>
									<a href="<?php echo esc_url( wp_logout_url( trav_get_current_page_url() ) ); ?>" class="btn btn-link"><?php _e( 'LogOut', 'trav' ) ?></a>
								<?php } else { ?>
									<a href="<?php echo $login_url ?>" class="btn btn-link"><?php _e( 'LogIn', 'trav' ) ?></a>
									<?php if ( get_option('users_can_register') ) { ?>
										<a href="<?php echo $signup_url ?>" class="btn btn-secondary"><?php _e( 'SignUp', 'trav' ) ?></a>
									<?php } ?>
								<?php } ?>
							</div>
						</div>

						<div class="header-mobile-nav">
							<button class="travelo-burger-wrap">
								<span></span>
								<span></span>
								<span></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	