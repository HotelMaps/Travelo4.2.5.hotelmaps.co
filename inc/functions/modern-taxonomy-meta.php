<?php

add_filter( 'rwmb_meta_boxes', 'trav_register_amenity_taxonomy_meta_boxes' );

if ( ! function_exists( 'trav_register_amenity_taxonomy_meta_boxes' ) ) {
	function trav_register_amenity_taxonomy_meta_boxes( $meta_boxes ){
		$meta_boxes[] = array(
			'title'      => esc_html__( 'Extra Informations', 'trav' ),
			'taxonomies' => 'amenity',

			'fields' => array(
				array(
					'name' => esc_html__( 'Icon Class', 'trav' ),
					'id'   => 'icon_class',
					'type' => 'text',
				),
			),
		);
		return $meta_boxes;
	}
}