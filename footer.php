<?php 
/**
* Footer
 */

if ( trav_get_current_style() == 'modern' ) {
    trav_get_template( 'footer.php', '/templates/modern/' ); 
} else {

    global $trav_options, $logo_url;
    $footer_skin = empty( $trav_options['footer_skin'] ) ? 'style-def' : $trav_options['footer_skin'];
    ?>

    <footer id="footer" class="<?php echo esc_attr( $footer_skin ) ?>">
        <div class="footer-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <?php dynamic_sidebar( 'sidebar-footer-1' ); ?>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <?php dynamic_sidebar( 'sidebar-footer-2' ); ?>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <?php dynamic_sidebar( 'sidebar-footer-3' ); ?>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <?php dynamic_sidebar( 'sidebar-footer-4' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom gray-area">
            <div class="container">
                <div class="logo pull-left">
                    <a href="<?php echo esc_url( home_url() ); ?>">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo('name'); ?>" />
                    </a>
                </div>
                <div class="pull-right">
                    <a id="back-to-top" href="#"><i class="soap-icon-longarrow-up circle"></i></a>
                </div>
                <div class="copyright pull-right">
                    <p>&copy; <?php echo esc_html( $trav_options['copyright'] ); ?></p>
                </div>
            </div>
        </div>
    </footer>
    </div>
    <div class="opacity-overlay opacity-ajax-overlay"><i class="fa fa-spinner fa-spin spinner"></i></div>
    <?php
}
?>

<!-- إضافة wp_footer قبل إغلاق body مباشرة -->
<?php wp_footer(); ?>
</body>
</html>
