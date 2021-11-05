<?php


/*
This class serves as a "package" or "library".
It serves as a set of functions that will assist in testing and development of code.

It will contain only static methods so that it can be used without creating an object.

Unfortunately, due to the nature of "debug_backtrace()", we must repeat that function in every method.
*/



class Test {

    static public function print_pre( $value )
    {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ;
        $trace = self::prepare_trace( $bt ) ;

        $value = print_r( $value , TRUE ) ;
        $value = "<pre>$trace<br>$value</pre>" ;

        return $value ;
    }


    ////////////////////////////////////////////////////////////

    static public function dump( $value )
    {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ;
        $trace = self::prepare_trace( $bt ) ;

        $value = var_dump( $value , TRUE ) ;
        $value = "<pre>$trace<br>$value</pre>" ;

        return $value ;
    }


    ////////////////////////////////////////////////////////////

    static public function run_query( $q_statement )
    {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ;
        $trace = self::prepare_trace( $bt ) ;

        $q_test = "EXPLAIN $q_statement" ;

        try {
            $r_test = get_query( $q_test , TRUE ) ;
            $output = "<pre>Successful Query Test | $trace<br>$q_test</pre>" ;
            return $output ;
        }
        catch ( Throwable $e ) {
            $output = "<pre>Failed Query Test | $trace<br>$q_test</pre>" ;
            return $output ;
        }
    }


    ////////////////////////////////////////////////////////////

    static private function prepare_trace( $bt )
    {
        $bt = $bt[0] ;
        $bt['file'] = self::clean_path( $bt['file'] ) ;
        $trace = "<b>File:</b> {$bt['file']} | <b>Line:</b> {$bt['line']}" ;
        return $trace ;
    }

    ////////////////////////////////////////////////////////////

    static private function clean_path( $path )
    {
        $path_arr = explode( "/" , $path ) ;
        $path_arr = array_slice( $path_arr , -3 ) ;
        $path = implode( "/" , $path_arr ) ;
        return $path ;
    }

    ////////////////////////////////////////////////////////////

}


?>
