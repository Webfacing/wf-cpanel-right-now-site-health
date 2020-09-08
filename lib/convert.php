<?php
function convertToBytes( string $from ): ?int {
	str_replace( 'BB', 'B', $from );
    $units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB' ];
    $number = substr( $from, 0, -2);
    $suffix = strtoupper( substr( $from, -2 ) );

    //B or no suffix
    if( is_numeric( substr( $suffix, 0, 1 ) ) ) {
        return preg_replace( '/[^\d]/', '', $from );
    }

    $exponent = array_flip( $units )[ $suffix ] ?? null;
    if( $exponent === null ) {
        return null;
    }

    return $number * ( 1024 ** $exponent );
}
