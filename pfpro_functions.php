<?php
/**
 * pfpro_functions
 * Copyright (c) 2009 Regents of The University of Michigan.
 *
 * This file is designed to be a drop-in replacement for the pfpro PHP
 * extension. If the pfpro extension is enabled, the functions defined in this
 * file will cause PHP to issue an error and exit. Make sure the pfpro.so
 * extension is not enabled.
 *
 * @package pfpro_functions
 */

/**
 * pfpro_cleanup
 *
 * This empty function is defined because it was defined in the pfpro PHP
 * extension.
 */
function pfpro_cleanup()
{
}

/**
 * pfpro_init
 *
 * This empty function is defined because it was defined in the pfpro PHP
 * extension.
 */
function pfpro_init()
{
}

/**
 * pfpro_process_raw
 *
 * An implementation of the pfpro_process_raw function from the pfpro PHP
 * extension using the PayPal Payflow Pro HTTPS interface.
 *
 * @param string $param_str
 * @param string $address
 * @param integer $port
 * @param integer $timeout
 * @param string $proxy_address
 * @param port $proxy_port
 * @param string $proxy_logon
 * @param string $proxy_password
 * @return string
 */
function pfpro_process_raw( $param_str, $address = NULL, $port = NULL, $timeout = NULL,
        $proxy_address = NULL, $proxy_port = NULL, $proxy_logon = NULL, $proxy_password = NULL )
{
    $ch = curl_init();
    // Some curl_setopt values copied from PayPal PayFlow Pro - PHP API
    // https://sourceforge.net/projects/payflowphpapi/

    // Set the address
    if ( $address === NULL ) {
        $ini_value = ini_get( 'pfpro.defaulthost' );
        if ( $ini_value !== '' ) {
            $address = $ini_value;
        } else {
            $address = 'pilot-payflowpro.paypal.com';
        }
    }

    if ( ! empty( $address )) {
        curl_setopt($ch, CURLOPT_URL, 'https://'. $address);
    }

    // Set the port
    if ( $port === NULL ) {
        $ini_value = ini_get( 'pfpro.defaultport' );
        if ( $ini_value !== '' ) {
            $port = $ini_value;
        } else {
            $port = 443;
        }
    }

    if ( ! empty( $port )) {
        curl_setopt($ch, CURLOPT_PORT, $port );
    }

    // Set the timeout
    if ( $timeout === NULL ) {
        $ini_value = ini_get( 'pfpro.defaulttimeout' );
        if ( $ini_value !== '' ) {
            $timeout = $ini_value;
        } else {
            $timeout = 30;
        }
    }

    if ( ! empty( $timeout )) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    }

    // Set the proxy address
    if ( $proxy_address === NULL ) {
        $ini_value = ini_get( 'pfpro.proxyaddress' );
        if ( $ini_value !== '' ) {
            $proxy_address = $ini_value;
        }
    }

    if ( ! empty( $proxy_address )) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy_address);
    }

    // Set the proxy port
    if ( $proxy_port === NULL ) {
        $ini_value = ini_get( 'pfpro.proxyport' );
        if ( $ini_value !== '' ) {
            $proxy_port = $ini_value;
        }
    }

    if ( ! empty( $proxy_port )) {
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
    }

    // Set the proxy logon and password
    if ( $proxy_logon === NULL ) {
        $ini_value = ini_get( 'pfpro.proxylogon' );
        if ( $ini_value !== '' ) {
            $proxy_logon = $ini_value;
        }
    }

    if ( $proxy_password === NULL ) {
        $ini_value = ini_get( 'pfpro.proxypassword' );
        if ( $ini_value !== '' ) {
            $proxy_password = $ini_value;
        }
    }

    if ( ! empty( $proxy_logon ) and ! empty( $proxy_password )) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_logon .':'. $proxy_password );
    }


    $headers = array();
    $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
    $headers[] = "Content-Length: ". strlen( $param_str );
    $headers[] = "Connection: close";
    $headers[] = "Host: ". $address;
    $headers[] = "X-VPS-Timeout: ". $timeout;
    $headers[] = "X-VPS-Request-ID: ". sha1( uniqid( rand(), TRUE ));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
    curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param_str); //adding POST data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable

    return curl_exec($ch);
}

/**
 * pfpro_process
 *
 * Converts an array to a parameter string, passes the string to
 * pfpro_process_raw(), and converts the result into an array.
 *
 * @param array $parameters
 * @param string $address
 * @param integer $port
 * @param integer $timeout
 * @param string $proxy_address
 * @param port $proxy_port
 * @param string $proxy_logon
 * @param string $proxy_password
 * @return array
 */
function pfpro_process( $parameters, $address = NULL, $port = NULL, $timeout = NULL,
        $proxy_address = NULL, $proxy_port = NULL, $proxy_logon = NULL, $proxy_password = NULL )
{
    $param_parts = array();
    if ( is_array( $parameters )) {
        foreach ( $parameters as $key => $value ) {
            $param_parts[] = $key .'['. strlen( $value ) .']='. $value;
        }
    }
    $param_str = implode( '&', $param_parts );

    $result_raw = pfpro_process_raw( $param_str, $address, $port, $timeout,
        $proxy_address, $proxy_port, $proxy_logon, $proxy_password );

    if ( ! empty( $result_raw )) {
        foreach ( explode( '&', $result_raw ) as $pair ) {
            list( $key, $value ) = explode('=', $pair);
            $result[$key] = $value;
        }
        return $result;
    }
    return array();
}

/**
 * pfpro_version
 *
 * @return string
 */
function pfpro_version()
{
    return 'HTTPS Interface';
}
