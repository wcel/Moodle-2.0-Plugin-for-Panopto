<?php
// Prepend the instance name to the Moodle course ID to create an external ID for Panopto Focus.
function decorate_course_id($moodle_course_id)
{
  global $CFG;
	return ($CFG->block_panopto_instance_name . ":" . $moodle_course_id);
}

// Decorate a moodle username with the instancename outside the context of a panopto_data object.
function decorate_username($moodle_username)
{
  global $CFG;
	return ($CFG->block_panopto_instance_name . "\\" . $moodle_username); 
}

// Sign the payload with the proof that it was generated by trusted code. 
function generate_auth_code($payload)
{
  global $CFG;
	$sharedSecret = $CFG->block_panopto_application_key;
	
	$signed_payload = $payload . "|" . $sharedSecret;
	
	$auth_code = sha1($signed_payload);
	$auth_code = strtoupper($auth_code);
	
	return $auth_code;
}

function validate_auth_code($payload, $auth_code)
{
	return (generate_auth_code($payload) == $auth_code);
}
?>