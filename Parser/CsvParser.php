<?php

require_once "classes/Parser/Parser.php" ;

/**
 *
 */




class CsvParser extends Parser {

    private $path ;
    private $headers ;

    private $csv_arr ;
    private $total_records ;

    private $filter ; #filter object
    private $log ; #log object

    ////////////////////////////////////////////////////////////

    /* constructor
      enter the file path to the csv file
    */
    public function __construct( array $param_arr ) {

        foreach ( $param_arr AS $key => $value ) {
            $this->$key = $value ;
            // assert( isset( $value ) , "$key MUST be set" ) ;
        }

        if ( !isset( $this->logger ) ) {
            $this->logger = new Logger\Log( "Parser Class" ) ;
        }

        $param_arr = array_filter( $param_arr ) ;
        $c_args = count( $param_arr ) ;
        $this->logger->add_entry( 'INFO' , "$c_args Parameters were Passed to the CSV Parser" ) ;
    }


    /////////////////////////////////////////////

    public function __get( $property ){
        return $this->$property ;
    }


    /////////////////////////////////////////////

    public function parse() {

        try {
            $this->convert_file() ;
            $this->assign_headers() ;

            $this->csv_arr = $this->filter->filter( $this->csv_arr ) ;
        }
        catch ( Throwable $e ) {
            throw $e ;
        }

        $this->logger->add_entry( 'success' , 'CSV successfully parsed' ) ;

        return $this->csv_arr ;
    }


    /////////////////////////////////////////////

    private function convert_file() {

        if ( !file_exists( $this->path ) ) {
            throw new Exception( "File/Directory ( $this->path ) does not Exist" ) ;
        }

        $this->csv_arr = file( $this->path , FILE_IGNORE_NEW_LINES ) ;
        $this->total_records = count( $this->csv_arr ) ;

        $this->logger->add_entry( 'info' , "$this->total_records Rows Were successfullly parsed from $this->path" ) ;

        return TRUE ;
    }


    /////////////////////////////////////////////

    private function assign_headers() {

        assert( is_array( $this->csv_arr ) ) ;

        foreach ( $this->csv_arr AS $i => $row_str ) :

              // parse csv row into str
            $row_arr = str_getcsv( $row_str ) ;

            if ( count( $row_arr ) != count( $this->headers ) )
            {
                throw new Exception( "Row count($c_row) MUST be equal to the Header count($c_headers)" ) ;
            }


              // values from csv are combined with field list
            $row_arr = array_combine( $this->headers , $row_arr ) ;


            if ( count( array_filter( $row_arr ) ) > 0 ) {
                $csv_arr[] = $row_arr ;
            }

        endforeach ;

        $csv_arr = array_filter( $csv_arr ) ;

        unset( $this->csv_arr ) ;
        $this->csv_arr = $csv_arr ;
    }

    ////////////////////////////////////////////////////////////
    // Internal Functions

    private function parse_header() {

        $arr = $this->csv_arr ;

        if ( is_title_row( $arr[0] ) ) {
            array_shift( $this->csv_arr ) ;
        }

        if ( is_header_row( $arr[0] ) ) {
            $this->header_cols = $arr[0] ;
        }
    }


    /////////////////////////////////////////////

    private function is_title_row( string $row ) {

        // parse row into array
        $row_arr = str_getcsv( $row ) ;

        // remove empty elements
        $filtered_arr = array_filter( $row_arr ) ;

        // a title would only have one element
        // a title row would also only have the first element filled
        if (
          count( $filtered_arr ) != 1
          &&
          $row_arr[0] != ""
        ) {
            return TRUE ;
        }
    }


    /////////////////////////////////////////////

    // REVIEW: Im not quite sure the best way to blindly itentify if a row is a header row.

    private function is_header_row( string $row ) {

        $row_arr = str_getcsv( $row ) ;

        // a header row would not have any empty elements
        if ( in_array( "" , $row_arr ) ) {
            return FALSE ;
        }

        // a header row would only be strings.
        foreach ( $row_arr AS $i => $value ) {
            if ( is_numeric( $value ) ) {
                return FALSE ;
            }
        }

        return TRUE ;
    }

}







 ?>
