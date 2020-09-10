<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace all occurrences of the search string with the replacement string.
 *
 * @author Sean Murphy <sean@iamseanmurphy.com>
 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 * @link http://php.net/manual/function.str-replace.php
 *
 * @param mixed $search
 * @param mixed $replace
 * @param mixed $subject
 * @param int $count
 * @return mixed
 */

if ( ! function_exists( 'mb_str_replace' ) ) {
	function mb_str_replace( $search, $replace, $subject, int &$count = 0 ) {
		if ( ! is_array( $subject ) ) {
			// Normalize $search and $replace so they are both arrays of the same length
			$searches = is_array( $search ) ? array_values( $search ) : array( $search );
			$replacements = is_array( $replace ) ? array_values( $replace ) : array( $replace );
			$replacements = array_pad( $replacements, count( $searches ), '');

			foreach ( $searches as $key => $search ) {
				$parts = mb_split( preg_quote( $search ), $subject );
				$count += count( $parts ) - 1;
				$subject = implode( $replacements[ $key ], $parts );
			}
		} else {
			// Call mb_str_replace for each subject in array, recursively
			foreach ( $subject as $key => $value ) {
				$subject[ $key ] = mb_str_replace( $search, $replace, $value, $count );
			}
		}

		return $subject;
	}
}

if ( ! function_exists( 'array_key_first' ) ) {
	function array_key_first( array $array ) {
		foreach ( $array as $key => $value ) {
			return $key;
		}
	}
}

if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( string $haystack, string $needle ): bool {
		return strpos( $haystack, $needle ) !== false;
	}
}

if ( ! function_exists( 'str_starts_with' ) ) {
    /**
     * Convenient way to check if a string starts with another string.
     *
     * @param string $haystack String to search through.
     * @param string $needle Pattern to match.
     * @return bool Returns true if $haystack starts with $needle.
     */
    function str_starts_with( string $haystack, string $needle ): bool {
        $length = strlen( $needle );
        return $needle === '' || substr( $haystack, 0, $length ) === $needle;
    }
}

if ( ! function_exists( 'str_ends_with' ) ) {
    /**
     * Convenient way to check if a string ends with another string.
     *
     * @param string $haystack String to search through.
     * @param string $needle Pattern to match.
     * @return bool Returns true if $haystack ends with $needle.
     */
    function str_ends_with( string $haystack, string $needle ): bool {
        $length = strlen( $needle );
        return $needle === '' || substr( $haystack, -$length ) === $needle;
    }
}

if ( ! function_exists( 'mb_str_starts_with' ) ) {
    /**
     * Multibyte - Convenient way to check if a string starts with another string.
     *
     * @param string $haystack String to search through.
     * @param string $needle Pattern to match.
     * @return bool Returns true if $haystack starts with $needle.
     */
    function mb_str_starts_with( string $haystack, string $needle ): bool {
        $length = mb_strlen( $needle );
        return mb_substr( $haystack, 0, $length ) === $needle;
    }
}

if ( ! function_exists( 'mb_str_ends_with' ) ) {
    /**
     * Multibyte - Convenient way to check if a string ends with another string.
     *
     * @param string $haystack String to search through.
     * @param string $needle Pattern to match.
     * @return bool Returns true if $haystack ends with $needle.
     */
    function mb_str_ends_with( string $haystack, string $needle ): bool {
        $length = mb_strlen( $needle );
        return mb_substr( $haystack, -$length ) === $needle;
    }
}
