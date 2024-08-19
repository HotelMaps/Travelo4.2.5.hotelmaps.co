<?php get_header(); ?>

<section id="content">
	<div class="container">
		<div class="row">
			<div id="main" class="col-sm-8 col-md-9">
				<!-- <div class="travelo-box">
					<h3><?php echo __( 'New Search', 'trav' );?></h3>
					<p><?php echo __( 'If you are not happy with the results below please do another search.', 'trav' ) ?></p>
					<div class="row">
						<div class="col-md-5">
							<?php get_search_form(); ?>
						</div>
					</div>
				</div> -->
				<div class="page">
					<div class="post-content">
						<?php if ( have_posts() ): ?>
							<div class="blog-infinite">
								<?php while(have_posts()): the_post();
									trav_get_template( 'loop-blog.php', '/templates' );
								endwhile; ?>
							</div>
							<?php
								global $ajax_paging;
								if ( ! empty( $ajax_paging ) ) {
									next_posts_link( __( 'LOAD MORE POSTS', 'trav' ) );
								} else {
									echo paginate_links( array( 'type' => 'list' ) );
								}
							?>
						<?php else: ?>
							<div class="travelo-box">
								<h2><?php echo __( "Nothing Found", 'trav'); ?></h2>
								<p><?php echo __( "Sorry, no posts matched your criteria. Please try another search.", 'trav' ); ?><br /><?php echo __( "You might want to consider some of our suggestions to get better results:", 'trav' ); ?></p>
								<ul class="triangle">
									<li><?php echo __( "Check your spelling.", 'trav' ); ?></li>
									<li><?php echo __( "Try a similar keyword.", 'trav' ); ?></li>
									<li><?php echo __( "Try using more than one keyword.", 'trav' ); ?></li>
									<li><?php echo __( "See frequently asked questions.", 'trav' ); ?></li>
									<li><?php echo __( "Contact the support center.", 'trav' ); ?></li>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="sidebar col-sm-4 col-md-3">
				<?php dynamic_sidebar( 'sidebar-post' ); ?>
			</div>
		</div>
	</div>
</section>

<?php get_footer();