<?php

/**
 * Order statuses for CyberSource Online Gateway.
 *
 * @package Abzer
 */
defined('ABSPATH') || exit;

return array(
	array(
		'status' => 'wc-pending',
		'label' => 'Recurrente Pending',
	),
	array(
		'status' => 'wc-completed',
		'label' => 'Recurrente Complete',
	),
	array(
		'status' => 'wc-error',
		'label' => 'Recurrente Error',
	),
	array(
		'status' => 'wc-reject',
		'label' => 'Recurrente Reject',
	),
	array(
		'status' => 'wc-review',
		'label' => 'Recurrente Review',
	),
	array(
		'status' => 'wc-failed',
		'label' => 'Recurrente Failed',
	),
	array(
		'status' => 'wc-declined',
		'label' => 'Recurrente Declined',
	),
);
