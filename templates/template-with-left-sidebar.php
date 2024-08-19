<?php
 /*
 Template Name: Page Template With Left Sidebar
 */
get_header();

if ( trav_get_current_style() == 'modern' ) {
	trav_modern_page_heading();

	echo '<div class="main-content"><div class="main-wrap">';
}

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		?>
	
		<?php if ( trav_get_current_style() == 'default' ) : ?>
			<section id="content">
		<?php endif; ?>

			<div class="container">
				<div class="row">
					<div class="sidebar col-lg-3">
						<?php generated_dynamic_sidebar(); ?>
					</div>
					<div id="main" class="col-lg-9 entry-content">
						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="image-container block">
								<?php the_post_thumbnail(); ?>
							</figure>
						<?php } ?>
						<?php the_content(); ?>
						<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
					</div>
				</div>
			</div>
		
		<?php if ( trav_get_current_style() == 'default' ) : ?>
			</section>
		<?php endif; ?>
	<?php endwhile;
endif;

if ( trav_get_current_style() == 'modern' ) {
	echo '</div></div>';
}

get_footer();