<?php
/**
 * Theme setup for Blackthorn Hunting.
 *
 * Kept deliberately small. The theme is configured through theme.json.
 * This file registers the pattern category and button styles, runs the
 * one-time site setup, and loads the stylesheet and the small front end
 * script.
 *
 * @package cambridgeshire-deer-stalking
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the pattern category used by the theme patterns.
 */
function cds_register_pattern_categories() {
	register_block_pattern_category(
		'cds-pages',
		array(
			'label' => __( 'Site pages', 'cambridgeshire-deer-stalking' ),
		)
	);
}
add_action( 'init', 'cds_register_pattern_categories' );

/**
 * Register the three button styles the patterns use, so they appear in
 * the editor's Styles panel and can be chosen for new buttons.
 */
function cds_register_block_styles() {
	$styles = array(
		'cds-primary'   => __( 'Primary', 'cambridgeshire-deer-stalking' ),
		'cds-secondary' => __( 'Secondary', 'cambridgeshire-deer-stalking' ),
		'cds-ondark'    => __( 'On dark', 'cambridgeshire-deer-stalking' ),
	);

	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/button',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'cds_register_block_styles' );

/**
 * The pages the one-time setup creates: slug, title, pattern, menu order.
 *
 * @return array
 */
function cds_site_pages() {
	return array(
		array( 'home', 'Home', 'cds/page-home', 10 ),
		array( 'deer-stalking', 'Deer Stalking', 'cds/page-deer-stalking', 20 ),
		array( 'the-deer', 'The Deer', 'cds/page-the-deer', 30 ),
		array( 'deer-management', 'Deer Management', 'cds/page-deer-management', 45 ),
		array( 'contact', 'Contact', 'cds/page-contact', 50 ),
	);
}

/**
 * One-time site setup: create the pages from their patterns, set the
 * front page, and switch on pretty permalinks. Runs once, guarded by an
 * option, so it is safe on every install including the eventual live host.
 * After it has run, the pages belong to the site owner: edits made in the
 * editor are never overwritten.
 */
function cds_create_site_pages() {
	if ( get_option( 'cds_pages_created' ) ) {
		return;
	}

	$registry = WP_Block_Patterns_Registry::get_instance();
	$pages    = cds_site_pages();

	// Every pattern must be registered before anything is created, so a
	// half-built site is never left behind. If not, try again next load.
	foreach ( $pages as $page ) {
		if ( ! $registry->is_registered( $page[2] ) ) {
			return;
		}
	}
	if ( ! $registry->is_registered( 'cds/page-privacy' ) ) {
		return;
	}

	// Setup runs on whichever request first loads the theme, which is
	// often a logged-out visitor. Content filtering for logged-out users
	// would strip the enquiry form fields, and the content here is the
	// theme's own, so filtering is lifted for the inserts and restored
	// straight after.
	kses_remove_filters();

	foreach ( $pages as $page ) {
		list( $slug, $title, $pattern_name, $order ) = $page;

		if ( get_page_by_path( $slug ) ) {
			continue;
		}

		$pattern = $registry->get_registered( $pattern_name );

		wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => $pattern['content'],
					'menu_order'   => $order,
				)
			)
		);
	}

	$home = get_page_by_path( 'home' );
	if ( $home && 'publish' === $home->post_status ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}

	// Privacy page: WordPress ships a draft that sits on the
	// /privacy-policy/ slug, so our content is published into that draft
	// rather than creating a second page. A privacy page that is already
	// published is left alone.
	$privacy_pattern = $registry->get_registered( 'cds/page-privacy' );
	$privacy_args    = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Privacy Policy',
		'post_name'    => 'privacy-policy',
		'post_content' => $privacy_pattern['content'],
		'menu_order'   => 60,
	);

	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
	$existing   = $privacy_id ? get_post( $privacy_id ) : get_page_by_path( 'privacy-policy' );

	if ( $existing && 'page' === $existing->post_type ) {
		if ( 'publish' !== $existing->post_status ) {
			$privacy_args['ID'] = $existing->ID;
			wp_update_post( wp_slash( $privacy_args ) );
		}
	} else {
		$new_id = wp_insert_post( wp_slash( $privacy_args ) );
		if ( $new_id && ! is_wp_error( $new_id ) ) {
			update_option( 'wp_page_for_privacy_policy', $new_id );
		}
	}

	kses_init();

	// Pretty permalinks. The stored rules are cleared rather than
	// flushed, so WordPress rebuilds them on the next request with the
	// new structure fully loaded. Flushing here would save rules built
	// for the old structure, and unknown addresses would not return 404.
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	update_option( 'rewrite_rules', '' );

	update_option( 'cds_pages_created', 1 );
}
add_action( 'init', 'cds_create_site_pages', 20 );

/**
 * One-time site identity and cleanup pass: name the site and remove the
 * default sample content.
 */
function cds_site_identity_cleanup() {
	if ( get_option( 'cds_setup_v2' ) ) {
		return;
	}

	update_option( 'blogname', 'Blackthorn Hunting' );
	update_option( 'blogdescription', 'Guided deer stalking in Cambridgeshire' );

	$sample_page = get_page_by_path( 'sample-page' );
	if ( $sample_page ) {
		wp_delete_post( $sample_page->ID, true );
	}

	$hello = get_posts(
		array(
			'name'        => 'hello-world',
			'post_type'   => 'post',
			'post_status' => 'any',
			'numberposts' => 1,
		)
	);
	if ( $hello ) {
		wp_delete_post( $hello[0]->ID, true );
	}

	update_option( 'cds_setup_v2', 1 );
}
add_action( 'init', 'cds_site_identity_cleanup', 21 );

/**
 * Enqueue the theme stylesheet on the front of the site.
 */
function cds_enqueue_styles() {
	wp_enqueue_style(
		'cds-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'cds_enqueue_styles' );

/**
 * Enqueue the front end script in the footer. The version is the file
 * modification time so browser caches refresh whenever the file changes.
 */
function cds_enqueue_scripts() {
	$script_path = get_theme_file_path( 'assets/js/site.js' );

	wp_enqueue_script(
		'cds-site',
		get_theme_file_uri( 'assets/js/site.js' ),
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cds_enqueue_scripts' );

/**
 * Load the same stylesheet inside the editor canvas.
 */
function cds_editor_styles() {
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'cds_editor_styles' );

/**
 * Start the html element with a no-js class. The head script below swaps
 * it for js before first paint, so the fade-in styles only ever apply
 * when the script can run.
 *
 * @param string $output The language attributes for the html element.
 * @return string
 */
function cds_no_js_class( $output ) {
	return $output . ' class="no-js"';
}
add_filter( 'language_attributes', 'cds_no_js_class' );

/**
 * Swap the no-js class for js as early as possible in the head. If the
 * main script has not reported in after three seconds (blocked, failed
 * to load, or broken), swap back so faded content is shown rather than
 * left invisible.
 */
function cds_no_js_swap() {
	echo "<script>( function ( d ) { d.classList.replace( 'no-js', 'js' ); setTimeout( function () { if ( ! window.cdsReady ) { d.classList.replace( 'js', 'no-js' ); } }, 3000 ); } )( document.documentElement );</script>\n";
}
add_action( 'wp_head', 'cds_no_js_swap', 1 );
