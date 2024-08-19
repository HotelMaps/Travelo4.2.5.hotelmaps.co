<?php
/**
 * Blog loop item
 */

$post_id = get_the_ID();
$post_content = '';
$post_content = get_the_content();
$blog_excerpt_length = 40;
$post_content = explode( ' ', $post_content, $blog_excerpt_length );

if ( count( $post_content ) >= $blog_excerpt_length ) {
	array_pop( $post_content );
	$post_content = implode( " ", $post_content ) . '... ';
} else {
	$post_content = implode( " ", $post_content );
}
?>

<div id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class(); ?>>
	<div class="post-content-wrapper">
		<div class="details-info">
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
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
			<div class="excerpt-container entry-content">
				<p class="summary-content"><?php echo $post_content; ?></p>
			</div>
		</div>

		<?php trav_blog_morden_gallery( $post_id ) ?>
	</div>
</div>