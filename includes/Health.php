<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
	exit;
}

abstract class Health extends Plugin {

	public    static function load(): void {

		if ( self::$is_proisp ) {
			\add_filter( 'wp_update_https_url', function( string $update_url ): string {
				$update_url = _x( 'https://www.proisp.eu/guides/install-free-ssl-certificate-autossl/', 'Site Health Status' );
				return $update_url;
			} );
		}
	}

	public    static function admin_load(): void {

		\add_filter( 'site_status_tests', function( array $tests ): array {
			self::init_data();
			if ( self::$disk_space_max && self::$disk_space_used ) {
				$tests['direct']['disk-space'] = [
					'label' => _x( 'Disk usage', 'Site Health Status Label' ),
					'test'  => [ __CLASS__, 'disk_space_test' ],
				];
			}
			if ( \str_starts_with( \get_home_url(), 'https://' ) ) {
				$tests['direct']['https-only'] = [
					'label' => _x( 'Security', 'Site Health Info' ),
					'test'  => [ __CLASS__, 'https_only_test' ],
				];
			}
			if ( self::$is_cpanel ) {
				$tests['direct']['email-routing'] = [
					'label' => _x( 'Email routing', 'Site Health Status Label' ),
					'test'  => [ __CLASS__, 'email_routing_test' ],
				];
				$tests['direct']['cpanel-errors'] = [
					'label' => _x( 'Server Errors', 'Site Health Status Label' ),
					'test'  => [ __CLASS__, 'errors_test' ],
				];
			}
			return $tests;
		} );

		\add_filter( 'debug_information', function( array $debug_info ): array {
			/// Dummies
			$text = _x( 'MySQL® Disk Usage', 'Site Health Status Label' );
			$text = _x( 'Entry Processes', 'Site Health Status Label' );
			$text = _x( 'CPU Usage', 'Site Health Status Label' );
			$text = _x( 'I/O Usage', 'Site Health Status Label' );
			$text = _x( 'Physical Memory Usage', 'Site Health Status Label' );
			$text = _x( 'IOPS', 'Site Health Status Label' );
			$text = _x( 'Number of Processes', 'Site Health Status Label' );
			$text = _x( 'ok', 'Site Health Status - Usage status' );

			self::init_data();
			$debug_info['wp-constants']['fields']['WP_CONTENT_URL' ] = [
				'label'   => 'WP_CONTENT_URL',
				'value'   => \defined( 'WP_CONTENT_URL' ) ? ( \is_string( \WP_CONTENT_URL ) ? \WP_CONTENT_URL : ( \WP_CONTENT_URL ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'WP_CONTENT_URL' ) ? \WP_CONTENT_URL : 'undefined',
			];
			$debug_info['wp-constants']['fields']['FORCE_SSL_ADMIN' ] = [
				'label'   => 'FORCE_SSL_ADMIN',
				'value'   => \defined( 'FORCE_SSL_ADMIN' ) ? ( \is_string( \FORCE_SSL_ADMIN ) ? \FORCE_SSL_ADMIN : ( \FORCE_SSL_ADMIN ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'FORCE_SSL_ADMIN' ) ? \FORCE_SSL_ADMIN : 'undefined',
			];
			$debug_info['wp-constants']['fields']['SAVEQUERIES' ] = [
				'label'   => 'SAVEQUERIES',
				'value'   => \defined( 'SAVEQUERIES' ) ? ( \is_string( \SAVEQUERIES ) ? \SAVEQUERIES : ( \SAVEQUERIES ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'SAVEQUERIES' ) ? \SAVEQUERIES : 'undefined',
			];
			$debug_info['wp-constants']['fields']['WP_DISABLE_FATAL_ERROR_HANDLER' ] = [
				'label'   => 'WP_DISABLE_FATAL_ERROR_HANDLER',
				'value'   => \defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ? ( \is_string( \WP_DISABLE_FATAL_ERROR_HANDLER ) ? \WP_DISABLE_FATAL_ERROR_HANDLER : ( \WP_DISABLE_FATAL_ERROR_HANDLER ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ? \WP_DISABLE_FATAL_ERROR_HANDLER : 'undefined',
			];
			$debug_info['wp-constants']['fields']['ALLOW_UNFILTERED_UPLOADS' ] = [
				'label'   => 'ALLOW_UNFILTERED_UPLOADS',
				'value'   => \defined( 'ALLOW_UNFILTERED_UPLOADS' ) ? ( \is_string( \ALLOW_UNFILTERED_UPLOADS ) ? \ALLOW_UNFILTERED_UPLOADS : ( \ALLOW_UNFILTERED_UPLOADS ? _x( 'Enabled', 'Site Health Info' ) : _x( 'Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'ALLOW_UNFILTERED_UPLOADS' ) ? \ALLOW_UNFILTERED_UPLOADS : 'undefined',
			];
			$debug_info['wp-constants']['fields']['WP_ALLOW_REPAIR' ] = [
				'label'   => 'WP_ALLOW_REPAIR',
				'value'   => \defined( 'WP_ALLOW_REPAIR' ) ? ( \is_string( \WP_ALLOW_REPAIR ) ? \WP_ALLOW_REPAIR : ( \WP_ALLOW_REPAIR ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'WP_ALLOW_REPAIR' ) ? \WP_ALLOW_REPAIR : 'undefined',
			];
			$debug_info['wp-constants']['fields']['AUTOMATIC_UPDATER_DISABLED' ] = [
				'label'   => 'AUTOMATIC_UPDATER_DISABLED',
				'value'   => \defined( 'AUTOMATIC_UPDATER_DISABLED' ) ? ( \is_string( \AUTOMATIC_UPDATER_DISABLED ) ? \AUTOMATIC_UPDATER_DISABLED : ( \AUTOMATIC_UPDATER_DISABLED ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'AUTOMATIC_UPDATER_DISABLED' ) ? \AUTOMATIC_UPDATER_DISABLED : 'undefined',
			];
			if ( ! \defined( 'AUTOMATIC_UPDATER_DISABLED' ) || ! \AUTOMATIC_UPDATER_DISABLED ) {
				$debug_info['wp-constants']['fields']['WP_AUTO_UPDATE_CORE' ] = [
					'label'   => 'WP_AUTO_UPDATE_CORE',
					'value'   => \defined( 'WP_AUTO_UPDATE_CORE' ) ? ( \is_string( \WP_AUTO_UPDATE_CORE ) ? \WP_AUTO_UPDATE_CORE : ( \WP_AUTO_UPDATE_CORE ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'WP_AUTO_UPDATE_CORE' ) ? \WP_AUTO_UPDATE_CORE : 'undefined',
				];
			}
			$debug_info['wp-constants']['fields']['CORE_UPGRADE_SKIP_NEW_BUNDLED' ] = [
				'label'   => 'CORE_UPGRADE_SKIP_NEW_BUNDLED',
				'value'   => \defined( 'CORE_UPGRADE_SKIP_NEW_BUNDLED' ) ? ( \is_string( \CORE_UPGRADE_SKIP_NEW_BUNDLED ) ? \CORE_UPGRADE_SKIP_NEW_BUNDLED : ( \CORE_UPGRADE_SKIP_NEW_BUNDLED ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'CORE_UPGRADE_SKIP_NEW_BUNDLED' ) ? \CORE_UPGRADE_SKIP_NEW_BUNDLED : 'undefined',
			];
			$debug_info['wp-constants']['fields']['DISALLOW_FILE_MODS' ] = [
				'label'   => 'DISALLOW_FILE_MODS',
				'value'   => \defined( 'DISALLOW_FILE_MODS' ) ? ( \DISALLOW_FILE_MODS ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'DISALLOW_FILE_MODS' ) ? \DISALLOW_FILE_MODS : 'undefined',
			];
			if ( ! \defined( 'DISALLOW_FILE_MODS' ) || ! \DISALLOW_FILE_MODS ) {
				$debug_info['wp-constants']['fields']['DISALLOW_FILE_EDIT' ] = [
					'label'   => 'DISALLOW_FILE_EDIT',
					'value'   => \defined( 'DISALLOW_FILE_EDIT' ) ? ( \DISALLOW_FILE_EDIT ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) ) : _x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'DISALLOW_FILE_EDIT' ) ? \DISALLOW_FILE_EDIT : 'undefined',
				];
			}
			$debug_info['wp-constants']['fields']['WP_POST_REVISIONS' ] = [
				'label'   => 'WP_POST_REVISIONS',
				'value'   => \defined( 'WP_POST_REVISIONS' ) ?
					( \is_int( \WP_POST_REVISIONS ) && \WP_POST_REVISIONS ?
						\WP_POST_REVISIONS :
						( \WP_POST_REVISIONS ? _x( 'Enabled', 'Site Health Info' ) : _x('Disabled', 'Site Health Info' ) )
					) :
					_x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'WP_POST_REVISIONS' ) ? \WP_POST_REVISIONS : 'undefined',
			];
			if ( self::$is_debug ) {
				$debug_info['wp-constants']['fields']['CUSTOM_USER_TABLE'] = [
					'label'   => 'CUSTOM_USER_TABLE',
					'value'   => \defined( 'CUSTOM_USER_TABLE' ) ?
						\CUSTOM_USER_META_TABLE :
						_x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'CUSTOM_USER_TABLE' ) ? \CUSTOM_USER_TABLE : 'undefined',
					'private' => true,
				];
				$debug_info['wp-constants']['fields']['CUSTOM_USER_META_TABLE'] = [
					'label'   => 'CUSTOM_USER_META_TABLE',
					'value'   => \defined( 'CUSTOM_USER_META_TABLE' ) ?
						\CUSTOM_USER_META_TABLE :
						_x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'CUSTOM_USER_META_TABLE' ) ? \CUSTOM_USER_META_TABLE : 'undefined',
					'private' => true,
				];
			}
			$debug_info['wp-constants']['fields']['WF_DEBUG'] = [
				'label'   => 'WF_DEBUG',
				'value'   => \defined( 'WF_DEBUG'           ) ? ( \WF_DEBUG           ? _x( 'Enabled', 'Site Health Info' ) : _x( 'Disabled', 'Site Health Info' ) ) : _x( 'Undefined', 'Site Health Info' ),
				'debug'   => \defined( 'WF_DEBUG' ) ? \WF_DEBUG : 'undefined',
				'private' => false,
			];
			if ( self::$is_debug ) {
				$debug_info['wp-constants']['fields']['WF_DEV_LOGIN'] = [
					'label'   => 'WF_DEV_LOGIN',
					'value'   => \defined( 'WF_DEV_LOGIN' ) ?
						( \is_string( \WF_DEV_LOGIN ) ?
							\WF_DEV_LOGIN :
							( \WF_DEV_LOGIN ? _x( 'Enabled', 'Site Health Info' ) : _x( 'Disabled', 'Site Health Info' ) )
						) :
						_x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'WF_DEV_LOGIN' ) ? \WF_DEV_LOGIN : 'undefined',
					'private' => true,
				];
				$debug_info['wp-constants']['fields']['WF_DEV_EMAIL'] = [
					'label'   => 'WF_DEV_EMAIL',
					'value'   => \defined( 'WF_DEV_EMAIL' ) ?
						( \is_string( \WF_DEV_EMAIL ) ?
							\WF_DEV_EMAIL :
							( \WF_DEV_EMAIL ? _x( 'Enabled', 'Site Health Info' ) : _x( 'Disabled', 'Site Health Info' ) )
						) :
						_x( 'Undefined', 'Site Health Info' ),
					'debug'   => \defined( 'WF_DEV_EMAIL' ) ? \WF_DEV_EMAIL : 'undefined',
					'private' => true,
				];
			}
			$debug_info['wp-server']['fields']['errors'] = [
				'label'   => _x( 'cPanel® Server errors last 24 hours', 'Site Health Info' ),
				'value'   => \count( self::$cpanel_errors ),
				'debug'   => \count( self::$cpanel_errors ) ?: __( '(none)' ),
				'private' => false,
			];

			$debug_info[ self::$plugin->TextDomain ] = [
				'label'  => _x( 'Your cPanel® Account &mdash; Disk Space &amp; Resources', 'Site Health Info label' ),
				'fields' => [
					'cpanel'                => [
						'label'    => _x( 'Is on cPanel®?', 'Site Health Info' ),
						'value'    => self::$is_cpanel ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$is_cpanel,
						'private'  => false,
					],
					'cpanel_version' => self::$is_cpanel ? [
						'label' => _x( ' &ndash; Version', 'Site Health Info' ),
						'value'    => self::$cpanel_version,
						'debug'    => self::$cpanel_version,
						'private'  => false,
					] : [],
					'cpanel_user'  => self::$is_cpanel ? [
						'label' => _x( ' &ndash; User', 'Site Health Info' ),
						'value'    => self::$cpanel_user . ' { uid:' . self::$cpanel_user_ids->uid . ', gid: ' . self::$cpanel_user_ids->gid . ' }',
						'private'  => ! self::$is_debug,
					] : [],
					'user_created' => self::$is_cpanel ? [
						'label' => \sprintf(
							_x( ' &ndash;&ndash; User %1$s created',
								'Site Health Info, %1$s = user'
							),
							self::$cpanel_user
						),
						'value'    => \date_i18n( \get_option( 'date_format' ), self::$cpanel_user_created ),
						'debug'    => \date( 'Y-m-d', self::$cpanel_user_created ),
						'private'  => ! self::$is_debug,
					] : [],
					'user_updated' => self::$is_cpanel ? [
						'label' => \sprintf(
							_x( ' &ndash;&ndash; User %1$s updated',
								'Site Health Info, %1$s = user'
							),
							self::$cpanel_user
						),
						'value'    => \date_i18n( \get_option( 'date_format' ), self::$cpanel_user_updated ),
						'debug'    => \date( 'Y-m-d', self::$cpanel_user_updated ),
						'private'  => ! self::$is_debug,
					] : [],
					'cpanel_subaccounts'  => self::$is_cpanel ? [
						'label' => _x( ' &ndash;&ndash; Subaccounts {services}', 'Site Health Info' ) . ' (' . ( self::$cpanel_subaccounts ? \count( \explode( ', ', self::$cpanel_subaccounts ) ) : 0 ) . ')',
						'value'    => self::$cpanel_subaccounts,
						'private'  => ! self::$is_debug,
					] : [],
					'2fa_used'            => self::$is_cpanel ? [
						'label'    => _x( ' &ndash;&ndash; Two Factor Authentication?', 'Site Health Info' ),
						'value'    => self::$two_factor_enabled ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$two_factor_enabled,
						'private'  => ! self::$is_debug,
					] : [],
					'main_domain'  => self::$main_domain ? [
						'label'    => _x( 'Main domain', 'Site Health Info' ),
						'value'    => \idn_to_utf8( self::$main_domain ),
						'debug'    => self::$main_domain,
						'private'  => ! self::$is_debug,
					] : [],
					'addon_domains' => \is_array( self::$addon_domains ) ? [
						'label'    => _x( ' &ndash; Addon domains', 'Site Health Info' ) . ' (' . \count( self::$addon_domains ) . ')',
						'value'    => \implode( _x( ', ', 'Site Health Info list delimiter' ), \array_map( 'idn_to_utf8', self::$addon_domains ) ),
						'debug'    => \count( self::$addon_domains ) ? \implode( ', ', self::$addon_domains ) : __( '(none)' ),
						'private'  => ! self::$is_debug,
					] : [],
					'parked_domains' => \is_array( self::$parked_domains ) ? [
						'label'    => _x( ' &ndash; Parked domains', 'Site Health Info' ) . ' (' . \count( self::$parked_domains ) . ')',
						'value'    => \implode( _x( ', ', 'Site Health Info list delimiter' ), \array_map( 'idn_to_utf8', self::$parked_domains ) ),
						'debug'    => \count( self::$parked_domains ) ? \implode( ', ', self::$parked_domains ) : __( '(none)' ),
						'private'  => ! self::$is_debug,
					] : [],
					'dead_domains' => \is_array( self::$dead_domains ) ? [
						'label'    => _x( ' &ndash; Dead domains', 'Site Health Info' ) . ' (' . \count( self::$dead_domains ) . ')',
						'value'    => \implode( _x( ', ', 'Site Health Info list delimiter' ), \array_map( 'idn_to_utf8', self::$dead_domains ) ),
						'debug'    => \count( self::$dead_domains ) ? \implode( ', ', self::$dead_domains ) : __( '(none)' ),
						'private'  => ! self::$is_debug,
					] : [],
					'max_space'    => [
						'label'    => _x( 'Max disk space', 'Site Health Info' ),
						'value'    => \is_null( self::$disk_space_max ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$disk_space_max,  0 ),
						'debug'    => \is_null( self::$disk_space_max ) ? 'N/A' : \size_format( self::$disk_space_max, 0 ),
						'private'  => false,
					],
					'used_space'   => [
						'label'    => _x( ' &ndash; Used disk space &ndash; total', 'Site Health Info' ),
						'value'    => \is_null( self::$disk_space_used ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$disk_space_used, 1 ),
						'debug'    => \is_null( self::$disk_space_used ) ? 'N/A' : \size_format( self::$disk_space_used, 2 ),
						'private'  => false,
					],
					'dbs_used'     => [
						'label'    => _x( ' &ndash;&ndash; Databases', 'Site Health Info' ),
						'value'    => \is_null( self::$database_used ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$database_used, 1 ),
						'debug'    => \is_null( self::$database_used ) ? 'N/A' : \size_format( self::$database_used, 2 ),
						'private'  => false,
					],
					'upload_used'  => [
						'label'    => _x( ' &ndash;&ndash; Uploaded files', 'Site Health Info' ),
						'value'    => \is_null( self::$uploads_used ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$uploads_used, 1 ),
						'debug'    => \is_null( self::$uploads_used ) ? 'N/A' : \size_format( self::$uploads_used, 2 ),
						'private'  => false,
					],
					'error_log'    => self::$php_log ? [
						'label'    => self::$is_debug ?
							_x( ' &ndash;&ndash; PHP Debug Log File', 'Site Health Info' ) :
							_x( ' &ndash;&ndash; PHP Error Log File', 'Site Health Info' ),
						'value'    => ( \is_null( self::$logfile->Size ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$logfile->Size, 1 ) ) . ' &nbsp; ' . self::$logfile->ShortPath,
						'debug'    => \is_null( self::$logfile->Size ) ? 'N/A' : \size_format( self::$logfile->Size, 2 ),
						'private'  => false,
					] : [],
					'email_used'   => [
						'label'    => _x( ' &ndash;&ndash; Emails', 'Site Health Info' ),
						'value'    => \is_null( self::$emails_used ) ? _x( 'N/A', 'Site Health Info' ) : \size_format( self::$emails_used, 1 ) . ( \class_exists( 'WebFacing\cPanel\Email\Plugin' ) ? '' : '  &nbsp; &ndash; &nbsp; ' . __( 'To manage accounts, install plugin ' ) . ' &laquo;' . _x( 'WebFacing – Email Accounts in cPanel®', 'Plugin Name' ) . '&raquo;' ),
						'debug'    => \is_null( self::$emails_used ) ? 'N/A' : \size_format( self::$emails_used, 2 ),
						'private'  => false,
					],
				] + self::mail_accounts() + [
					'dns_authority_local' => self::$is_cpanel ? [
						'label'    => \sprintf(
							_x( 'Local DNS for %1$s is authoritative?',
							'Site Health, %1$s = main domain'
							),
							\idn_to_utf8( self::$site_domain )
						),
						'value'    => self::$dns_authority_local ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$dns_authority_local,
						'private'  => false,
					] : [],
					'mx_domain_self'        => self::$is_cpanel ? [
						'label'    => _x( ' &ndash; MX entry points to self?', 'Site Health Info' ),
						'value'    => self::$mx_domain_self ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$mx_domain_self,
						'private'  => false,
					] : [],
					'email_routing_local'   => self::$is_cpanel ? [
						'label'    => _x( ' &ndash; Email routing is local?', 'Site Health Info' ),
						'value'    => self::$email_routing_local ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$email_routing_local,
						'private'  => false,
					] : [],
					'maximum_emails'        => self::$is_cpanel ? [
						'label'    => _x( 'Maximum emails sending frequency', 'Site Health' ),
						'value'    => \sprintf(
							_x(
								'%1$s per hour',
								'Site Health Info - Number of emails per hour, %1$s = formatted number'
							),
							\number_format_i18n( self::cpanel_maximum_emails() ),
						),
						'debug'    => self::cpanel_maximum_emails(),
						'private'  => false,
					] : [],
				] + self::info_usages() + [
					'errors'                => [ 'label' => _x( 'cPanel® Server errors last 24 hours', 'Site Health Info' ),
						'value'   => \count( self::$cpanel_errors ),
						'debug'   => \count( self::$cpanel_errors ) ?: __( '(none)' ),
						'private' => false,
					],
					'php-errors'            => [ 'label' => _x( 'PHP Fatal errors lately',             'Site Health Info' ),
						'value'   => self::$php_log ? (
							self::$php_errors ?: __( '(none)' ) ) :
							_x( 'PHP Logging not enabled', 'Site Health Info' ),
						'debug'   => self::$php_errors,
						'private' => false,
					],
					'contact'               => [ 'label' => _x( 'Contact email addresses', 'Site Health Info' ),
						'value'    => self::$contact_emails ?? '',
						'debug'    => self::$contact_emails ?? __( '(none)' ),
						'private'  => ! self::$is_debug,
					],
					'proisp'                => [ 'label' => _x( 'At PRO ISP?', 'Site Health Info' ),
						'value'    => self::$is_proisp ? __( 'Yes' ) : __( 'No' ),
						'debug'    => self::$is_proisp,
						'private'  => ! self::$is_debug,
					],
				],
			];
			return $debug_info;
		} );
	}

	public    static function disk_space_test(): array {
		$result = [
			'label'       => _x( 'Your server has enough disk space', 'Site Health Status' ),
			'status'      => 'good',
			'badge'       => [
				'label'   => _x( 'Disk Usage', 'Site Health Status' ),
				'color'   => 'blue',
			],
			'description' => \wpautop( \sprintf( _x( 'In internet services providing (ISPs) or pure web hosting, disk space is the amount of space actually used or available on the server for storing the content of your site. This content includes posts, pages, images, videos, logs, other files, preferences, settings, configurations, and whatever else stored on as files or in databases. In case a full ISP, it is also used to store emails, including their full content and attachments. The amount of used disk space tend to grow over time.</p><p>The maximum amount depend on the subscribed package or plan typically from 1GB to over 100GB. When your available disk space is exhausted, your site may break or fail in strange, unpredictable ways. Deleting redundant temporary files and oher "garbage" may rectify it short term. Upgrading your plan/package/account is a more sustainable solution.</p><p>Disk space used is %1$s out of %2$s available. Your uploaded media files takes up %3$s.', 'Site Health Info Test Description' ), self::$disk_space_used ? \size_format( self::$disk_space_used, 1 ) : _x( 'N/A', 'Site Health Status' ), self::$disk_space_max ? \size_format( self::$disk_space_max ) : _x( 'N/A', 'Site Health Status' ), self::$uploads_used ? \size_format( self::$uploads_used, 1 ) : _x( 'N/A', 'Site Health Status' ) ) ),
			'actions'     => ( self::$is_cpanel ? '<a href="https://' . self::$host_name . ( self::$host_port ? ':' . self::$host_port : '' ) . '?locale=' . self::$user_locale . '">' . _x( 'Your cPanel Server', 'Site Health Status' ) . '</a>' : '' ) . ( self::$host_label ? ( self::$host_url ? ' &nbsp; | &nbsp; <a href="' . self::$host_url . '">' : '' ) . self::$host_label . ( self::$host_url ? '</a>' : '' ) : '' ),
			'test'        => 'disk-space',
		];
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['recommended'] ) {
			$result['label'  ]      = _x( 'You are close to reaching the quota on your server', 'Site Health Status' );
			$result['status' ]      = 'recommended';
			$result['badge'  ]['color'] = 'orange';
			$result['description'] .= \wpautop( _x( 'You are advised to inspect your server or consult your host for further advice or upgrade.', 'Site Health Info' ) . '%s' );
			$result['description']  = \str_replace( '%s', self::$is_cpanel ? ' ' . _x( 'See links below.', 'Site Health Status' ) : '', $result['description'] );
		}
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['critical'] ) {
			$result['label'  ]      = _x( 'You are very close to reaching the quota on your server', 'Site Health Info' );
			$result['status' ]      = 'critical';
			$result['badge'  ]['color'] = 'red';
			$result['actions']     .= ' &nbsp; | &nbsp; <mark>' . _x( 'Immediate action is necessary to keep normal site behaviour, and to allow for new content.', 'Site Health Info' ) . '</mark>';
		}
		return $result;
	}

	public    static function https_only_test(): array {
		$result = [
			'label'       => _x( 'Your site only accepts secure requests (https).', 'Site Health Status' ),
			'status'      => 'good',
			'badge'       => [
				'label'   => _x( 'Security', 'Site Health Info Label' ),
				'color'   => 'blue',
			],
			'description' => \wpautop( _x( 'You should ensure that visitors to your web site always use a secure connection. When visitors use an insecure connection it can be because used an old link or bookmark, or just typed in the domain. Using https instead of https means that communications between your browser and a website is encrypted via the use of TLS (Transport Layer Security). Even if your website doesn\'t handle sensitive data, it\'s a good idea to make sure your website always loads securely over https.', 'Site Health Status Description' ) ),
			'actions'     => '',
			'test'        => 'https-only',
		];
		$home_url = \get_home_url( null, '/', 'http' );
		$response = \wp_remote_get( $home_url, [ 'method' => 'HEAD', 'redirection' => 0 ] );
		$status = \intval( \wp_remote_retrieve_response_code( $response ) );
		if ( \intval( $status / 100 ) === 2 ) {
			$result['description'] .= \wpautop( _x( 'This situation can and should be fixed by forwarding all http requests to a https version of the requested URL. See link below.', 'Site Health Status' ) ) . \wpautop( \sprintf( _x( 'Response status for <code>%1$s</code> is %2$s.', 'Site Health Status, %1$s = HTTP status' ), $home_url, $status ) );
			$result['label'  ]          = _x( 'Your site also accepts insecure requests (http).', 'Site Health Status' );
			$result['status' ]          = 'recommended';
			$result['badge'  ]['color'] = 'orange';
			$text = _x( 'Force all traffic to your site to use https', 'Site Health Status' ) . ( self::$host_label ? ' - ' . self::$host_label : '' ) . '.';
			$url  = self::$is_cpanel ? \esc_url( _x( 'https://www.proisp.eu/guides/force-https-domain/', 'Site Health Status' ) ) : _x( 'https://stackoverflow.com/questions/4083221/how-to-redirect-all-http-requests-to-https', 'Site Health Status' );
			$tip  = __( 'Opens in a new tab.' );
			$result['actions']     .= \sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s', $url, $tip, $text ) . '<span class="dashicons dashicons-external" aria-hidden="true"></span></a>';
		}
		return $result;
	}

	public    static function email_routing_test(): array {
		$result = [
			'label'       => _x( 'Your email routing is fine', 'Site Health Status' ),
			'status'      => 'good',
			'badge'       => [
				'label'   => _x( 'Email routing', 'Site Health Info' ),
				'color'   => 'blue',
			],
			'description' => \wpautop(
				_x( 'When sending mail from a server to a hosted/known domain that has its mail exchange (MX) on another server it\'s crucial that it uses remote delivery by SMTP, and not just dropping it in the domain\'s local mailbox.', 'Site Health Status Description' )
			),
			'actions'     => '',
			'test'        => 'email-routing',
		];
		if ( ! self::$dns_authority_local && ! self::$mx_domain_self && self::$email_routing_local ) {
			$result['label'  ]          = \sprintf( _x( 'You are having issues receiving email from this server to addresses on \'%1$s\'.', 'Site Health Status Label' ),
				self::$site_domain
				);
			$result['status' ]          = 'critical';//'recommended';
			$result['badge'  ]['color'] = 'purple';
			$result['description']     .= \wpautop( \PHP_EOL . \sprintf(
				_x( 'Your server uses <strong>local</strong> delivery for this domain, but the mail exchange server for your domain is a remote one. The emails will therefore stay on this server and never be delivered where it should. WordPress admin emails will not work. Visitors submitting an email through a contact form in WordPress will not be received in your inbox.

You are advised to set the Email Routing in cPanel® for <code>%1$s</code> to <strong>Remote Mail Exchanger</strong>.', 'Site Health Status Description' ),
				self::$site_domain
			) );
			$text = \sprintf(
				_x( 'MX Entry and email routing in cPanel®', 'Site Health Status' ),
				self::$site_domain
			);
			$url  = _x( 'https://www.proisp.eu/guides/mx-entry-cpanel/', 'Site Health Status' );
			$tip  = _x( 'Opens in a new tab.', 'Site Health Status' );
			$result['actions']         .= self::$is_proisp ? \sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s', $url, $tip, $text ) . '<span class="dashicons dashicons-external" aria-hidden="true"></span></a>' : '';
		}
		return $result;
	}

	public static function errors_test(): array {
		$result = [
			'label'       => _x( 'You have no errors logged last 24 hours', 'Site Health Status' ),
			'status'      => 'good',
			'badge'       => [
				'label'   => _x( 'Server Errors', 'Site Health Info' ),
				'color'   => 'blue',
			],
			'description' => \wpautop(
				_x( 'Server errors may indicate exhausted server resources, like maximum allowed physical memory, CPU utlization or entry processes. Also issues with your theme, plugins or just some broken links you have added may cause some, but less severe errors. It may also indicate your site is targeted by spamming or hacking robots, probing for vulnerable code. A high frequency of such errors may reduce the servers performance.', 'Site Health Status Description' )
			),
			'actions'     => '',
			'test'        => 'cpanel-errors',
		];
		if ( \count( self::$cpanel_errors ) ) {
			$result['label'  ]          = \sprintf( _x( 'You have %d server errors logged last 24 hours.', 'Site Health Status Label' ),
				\count( self::$cpanel_errors )
			);
			$result['status']           = \count( self::$cpanel_errors ) > 24 ? 'critical' : 'recommended';
			$result['badge'  ]['color'] = 'purple';
			$list = '';
			foreach ( self::$cpanel_errors as $error ) {
			}
			$result['description']     .= '<ul style="font-family: monospace;"><li>' . \implode( '</li><li>', \array_map( 'trim', \wp_list_pluck( self::$cpanel_errors, 'entry' ) ) ) . '</li></ul>';
			$result['actions']         .= \sprintf(
				_x( 'Identify possible causes to these errors. If internal server errors occur, you may have serious software bugs, or you may need to add more resources by upgrading your hosting plan. See also <a href="%1$s">this FAQ</a> for more tips.', 'Site Health Status Actions' ),
				_x( 'https://www.proisp.eu/faq/#Linux+web+hostingError+messages', 'Site Health Actions FAQ' )
			);
		}
		return $result;
	}

	protected static function info_usages(): array {
		$arr = [];
		foreach ( self::cpanel_usages() ?? [] as $key => $usage ) {
			$arr[ $key ] = [
				'label'   => _x( $usage->description, 'Site Health Status Label' ),
				'value'   => \sprintf(
					_x( '%1$s of %2$s &ndash; %3$s',
						'%1$s = used, %2$s = limit, %3$s = ok or error message.'
					),
					\call_user_func( $usage->formatter, $usage->usage   ),
					\call_user_func( $usage->formatter, $usage->maximum ),
					_x( $usage->error, 'Site Health Status - Usage status' )
				),
				'debug' => \sprintf(
					'%1$s / %2$s',
					\call_user_func( $usage->formatter, $usage->usage   ),
					\call_user_func( $usage->formatter, $usage->maximum )
				),
				'private' => true,
			];
		}
		return $arr;
	}

	protected static function mail_accounts(): array {
		$arr = [];
		foreach ( \array_merge( [ self::cpanel_main_email_account() ], self::$email_accounts ?? [] ) as $account ) {
			$arr[ $account->login ] = [
				'label'   => \sprintf(
					' &ndash;&ndash;&ndash; %1$s',
					self::email_to_utf8( $account->email ) ),
				'value'   => \sprintf(
					_x( '%1$s of %2$s &ndash; %3$s', 'size: %1$s = used, %2$s = limit, %3$s = suspended/ok' ),
					$account->_diskused ? \size_format( $account->_diskused ) : '0 B',
					$account->_diskquota ? \size_format( $account->_diskquota ) : $account->diskquota,
					$account->_diskquota && \intval( $account->_diskused ) >= $account->_diskquota && ( $account->suspended_login || $account->suspended_incoming ) ? _x( 'suspended', 'Site Health Status' ) : _x( 'ok', 'Site Health Status' ),
				),
				'debug'   => \sprintf(
					'%1$s / %2$s (%3$s)',
					\size_format( $account->_diskused ),
					$account->_diskquota ? \size_format( $account->_diskquota ) : $account->diskquota,
					$account->_diskquota && \intval( $account->_diskused ) >= $account->_diskquota && ( $account->suspended_login || $account->suspended_incoming ) ? 'suspended' : 'ok',
				),
				'private' => ! self::$is_debug,
			];
		}
		return $arr;
	}

	protected static function mail_forwarders(): array {	// No longer used here
		$arr = [];
		foreach ( self::cpanel_email_forwarders() ?? [] as $forward ) {
			$arr[ $forward->dest ] = [
				'label'   => \sprintf(
					_x( 'Email forwarding destination %1$s',
						'%1$s = email address/account'
					),
					self::email_to_utf8( $forward->dest )
				),
				'value'   => $forward->html_forward,
				'private' => ! self::$is_debug,
			];
		}
		return $arr;
	}
}
