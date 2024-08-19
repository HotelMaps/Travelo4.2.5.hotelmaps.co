<?php 
get_header(); 

$content_class = "col-lg-9";
$side_class = "col-lg-3";

if ( trav_get_current_style() == 'modern' ) {
	$content_class = "col-lg-8";
	$side_class = "col-lg-4";
}
?>

<section id="content">
	<?php trav_modern_page_heading(); ?>
	
	<div class="container">
		<div class="row">
			<div id="main" class="<?php echo esc_attr( $content_class ); ?> entry-content">
				<div class="page">
					<div class="post-content">
						<div class="blog-infinite">
							<?php if ( have_posts() ) : ?>
								<?php while(have_posts()): the_post();
									if ( trav_get_current_style() == 'modern' ) {
										trav_get_template( 'loop-blog.php', '/templates/modern/' ); 
									} else {
										trav_get_template( 'loop-blog.php', '/templates' );
									}
								endwhile; ?>
							<?php endif; ?>
						</div>

						<?php
						if ( trav_get_current_style() == 'modern' ) {
							?>
							<div class="travelo-pagination">
								<?php echo paginate_links( array(
															'type'		=>	'list',
															'prev_text'	=>	esc_html__( 'Prev', 'trav' ),
															'next_text'	=>	esc_html__( 'Next', 'trav' ),
														) ); ?>
							</div>
							<?php
						} else {
							global $ajax_paging;
							if ( ! empty( $ajax_paging ) ) {
								next_posts_link( __( 'LOAD MORE POSTS', 'trav' ) );
							} else {
								echo paginate_links( array( 'type' => 'list' ) );
							}
						}
						?>
					</div>
				</div>
				<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
			</div>
			<div class="sidebar <?php echo esc_attr( $side_class ); ?>">
				<?php generated_dynamic_sidebar(); ?>
			</div>
		</div>
	</div>
</section>

<?php get_footer();