<?php
get_header();

trav_modern_page_heading();

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		?>

		<section id="content">
			<div class="container">
				<div id="main" class="entry-content main-content">
					<?php if ( has_post_thumbnail() ) { ?>
						<figure class="image-container block">
							<?php the_post_thumbnail(); ?>
						</figure>
					<?php } ?>
					<?php the_content(); ?>
					<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
					<?php //comments_template(); ?>
				</div>
			</div>
		</section>
	<?php endwhile;
endif;
get_footer();