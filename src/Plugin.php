<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( '\WP' ) ) {
	exit;
}

abstract class Plugin {

	const pf = 'wf_cpanel_';
	const hosts = [
		'proisp.no' => [ 'label' => 'PRO ISP', 'url' => 'https://proisp.eu/', 'port' => 2083 ],
		'proisp.eu' => [ 'label' => 'PRO ISP', 'url' => 'https://proisp.eu/', 'port' => 2083 ],
	];
	const limits = [
		'good'        => 0.00,
		'recommended' => 0.90,
		'critical'    => 0.95,
	];

	protected static array          $plugin_data;

	public    static string         $plugin_name;

	public    static string         $plugin_version;

	protected static string         $domain_path;
	
	public    static string         $text_domain;

	public    static string         $pf;

	protected static ?\WP_User      $me;

	protected static bool           $is_debug;

	protected static \WP_Screen     $wp_screen;

	protected static string         $screen_id;

	protected static bool           $has_caps;
	
	protected static bool           $is_cpanel;

	protected static array          $hosts;

	protected static ?string        $host_name;

	protected static ?string        $host_id;

	protected static ?string        $host_label;

	protected static ?string        $host_url;

	protected static ?string        $host_port;

	protected static string         $user_locale_short;

	protected static bool           $is_known_isp;

	protected static bool           $is_proisp;
	
	protected static ?string        $cpanel_user;

	protected static bool           $has_quota_cmd;

	protected static ?array         $cpanel_quotas;

	protected static bool           $cpanel_quotas_fresh;

	protected static ?int           $disk_space_max;

	protected static ?int           $disk_space_used;

	protected static ?int           $uploads_used;

	protected static ?int           $emails_used;

	protected static array          $limits;

	public    static function init() {
		
		self::$plugin_data     = \get_plugin_data( PLUGIN_FILE );

		self::$plugin_name     = self::$plugin_data['Name'];

		self::$plugin_version  = self::$plugin_data['Version'];

		self::$domain_path     = \trailingslashit( \dirname( PLUGIN_FILE ) . self::$plugin_data['DomainPath'] );

		self::$text_domain     = self::$plugin_data['TextDomain'];
		$tx = self::$text_domain;

		self::$pf              = \trim( \str_replace( '_', '-', self::pf ) );

		self::$is_debug        = \defined( '\WF_DEBUG' ) && \WF_DEBUG;

		\add_action( 'plugins_loaded', function() {
			\load_plugin_textdomain( self::$text_domain, false, self::$domain_path );
		} );
		
		\add_action( 'init', function() {
			self::init_data();
			$bits = \explode( '/', \rtrim( self::$plugin_data['AuthorURI'], ' /' ) );
			self::$me = \get_user_by( 'login', \end( $bits ) ) ?: null;
		} );

		\add_action( 'current_screen', function() {
			self::$wp_screen = \get_current_screen();
			self::$screen_id = self::$wp_screen ? self::$wp_screen->id : '';
		} );
		
		if ( self::$is_debug  ) {
			\add_action( 'admin_notices', function() use ( $tx ) {
				if ( \is_object( self::$me ) && self::$me->ID === \get_current_user_id() ) {
					self::init_data(); ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php echo self::$plugin_name, ' ', self::$plugin_version; ?><br />
					<?php echo \plugin_basename( PLUGIN_FILE ); ?><br />
					<?php echo PLUGIN_FILE; ?><br />
					<?php
						$locale = \get_user_locale( \get_current_user_id() );
						if ( $locale !== 'en_US' ) {
							echo self::$domain_path, self::$text_domain, '-', $locale, '.mo', '</br />';
						} ?>
					<?php echo __FILE__; ?><br />
					<?php echo self::$me ? self::$me->display_name : ''?><br />
					<?php echo self::$screen_id ?><br />
					<?php echo self::$host_id, ' | ', self::$host_label ?>
				</p>
			</div>
<?php
				}
			} );
		}
		RightNow::init();
		SiteHealth::init();
	}

	protected static function init_data() {

		self::$has_caps        = \current_user_can( 'edit_posts' ) || \current_user_can( 'edit_pages' ) || \current_user_can( 'upload_files' );
		
		$root = '/' . \explode( '/', \ABSPATH )[1] . '/';
		self::$is_cpanel     = \is_dir( $root . \explode( '/', \ABSPATH )[2] . '/.cpanel' );

		self::$host_name     = \gethostbyaddr( $_SERVER['SERVER_ADDR'] ) ?: null;

		$host_ids = \explode( '.', self::$host_name ?? '' );
		$num_f = \count( $host_ids );
		self::$host_id       = \array_key_exists( $num_f - 1, $host_ids ) ? $host_ids[ $num_f - 2] . '.' . $host_ids[ $num_f - 1] : null;
		
		self::$hosts = \apply_filters( self::pf . 'hosts', self::hosts ) ?? self::hosts;
		foreach ( self::$hosts as &$host ) {
			if ( $host['url'] == 'https://proisp.eu/' && \in_array( \get_user_locale( \wp_get_current_user() ), [ 'nb_NO', 'nn_NO' ] ) ) {
				$host['url'] = 'https://proisp.no/';
			}
		}
		
		$exists = \array_key_exists( self::$host_id, self::$hosts );
		self::$host_label    = $exists ? self::$hosts[ self::$host_id ]['label'] : null;

		self::$host_url      = $exists ? \__( self::$hosts[ self::$host_id ]['url'  ], self::$text_domain ) : null;

		self::$host_port     = $exists ? self::$hosts[ self::$host_id ]['port' ] : null;

		self::$user_locale_short = \explode( '_', \get_user_locale( \wp_get_current_user() ) )[0];

		self::$is_known_isp  = \in_array( self::$host_id, \array_keys( self::hosts ), true );
		
		self::$is_proisp     = \in_array( self::$host_id, [ 'proisp.no', 'proisp.eu' ], true );

		$cpanel_users = \explode( '/', \ABSPATH );
		self::$cpanel_user   = self::$is_cpanel && \array_key_exists( 2, $cpanel_users ) ? $cpanel_users[2] : null;
		
		self::$cpanel_quotas = \explode( ' ', \trim( \preg_replace('/\s+/', ' ', \exec( 'quota' ) ) ), 4 );
		\array_shift( self::$cpanel_quotas );
		self::$cpanel_quotas = \array_map( function( $val ){
			$val = 1024 * \intval( $val );
			return $val;
		}, self::$cpanel_quotas );
		
		$transient_name = self::pf . 'cpanel_quotas';
		self::$has_quota_cmd = \is_array( self::$cpanel_quotas ) && ! empty( self::$cpanel_quotas[0] ) && ! empty( self::$cpanel_quotas[1] );
		if ( self::$has_quota_cmd ) {
			$transient_time = HOUR_IN_SECONDS;
		} else {
			self::$cpanel_quotas = self::$cpanel_user ? ( \json_decode( \file_get_contents( $root . self::$cpanel_user . '/.cpanel/datastore/_Cpanel::Quota.pm__' . self::$cpanel_user ) )->data ) : null;
			$transient_time = YEAR_IN_SECONDS;
		}

		if ( \is_array( self::$cpanel_quotas ) && ! empty( self::$cpanel_quotas[0] ) && ! empty( self::$cpanel_quotas[1] ) ) {
			self::$cpanel_quotas_fresh = true;
			\set_transient( $transient_name, self::$cpanel_quotas, self::$is_debug ? MINUTE_IN_SECONDS : $transient_time );
		} else {
			$cpanel_quotas = \get_transient( $transient_name );
			self::$cpanel_quotas = \is_array( $cpanel_quotas ) ? $cpanel_quotas : [];
			self::$cpanel_quotas_fresh = false;
		}

		self::$disk_space_used = self::get_disk_space_used();

		self::$disk_space_max  = \is_array( self::$cpanel_quotas ) && \array_key_exists( 1, self::$cpanel_quotas ) ? self::$cpanel_quotas[1] : null;

		self::$limits = \apply_filters( self::pf . 'limits', self::limits ) ?? self::limits;

		$uploads_used = \explode( "\t", \exec( 'du -sh ' . \wp_upload_dir()['basedir'] ) )[0];
		$uploads_used = $uploads_used ?: \get_dirsize( \wp_upload_dir()['basedir'] );
		self::$uploads_used = \convertToBytes( $uploads_used . 'B' );

		if ( self::$is_cpanel ) {
			$emails_used = \explode( "\t", \exec( 'du -sh ' . $root . self::$cpanel_user . '/mail' ) )[0];
			$emails_used = $emails_used ?: \get_dirsize( $root . self::$cpanel_user . '/mail' );
			self::$emails_used  = \convertToBytes( $emails_used . 'B' );
		} else {
			self::$emails_used = null;
		}
	}

	protected static function get_disk_space_used(): ?int {
		$results = $GLOBALS['wpdb']->get_results( 'SELECT SUM( data_length + index_length ) AS b FROM information_schema.tables' );
		$result  = \array_key_exists( 0, $results ) ? $results[0] : null;
		$result  = \is_object( $result ) && \property_exists( $result, 'b' ) ? $result->b : null;
		$used    = $result ? \intval( $result ) : 0;
		if ( \is_array( self::$cpanel_quotas ) && \array_key_exists( 0, self::$cpanel_quotas ) ) {
			$used  += self::$cpanel_quotas[0];
		} else {
			$result = \shell_exec( 'du -sh ' . \ABSPATH );
			$result = $result ? \explode( "\t", $result )[0] : false;
			$result = $result ?: \get_dirsize( \dirname( \ABSPATH ) );
			if ( $result ) {
				$used += \convertToBytes( $result . 'B' );
			} else {
				$used = null;
			}
		}
		$used = self::$is_debug && self::$disk_space_max ? \rand( \ceil( self::$disk_space_max / 1.25 ), self::$disk_space_max ) : $used;
		return $used;
	}
}
