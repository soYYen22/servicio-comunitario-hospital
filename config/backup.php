<?php

use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;

return [
	'backup' => [
		// Use BACKUP_NAME env var so backups can be stored in a predictable folder
		// Default to 'backups' so the app's BackupController (which expects 'backups/') works.
		'name' => env('BACKUP_NAME', 'backups'),

		'source' => [
			'files' => [
				'include' => [
					base_path(),
				],

				'exclude' => [
					base_path('vendor'),
					storage_path(),
				],
			],

			// Leave empty to avoid calling external dump utilities on this machine.
			// If you want database backups, set the connections here, for example:
			// ['mysql'] or ['pgsql'] and ensure the related dump binary (mysqldump/pg_dump)
			// is installed and available in PATH.
			'databases' => [],
		],

		'destination' => [
			'filename_prefix' => '',
			'disks' => [
				// Use the local disk so backups are stored on your PC (storage/app)
				'local',
			],
		],
	],

	'cleanup' => [
		'strategy' => DefaultStrategy::class,

		'default_strategy' => [
			'keep_all_backups_for_days' => 7,
			'keep_daily_backups_for_days' => 16,
			'keep_weekly_backups_for_weeks' => 8,
			'keep_monthly_backups_for_months' => 4,
			'keep_yearly_backups_for_years' => 2,
			'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
		],
	],

	'notifications' => [
		// Map the package's notification classes to empty arrays so no channels are used.
		// This prevents the package from throwing "no notification class" and also
		// disables sending emails/slack/etc.
		'notifications' => [
			\Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => [],
			\Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => [],
			\Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => [],
			\Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => [],
			\Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => [],
			\Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => [],
		],

		'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

		'mail' => [
			'to' => env('BACKUP_MAIL_TO', null),
			'from' => [
				'address' => env('MAIL_FROM_ADDRESS', null),
				'name' => env('MAIL_FROM_NAME', null),
			],
		],
	],

	'monitor_backups' => [
		// empty; no monitoring checks
	],

	'backup_views' => [],
];
