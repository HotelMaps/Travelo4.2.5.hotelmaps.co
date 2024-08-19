<?php
get_header();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID(); ?>

		<section id="content">
			<div class="container">
				<div class="row">
					<div id="main" class="col-sm-8 col-md-9">
						<div class="post" id="post-<?php echo esc_attr( $post_id ); ?>">
							<?php $isv_setting = get_post_meta( $post_id, 'trav_post_media_type', true ); ?>
							<?php trav_post_gallery( $post_id ) ?>
							<div class="details<?php echo ( empty( $isv_setting ) || ( $isv_setting == 'no' ) )?' without-featured-item':''; ?>">
								<h1 class="entry-title"><?php the_title();?></h1>
								<div class="post-content entry-content">
									<?php the_content();?>
									<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="sidebar col-sm-4 col-md-3">
						<?php generated_dynamic_sidebar(); ?>
					</div>
				</div>
			</div>
		</section>
<?php endwhile;
}
get_footer();