<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://jewmich.com
 * @since             1.0.0
 * @package           Jewmich_Sidebar
 *
 * @wordpress-plugin
 * Plugin Name:       Jewmich Sidebar
 * Plugin URI:        https://github.com/jewmich/jewmich-sidebar
 * Description:       Sidebar for jewmich.com
 * Version:           1.0.0
 * Author:            Mason Malone
 * Author URI:        http://masonm.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

add_shortcode('sidebar', 'jewmich_sidebar_shortcode');
add_shortcode('current_jewish_year', 'jewmich_sidebar_current_jewish_year');

add_action('admin_menu', 'jewmich_sidebar_admin_menu');

function jewmich_sidebar_admin_menu() {
	add_menu_page(
		'Sidebar Editor',
		'Sidebar Editor',
		'manage_options',
		'jewmich-sidebar',
		'jewmich_sidebar_admin_page'
	);
}

function jewmich_sidebar_admin_page() {
	if (!current_user_can('manage_options')) {
		wp_die('You do not have permission to access this page');
	}
	wp_enqueue_script('jquery');
	wp_enqueue_script('jewmich_sidebar_admin.js', plugins_url('jewmich_sidebar_admin.js', __FILE__), ['jquery'], '1', true);
	require('jewmich_sidebar_admin.php');
}

function jewmich_sidebar_shortcode($atts) {
	global $wpdb;
	$query = "SELECT position, description, url, img_src, date_type, date_start, date_end FROM Sidebar ORDER BY position ASC";
	$images = jewmich_sidebar_filter_rows_by_date($wpdb->get_results($query, ARRAY_A));
	$html = '';
	foreach ($images as $image) {
		$html .= '<p><a href="' . $image['url'] . '"><img border="0" src="' . $image['img_src'] . '" alt="' . $image['description'] . '"/></a></p>';
	}
	return $html;
}

function jewmich_sidebar_filter_rows_by_date($rows) {
	$ordered = array();
	foreach ($rows as $row) {
		if (is_null($row['date_end'])) {
			// default row
			if (isset($ordered[$row['position']])) {
				// position already taken
				continue;
			}
		} else {
			// non-default row. Check that today is in the date range 
			list($startYear, $startMonth, $startDay) = explode('-', $row['date_start']);
			list($endYear, $endMonth, $endDay) = explode('-', $row['date_end']);
			$calendar = ($row['date_type'] === 'Gregorian') ? CAL_GREGORIAN : CAL_JEWISH;
			$curYear = ($row['date_type'] === 'Gregorian') ? idate('Y') : jewmich_sidebar_current_jewish_year();
			if ($startYear === '0000') $startYear = $curYear;
			if ($endYear === '0000') $endYear = $curYear;
			if ($endMonth < $startMonth || ($endMonth === $startMonth && $endDay < $startDay)) {
				// end date is before start date, which must mean the date range spans a year (e.g. Dec 1st to Feb 15th)
				// need to figure out if we need to subtract 1 from the startDate or add 1 to the endDate.
				// do that by checking both cases
				if (isCurrentDateBetween(
					cal_to_jd($calendar, $startMonth, $startDay, $startYear-1),
					cal_to_jd($calendar, $endMonth, $endDay, $endYear)
				)) {
				$startYear--; 
				} else {
					$endYear++;
				}
			}
			$startJd = cal_to_jd($calendar, $startMonth, $startDay, $startYear);
			$endJd = cal_to_jd($calendar, $endMonth, $endDay, $endYear);
			if (!isCurrentDateBetween($startJd, $endJd)) continue; // today is outside date range
		}
		// if we got here, then row is valid
		$ordered[$row['position']] = $row;
	}
	return $ordered;
}

function jewmich_sidebar_current_jewish_year() {
	$currentJewishCal = cal_from_jd(unixtojd(time()), CAL_JEWISH);
	return $currentJewishCal['year'];
}

function isCurrentDateBetween($startJd, $endJd) {
   $curJd = unixtojd();
   return $startJd <= $curJd && $curJd <= $endJd;
}
