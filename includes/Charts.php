<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
	exit;
}

abstract class Charts extends Plugin {

	private   static /*string*/ $class;

	protected static /*string*/ $id;

	protected static /*bool*/   $interference;

	public    static function   load(): void {

		$class = \explode( '\\', __CLASS__ );
		self::$class = \strtolower( \end( $class ) );
	}

	public    static function   admin_load(): void {

		self::$id = self::$pf . self::$class;

		\add_action( 'plugins_loaded', function(): void {
			self::$interference = \class_exists( 'Google\Site_Kit\Plugin' );
		} );

		\add_action( 'wp_dashboard_setup', function(): void {
			$cap = \apply_filters( self::pf . 'widget_capability', 'manage_options' );

			if ( self::$is_cpanel && \current_user_can( $cap ) ) {
				\wp_add_dashboard_widget(
					self::$pf . 'widget',
					\esc_html( _x( 'cPanel® Resource Usage &amp; Server Errors', 'Dashboard Widget Title' ) ),
					function(): void {
						echo
							'<div id="',
							self::$id,
							'">',
							self::$interference ?
								_x(
									'No gauges can be displayed! Interference with your Site Kit by Google plugin.',
									'Replacement Error'
								) :
								'',
							'</div>';
						$style = \count( self::$cpanel_errors ) + self::$php_errors > 2 ? ' font-weight: bold; color: orangered;' : '';
						echo
							'<p style="',
							$style,
							' text-align: center;" title="',
							\esc_attr( \implode( \PHP_EOL, \wp_list_pluck( self::$cpanel_errors, 'entry' ) ) ),
							'.">',
							_x( 'cPanel® Server errors last 24 hours', 'Site Health Info' ),
							': ',
							\count( self::$cpanel_errors )
						;
						if ( self::$php_log ) {
							echo
								' | ',
								_x( 'PHP Fatal errors lately',         'Site Health Info' ),
								': ',
								self::$php_errors
							;
						}
						echo '</p>';
					}
				);
			}
		} );

		\add_action( 'admin_enqueue_scripts', function( string $hook ): void {

			if ( self::$is_cpanel && $hook === 'index.php' && ! self::$interference ) {
				$google = self::$pf . 'google-' . self::$class;
				$handle = self::$pf . self::$class;
				$pl_dir = \plugin_dir_url( PLUGIN_FILE ) . 'admin/';

				\wp_register_script( $google, 'https://www.gstatic.com/charts/loader.js' );
				\wp_register_script( $handle, $pl_dir . 'js/' . self::$class . '.js', [ $google ], self::$plugin->Version );
				\wp_localize_script( $handle, 'wFcPanelSettings', [
					'chartID'  => self::$id,
					'adminURI' => \admin_url(),
					'dataURI'  => $pl_dir . 'cpanel.php',
					'token'    => self::$cpanel_token,
					'interval' => (int)\apply_filters( self::pf . 'gauges_interval', 10 ),
					'labels'   => [
						'mem' => _x( 'Memory',      'Gauge Label' ),
						'cpu' => _x( 'CPU',         'Gauge Label' ),
						'epr' => _x( 'Procs',       'Gauge Label' ),
						'iop' => _x( 'Disk in/out', 'Gauge Label' ),
					],
				] );
				\wp_enqueue_script( $handle );
			}
		} );
	}
}
