<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function probonoseo_breadcrumb_items() {

	$items = array();
	$pos   = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => get_bloginfo( 'name' ),
		'item'     => home_url( '/' ),
	);

	if ( is_singular() ) {
		$post = get_post();

		if ( $post->post_type !== 'page' ) {
			$type = get_post_type_object( $post->post_type );
			if ( $type && ! empty( $type->has_archive ) ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $type->labels->name,
					'item'     => get_post_type_archive_link( $post->post_type ),
				);
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title(),
		);

		return $items;
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term->parent ) {
			$anc = array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) );
			foreach ( $anc as $id ) {
				$t = get_term( $id );
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $t->name,
					'item'     => get_term_link( $t ),
				);
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => $term->name,
		);

		return $items;
	}

	if ( is_archive() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_archive_title(),
		);

		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => 'Search results',
		);

		return $items;
	}

	if ( is_404() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => '404 Not Found',
		);

		return $items;
	}

	return $items;
}
