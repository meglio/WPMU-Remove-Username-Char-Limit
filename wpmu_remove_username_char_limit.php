<?php
/*
Plugin Name: Remove Username Char Limit
Description: Removes username char limit in Multisite Setup; introduces new limit to min 2 chars instead of 4.
Author: Anton Andriyevskyy
Version: 1.0
Author URI: http://megliosoft.org
*/


/**
 * How it will be called:
 *
 * $result = array('user_name' => $user_name, 'orig_username' => $orig_username, 'user_email' => $user_email, 'errors' => $errors);
 * return apply_filters('wpmu_validate_user_signup', $result);
 *
 * @param array $result Validation result passed when apply_filters called
 * @return mixed
 */

function wpmu_remove_username_char_limit($result)
{
	# $result must have key we will work with
	if (!array_key_exists('errors', $result) || !array_key_exists('orig_username', $result) || !array_key_exists('user_name', $result))
		return $result;

	# Only deal if there is really short username
	if (strlen($result['orig_username']) >= 4 && strlen($result['user_name']) >= 4)
		return $result;

	# Only deal if there are errors
	/** @var WP_Error $errors */
	$errors = $result['errors'];
	if (!is_wp_error($errors) || empty($errors->errors))
		return $result;

	# At least one user_name error must be present
	if (!is_array($errors->errors) || empty($errors->errors) || !array_key_exists('user_name', $errors->errors))
		return $result;

	$lookupMsg = __('Username must be at least 4 characters');

	# Loop over user_name errors and remove character limit
	$newErrors = array();
	foreach($errors->errors['user_name'] as $ind => $msg)
		if (strcasecmp($msg, $lookupMsg) != 0)
			$newErrors[] = $msg;
	if (!empty($newErrors))
		$errors->errors['user_name'] = $newErrors;
	else
		unset($errors->errors['user_name']);

	# Now confirm new username limit
	if (strlen($result['user_name']) < 2)
		$errors->add('user_name',  __('Username must be at least 2 characters'));

	return $result;
}

add_action('wpmu_validate_user_signup', 'wpmu_remove_username_char_limit');