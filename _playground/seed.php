<?php
/**
 * Demo content for the WordPress Playground "Try it live" link.
 *
 * Runs once inside Playground: blueprint.json embeds this file (rebuild it
 * with `php bin/build-blueprint.php`). Uploads six images, four of them missing alt text, and opens the audit screen where they can be fixed inline.
 *
 * Not loaded by the plugin.
 *
 * @package Image_Alt_Text_Auditor
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

wp_set_current_user( 1 );

// Start from a clean site: remove the default "Hello world!" post and sample page.
foreach ( get_posts( array( 'post_type' => array( 'post', 'page' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $demo_old ) {
	wp_delete_post( $demo_old->ID, true );
}

update_option( 'blogname', 'Image Alt Text Auditor' );
update_option( 'blogdescription', 'Live demo' );
// Pretty permalinks. Set them through WP_Rewrite, not update_option(): in
// Playground the routing rules already exist by now, and only this updates
// WordPress's in-memory copy so the flush rebuilds them correctly.
$GLOBALS['wp_rewrite']->set_permalink_structure( '/%postname%/' );
flush_rewrite_rules();

/**
 * Upload one of the Fieldnote theme's images and return its attachment ID.
 *
 * @param string $name Image file name without extension.
 * @return int Attachment ID, or 0 when the theme image is unavailable.
 */
function demo_theme_image( $name ) {
	$source = get_theme_file_path( "assets/images/$name.webp" );
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	$upload = wp_upload_bits( "$name.webp", null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $name,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	return $id;
}

/**
 * A long article in block markup: H2 sections with H3 subsections.
 *
 * @param array $sections Section titles.
 * @param int   $paras    Paragraphs per section.
 * @return string Block markup.
 */
function demo_article( $sections, $paras = 2 ) {
	$text = 'A good website is less about the launch and more about the years after it. The people editing it next need clear patterns, sensible defaults and documentation they will actually read. Small decisions made early, like naming, structure and what is configurable, decide how easy every later change will be.';
	$html = '';
	foreach ( $sections as $section ) {
		$html .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $section ) . '</h2><!-- /wp:heading -->';
		for ( $i = 0; $i < $paras; $i++ ) {
			$html .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
		}
		$html .= '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $section ) . ' checklist</h3><!-- /wp:heading -->';
		$html .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
	}
	return $html;
}

/**
 * Insert a published post or page.
 *
 * @param array $args wp_insert_post() arguments.
 * @return int Post ID.
 */
function demo_post( $args ) {
	return wp_insert_post( array_merge( array( 'post_status' => 'publish' ), $args ) );
}

/**
 * Write a small demo-only must-use plugin. These files exist only inside
 * the Playground demo; they are not part of the plugin.
 *
 * @param string $name File name.
 * @param string $code PHP source.
 */
function demo_mu_plugin( $name, $code ) {
	wp_mkdir_p( WPMU_PLUGIN_DIR );
	file_put_contents( trailingslashit( WPMU_PLUGIN_DIR ) . $name, $code );
}

/** Skip the block editor's welcome guide so visitors land straight on the content. */
function demo_skip_welcome_guide( $extra = array() ) {
	update_user_meta(
		1,
		$GLOBALS['wpdb']->get_blog_prefix() . 'persisted_preferences',
		array_merge(
			array(
				'core/edit-post' => array( 'welcomeGuide' => false ),
				'core'           => array( 'welcomeGuide' => false ),
				'_modified'      => gmdate( 'c' ),
			),
			$extra
		)
	);
}

// Six media library images: four without alt text (they appear in the audit),
// two with good alt text (they don't).
$demo_images = array(
	array( 'studio-shapes', 'homepage-hero', '' ),
	array( 'field-study', 'team-retreat-photo', '' ),
	array( 'notebook', 'blog-header-notebook', '' ),
	array( 'studio-shapes', 'product-shot-final-v2', '' ),
	array( 'field-study', 'about-page-illustration', 'Abstract illustration of cream, terracotta and gold shapes on a sage green field.' ),
	array( 'notebook', 'newsletter-banner', 'Terracotta circle and sage half circle over a cream card on a gold background.' ),
);
foreach ( $demo_images as $i => $img ) {
	$source = get_theme_file_path( "assets/images/{$img[0]}.webp" );
	if ( ! file_exists( $source ) ) {
		continue;
	}
	$upload = wp_upload_bits( "{$img[1]}.webp", null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		continue;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $img[1],
			'post_status'    => 'inherit',
			'post_date'      => gmdate( 'Y-m-d H:i:s', time() - $i * HOUR_IN_SECONDS ),
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	if ( '' !== $img[2] ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $img[2] );
	}
}
