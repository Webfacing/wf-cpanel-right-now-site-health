<?php
namespace WebFacing\cPanel;

/**
 * Exit if accessed directly
 */
if ( ! \class_exists( 'WP' ) ) {
	exit;
}

function __( string $text ): string {
	return \__( $text, Plugin::$plugin->TextDomain );
}

function _x( string $text, string $context ): string {
	return \_x( $text, $context, Plugin::$plugin->TextDomain );
}

function _n( string $singular, string $plural, int $number ): string {
	return \_nx( $singular, $plural, $number, Plugin::$plugin->TextDomain );
}

function _nx( string $singular, string $plural, int $number, string $context ): string {
	return \_nx( $singular, $plural, $number, $context, Plugin::$plugin->TextDomain );
}

function _e( string $text ): void {
	\_e( $text, Plugin::$plugin->TextDomain );
}

function _ex( string $text, string $context ): void {
	\_ex( $text, $context, Plugin::$plugin->TextDomain );
}
