<?php
/**
 * Walkthrough router — dispatches to the correct walkthrough sub-page.
 * Included by profile.php when action=walkthroughs or action=walkthrough
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}

$topic = $_GET['topic'] ?? '';

$allowed = [
    'login_bypass',
    'union',
    'error_based',
    'blind_boolean',
    'time_based',
    'second_order',
    'privesc',
    'waf_bypass',
    'oob',
    'routed',
    'api',
    'tools',
];

if ($topic !== '' && in_array($topic, $allowed, true)) {
    require __DIR__ . "/walkthroughs/{$topic}.php";
} else {
    require __DIR__ . '/walkthroughs/index.php';
}
