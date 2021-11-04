<?php
namespace WebFacing\cPanel;

function error_log( string $message, int $message_type = 0 , ?string $destination = null, ?string $extra_headers = null ): bool {

	if ( Plugin::$is_debug ) {
		return \error_log( \plugin_basename( PLUGIN_FILE ) . ' ' . $message, $message_type, $destination, $extra_headers );
	} else {
		return true;
	}
}
