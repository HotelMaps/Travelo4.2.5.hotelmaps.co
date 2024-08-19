<?php
/**
 *	Team Member Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [trav_modern_team_member]
function trav_shortcode_team_member( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'member_picture'	=>	'',
		'member_name'		=>	'',
		'member_job'		=>	'',
		'extra_class'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	'',
		'facebook_link'		=>	'#',
		'twitter_link'		=>	'#',
		'linkedin_link'		=>	'#'
	), $atts ) );

	$rand_id = rand( 100, 9999 );
	$team_member_id = uniqid( 'trav-team-member-' . $rand_id );

	$classes = $socials = $social_btn_classes = $html = '';

	if ( ! empty( $extra_class ) ) {
		$classes .= ' ' . $extra_class;
	}

	$member_img = trav_get_image( $member_picture, 'member-thumb' );

	$social_btn_classes = 'trav-social-buttons';

	if ( '' != $facebook_link || '' != $twitter_link || '' != $linkedin_link || '' != $google_plus_link || '' != $instagram_link ) {
		$socials .= '<div class="' . esc_attr( $social_btn_classes ) . '">';
			if ( '' != $facebook_link ) {
				$socials .= '<div class="trav-social-button facebook-icon"><a href="' . esc_url( $facebook_link ) . '"><i class="fab fa-facebook-f"></i></a></div>';
			}

			if ( '' != $twitter_link ) {
				$socials .= '<div class="trav-social-button twitter-icon"><a href="' . esc_url( $facebook_link ) . '"><i class="fab fa-twitter"></i></a></div>';
			}

			if ( '' != $linkedin_link ) {
				$socials .= '<div class="trav-social-button linkedin-icon"><a href="' . esc_url( $facebook_link ) . '"><i class="fab fa-linkedin-in"></i></a></div>';
			}
		$socials .= '</div>';
	}

	$html .= '<div id="' . esc_attr( $team_member_id ) . '" class="trav-team-member ' . esc_attr( $classes ) . '">';
		$html .= '<div class="member-image-wrap">';
			$html .= '<div class="member-image">' . $member_img . '</div>';
			$html .= $socials;
		$html .= '</div>';

		$html .= '<div class="member-detail-info">';
			$html .= '<h5 class="member-name">' . stripslashes( $member_name ) . '</h5>';
			$html .= '<span class="member-job">' . stripslashes( $member_job ) . '</span>';
		$html .= '</div>';
	$html .= '</div>';

	return $html;
}

add_shortcode( 'trav_modern_team_member', 'trav_shortcode_team_member' );

/**
 * WPBakery
 */
function trav_vc_shortcode_team_member() {
	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Team Member', 'trav' ),
		'base'			=>	'trav_modern_team_member',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Show information about team member.', 'trav' ),
		'params'		=>	array(
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Member Picture', 'trav' ),
				'param_name'	=>	'member_picture',
			),

			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Member Name', 'trav' ),
				'param_name'	=>	'member_name'
			),

			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Member Job', 'trav' ),
				'param_name'	=>	'member_job',
			),

			$extra_class,

			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'FaceBook Social Link', 'trav' ),
				'param_name'	=>	'facebook_link',
				'group'			=>	esc_html__( 'Social', 'trav' ),
				'std'			=>	'#'
			),

			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Twitter Social Link', 'trav' ),
				'param_name'	=>	'twitter_link',
				'group'			=>	esc_html__( 'Social', 'trav' ),
				'std'			=>	'#'
			),

			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'LinkedIn Social Link', 'trav' ),
				'param_name'	=>	'linkedin_link',
				'group'			=>	esc_html__( 'Social', 'trav' ),
				'std'			=>	'#'
			)
		)
	) );
}

trav_vc_shortcode_team_member();