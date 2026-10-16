<?php
/**
 * Builds _playground/blueprint.json from _playground/seed.php.
 *
 * The seed lives in a real PHP file so it can be linted and tested; this
 * script embeds it into the blueprint's runPHP step.
 *
 * Usage: php bin/build-blueprint.php
 *
 * @package Image_Alt_Text_Auditor
 */

$root = dirname( __DIR__ );
$seed = file_get_contents( $root . '/_playground/seed.php' );

$blueprint = array(
	'$schema'           => 'https://playground.wordpress.net/blueprint-schema.json',
	'meta'              => array(
		'title'       => 'Image Alt Text Auditor demo',
		'description' => 'Uploads six images, four of them missing alt text, and opens the audit screen where they can be fixed inline.',
		'author'      => 'jdyach',
		'categories'  => array( 'Plugin' ),
	),
	'landingPage'       => '/wp-admin/upload.php?page=image-alt-text-auditor',
	'preferredVersions' => array(
		'php' => '8.3',
		'wp'  => 'latest',
	),
	'features'          => array( 'networking' => true ),
	'login'             => true,
	'steps'             => array(
		array(
			'step'      => 'installTheme',
			'themeData' => array(
				'resource' => 'git:directory',
				'url'      => 'https://github.com/jdyach/fieldnote',
				'ref'      => 'main',
			),
			'options'   => array( 'activate' => true ),
		),
		array(
			'step'       => 'installPlugin',
			'pluginData' => array(
				'resource' => 'git:directory',
				'url'      => 'https://github.com/jdyach/image-alt-text-auditor',
				'ref'      => 'main',
			),
			'options'    => array( 'activate' => true ),
		),
		array(
			'step' => 'runPHP',
			'code' => $seed,
		),
	),
);

file_put_contents(
	$root . '/_playground/blueprint.json',
	json_encode( $blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n"
);
echo "Wrote _playground/blueprint.json\n";
