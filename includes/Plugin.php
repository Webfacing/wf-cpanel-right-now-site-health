<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
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

	public    static /*stdClass*/   $plugin;

	public    static /*string*/     $pf;

	protected static /*?\WP_User*/  $dev;

	protected static /*bool*/       $is_debug;

	protected static /*\WP_Screen*/ $screen;

	protected static /*int*/        $transient_time;

	protected static /*\stdClass*/  $plugin1;

	protected static /*bool*/       $has_caps;

	protected static /*bool*/       $is_cpanel = true;

	protected static /*array*/      $hosts;

	protected static /*?string*/    $host_name;

	protected static /*?string*/    $host_id;

	protected static /*?string*/    $host_label;

	protected static /*?string*/    $host_url;

	protected static /*?string*/    $host_port;

	protected static /*string*/     $user_locale;

	protected static /*string*/     $site_domain;

	protected static /*bool*/       $is_known_isp;

	protected static /*bool*/       $is_proisp = false;

	protected static /*?string*/    $cpanel_user;

	protected static /*string*/     $cpanel_subaccounts;

	protected static /*?string*/    $cpanel_version;

	protected static /*?string*/    $cpanel_user_created;

	protected static /*?string*/    $cpanel_user_updated;

	protected static /*bool*/       $cpanel_data_fresh = false; // important

	protected static /*array*/      $cpanel_errors;

	protected static /*int*/        $php_errors;

	protected static /*\stdClass*/  $cpanel_user_ids;

	protected static /*int*/        $cpanel_token = 0;	// important

	protected static /*?int*/       $disk_space_max;

	protected static /*?int*/       $disk_space_used;

	protected static /*?int*/       $database_used;

	protected static /*?int*/       $uploads_used;

	protected static /*?int*/       $emails_used;

	protected static /*array*/      $limits;

	protected static /*bool*/       $two_factor_enabled;

	protected static /*string*/     $main_domain;

	protected static /*array*/      $sub_domains;

	protected static /*array*/      $addon_domains;

	protected static /*array*/      $parked_domains;

	protected static /*array*/      $dead_domains;

	protected static /*array*/      $email_accounts;

	protected static /*string*/     $contact_emails;

	protected static /*bool*/       $email_routing_local;

	protected static /*bool*/       $dns_authority_local;

	protected static /*bool*/       $mx_domain_self;

	protected static /*bool*/       $php_log;

	protected static /*\stdClass*/  $logfile;

	public    static function load(): void {

		if ( $_SERVER['SERVER_ADDR'] ?? '' ) {

			self::$host_name     = \gethostbyaddr( $_SERVER['SERVER_ADDR'] ) ?: null;

			$host_ids = \explode( '.', self::$host_name ?? '' );
			$num_f = \count( $host_ids );
			self::$host_id       = $num_f > 1 && \array_key_exists( $num_f - 1, $host_ids ) ? $host_ids[ $num_f - 2] . '.' . $host_ids[ $num_f - 1] : null;

			self::$is_proisp     = \in_array( self::$host_id, [ 'proisp.no', 'proisp.eu' ], true );

			self::$plugin1 = new \stdClass;
			self::$plugin1->Name = 'WebFacing – Email Accounts in cPanel®';
			self::$plugin1->URI  = 'https://wordpress.org/plugins/wf-cpanel-email-accounts/';
		}

		self::$pf                = \trim( \str_replace( '_', '-', self::pf ) );

		self::$is_debug          = \defined( 'WF_DEBUG' ) && \WF_DEBUG;

		self::$transient_time    = self::$is_debug ? 1 : 11;

		if ( ! \function_exists( 'get_plugin_data' ) ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}
		Glance::load();
		Charts::load();
		Health::load();
	}

	public    static function admin_load(): void {

		self::$plugin          = (object)\get_plugin_data( PLUGIN_FILE );

		\add_action( 'init', function(): void {
			self::init_data();
			$bits = \explode( '/', \rtrim( self::$plugin->AuthorURI, ' /' ) );
			self::$dev = \get_user_by( 'login', \defined( 'WF_DEV_LOGIN' ) ? \WF_DEV_LOGIN : \end( $bits ) ) ?: null;
		} );

		\add_action( 'current_screen', function(): void {
			self::$screen = \get_current_screen();
		} );

		\add_action( 'admin_notices', function(): void {

			if ( self::$is_debug && self::$dev instanceof \WP_User && self::$dev->ID === \get_current_user_id() ) {
				self::init_data(); ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php echo self::$plugin->Name, ' ', self::$plugin->Version; ?><br />
						<?php \var_dump( \intval(  \shell_exec( "tac '".self::$logfile->Path."' | head -n 100 | grep '^\[' | grep -c 'PHP Fatal error'" ) ) )?>
					</p>
				</div>
<?php
			}
		} );

		\add_action( 'admin_footer', function(): void {
			global $wpdb;

			\do_action( 'qm/debug', 'Data fresh: {fresh}.', [ 'fresh' => self::$cpanel_data_fresh ] );

			/**
			  *	Remove expired trtansients.
			  */
			if ( self::$screen->id === 'dashboard' && \idate( 's' ) % ( self::$is_debug ? 2 : 12 ) === 0 ) {
				$pf        = self::$pf;
				$threshold = \time() - ( 2 * self::$transient_time );
				$sql       = "
					DELETE FROM `T1`, `T2`
					USING `{$wpdb->options}` AS `T1`
					JOIN `{$wpdb->options}` AS `T2` ON `T2`.`option_name` = REPLACE( `T1`.`option_name`, '_timeout', '' )
					WHERE ( `T1`.`option_name` LIKE '\_transient\_timeout\_{$pf}%' OR `T1`.`option_name` LIKE '\_site\_transient\_timeout\_{$pf}%' )
					AND CONVERT( `T1`.`option_value`, UNSIGNED ) < {$threshold}
				;";
				$rows = \ceil( $wpdb->query( $sql ) / 2 );
				\do_action( 'qm/info', 'Deleted {rows} expired transients.', [ 'rows' => $rows ] );
			}
		} );

		Glance::admin_load();
		Charts::admin_load();
		Health::admin_load();
	}

	protected static function init_data(): void {

		self::$has_caps        = \current_user_can( 'edit_posts' ) || \current_user_can( 'edit_pages' ) || \current_user_can( 'upload_files' );

		self::$cpanel_version      = self::cpanel_version();

		self::$is_cpanel           = \function_exists( 'shell_exec' ) && self::$cpanel_version;

		self::$hosts = \apply_filters( self::pf . 'hosts', self::hosts ) ?? self::hosts;
		foreach ( self::$hosts as &$host ) {
			if ( $host['url'] == 'https://proisp.eu/' && \in_array( \get_user_locale( \wp_get_current_user() ), [ 'nb_NO', 'nn_NO' ] ) ) {
				$host['url'] = 'https://proisp.no/';
			}
		}

		$exists = \array_key_exists( self::$host_id, self::$hosts );
		self::$host_label    = $exists ? self::$hosts[ self::$host_id ]['label'] : null;

		self::$host_url      = $exists ? __( self::$hosts[ self::$host_id ]['url'  ] ) : null;

		self::$host_port     = $exists ? self::$hosts[ self::$host_id ]['port' ] : null;

		self::$user_locale   = \explode( '_', \get_user_locale( \wp_get_current_user() ) )[0];

		self::$is_known_isp  = \in_array( self::$host_id, \array_keys( self::hosts ), true );

		self::$cpanel_user         = self::cpanel_user() ?? '';

		self::$cpanel_user_ids     = self::cpanel_user_ids();

		self::$cpanel_user_created = self::cpanel_user_created();

		self::$cpanel_user_updated = self::cpanel_user_updated();

		$accounts = [];
		foreach ( self::cpanel_subaccounts() as $name => $account ) {
			$accounts[] = $name . ' {' . \implode( ',', $account->services ) . '}';
		}
		self::$cpanel_subaccounts  = \implode( ', ', $accounts );

		self::$cpanel_errors       = self::cpanel_errors();

		self::$cpanel_token        = self::$cpanel_token ?: self::cpanel_token();

		self::$limits = \apply_filters( self::pf . 'limits', self::limits ) ?? self::limits;

		$uploads_used = \explode( "\t", \exec( 'du -sh ' . \wp_upload_dir()['basedir'] ) )[0];
		$uploads_used = $uploads_used ?: \get_dirsize( \wp_upload_dir()['basedir'] );
		self::$uploads_used = self::to_bytes( $uploads_used . 'B' );

		self::$emails_used         = self::cpanel_email_disk_usage();

		self::$disk_space_max      = self::cpanel_total_disk_limit();

		self::$disk_space_used     = self::cpanel_total_disk_used();

		self::$database_used       = self::cpanel_databases_disk_used();

		self::$two_factor_enabled  = self::cpanel_two_factor() ?? false;

		self::$main_domain         = self::cpanel_main_domain() ?? '';

		$parts = \explode( '.', \parse_url( \home_url(), PHP_URL_HOST ) );
		self::$site_domain         = \count( $parts ) > 1 ? $parts[ \count( $parts ) - 2 ] . '.' . $parts[ \count( $parts ) - 1 ] : self::$main_domain;

		self::$addon_domains       = self::cpanel_list_domains( 'addon_domains'  ) ?? [];

		self::$parked_domains      = self::cpanel_list_domains( 'parked_domains' ) ?? [];

		self::$dead_domains        = self::cpanel_dead_domains() ?? [];

		self::$email_accounts      = self::cpanel_email_accounts();

		self::$contact_emails      = self::cpanel_contact();

		self::$email_routing_local = self::cpanel_email_routing_local();

		self::$dns_authority_local = self::cpanel_dns_local();

		self::$mx_domain_self      = self::cpanel_email_mx_self();

		self::$php_log             = @\ini_get( 'log_errors' );

		if ( self::$php_log ) {
			self::$logfile             = new \stdClass;
			self::$logfile->Path       = @\ini_get( 'error_log' );
			self::$php_log             = ! empty( self::$logfile->Path );
			if ( self::$php_log ) {
				self::$logfile->ShortPath  = \substr( self::$logfile->Path, \similar_text( \ABSPATH, self::$logfile->Path ) );
				self::$logfile->ShortPath  = '~/' . ( \strlen( self::$logfile->ShortPath ) > \strlen( \basename( self::$logfile->Path ) ) ? self::$logfile->ShortPath : \basename( self::$logfile->Path ) );
				self::$logfile->Size       = self::$logfile->Path ? \filesize( self::$logfile->Path ) : null;

				self::$php_errors          = \intval(  \shell_exec( "tac '".self::$logfile->Path."' | head -n 100 | grep '^\[' | grep -c 'PHP Fatal error'" ) );
			}
		}
	}

	protected static function cpanel_uapi( string $module, string $function, ?array $params = [], string $output = 'json' ): ?string {
		$paramst = '';
		foreach( $params as $name => $value ) {
//			$paramst .= ' ' . $name . '=' . $value;
			$paramst .= " " . $name . '=' . ( \is_integer( $value ) ? $value : "'" . \urlencode( $value ) . "'" );
		}
//		if ( $function === 'list_pops_with_disk' ) \var_dump( 'paramst', $paramst );
		return self::$is_cpanel ? \shell_exec( 'uapi --output=' . $output . ' ' . $module . ' ' . $function . $paramst ) : null;
	}

	protected static function cpanel_uapi_result( string $module, string $function, ?array $params = [] )/*: \stdClass | array*/ {
		$transient_name = self::$pf . $module . '-' . $function . ( \count( $params ) ? '-' . \md5( \serialize( $params ) ) : '' );
		$result = \json_decode( \get_transient ( $transient_name ) );
		$data_fresh = ! (
			( \is_string( $result ) && \strlen( $result ) > 0     ) ||
			( \is_object( $result ) &&   isset( $result->result ) ) ||
			( \is_array(  $result ) &&  \count( $result ) > 0     ) ||
		false );
		if ( $data_fresh ) {
			$json_result = self::cpanel_uapi( $module, $function, $params );
			\set_transient( $transient_name, $json_result, self::$transient_time );
			$result = \json_decode( $json_result );
		}
		self::$cpanel_data_fresh = self::$cpanel_data_fresh || $data_fresh;
		return $result->result->data;
	}

	protected static function nocache_cpanel_uapi_result( string $module, string $function, ?array $params = [] )/*: \stdClass | array*/ {
		$json_result = self::cpanel_uapi( $module, $function, $params, 'json' );
		$result = \json_decode( $json_result );
		return $result->result->data;
	}

	protected static function cpanel_user(): ?string {
		return self::cpanel_uapi_result( 'Variables', 'get_user_information' )->user;
	}

	protected static function cpanel_user_created(): ?int {
		return self::cpanel_uapi_result( 'Variables', 'get_user_information' )->created;
	}

	protected static function cpanel_user_updated(): ?int {
		return self::cpanel_uapi_result( 'Variables', 'get_user_information' )->last_modified;
	}

	protected static function cpanel_user_ids(): \stdClass {
		$data = self::cpanel_uapi_result( 'Variables', 'get_user_information' );
		return (object)[ 'uid' => $data->uid, 'gid' => $data->gid ];
	}

	protected static function cpanel_plan(): ?string {
		return self::cpanel_uapi_result( 'Variables', 'get_user_information' )->plan;
	}

	protected static function cpanel_contact(): ?string {
		return \implode( ', ', \array_filter( [
			self::cpanel_uapi_result( 'Variables', 'get_user_information' )->contact_email,
			self::cpanel_uapi_result( 'Variables', 'get_user_information' )->contact_email_2
		] ) );
	}

	protected static function cpanel_quotas(): ?\stdClass {
		return self::cpanel_uapi_result( 'Quota', 'get_local_quota_info' );
	}

	protected static function cpanel_databases( string $vendor = 'Mysql' ): ?array {
		return self::cpanel_uapi_result( $vendor, 'list_databases' );
	}

	protected static function cpanel_total_disk_used(): ?int {
		return self::cpanel_quotas()->bytes_used;
	}

	protected static function cpanel_total_disk_limit(): ?int {
		return self::cpanel_quotas()->byte_limit;
	}

	protected static function cpanel_databases_disk_used(): ?int {
		$disk = 0;
		foreach ( [ 'Mysql', 'Postgresql' ] as $vendor ) {
			$dbs = self::cpanel_databases( $vendor ) ?? [];
			foreach ( $dbs as $db ) {
				$disk += $db->disk_usage;
			}
		}
		return $disk;
	}

	protected static function cpanel_usages(): ?array {
		$usages = self::cpanel_uapi_result( 'ResourceUsage', 'get_usages' ) ?? [];
		$retain = [
			'cachedmysqldiskusage',
			'lvememphy',
			'lvenproc',
			'lveep',
			'lvecpu',
			'lveiops',
			'lveio',
		];
		$kept = [];
		foreach ( $usages as $usage ) {
			if ( \in_array( $usage->id, $retain, true ) ) {
				$use = new \stdClass;
				$use->description   = $usage->description;
				$use->usage         = $usage->usage;
				$use->maximum       = $usage->maximum;
				$use->formatter     = $usage->formatter === 'format_bytes' ? 'size_format' : ( $usage->id === 'lvecpu' ? function( string $v ): string { return "{$v}%"; } : ( $usage->formatter === 'format_bytes_per_second' ? function( string $v ): string { return "{$v} B/s"; } : 'intval' ) );
				$use->error         = $usage->error ?? 'ok';
				$kept[ $usage->id ] = $use;
			}
		}
		return $kept;
	}

	protected static function get_usages(): string {
		$html = \PHP_EOL . '<dl>';
		foreach ( self::cpanel_usages() as $usage ) {
			$html .= \PHP_EOL . '<dt>' . $usage->description . ':</dt>';
//			$html .= \PHP_EOL . '<dd>' . \call_user_func( $usage->formatter, $usage->usage ) . ' of ' . \call_user_func( $usage->formatter, $usage->maximum ) . '</dd>';
			$html .= \PHP_EOL . '<dd>' . $usage->formatter( $usage->usage ) . ' of ' . $usage->formatter( $usage->maximum ) . '</dd>';
		}
		$html .= \PHP_EOL . '</dl>';
		return $html;
	}

	protected static function cpanel_main_domain(): ?string {
		return self::cpanel_uapi_result( 'DomainInfo', 'list_domains' )->main_domain;
	}

	protected static function cpanel_two_factor(): bool {
		return \boolval( self::cpanel_uapi_result( 'TwoFactorAuth', 'get_user_configuration' )->is_enabled );
	}

	protected static function cpanel_list_domains( string $domain_type = 'addon_domains' ): ?array {
		return self::cpanel_uapi_result( 'DomainInfo', 'list_domains' )->$domain_type;
	}

	protected static function cpanel_dead_domains(): ?array {
		return self::cpanel_uapi_result( 'Variables', 'get_user_information', [ 'name' => 'dead_domains' ] )->dead_domains;
	}

	protected static function cpanel_email_accounts( string $domain = '' ): ?array {
		$params = [ 'infinitylang' => 1, 'maxaccounts' => 10 ];
		if ( $domain ) {
			$params['domain'] = $domain;
		}
		return self::cpanel_uapi_result( 'Email', 'list_pops_with_disk', $params );
	}

	protected static function cpanel_email_forwarders(): array {
		$result = self::cpanel_uapi_result( 'Email', 'list_forwarders' );
		return \array_slice( $result ?? [], 0, 10 );
	}

	protected static function cpanel_email_disk_usage(): ?int {
		$used = self::cpanel_uapi_result( 'Email', 'get_main_account_disk_usage_bytes' );
		foreach ( self::cpanel_uapi_result( 'Email', 'list_pops_with_disk' ) ?? [] as $account ) {
			$used += $account->_diskused;
		}
		return $used;
	}

	protected static function cpanel_email_routing_local( string $domain = '' ): bool {
		$domain = $domain ?: self::$site_domain;
		$result = self::cpanel_uapi_result( 'Email', 'list_mxs', [ 'domain' => $domain ] );
		$result = \is_array( $result ) ? \wp_list_pluck( $result, 'local' ) : [];
		return \count( $result ) && \boolval( $result[0] );
	}

	protected static function cpanel_email_mx_self( string $domain = '' ): bool {
		$domain = $domain ?: self::$site_domain;
		$hosts = [];
		$result = \getmxrr( $domain, $hosts );
		return $result && \str_ends_with( $hosts[0], $domain );
	}

	protected static function cpanel_dns_local( string $domain = '' ): bool {
		$domain = $domain ?: self::$site_domain;
		$result = self::cpanel_uapi_result( 'DNS', 'has_local_authority', [ 'domain' => $domain ] );
		$result = \is_array( $result ) ? \wp_list_pluck( $result, 'local_authority' ) : [];
		return \count( $result ) && \boolval( $result[0] );
	}

	public    static function cpanel_main_email_account(): \stdClass {
		$account = new \stdClass;
		$domain = self::cpanel_main_domain();
		$account->domain = $domain;
		$account->login  = self::cpanel_user();
		$account->email  = self::cpanel_uapi_result( 'Variables',  'get_user_information' )->user . '@' . $domain;
		$account->_diskused  = (int)self::cpanel_uapi_result( 'Email', 'get_main_account_disk_usage_bytes' );
		$account->_diskquota = false;
		$account->diskquota = '∞';
		return $account;
	}

	protected static function cpanel_maximum_emails(): int {
		return \intval( self::cpanel_uapi_result( 'Variables', 'get_user_information' )->maximum_emails_per_hour );
	}

	protected static function cpanel_version(): ?string {
		return self::cpanel_uapi_result( 'Variables', 'get_server_information' )->version;
	}

	protected static function cpanel_errors( string $domain = '' ): array {
		$domain = $domain ?: self::$site_domain;
		$results = self::cpanel_uapi_result( 'Stats', 'get_site_errors', [ 'domain' => $domain ] );
		foreach ( $results ?? [] as $key => $result ) {
			$pos = \strpos( $result->entry, '20' );
			$results[ $key ]->date = \strtotime( \substr( $result->entry, $pos, 19 ) );
		}
		$results = \array_filter( $results ?? [], function( \stdClass $obj ): bool {
			$entry = \strtolower( $obj->entry );
			return ! \str_contains( $entry, self::$site_domain );
		} );
		return \array_filter( $results ?? [], function( \stdClass $obj ): bool {
			$entry = \strtolower( $obj->entry );
			return \intval( $obj->date ) > \current_time( 'U' ) - \DAY_IN_SECONDS &&
				( self::$is_debug || \str_contains( $entry, ' [error] ' ) );
		} );
	}

	protected static function cpanel_token(): int {
		$time = \strtotime( 'first day of last month 00:00:00' );
		$did = \idate( 'Y' );
		return ( ( self::$cpanel_user_updated ?? $time ) - ( self::$cpanel_user_created ?? $time ) ) + $time + ( self::$cpanel_user_ids->uid ?? $did ) + ( self::$cpanel_user_ids->gid ?? $did );
	}

	protected static function cpanel_subaccounts(): array {
		$text = _x( 'email',   'cPanel® Service' );
		$text = _x( 'ftp',     'cPanel® Service' );
		$text = _x( 'webdisk', 'cPanel® Service' );
		$accounts = [];
		foreach ( (array)self::cpanel_uapi_result( 'UserManager', 'list_users' ) as $account ) {
			if ( ! \str_starts_with( $account->username, self::$cpanel_user ) ) {
				$o_account = new \StdClass;
				$o_account->name = $account->real_name ?? $account->full_username;
				foreach ( (array)$account->services as $sname => $service ) {
					if ( $service->enabled ?? false ) {
						$o_account->services[] = _x( $sname, 'cPanel® Service' );
					}
				}
				$accounts[ $account->username ] = $o_account;
			}
		}
		return $accounts;
	}

	final public static function email_to_utf8( string $email ): string {
		if ( \is_email( $email ) && \function_exists( 'idn_to_utf8' ) ) {
			$parts = \explode( '@', $email, 2 );
			return $parts[0] . '@' . \idn_to_utf8( $parts[1] );
		} else {
			return $email;
		}
	}

	protected static function to_bytes( string $from ): ?int {
		$from   = \str_replace( 'BB', 'B', $from );
		$units  = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB' ];
		$number = \intval( \str_replace( ' ', '', \substr( $from, 0, -2 ) ) );
		$suffix = \strtoupper( \substr( $from, -2 ) );

		if ( \is_numeric( \substr( $suffix, 0, 1 ) ) ) {
			return \preg_replace( '/[^\d]/', '', $from );
		}

		$exponent = \array_flip( $units )[ $suffix ] ?? null;

		if ( $exponent === null ) {
			return null;
		}
		return $number * ( \KB_IN_BYTES ** $exponent );
	}
}
