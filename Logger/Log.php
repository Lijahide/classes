<?php
namespace Logger ;

// TODO: this class currently will work as expected when used in an open page. Not when it is used within a class, neither as an inner close, nor an injected class, nor a child-class.


/* NOTE
The function *debug_backtrace()* is the centerpoint of this class.

When compiled together, the structure becomes as the following:

$log = array(
    $i_log_entries => array(
        $i_trace_depth => array(
            [file] ,
            [line] ,
            [function] ,
            [class] ,
            [etc]
        )
    )
) ;

$log is the total array of "backtrace" elements.

$i_log_entries is the iteration of the backtrace elements

$i_trace_depth is the "depth" the backtrace had to traverse to reach the root of the function.


To further explain $i_trace_depth:

If you use debug_backtrace() inside of a function, it will return the following information:

the file where the parent function was called ,
the line it was called on ,
the parent function ,
the class the parent function belongs todo,
the object that called the function,
the arguments passed to the parent function.


TODO: My leading idea right now is to solve this problem using recursion. What im not sure about, is the limit of the depth, play with that parameter "limit" for debug_backtrace()

Also not sure about how i want to modularize logs so they can stack into each other i.e.: log entry -> file log -> section log -> etc.




$control_log = new Log() ;

*/


class Log {

	private $project_file ;
	private $log ;
    private $log_stack ; # array of log objects


    public function __construct( $root_file ) {
    	$this->project_file = $root_file ;
      $this->log = array() ;
    }


    /////////////////////////////////////////////

    public function add_entry(
      $level ,
      $msg ,
      $context = NULL
    )
    {

        $args = array( "level" , "msg" , "context" ) ;
        $args = compact( $args ) ;
        $args = array_filter( $args ) ;

        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ;

        $entry = $this->clean_trace( $bt ) ;

        $entry = array_merge( $args , $entry ) ;
        $entry['file'] = $this->clean_path( $entry['file'] ) ;

        array_push( $this->log , $entry ) ;

        // $this->log[] = $entry ;
    }


    /////////////////////////////////////////////
    /////////////////////////////////////////////

    private function clean_trace( $bt )
    {

        $entry = $bt[0] ;

        if (
          $bt[1]['function'] == "require"
          ||
          $bt[1]['function'] == "include"
        ) {

            unset( $entry['function'] , $entry['class'] , $entry['type'] ) ;
        }
        else {

            $entry['function'] = $bt[1]['function'] ;

            unset( $entry['class'] , $entry['type'] ) ;
        }

        return $entry ;
    }


    /////////////////////////////////////////////

    private function clean_path( $path )
    {
        $path_arr = explode( "/" , $path ) ;
        $path_arr = array_slice( $path_arr , -3 ) ;
        $path = implode( "/" , $path_arr ) ;
        return $path ;
    }


    /////////////////////////////////////////////

    public function get_log()
    {
    	 return $this->log ;
    }


    ///////////////////////////////////////////////////////

}




?>
