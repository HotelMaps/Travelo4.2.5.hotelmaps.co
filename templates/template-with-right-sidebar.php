<?php
 /*
 Template Name: Page Template With Right Sidebar
 */
get_header();

trav_modern_page_heading();

if ( have_posts() ) :
	while ( have_posts() ) : the_post();		
		?>

		<section id="content">
			<div class="container">
				<div class="row">
					<div id="main" class="col-sm-8 col-md-9 entry-content">
						<?php if ( has_post_thumbnail() ) { ?>
							<figure class="image-container block">
								<?php the_post_thumbnail(); ?>
							</figure>
						<?php } ?>
						<?php the_content(); ?>
						<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
					</div>
					<div class="sidebar col-sm-4 col-md-3">
						<?php generated_dynamic_sidebar(); ?>
					</div>
				</div>
			</div>
		</section>
	<?php endwhile;
endif;
get_footer();