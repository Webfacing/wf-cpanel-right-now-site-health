<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
	exit;
}

abstract class Glance extends Plugin {

	public    static function load(): void {
	}

	public    static function admin_load(): void {

		\add_filter( 'dashboard_glance_items', function ( ?array $elements ): array {
			$elements = $elements ?? [];

			if ( self::$is_cpanel && self::$has_caps ) {
				self::init_data();
				$href = 'https://' . self::$host_name . ( self::$host_port ? ':' . self::$host_port : '' ) . '?locale=' . self::$user_locale;

				if ( ! \class_exists( 'WebFacing\cPanel\Email\Plugin' ) ) {
					$num_accounts = 1 + ( \is_array( self::$email_accounts ) ? \count( self::$email_accounts ) : 0 );
					if ( $num_accounts > 1 ) {
						$title = \sprintf(
							_x( 'Your emails use %s.', 'At a Glance item' ),
							\size_format( self::$emails_used )
						) . '.';
						$elements[] = '<a href="' . $href . '" class="email-count" title="' . $title . '">' . $num_accounts . ' ' .
							_nx( 'email account created in cPanel®',
								'email accounts created in cPanel®',
								$num_accounts,
								'At a Glance - suffix for number for email accounts'
							) .
						'</a>';
					}
				}
				if ( self::$disk_space_used ) {
					$class = self::$disk_space_max ?
						( self::$disk_space_used / self::$disk_space_max > self::$limits['critical'] ?
							'critical' :
							( self::$disk_space_used / self::$disk_space_max > self::$limits['recommended'] ?
								'recommended' :
								'good'
							)
						) :
						'good'
					;
					$title = self::$uploads_used ?
						( ' title="' . \sprintf( \esc_html( __( 'Maximum allowed for your account %1$s: %2$s.' ) ), \current_user_can( 'manage_options' ) ?
							self::$cpanel_user :
							'', self::$disk_space_max ?
								\size_format( self::$disk_space_max ) :
								_x( 'N/A', 'Site Health Info' ) ) .
							( self::$database_used ?
								' ' . \sprintf( __( 'Your databases use %s.' ), \size_format( self::$database_used ) ) :
								'' ) .
							( self::$uploads_used ?
								' ' . \sprintf( __( 'Your uploaded files use %s.' ), \size_format( self::$uploads_used ) ) :
								'' ) .
							( self::$emails_used ?
								' ' . \sprintf( __( 'Your emails use %s.' ), \size_format( self::$emails_used ) ) :
								'' ) .
						'"' ) :
					'';
					$elements[] = '<a href="' . $href . '" class="disk-count ' . $class . '"' . $title . '>' . \size_format( self::$disk_space_used, 1 ) . ' ' . __( 'disk space used on cPanel®' ) . '</a>';
				}
			}
			return $elements;
		}, 100 );

		\add_action( 'rightnow_end', function(): void {
			if ( self::$has_caps ) {
				$proisp_packages = [
					'prostart'     => 'Pro Start',
					'promedium'    => 'Pro Medium',
					'propremium'   => 'Pro Premium',
					'enterprise10' => 'Enterprise 10',
					'enterprise30' => 'Enterprise 30',
					'enterprise60' => 'Enterprise 60',
				];
				$proisp_package  = self::$is_proisp ? $proisp_packages[ self::cpanel_plan() ] : '';
				if ( self::$host_label ) {
					if ( self::$is_proisp ) {
						echo \PHP_EOL, \wpautop( \sprintf( __( 'Hosted at <a href="%1$s">%2$s</a> using a <strong>%3$s</strong> account with %4$s.' ), self::$host_url, self::$host_label, $proisp_package, self::$disk_space_max ? \size_format( self::$disk_space_max ) : _x( 'N/A', 'Site Health Info' ) ) );
					} elseif ( self::$is_known_isp ) {
						echo \PHP_EOL, \wpautop( \sprintf( __( 'Hosted at <a href="%1$s">%2$s</a>.' ), self::$host_url, self::$host_label ) );
					}
				}
				if ( \current_user_can( 'install_plugins' ) && \current_user_can( 'activate_plugins' ) ) {

					if ( ( self::$dns_authority_local || self::$mx_domain_self || ! self::$email_routing_local ) && ! \class_exists( 'WebFacing\cPanel\Email\Plugin' ) ) { ?>
						<p><small><strong><?php \printf(
						_x( 'Author of the &laquo;%1$s&raquo; plugin tip:',
								'At a Glance note, %1$s = Plugin Name,' ),
							self::$plugin->Name
						);
						?></strong><br/><?php \printf(
							_x( 'Also check out this complementary <a href="%2$s">&laquo;%1$s&raquo;</a> plugin.',
								'%1$s = Plugin Name, %2$s = uri (also localize)'
							),
							_x( self::$plugin1->Name, 'Plugin Name' ),
							_x( self::$plugin1->URI, 'Plugin URI' )
						);
						if ( ! \is_multisite() ) {
							echo ' &nbsp; | &nbsp; <a href="', \add_query_arg( [ 's' => \urlencode( self::$plugin1->Name ), 'tab' => 'search', 'type' => 'term' ], \admin_url( 'plugin-install.php' ) ), '" title="', __( 'Install' ), ' &laquo;', _x( self::$plugin1->Name, 'Plugin Name' ), '&raquo;.">', __( 'Install' ), '</a>';
						} ?></small></p> <?php
					}
				}
			}
		} );

		/*
		 * Custom Icon for Disk space in "At a Glance" widget
		 */
		\add_action( 'admin_head', function(): void {
			if ( self::$has_caps && self::$screen->id === 'dashboard' ) { ?>
	<style>
		#dashboard_right_now li a.email-count:before {
			content: '\f465';
			margin-left: -1px;
		}
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
