<?php
/**
 * @link              https://greentreemediallc.com
 * @since             1.0.0
 * @wordpress-plugin
 * Plugin Name:       Divi Icon King
 * Description:       Add almost 2000 icons to the Divi Builder UI from Font Awesome and Material Design. Features a built in filter so you can find the icon you're looking for quickly. Buckle up, buddy.
 * Plugin URI:	      http://divi-icon-plugin.com/
 * Version:           2.0.0
 * Author:            Alex Brinkman
 * Author URI:        https://greentreemediallc.com
 * Text Domain:       divi-icon-king-gtm
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

define('DIKG_VERSION', '2.0.0');

define('DIKG_FONTAWESOME_URL', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
define('DIKG_MATERIAL_URL', 'https://fonts.googleapis.com/icon?family=Material+Icons');

define('DIKG_OPTIONS_NAME', 'dikg_settings');
define('DIKG_PLUGIN_SLUG', 'divi-icon-king-gtm');
define('DIKG_SETTINGS', 'divi_icon_king_gtm_settings');

add_filter('plugin_action_links', 'dikg_add_action_plugin', 10, 5);
add_filter('dikg_filter_front_icon', 'dikg_front_icon_filter');
add_filter('script_loader_tag', 'dikg_no_rocketscript', 10, 3); // No RocketScript

add_action('init', 'dikg_iconsplosion');
add_filter('body_class', 'dikg_custom_public_class');
add_filter('admin_body_class', 'dikg_custom_admin_class');

// Admin pages.
add_action('admin_init', 'dikg_setup_sections');
add_action('admin_init', 'dikg_setup_fields');
add_action('admin_menu', 'dikg_admin_menu');

// Load the script on both the admin and public.
add_action('admin_enqueue_scripts', 'dikg_admin_style');
add_action('admin_enqueue_scripts', 'dikg_plugin_style');

add_action('wp_enqueue_scripts', 'dikg_plugin_style');

function dikg_custom_public_class($classes) {
	$classes[] = 'divi-icon-king';
	return $classes;
}

function dikg_custom_admin_class($classes) {
	$classes .= 'divi-icon-king';
	return $classes;
}

/**
 * Filter plugin action links.
 */
function dikg_add_action_plugin($actions, $plugin_file) {
	static $plugin;

	if (! isset($plugin)) {
		$plugin = plugin_basename(__FILE__);
	}

	if ($plugin === $plugin_file) {
		$settings = ['settings' => '<a href="options-general.php?page=' . DIKG_SETTINGS . '">' . __('Settings', 'General') . '</a>'];

		$actions = array_merge($settings, $actions);
	}

	return $actions;
}

/**
 * Register the admin stylesheet on our settings page.
 */
function dikg_admin_style($hook) {
	if ($hook != 'settings_page_' . DIKG_SETTINGS) {
		return;
	}
	wp_enqueue_style(DIKG_PLUGIN_SLUG . 'admin', plugin_dir_url(__FILE__) . 'assets/' . DIKG_PLUGIN_SLUG . '-admin.css', [], DIKG_VERSION, 'all');
}

/**
 * Register the admin menu.
 */
function dikg_admin_menu() {
	add_submenu_page(
		'options-general.php',
		'Divi Icon King',
		'Divi Icon King',
		'manage_options',
		DIKG_SETTINGS,
		'dikg_settings_page'
	);
}

/**
 * Generate the admin settings page.
 */
function dikg_settings_page() {
	settings_errors('settings_messages'); ?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<section id="post-body" class="metabox-holder columns-2 gtm_plugin_settings__section">
			<form method="post" action="options.php" class="gtm_plugin_settings__form">
				<div class="gtm_plugin_settings">
					<?php
					settings_fields(DIKG_SETTINGS);
	do_settings_sections(DIKG_SETTINGS);
	submit_button(); ?>
				</div>
			</form>
		</section>
	</div>
	<?php
}

function dikg_setup_sections() {
	add_settings_section('divi_icon_king_settings', '', 'dikg_section_callback', DIKG_SETTINGS);
}

function dikg_section_callback($arguments) {
	switch ($arguments['id']) {
	case 'divi_icon_king_settings':
		echo '<p>Choose the icon fonts you\'d like to use. You can access the icons directly in the Divi Builder.</p>';
		break;
	}
}

function dikg_setup_fields() {
	// Our main setting we'll be saving our settings under.
	register_setting(DIKG_SETTINGS, DIKG_OPTIONS_NAME);
	register_setting(DIKG_SETTINGS, 'dikg_license_key');

	$settings = get_option(DIKG_OPTIONS_NAME);

	$enable_fontawesome = isset($settings['enable_fontawesome']) ? trim($settings['enable_fontawesome']) : false;
	$enable_material	= isset($settings['enable_material']) ? trim($settings['enable_material']) : false;
	$load_locally 		= isset($settings['load_locally']) ? trim($settings['load_locally']) : false;

	$fields = [
		[
			'uid' 				=> 'enable_fontawesome',
			'label' 			=> 'Enable Font Awesome',
			'section' 			=> 'divi_icon_king_settings',
			'type' 				=> 'checkbox',
			'is_toggle'			=> true,
			'options' 			=> false,
			'placeholder' 		=> '',
			'helper' 			=> '',
			'supplemental' 		=> '',
			'default' 			=> $enable_fontawesome,
		],
		[
			'uid' 				=> 'enable_material',
			'label' 			=> 'Enable Material Icons',
			'section'	 		=> 'divi_icon_king_settings',
			'type' 				=> 'checkbox',
			'is_toggle'			=> true,
			'options' 			=> false,
			'placeholder' 		=> '',
			'helper' 			=> '',
			'supplemental' 		=> '',
			'default' 			=> $enable_material,
		],
		[
			'uid' 				=> 'load_locally',
			'label'	 			=> 'Use local version(s)',
			'section' 			=> 'divi_icon_king_settings',
			'type' 				=> 'checkbox',
			'is_toggle'			=> true,
			'options' 			=> false,
			'placeholder' 		=> '',
			'helper' 			=> '',
			'tooltip'			=> 'If you want to load versions of the icons locally from your server instead of loading them remotely from their CDNs, check this box.',
			'supplemental'		=> '',
			'default' 			=> $load_locally,
		],
	];

	foreach ($fields as $field) {
		add_settings_field(
			$field['uid'],
			$field['label'],
			'dikg_field_callback',
			DIKG_SETTINGS,
			$field['section'],
			$field
		);
	}
}

function dikg_field_callback($arguments) {
	$option_name = DIKG_OPTIONS_NAME;
	$value = $arguments['default'] ?? '';

	// Check which type of field we want.
	switch ($arguments['type']) {
	case 'checkbox':
		if (isset($arguments['is_toggle']) && $arguments['is_toggle']) {
			printf('<input name="%4$s[%1$s]" id="%1$s" type="%2$s" class="tgl tgl-flat" %3$s /><label class="tgl-btn" for="%1$s"></label>', $arguments['uid'], $arguments['type'], (($value == 'on') ? 'checked' : ''), $option_name);
		} else {
			printf('<input name="%4$s[%1$s]" id="%1$s" type="%2$s" %3$s />', $arguments['uid'], $arguments['type'], (($value == 'on') ? 'checked' : ''), $option_name);
		}
		break;
	case 'text':
		printf('<input name="%5$s[%1$s]" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value, $option_name);
		break;
	case 'number':
		printf('<input name="%5$s[%1$s]" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value, $option_name);
		break;
	case 'textarea':
		printf('<textarea name="%4$s[%1$s]" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value, $option_name);
		break;
	case 'select':
		if (!empty($arguments['options']) && is_array($arguments['options'])) {
			$options_markup = '';

			foreach ($arguments['options'] as $key => $label) {
				$options_markup .= sprintf('<option value="%s" %s>%s</option>', $key, selected($value, $key, false), $label);
			}

			printf('<select name="%3$s[%1$s]" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup, $option_name);
		}
		break;
	}

	// If there is help text
	if ($helper = $arguments['helper']) {
		printf('<span class="helper"> %s</span>', $helper);
	}

	// If there is a tooltip
	if ($tooltip = $arguments['tooltip'] ?? false) {
		printf('<a href="#" class="gtm-tooltip" data-tooltip="%s"><span class="dashicons dashicons-editor-help"></span></a>', $tooltip);
	}

	// If there is supplemental text
	if ($supplemental = $arguments['supplemental']) {
		printf('<p class="description">%s</p>', $supplemental);
	}
}

function dikg_plugin_style() {
	$settings = get_option(DIKG_OPTIONS_NAME);

	$enable_fontawesome = isset($settings['enable_fontawesome']) ? trim($settings['enable_fontawesome']) : false;
	$enable_material	= isset($settings['enable_material']) ? trim($settings['enable_material']) : false;
	$load_locally 		= isset($settings['load_locally']) ? trim($settings['load_locally']) : false;

	if ($enable_fontawesome) {
		if (isset($load_locally) && $load_locally) {
			wp_enqueue_style(DIKG_PLUGIN_SLUG . '-fontawesome', plugin_dir_url(__FILE__) . 'vendor/font-awesome/css/font-awesome.min.css', [], DIKG_VERSION, 'all');
		} else {
			wp_enqueue_style(DIKG_PLUGIN_SLUG . '-fontawesome', DIKG_FONTAWESOME_URL, [], DIKG_VERSION, 'all');
		}
	}

	if ($enable_material) {
		if (isset($load_locally) && $load_locally) {
			wp_enqueue_style(DIKG_PLUGIN_SLUG . '-material', plugin_dir_url(__FILE__) . 'vendor/material/iconfont/material-icons.css', [], DIKG_VERSION, 'all');
		} else {
			wp_enqueue_style(DIKG_PLUGIN_SLUG . '-material', DIKG_MATERIAL_URL, [], DIKG_VERSION, 'all');
		}
	}

	// Load our custom stylesheet
	wp_enqueue_style(DIKG_PLUGIN_SLUG . '-custom', plugin_dir_url(__FILE__) . 'assets/' . DIKG_PLUGIN_SLUG . '.css', [], DIKG_VERSION, 'all');

	if (is_user_logged_in()) {
		wp_enqueue_script(DIKG_PLUGIN_SLUG . '-icon-filter', plugin_dir_url(__FILE__) . 'assets/divi-icon-king-gtm-icon-search.js', ['jquery'], DIKG_VERSION, true);
		wp_enqueue_style(DIKG_PLUGIN_SLUG . '-icon-filter', plugin_dir_url(__FILE__) . 'assets/divi-icon-king-gtm-icon-search.css', [], DIKG_VERSION, 'all');
	}

	wp_enqueue_script(DIKG_PLUGIN_SLUG . '-script', plugin_dir_url(__FILE__) . 'assets/' . DIKG_PLUGIN_SLUG . '.js', [], DIKG_VERSION . time(), true);
	add_filter('script_loader_tag', 'dikg_no_rocketscript', 10, 3); // No RocketScript
}

function dikg_iconsplosion() {
	$settings = get_option(DIKG_OPTIONS_NAME);

	$enable_fontawesome = isset($settings['enable_fontawesome']) ? trim($settings['enable_fontawesome']) : false;
	$enable_material	= isset($settings['enable_material']) ? trim($settings['enable_material']) : false;

	// Add new structured ET icons to the divi builder so we can filter them.
	add_filter('et_pb_font_icon_symbols', 'dikg_et_icons', 20);

	if ($enable_fontawesome) {
		add_filter('et_pb_font_icon_symbols', 'dikg_fontawesome_icons', 25);
	}

	if ($enable_material) {
		add_filter('et_pb_font_icon_symbols', 'dikg_material_icons', 30);
	}
}

/**
 * Add structured ET icons to Divi.
 */
function dikg_et_icons($icons) {
	// Ditch the original icons.
	$icons = [];

	require(__DIR__ . '/assets/elegantthemes.php');

	foreach ($elegantthemes_icons as $icon) {
		$icons[] = sprintf(
			'%1$s~|%2$s~|%3$s~|%4$s',
			$icon['unicode'],
			$icon['name'],
			$icon['family'],
			$icon['style']
		);
	}

	return $icons;
}

/**
 * Add Font Awesome icons to Divi.
 */
function dikg_fontawesome_icons($icons) {
	require(__DIR__ . '/assets/fontawesome.php');

	foreach ($fontawesome_icons as $icon) {
		$icons[] = sprintf(
			'%1$s~|%2$s~|%3$s~|%4$s',
			$icon['unicode'],
			$icon['name'],
			$icon['family'],
			$icon['style']
		);
	}

	return $icons;
}

/**
 * Add Material icons to Divi.
 */
function dikg_material_icons($icons) {
	require(__DIR__ . '/assets/material.php');

	foreach ($material_icons as $icon) {
		$icons[] = sprintf(
			'%1$s~|%2$s~|%3$s~|%4$s',
			$icon['unicode'],
			$icon['name'],
			$icon['family'],
			$icon['style']
		);
	}

	return $icons;
}

/**
 * Overwrites the same function in Divi's functions.php file.
 * Identify the new icons in the divi builder so we can handle
 * them accordingly.
 */
if (! function_exists('et_pb_get_font_icon_list_items')) {
	function et_pb_get_font_icon_list_items() {
		$output = '';

		$symbols = et_pb_get_font_icon_symbols();

		$filter_triggers = [];

		foreach ($symbols as $symbol) {
			$icon_data = explode('~|', $symbol);

			if (count($icon_data) > 1) {
				// Only ET icons in the customizer.
				if (is_customize_preview()) {
					if ($icon_data[2] !== 'elegant-themes') {
						continue;
					}
				}

				if (! in_array(esc_attr($icon_data[2]), $filter_triggers)) {
					$filter_triggers[] = $icon_data[2];
				}

				$output .= sprintf(
					'<li data-name=\'%1$s\' title=\'%1$s\' data-icon=\'%2$s\' data-family=\'%3$s\' class="divi-icon-king-gtm divi-icon-king-gtm--%3$s"></li>',
					$icon_data[1],
					$icon_data[0],
					$icon_data[2]
				);
			} else {
				$output .= sprintf('<li data-icon=\'%1$s\' data-family=\'elegant-themes\' class=\'divi-icon-king-gtm divi-icon-king-gtm--elegant-themes\'></li>', esc_attr($symbol));
			}
		}

		if (! is_customize_preview()) {
			$output .= '</ul>
			<div class="dikg_icon_filter dikg_icon_filter--closed">
			<span class="dikg_icon_filter__btn dikg_icon_filter--visible">Filter Icons</span>
			<div class="dikg_icon_filter__controls dikg_icon_filter--hidden">';

			if ($filter_triggers) {
				foreach ($filter_triggers as $trigger) {
					$output .=
						sprintf(
							'<span class="dikg_icon_filter__control_option dikg_icon_filter__control_option--inactive dikg_icon_filter__control_family" data-value="%1$s">%1$s</span>',
							$trigger
						);
				}
			}

			$output .=
				'<span class="dikg_icon_filter__control_option dikg_icon_filter__control_option--inactive dikg_icon_filter__control_action dikg_icon_filter__all">All</span> 
				<span class="dikg_icon_filter__control_option dikg_icon_filter__control_option--inactive dikg_icon_filter__control_action dikg_icon_filter__close">Close</span>
			</div>
			</div>';
		}
		return $output;
	}
}

/**
 * Overwrites the same function in Divi's functions.php file.
 * Handles icon output on the front end.
 */
if (! function_exists('et_pb_process_font_icon')) {
	function et_pb_process_font_icon($font_icon, $symbols_function = 'default') {
		// the exact font icon value is saved
		if (1 !== preg_match("/^%%/", trim($font_icon))) {
			return $font_icon;
		}

		// the font icon value is saved in the following format: %%index_number%%
		$icon_index   = (int) str_replace('%', '', $font_icon);
		$icon_symbols = 'default' === $symbols_function ? et_pb_get_font_icon_symbols() : call_user_func($symbols_function);
		$font_icon    = $icon_symbols[$icon_index] ?? '';

		// This is the only alteration to this function.
		return apply_filters('dikg_filter_front_icon', $font_icon);
	}
}

function dikg_front_icon_filter($font_icon) {
	if (dikg_is_json($font_icon)) {
		$icon = json_decode($font_icon, true);
		$icon = $icon['family'] . '-' . $icon['unicode'];
	} else {
		$icon = $font_icon;
	}
	return $icon;
}

/**
 * Tell CloudFlare to ignore RocketScripting my script.
 */
function dikg_no_rocketscript($tag, $handle, $src) {
	if (DIKG_PLUGIN_SLUG . '-script' === $handle) {
		$tag = '<script data-cfasync="false" src="' . esc_url($src) . '" ></script>';
	}
	return $tag;
}

/**
 * Checks if a string is valid json
 */
function dikg_is_json($string): bool {
	return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
}
