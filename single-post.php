<?php
get_header();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();

		//init variables
		$post_id = get_the_ID();
		$post_link = get_permalink( $post_id );
		$posttags = get_the_tags();

		// add to user recent activity
		trav_update_user_recent_activity( $post_id ); ?>

		<?php if ( trav_get_current_style() == 'modern' ) : ?>
			<section id="content">
				<div class="post-featured-part">
					<?php trav_blog_morden_gallery( $post_id ) ?>

					<?php
					$post_main_class = 'post-main-content';
					$content_class = 'col-lg-12';
					$sidebar_class = 'col-lg-3';
					$sidebar_setting = get_post_meta( $post_id, 'sbg_selected_sidebar_replacement', true );
					
					if ( ! empty( $sidebar_setting ) ) {
						if ( '0' == $sidebar_setting[0] ) {
							$post_main_class .= ' no-sidebar';
						} else {
							$content_class = 'col-lg-9';
						}

						if ( 'left' == $sidebar_setting[0] ) {
							$sidebar_class .= ' order-first';
						}
					} else {
						$post_main_class .= ' no-sidebar';
					}
					?>

					<div class="container <?php echo esc_attr( $post_main_class ); ?>">
						<div class="row">
							<div class="<?php echo esc_attr( $content_class ); ?>">
								<div class="post-details-wrap">
									<div class="post-share-buttons">
										<div class="travelo-social-button facebook-icon">
											<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $post_link ); ?>">
												<i class="fab fa-facebook-f"></i>
											</a>
										</div>
										
										<div class="travelo-social-button twitter-icon">
											<a href="https://twitter.com/share?url=<?php echo esc_attr( $post_link ); ?>">
												<i class="fab fa-twitter"></i>
											</a>
										</div>

										<div class="travelo-social-button instagram-icon">
											<a href="https://instagram.com/share?url=<?php echo esc_attr( $post_link ); ?>">
												<i class="fab fa-instagram"></i>
											</a>
										</div>
									
										<div class="travelo-social-button google-icon">
											<a href="https://plus.google.com/share?url=<?php echo esc_attr( $post_link ); ?>">
												<i class="fab fa-google-plus-g"></i>
											</a>
										</div>
									</div>

									<div class="detail-inner">
										<h1 class="entry-title"><?php the_title();?></h1>
										<div class="post-meta">
											<div class="entry-date">
												<i class="far fa-calendar-alt"></i>
												<span class="date-value"><?php echo get_the_date( 'F j, Y' , $post_id ); ?></span>
											</div>
											<div class="entry-author">
												<i class="far fa-user"></i>
												<?php the_author_posts_link(); ?>
											</div>
											<div class="entry-action">
												<i class="far fa-comment"></i>
												<a href="<?php echo esc_url( get_comments_link( $post_id ) ); ?>" class="button entry-comment btn-small"><?php comments_number();?></a>
											</div>
										</div>
										<div class="post-content entry-content">
											<?php the_content();?>
											<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
										</div>
										<div class="post-tags">
											<?php if ( ! empty( $posttags ) ) the_tags( '' , '' ); ?>
										</div>
										<div class="related-posts-section">
											<h2 class="inner-title"><?php _e( 'Related Posts', 'trav' ); ?></h2>

											<?php $related = trav_get_related_posts( $post_id );?>
											<?php if ( $related->have_posts() ): ?>
												<div class="posts-slide owl-carousel">
													<?php while($related->have_posts()): $related->the_post(); ?>
													<div class="post-item">
														<?php the_post_thumbnail( 'related-post-thumb' ); ?>
														
														<div class="detail-info">
															<h5 class="post-title">
																<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
															</h5>

															<div class="post-meta">
																<div class="entry-date">
																	<i class="far fa-calendar-alt"></i>
																	<span class="date-vale"><?php echo get_the_date( 'F j, Y' ); ?></span>
																</div>
																<div class="entry-author">
																	<i class="far fa-user"></i>
																	<?php the_author_posts_link(); ?>
																</div>
															</div>
														</div>
													</div>
													<?php endwhile; ?>
												</div>
											<?php endif; ?>
											<?php wp_reset_query(); ?>
										</div>
										<?php comments_template(); ?>
									</div>
								</div>	
							</div>

							<?php if ( ! empty( $sidebar_setting ) ) : ?>
								<?php if ( '0' != $sidebar_setting[0] ) : ?>
								<div class="sidebar <?php echo esc_attr( $sidebar_class ); ?>">
									<?php generated_dynamic_sidebar(); ?>
								</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>
		<?php else : ?>
			<section id="content">
				<div class="container">
					<div class="row">
						<div id="main" class="col-sm-8 col-md-9">
							<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<?php $isv_setting = get_post_meta( $post_id, 'trav_post_media_type', true ); ?>
								<?php trav_post_gallery( $post_id ) ?>
								<div class="details<?php echo ( empty( $isv_setting ) || ( $isv_setting == 'no' ) )?' without-featured-item':''; ?>">
									<h1 class="entry-title"><?php the_title();?></h1>
									<div class="post-content entry-content">
										<?php the_content();?>
										<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
									</div>
									<div class="post-meta">
										<div class="entry-date">
											<label class="date"><?php echo get_the_date( 'd' , $post_id ); ?></label>
											<label class="month"><?php echo get_the_date( 'M' , $post_id ); ?></label>
										</div>
										<div class="entry-author fn">
											<i class="icon soap-icon-user"></i> <?php esc_html_e( 'Posted By', 'trav' ); ?>:
											<a href="#" class="author-section"><?php the_author_posts_link(); ?></a>
										</div>
										<div class="entry-action">
											<a href="#" class="button entry-comment btn-small">
												<i class="soap-icon-comment"></i>
												<span>
													<?php comments_number(); ?>
												</span>
											</a>
											<?php if ( ! empty( $posttags ) ) { ?>
												<span class="entry-tags"><i class="soap-icon-features"></i><span><?php the_tags( '' ); ?></span></span>
											<?php } ?>
										</div>
									</div>
								</div>
								<div class="single-navigation block">
									<div class="row">
										<?php $prev_post = get_previous_post(); ?>
										<?php if ( ! empty( $prev_post ) ): ?>
											<div class="col-xs-6"><a rel="prev" href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="button btn-large prev full-width"><i class="soap-icon-longarrow-left"></i><span><?php _e( 'Previous Post', 'trav' ) ?></span></a></div>
										<?php endif; ?>
										<?php $next_post = get_next_post(); ?>
										<?php if ( ! empty( $next_post ) ): ?>
											<div class="col-xs-6<?php if ( empty($prev_post) ) echo ' col-xs-offset-6'; ?>"><a rel="next" href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="button btn-large next full-width"><span><?php _e( 'Next Post', 'trav' ) ?></span><i class="soap-icon-longarrow-right"></i></a></div>
										<?php endif; ?>
									</div>
								</div>
								<div class="about-author block">
									<h2><?php _e( 'About Author', 'trav' ) ?></h2>
									<div class="about-author-container">
										<div class="about-author-content">
											<div class="avatar">
												<?php echo trav_get_avatar( array( 'id' => get_the_author_meta( 'ID' ), 'email' => get_the_author_meta('email'), 'size' => 96 ) ); ?>
											</div>
											<div class="description">
												<h4><?php the_author_posts_link(); ?></h4>
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
								<h2><?php _e( 'You Might Also Like This', 'trav' ); ?></h2>
								<?php $related = trav_get_related_posts( $post_id );?>
								<?php if ( $related->have_posts() ): ?>
									<div class="travelo-box">
										<div class="suggestions image-carousel style2" data-animation="slide" data-item-width="150" data-item-margin="22">
											<ul class="slides">
												<?php while($related->have_posts()): $related->the_post(); ?>
												<li>
													<a href="<?php the_permalink(); ?>" class="hover-effect">
														<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'middle-item' ) ); ?>
													</a>
													<h5 class="caption"><?php the_title(); ?></h5>
												</li>
												<?php endwhile; ?>
											</ul>
										</div>
									</div>
								<?php endif; ?>
								<?php wp_reset_query(); ?>
								<?php comments_template(); ?>
							</div>
						</div>
						<div class="sidebar col-sm-4 col-md-3">
							<?php generated_dynamic_sidebar(); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
<?php endwhile;
}
get_footer();