<?php
 /*
 * 404 page
 */
?>
<?php global $trav_options, $logo_url; ?>
<!DOCTYPE html>
<!--[if IE 7 ]>    <html class="ie7 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE   ]>    <html class="ie" <?php language_attributes(); ?>> <![endif]-->
<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<html <?php language_attributes(); ?>>
<head>
	<!-- Page Title -->
	<title><?php wp_title(' - ', true, 'right'); ?></title>

	<!-- Meta Tags -->
	<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php if ( ! empty( $trav_options['favicon'] ) && ! empty( $trav_options['favicon']['url'] ) ): ?>
	<link rel="shortcut icon" href="<?php echo esc_url( $trav_options['favicon']['url'] ); ?>" type="image/x-icon" />
	<?php endif; ?>

	<!-- Theme Styles -->
	<link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500' rel='stylesheet' type='text/css'>

	<!-- CSS for IE -->
	<!--[if lte IE 9]>
		<link rel="stylesheet" type="text/css" href="css/ie.css" />
	<![endif]-->

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script type='text/javascript' src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	  <script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js"></script>
	<![endif]-->
	<?php wp_head();?>
</head>
<body <?php body_class( array( 'style1', 'error404' ) ); ?>>
	<div id="page-wrapper">
		<header id="header" class="navbar-static-top">
			<a href="#mobile-menu-01" data-toggle="collapse" class="mobile-menu-toggle blue-bg">Mobile Menu Toggle</a>
			<div class="container"><h1 class="logo"></h1></div>
			<!-- mobile menu -->
			<?php if ( has_nav_menu( 'header-menu' ) ) {
					wp_nav_menu( array( 'theme_location' => 'header-menu', 'container' => 'nav', 'container_class' => 'mobile-menu collapse', 'container_id' => 'mobile-menu-01', 'menu_class' => 'menu', 'menu_id' => 'mobile-primary-menu' ) ); 
				} else { ?>
					<nav id="mobile-menu-01" class="mobile-menu collapse">
						<ul id="mobile-primary-menu" class="wrap">
							<li class="menu-item"><a href="<?php echo esc_url( home_url() ); ?>"><?php _e('Home', "trav"); ?></a></li>
							<li class="menu-item"><a href="<?php echo esc_url( admin_url('nav-menus.php') ); ?>"><?php _e('Configure', "trav"); ?></a></li>
						</ul>
					</nav>
			<?php } ?>
			<!-- mobile menu -->
		</header>
		<section id="content">
			<div class="container">
				<div id="main">
					<h1 class="logo block">
						<a href="<?php echo esc_url( home_url() ); ?>" title="<?php bloginfo('name'); ?> - <?php _e( 'Home', 'trav' ); ?>">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo('name'); ?>" />
						</a>
					</h1>
					<div class="col-md-6 col-sm-9 no-float no-padding center-block">
						<div class="error-message"><?php _e( 'The page <i>you were looking for</i> could not be found.' ,'trav' ); ?></div>
					</div>
					<div class="error-message-404">
						404
					</div>
				</div>
			</div>
		</section>
		
		<footer id="footer">
			<div class="footer-wrapper">
				<div class="container">
					<?php if ( has_nav_menu( 'header-menu' ) ) {
							wp_nav_menu( array( 'theme_location' => 'header-menu', 'container' => 'nav', 'container_id' => 'main-menu', 'container_class'=>'inline-block hidden-mobile', 'menu_class' => 'menu', 'walker'=>new Trav_Walker_Nav_Menu ) ); 
						} else { ?>
							<nav id="main-menu" class="inline-block hidden-mobile">test
								<ul class="menu">
									<li class="menu-item"><a href="<?php echo esc_url( home_url() ); ?>"><?php _e('Home', "trav"); ?></a></li>
									<li class="menu-item"><a href="<?php echo esc_url( admin_url('nav-menus.php') ); ?>"><?php _e('Configure', "trav"); ?></a></li>
								</ul>
							</nav>
					<?php } ?>
					<div class="copyright">
						<p>&copy; <?php echo esc_html( $trav_options['copyright'] ) ?></p>
					</div>
				</div>
			</div>
		</footer>
	</div>
<?php wp_footer(); ?>
</body>
</html>