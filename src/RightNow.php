<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( '\WP' ) ) {
	exit;
}

class RightNow extends Plugin {

	public    static function init() {

		\add_filter( 'dashboard_glance_items', function ( ?array $elements ): array {
			
			$elements = $elements ?? [];
			self::init_data();
			if ( self::$disk_space_used && self::$disk_space_max ) {
				$class = self::$disk_space_used / self::$disk_space_max > self::$limits['critical'] ?
					'critical' : (
					self::$disk_space_used / self::$disk_space_max > self::$limits['recommended'] ?
						'recommended' :
						'good'
					)
				;
				$title = self::$is_cpanel ? ' title="' . \sprintf( \__( 'Maximum allowed for your account %1$s: %2$s.', self::$text_domain ), self::$cpanel_user, \size_format( self::$disk_space_max ) ) . ( self::$uploads_used ?  ' ' . \sprintf( \__( 'Your uploaded files is %s.', self::$text_domain ), \size_format( self::$uploads_used ) ) : '' ) . '"' : '';
				$href = self::$is_cpanel ? ' href="https://' . self::$host_name . ( self::$host_port ? ':' . self::$host_port : '' ) . '"' : '';
				$elements[] = '<a ' . $href . 'class="disk-count ' . $class . '"' . $title . '>' . \size_format( self::$disk_space_used, 1 ) . ' ' . \__( 'disk space used', self::$text_domain ) . '</a>';
			}
			return $elements;
		}, 100 );
		
		\add_action( 'rightnow_end', function() {
			$disk_space_max = \size_format( self::$disk_space_max );
			$proisp_packages = [
				              0 => \__('Unknown', self::$text_domain ),
				         '1 GB' => 'Start',
				        '30 GB' => 'Medium',
				       '100 GB' => 'Premium',
				      '1000 MB' => 'Start',
				     '1 000 MB' => 'Start',
				'1&nbsp;000 MB' => 'Start',
			];
			$proisp_package  = self::$is_proisp ? 'Pro&nbsp;' . $proisp_packages[ $disk_space_max ] : '';
			if ( self::$host_label ) {
				if ( self::$is_proisp ) {
					echo PHP_EOL, \wpautop( \sprintf( \__( 'Hosted at <a href="%1$s">%2$s</a> using a <strong>%3$s</strong> account with %4$s.', self::$text_domain ), self::$host_url, self::$host_label, $proisp_package, self::$disk_space_max ? \size_format( self::$disk_space_max ) : 'N/A' ) );
				} elseif ( self::$is_known_isp ) {
					echo PHP_EOL, \wpautop( \sprintf( \__( 'Hosted at <a href="%1$s">%2$s</a>.', self::$text_domain ), self::$host_url, self::$host_label ) );
				}
			}
		} );

		/*
		 * Custom Icon for Disk space in "Right Now"
		 */
		\add_action( 'admin_head', function() {
			if ( self::$screen_id === 'dashboard' ) { ?>
	<style>
		#dashboard_right_now li a.disk-count.recommended {
			background-color: inherit;
			color: brown;
		}
		#dashboard_right_now li a.disk-count.critical {
			background-color: inherit;
			color: red;
			font-weight: bold;
		}
		#dashboard_right_now li a.disk-count.critical:before {
			background-color: inherit;
			color: red;
		}
		#dashboard_right_now li a.disk-count:before {
			content: '\f17e';
			margin-left: -1px;
		}
	</style>
<?php
			}
		} );
	}
}
