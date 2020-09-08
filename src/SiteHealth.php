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
				$tests['direct']['disk-space'] = [ 'label' => \__( 'Disk usage', self::$text_domain ), 'test' => [ __CLASS__, 'disk_space_test' ] ];
			}
			return $tests;
		} );
	
		\add_filter( 'debug_information', function( array $debug_info ): array {
			self::init_data();
			$debug_info['wp-constants']['fields']['WF_DEBUG'] = [ 'label' => 'WF_DEBUG', 'value' => \defined( '\WF_DEBUG' ) ? ( \WF_DEBUG ? \__( 'Enabled' ) : 'Disabled' ) : \__( 'Undefined' ), 'debug' => 'true' ];
			$debug_info[ self::$text_domain ] = [
				'label'  => \__( 'Disk Space', self::$text_domain ),
				'fields' => [
					'max_space'   => self::$disk_space_max ? [ 'label' => \__( 'Max space', self::$text_domain ),
						'value' => \size_format( self::$disk_space_max,  0 ),
						'private' => false,
					] : null,
					'used_space'  => self::$disk_space_used ? [
						'label' => \__( 'Used space &ndash; total', self::$text_domain ),
						'value' => \size_format( self::$disk_space_used, 1 ),
						'private' => false,
					] : null,
					'upload_used' => self::$uploads_used ? [
						'label' => \__( ' &ndash; Uploaded files', self::$text_domain ),
						'value' => \size_format( self::$uploads_used,    1 ),
						'private' => false,
					] : null,
					'email_used' => self::$is_cpanel ? [
						'label' => \__( ' &ndash; Emails', self::$text_domain ),
						'value' => \size_format( self::$emails_used,     1 ),
						'private' => false,
					] : null,
					'cpanel'      => [ 'label' => \__( 'Is cPanel?', self::$text_domain ),
						'value' => self::$is_cpanel ? \__( 'Yes' ) : \__( 'No' ),
						'private' => false,
					],
					'proisp'      => [ 'label' => \__( 'At PRO ISP?', self::$text_domain ),
						'value' => self::$is_proisp ? \__( 'Yes' ) : \__( 'No' ),
						'private' => true,
					],
				],
			];
			return $debug_info;
		} );

		\add_filter( 'site_status_test_result', function( array $site_health_check ): array {
			if ( ( $site_health_check['test'] ?? '' ) === 'https_status' ) {
				if ( $site_health_check['status'] === 'good' ) {
					$result = wp_remote_get( get_home_url( '/', 'http' ), [ 'method' => 'HEAD' ] );
					$status = intval( wp_remote_retrieve_response_code( $result ) );
					if ( intval( $status / 100 ) === 2 ) {
						$title = \__( 'Force all traffic to your site to use https', self::$text_domain ) . ' - ' . self::$host_label . '.';
						$url   = \__( 'https://www.proisp.eu/guides/force-https-domain/', self::$text_domain );
						$tip   = \__( 'Opens in a new tab.', self::$text_domain );
						$site_health_check['actions'] .= sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s">%3$s', $url, $tip, $title ) . '<span class="dashicons dashicons-external" aria-hidden="true"></span></a>';
					}
				}
			}
			return $site_health_check;
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
//			'test'        => null,	// ?
		];
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['recommended'] ) {
			$result['label'  ] = \__( 'You are close to reaching the quota on your server', self::$text_domain );
			$result['status' ] = 'recommended';
			$result['badge'  ]['color'] = 'orange';
			$result['description'] .= \wpautop( \__( 'You are advised to inspect your server or consult your host for further advice or upgrade.', self::$text_domain ) . '%s' );
			$result['description']  = \str_replace( '%s', self::$is_cpanel ? ' ' . \__( 'See links below.', self::$text_domain ) : '', $result['description'] );
		}
		if ( self::$disk_space_used / self::$disk_space_max > self::$limits['critical'] ) {
			$result['label'  ] = \__( 'You are very close to reaching the quota on your server', self::$text_domain );
			$result['status' ] = 'critical';
			$result['badge'  ]['color'] = 'red';
			$result['actions'] .= ' &nbsp; | &nbsp; <mark>' . \__( 'Immediate action is necessary to keep normal site behaviour, and to allow for new content.', self::$text_domain ) . '</mark>';
		}
		return $result;
	}
}
