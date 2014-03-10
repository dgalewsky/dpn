<?php

require_once 'KLogger.php';
require_once 'common.php';
require_once 'dpn_registry_utils.php';

$db = db_connect();

$ret = $db->query("Select dpn_object_id from dpn_registry order by id");


while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
    $dpn_object_id = $res['dpn_object_id'];
 
    $reg = get_registry_info($dpn_object_id);
    
    echo prettyPrint(json_encode($reg));
    echo "\n";
    
}

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $prev_char = '';
    $in_quotes = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if( $char === '"' && $prev_char != '\\' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
        $prev_char = $char;
    }

    return $result;
}


function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}

