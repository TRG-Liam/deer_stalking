<?php
/**
 * Theme setup for Blackthorn Hunting.
 *
 * Kept deliberately small. The theme is configured through theme.json.
 * This file registers the pattern category, runs the one-time site setup,
 * and loads the stylesheet and the small front end script.
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
 * One-time site setup: create the five pages from their patterns, set the
 * front page, and switch on pretty permalinks. Runs once, guarded by an
 * option, so it is safe on every install including the eventual live host.
 */
function cds_create_site_pages() {
	if ( get_option( 'cds_pages_created' ) ) {
		return;
	}

	$registry = WP_Block_Patterns_Registry::get_instance();
	$pages    = array(
		array( 'home', 'Home', 'cds/page-home', 10 ),
		array( 'deer-stalking', 'Deer Stalking', 'cds/page-deer-stalking', 20 ),
		array( 'the-deer', 'The Deer', 'cds/page-the-deer', 30 ),
		array( 'your-guide', 'Your Guide', 'cds/page-your-guide', 40 ),
		array( 'deer-management', 'Deer Management', 'cds/page-deer-management', 45 ),
		array( 'contact', 'Contact', 'cds/page-contact', 50 ),
	);

	$front_id = 0;
	foreach ( $pages as $page ) {
		list( $slug, $title, $pattern_name, $order ) = $page;

		if ( get_page_by_path( $slug ) ) {
			continue;
		}

		$pattern = $registry->get_registered( $pattern_name );
		if ( ! $pattern ) {
			return; // Patterns not registered yet; try again next load.
		}

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $pattern['content'],
				'menu_order'   => $order,
			)
		);

		if ( 'home' === $slug && $page_id && ! is_wp_error( $page_id ) ) {
			$front_id = $page_id;
		}
	}

	if ( $front_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_id );
	}

	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
	update_option( 'cds_pages_created', 1 );
}
add_action( 'init', 'cds_create_site_pages', 20 );

/**
 * One-time site identity and cleanup pass: name the site and remove the
 * default sample content so it never appears in the page-list navigation.
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

	$hello = get_posts( array( 'name' => 'hello-world', 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1 ) );
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
 * Swap the no-js class for js as early as possible in the head.
 */
function cds_no_js_swap() {
	echo "<script>document.documentElement.classList.replace( 'no-js', 'js' );</script>\n";
}
add_action( 'wp_head', 'cds_no_js_swap', 1 );
