<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( '\WP' ) ) {
	exit;
}

class SiteHealth extends Plugin {

	public    static function init() {
		
		\add_filter( 'site_status_tests', function( array $tests ): array {
			self::init_data();
			if ( self::$disk_space_max && self::$disk_space_used ) {
				$tests['direct']['disk-space'] = [
					'label' => \__( 'Disk usage', self::$text_domain ),
					'test'  => [ __CLASS__, 'disk_space_test' ]
				];
			}
			if ( \str_starts_with( get_home_url(), 'https://' ) ) {
				$tests['direct']['https-only'] = [
					'label' => \__( 'HTTPS only', self::$text_domain ),
					'test'  => [ __CLASS__, 'https_only_test' ]
				];
			}
			return $tests;
		} );
	
		\add_filter( 'debug_information', function( array $debug_info ): array {
			self::init_data();
			$debug_info['wp-constants']['fields']['WF_DEBUG'] = [ 'label' => 'WF_DEBUG', 'value' => \defined( '\WF_DEBUG' ) ? ( \WF_DEBUG ? \__( 'Enabled' ) : 'Disabled' ) : \__( 'Undefined' ), 'debug' => 'true' ];
			$debug_info[ self::$text_domain ] = [
				'label'  => \__( 'Disk Space', self::$text_domain ),
				'fields' => [
					'max_space'    => [ 'label' => \__( 'Max space', self::$text_domain ),
						'value'    => self::$disk_space_max  ? \size_format( self::$disk_space_max,  0 ) : 'N/A',
						'private'  => false,
					],
					'used_space'   => [
						'label'    => \__( 'Used space &ndash; total', self::$text_domain ),
						'value'    => self::$disk_space_used ? \size_format( self::$disk_space_used, 1 ) : 'N/A',
						'private'  => false,
					],
					'upload_used'  => [
						'label'    => \__( ' &ndash; Uploaded files', self::$text_domain ),
						'value'    => is_null( self::$uploads_used ) ? 'N/A' : \size_format( self::$uploads_used, 1 ),
						'private'  => false,
					],
					'email_used'   => [
						'label'    => \__( ' &ndash; Emails', self::$text_domain ),
						'value'    => self::$is_cpanel       ? \size_format( self::$emails_used,     1 ) : 'N/A',
						'private'  => false,
					],
					'cpanel'       => [ 'label' => \__( 'Is cPanel&reg;?', self::$text_domain ),
						'value'    => self::$is_cpanel ? \__( 'Yes' ) : \__( 'No' ),
						'private'  => false,
					],
					'cpanel_fresh' => [ 'label' => \__( 'Is cPanel&reg; data fresh?', self::$text_domain ),
						'value'    => self::$cpanel_quotas_fresh ? \__( 'Yes' ) : \__( 'No' ),
						'private'  => false,
					],
					'cpanel_user'  => [ 'label' => \__( 'cPanel&reg; user', self::$text_domain ),
						'value'    => self::$cpanel_user,
						'private'  => true,
					],
					'proisp'       => [ 'label' => \__( 'At PRO ISP?', self::$text_domain ),
						'value'    => self::$is_proisp ? \__( 'Yes' ) : \__( 'No' ),
						'private'  => true,
					],
				],
			];
			if ( ! self::$is_cpanel ) {
				unset ( $debug_info[ self::$text_domain ]['fields']['cpanel_fresh'], $debug_info[ self::$text_domain ]['fields']['cpanel_user'] );
			}
			return $debug_info;
		} );
	}
	
	public    static function disk_space_test(): array {
		$result = [
			'label'       => \__( 'Your server has enough disk space', self::$text_domain ),
			'status'      => 'good',
			'badge'       => [
				'label'   => \__( 'Disk Usage', self::$text_domain ),
				'color'   => 'blue',
			],
			'description' => \wpautop( \sprintf( \__( 'In internet services providing (ISPs) or pure web hosting, disk space is the amount of space actually used or available on the server for storing the content of your site. This content includes posts, pages, images, videos, logs, other files, preferences, settings, configurations, and whatever else stored on as files or in databases. In case a full ISP, it is also used to store emails, including their full content and attachments. The amount of used disk space tend to grow over time.</p><p>The maximum amount depend on the subscribed package or plan typically from 1GB to over 100GB. When your available disk space is exhausted, your site may break or fail in strange, unpredictable ways. Deleting redundant temporary files and oher "garbage" may rectify it short term. Upgrading your plan/package/account is a more sustainable solution.</p><p>Disk space used is %1$s out of %2$s available. Your uploaded media files takes up %3$s.', self::$text_domain ), self::$disk_space_used ? \size_format( self::$disk_space_used, 1 ) : 'N/A', self::$disk_space_max ? \size_format( self::$disk_space_max ) : 'N/A', self::$uploads_used ? \size_format( self::$uploads_used, 1 ) : 'N/A' ) ),
			'actions'     => ( self::$is_cpanel ? '<a href="https://' . self::$host_name . ( self::$host_port ? ':' . self::$host_port : '' ) . '">' . \__( 'Your cPanel Server', self::$text_domain ) . '</a>' : '' ) . ( self::$host_label ? ( self::$host_url ? ' &nbsp; | &nbsp; <a href="' . self::$host_url . '">' : '' ) . self::$host_label . ( self::$host_url ? '</a>' : '' ) : '' ),
			'test'        => 'disk-space',
		];
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['recommended'] ) {
			$result['label'  ]      = \__( 'You are close to reaching the quota on your server', self::$text_domain );
			$result['status' ]      = 'recommended';
			$result['badge'  ]['color'] = 'orange';
			$result['description'] .= \wpautop( \__( 'You are advised to inspect your server or consult your host for further advice or upgrade.', self::$text_domain ) . '%s' );
			$result['description']  = \str_replace( '%s', self::$is_cpanel ? ' ' . \__( 'See links below.', self::$text_domain ) : '', $result['description'] );
		}
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['critical'] ) {
			$result['label'  ]      = \__( 'You are very close to reaching the quota on your server', self::$text_domain );
			$result['status' ]      = 'critical';
			$result['badge'  ]['color'] = 'red';
			$result['actions']     .= ' &nbsp; | &nbsp; <mark>' . \__( 'Immediate action is necessary to keep normal site behaviour, and to allow for new content.', self::$text_domain ) . '</mark>';
		}
		return $result;
	}

	public    static function https_only_test(): array {
		$result = [
			'label'       => \__( 'Your site only accepts secure requests (https).', self::$text_domain ),
			'status'      => 'good',
			'badge'       => [
				'label'   => \__( 'HTTPS only', self::$text_domain ),
				'color'   => 'blue',
			],
			'description' => \wpautop( \__( 'You should ensure that visitors to your web site always use a secure connection. When visitors use an insecure connection it can be because used an old link or bookmark, or just typed in the domain. Using https instead of https means that communications between your browser and a website is encrypted via the use of TLS (Transport Layer Security). Even if your website doesn\'t handle sensitive data, it\'s a good idea to make sure your website always loads securely over https.', self::$text_domain ) ),
			'actions'     => '',
			'test'        => 'https-only',
		];
		$home_url = \get_home_url( null, '/', 'http' );
		$response = \wp_remote_get( $home_url, [ 'method' => 'HEAD', 'redirection' => 0 ] );
		$status = \intval( \wp_remote_retrieve_response_code( $response ) );
		if ( \intval( $status / 100 ) === 2 ) {
			$result['description'] .= \wpautop( \__( 'This situation can and should be fixed by forwarding all http requests to a https version of the requested URL. See link below.', self::$text_domain ) ) . \wpautop( \sprintf( \__( 'Response status for \'%1$s\' is %2$s.', self::$text_domain ), $home_url, $status ) );
			$result['label'  ]      = \__( 'Your site also accepts insecure requests (http).', self::$text_domain );
			$result['status' ]      = 'recommended';
			$result['badge'  ]['color'] = 'orange';
			$text = \__( 'Force all traffic to your site to use https', self::$text_domain ) . ( self::$host_label ? ' - ' . self::$host_label : '' ) . '.';
			$url  = self::$is_cpanel ? \__( 'https://www.proisp.eu/guides/force-https-domain/', self::$text_domain ) : \__( 'https://stackoverflow.com/questions/4083221/how-to-redirect-all-http-requests-to-https', self::$text_domain  );
			$tip  = \__( 'Opens in a new tab.', self::$text_domain );
			$result['actions']     .= sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s', $url, $tip, $text ) . '<span class="dashicons dashicons-external" aria-hidden="true"></span></a>';
		}
		return $result;
	}
}
