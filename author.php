<?php get_header(); ?>

<?php trav_modern_page_heading(); ?>

<section id="content">
	<div class="container">
		<div class="row">
			<div id="main" class="col-sm-8 col-md-9">
				<div>
					<div class="about-author block">
						<h2><?php echo __( 'About', 'trav' ) . ' ' . get_the_author_meta('display_name'); ?></h2>
						<div class="about-author-container">
							<div class="about-author-content">
								<div class="avatar">
									<?php
										echo wp_kses_post( trav_get_avatar( array( 'id' => get_the_author_meta( 'ID' ), 'email' => get_the_author_meta('email'), 'size' => 96 ) ) );
									?>
								</div>
								<div class="description">
									<p><?php the_author_meta("description"); ?></p>
								</div>
							</div>
							<div class="about-author-meta clearfix">
								<ul class="social-icons">
									<?php $author_twitter = get_the_author_meta( 'author_twitter' );?>
									<?php $author_gplus = get_the_author_meta( 'author_gplus' );?>
									<?php $author_facebook = get_the_author_meta( 'author_facebook' );?>
									<?php $author_linkedin = get_the_author_meta( 'author_linkedin' );?>
									<?php $author_dribbble = get_the_author_meta( 'author_dribbble' );?>
									<?php if ( ! empty( $author_twitter ) ) { ?><li><a href="<?php echo esc_url( $author_twitter ) ?>" target="_blank"><i class="soap-icon-twitter"></i></a></li><?php } ?>
									<?php if ( ! empty( $author_gplus ) ) { ?><li><a href="<?php echo esc_url( $author_gplus ) ?>" target="_blank"><i class="soap-icon-googleplus"></i></a></li><?php } ?>
									<?php if ( ! empty( $author_facebook ) ) { ?><li><a href="<?php echo esc_url( $author_facebook ) ?>" target="_blank"><i class="soap-icon-facebook"></i></a></li><?php } ?>
									<?php if ( ! empty( $author_linkedin ) ) { ?><li><a href="<?php echo esc_url( $author_linkedin ) ?>" target="_blank"><i class="soap-icon-linkedin"></i></a></li><?php } ?>
									<?php if ( ! empty( $author_dribbble ) ) { ?><li><a href="<?php echo esc_url( $author_dribbble ) ?>" target="_blank"><i class="soap-icon-dribble"></i></a></li><?php } ?>
								</ul>
								<div class="wrote-posts-count"><i class="soap-icon-slider"></i><span><b><?php the_author_posts() ?></b> <?php _e( 'Posts', 'trav' );?></span></div>
							</div>
						</div>
					</div>
				</div>
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
								<?php echo __( 'No posts found.', 'trav' ) ?>
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