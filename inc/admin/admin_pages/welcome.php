<?php
$theme = wp_get_theme();

if ( $theme->parent_theme ) {
    $template_dir =  basename( get_template_directory() );
    $theme = wp_get_theme( $template_dir );
}
?>

<div class="wrap about-wrap travelo-wrap">
    <h1><?php _e( 'Welcome to Travelo Dashboard!', 'trav' ); ?></h1>

    <div class="about-text"><?php echo esc_html__( 'Travelo theme is now installed and ready to use! Read below for additional information. We hope you\'ll enjoy it!', 'trav' ); ?></div>

    <h2 class="nav-tab-wrapper">
        <?php
        printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', __( 'Welcome', 'trav' ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=travelo-demo' ), __( 'Plugins & Demo Content', 'trav' ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=theme_options' ), __( 'Theme Options', 'trav' ) );
        ?>
    </h2>

    <div class="travelo-section">
        <p class="about-description">
            <?php printf( __( 'Before you get started, please be sure to always check out <a href="%s" target="_blank">this documentation</a>. We outline all kinds of good information, and provide you with all the details you need to use Travelo.', 'trav'), 'http://www.soaptheme.net/document/travelo-wp/'); ?>
        </p>
        <p class="about-description">
            <?php printf( __( 'If you are unable to find your answer in our documentation, please contact us via <a href="%s">email</a> directly with your purchase code, site CPanel (or FTP) and admin login info. <br><br>We are very happy to help you and you will get reply from us more faster than you expected.', 'trav'), 'mailto:soaptheme@gmail.com'); ?>
        </p>
        <p class="about-description">
            <a target="_blank" href="https://themeforest.net/item/travelo-traveltour-booking-wordpress-theme/9806696#item-description__changelog" title="<?php _e('Change Logs', 'trav') ?>"><?php _e('Click here to view change logs.', 'trav') ?></a>
        </p>


        <p class="about-description">
            Regarding <b>customization services</b> based on Travelo theme or other WordPress projects, please contact us via <a href="mailto:soaptheme@gmail.com" title="Customization Services">soaptheme@gmail.com</a> directly. We have an amazing team to provide customization service who have rich experience and work on reasonable quote.
        </p>
    </div>

    <div class="travelo-thanks">
        <p class="description">Thank you for using <strong>Travelo</strong> theme! Powered by <a href="https://themeforest.net/user/soaptheme" target="_blank">SoapTheme</a></p>
    </div>
</div>