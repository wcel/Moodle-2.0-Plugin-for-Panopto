<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/panopto/block_panopto.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'courseid'   => false,
        'fullforce'  => false,
        'debug'      => false,
        'help'       => false
    ),
    array(
        'h' => 'help'
    )
);
$courseid  = $options['courseid'];
$fullforce = $options['fullforce'];
$debugthis = $options['debug'];
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['help']) {
    $help =
"Command line: Sync user list
Sync users from Moodle courses to Panopto folders

Options:
--courseid      Process just one course by id
--fullforce     Use option to force sync for courseid
--debug         Turn on developer debugging

-h, --help      Print out this help

Example:
\$sudo -u apache /usr/bin/php blocks/panopto/cli/syncusers.php
"; //TODO: localize - to be translated later when everything is finished
    echo $help;
    die;
}

if ($debugthis) {
    $CFG->debug = DEBUG_DEVELOPER;
}

$block = new block_panopto();
$trace = new progress_trace_buffer(new text_progress_trace(), true); // output and buffer
if ($courseid) {
    $prompt = 'Sync users for courseid#'.$courseid.'? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit(1);
    }
    $block->sync_users($courseid, $fullforce, $trace);
} else {
    $prompt = 'Full user sync users? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit(1);
    }
    $block->sync_users(null, false, $trace);
}
$messagetext = $trace->get_buffer();
email_to_user(get_admin(), get_admin(), 'Panopto cron notification', $messagetext);
mtrace('Done!');
