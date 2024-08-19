<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="with-icon full-width">
		<input type="text" class="input-text full-width" placeholder="<?php _e('story name or category', 'trav') ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" id="s">
		<button type="submit" class="icon green-bg white-color"><i class="soap-icon-search"></i></button>
		<input type="hidden" name="post_type" value="post">
	</div>
</form>