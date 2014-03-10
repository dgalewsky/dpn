<?php

require_once 'bagit.php';

define('NODE',      'tdr-local');
define('EXCHANGE',  'dpn-control-exchange');
#define('EXCHANGE', 'test-control-exchange');

define('RSYNC_HOST_STRING', 'ubuntu@ec2-50-16-41-0.compute-1.amazonaws.com:');
#define('RSYNC_HOST_STRING', 'dpn@dpn.lib.utexas.edu:');

define('QUEUE',     'utexas.queue');
define('APP_ID',    basename($argv[0]));

define('DEBUG', true);
define('QUEUED_STATUS', 'queued');
define('TRANSFERRING_STATUS', 'transferring');
define('COMPLETE_STATUS', 'complete');
define('RETRY_STATUS', 'retry');
define('ABORTED_STATUS', 'abort');
define('INITIATED_STATUS', 'initiated');


function bag_size_format($path) {       
        $size = filesize($path);
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

function uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,

                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
}

function print_envelope($envelope) {
        $body    = $envelope->getBody();
        $headers = $envelope->getHeaders();

        printf("    headers          \n");
        ksort($headers);
        $w = 15;
        foreach ( $headers as $header => $value ) {
                if ( strlen($header) > $w ) $w = strlen($header);
        }
        foreach ( $headers as $header => $value ) {
                if ( is_array( $value ) ) {
                        printf("      %-${w}s : %s\n", $header, implode(', ', $value) );
                } else {
                        printf("      %-${w}s : %s\n", $header, $value);
                }
        }

        printf("[PAYLOAD]\n");
        if ( $envelope->getContentType() == 'application/json' ) {
                $_body = json_decode($body, TRUE);
                ksort($_body);
                $body =  json_encode($_body, JSON_PRETTY_PRINT);
        }
        printf("%s\n", $body );
}


