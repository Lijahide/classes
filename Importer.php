<?php

require_once "classes/Parser/CsvParser.php" ;
require_once "classes/Logger/Log.php" ;
require_once "classes/Cleaner/Cleaner.php" ;
require_once "classes/Query/InsertQuery.php" ;

class Importer {

    private $log ; # Logging object
    private $parser ; # Parser Object
    private $cleaner ; # Cleaner object
    private $search_query ; # SelectQuery object
    private $q_insert ; # InsertQuery object
    private $q_update ; #UpdateQuery object
    private $duplicate_marker ; #UpdateQuery object

    private $permission ;
    private $index ;

    private $data_set ;
    private $total_rows ;

    private $insert_set ;
    private $update_set ;
    private $duplicate_set ;
    private $error_set ;

    private $pointer ;
    private $cur_row ;
    private $n_matches ;

    private $dup_ids = array() ;

    ////////////////////////////////////////////////////////////

    public function __construct( $param_arr ) {

        assert( $this->validate_params( $param_arr ) ) ;

        foreach ( $param_arr AS $key => $value ) {
            $this->$key = $value ;
        }

        $this->cur_date = date( 'Y-m-d h:m:s' ) ;
    }


    /////////////////////////////////////////////

    public function run( bool $permission = TRUE ) {

        $this->permission = $permission ;

        try {
            $this->data_set = $this->parser->parse() ;

            $this->filter_target_rows() ;

            $this->total_rows = count( $this->data_set ) ;

            assert( is_array( $this->data_set ) ) ;

            $this->sort_processes( $this->data_set ) ;


            if ( count( $this->insert_set ) > 0 ) {
                $this->insert( $this->insert_set ) ;
            }
            if ( count( $this->update_set ) > 0 ) {
                $this->update( $this->update_set ) ;
            }
            if ( count( $this->duplicate_set ) > 0 ) {
                $this->mark_duplicates( $this->duplicate_set ) ;
            }
            // $this->log_errors() ;
        }
        catch ( Throwable $e ) {
            echo error_msg( $e ) ;
        }

    }

    /////////////////////////////////////////////


    private function sort_processes( $data ) {

        if ( !is_array( $data[0] ) ) {

            $this->clean() ;

            $this->search_for_existing_record() ;

            if ( $this->n_matches == 1 ) {
                $this->update_set[] = $data ;
            }
            elseif ( $this->n_matches == 0 ) {
                $this->insert_set[] = $data ;
            }
            elseif ( $this->n_matches > 0 ) {
                $this->duplicate_set[] = $data ;
            }
        }
        else
        {
            $this->pointer = 0 ;
            foreach ( $data AS $i => $row ) :
                $this->pointer = $i ;
                $this->cur_row = $row ;
                $this->sort_processes( $row ) ;
                array_shift( $this->data_set ) ;
            endforeach ;

            return TRUE ;
        }

        return FALSE ;
    }

    /////////////////////////////////////////////

    private function clean() {

        $this->cur_row =
        $this->cleaner->clean( $this->cur_row ) ;
    }


    /////////////////////////////////////////////

    private function search_for_existing_record() {

        if ( count( array_filter( $this->cur_row ) ) == 0 ) {
            throw new Exception( "data_set must not be empty" ) ;
        }

        $q = $this->prepare_search_query() ;
        $q->run() ;

        $this->n_matches = $q->__get( "num_rows" ) ;
        $matches_arr = $q->__get( "result_arr" ) ;

        if ( $this->n_matches > 1 ) {
            $ids = array_column( $matches_arr , "id" ) ;
            $this->dup_ids = array_merge( $ids , $this->dup_ids ) ;
        }

        $this->log_search_results() ;
    }


    /////////////////////////////////////////////

    private function prepare_search_query()
    {
        $q = $this->search_query ;

        extract( $this->cur_row ) ;
        $query_params = compact( $q->__get( "parameter_fields" ) ) ;
        $q->__set( "query_params" , $query_params ) ;

        $q->write_query() ;

        return $q ;
    }


    /////////////////////////////////////////////

    private function insert( $data ) {

        if ( !is_array( $data[0] ) )
        {
            $this->q_insert->__set( "data_arr" , $data ) ;
            $this->q_insert->write_query() ;
            $this->q_insert->run( $this->permission ) ;
            return TRUE ;
        }
        else
        {
            $this->pointer = 0 ;
            foreach ( $data AS $i => $row ) :
                $this->pointer = $i ;
                $this->cur_row = $row ;
                $this->insert( $row ) ;
                array_shift( $this->insert_set ) ;
            endforeach ;
            return TRUE ;
        }
        return FALSE ;
    }


    /////////////////////////////////////////////

    private function mark_duplicates( $data )
    {
        $q = $this->duplicate_marker ;

        $id_list = implode( "' , '" , $this->dup_ids ) ;
        $q->__set(
            "where_clause" ,
            "$this->index IN( '$id_list' )"
        ) ;

        $q->write_query() ;

        echo Test::print_pre( $q->__get( "statement" ) ) ;

        $q->run( $this->permission ) ;
        return TRUE ;
    }


    /////////////////////////////////////////////

    private function update( $data )
    {
        $q = $this->q_update ;

        extract( $this->cur_row ) ;
        $query_params = compact( $q->__get( "parameter_fields" ) ) ;
        $q->__set( "query_params" , $query_params ) ;
    }


    /////////////////////////////////////////////

    private function compare( $data , $match )
    {

    }


    /////////////////////////////////////////////

    private function log_search_results()
    {
        $row = $this->pointer++ ;

        if ( $this->n_matches == 1 )
        {
            $this->log->add_entry( 'info' , "Row $row is already in the DB, and will not be re-imported, Check if it should be updated" ) ;
        }
        elseif ( $this->n_matches == 0 )
        {
            $this->log->add_entry( 'notice' , "Row $row is not yet in the DB, and will be inserted" ) ;
        }
        elseif ( $this->n_matches > 0 )
        {
            $this->log->add_entry( 'warning' , "Somehow the same record, Row $row, was inserted into the migration table multiple times" ) ;
        }
    }


    /////////////////////////////////////////////

    private function validate_params( $param_arr ) {

        assert( is_array( $param_arr ) ) ;

        extract( $param_arr ) ;

        assert( isset( $parser ) ) ;
        assert( is_a( $parser , 'Parser' ) ) ;
        assert( isset( $cleaner ) ) ;
        assert( is_a( $cleaner , 'Cleaner' ) ) ;

        return TRUE ;
    }


    /////////////////////////////////////////////

    private function filter_target_rows() {

        $c_data = count( $this->data_set ) ;
        $control = $_GET['control'] ?? NULL ;
        $start = $_GET['start'] ?? NULL ;
        $range = $_GET['range'] ?? NULL ;
        $row = $_GET['row'] ?? NULL ;


        if (
          !isset( $control )
          &&
          !isset( $row )
          &&
          ( !isset( $start ) && !isset( $range ) )
        ) {
            $this->data_set = array_slice( $this->data_set , 0 , 1 ) ;
        }

        if ( isset( $control ) ) {
            if (
                is_numeric( $control ) && $control > $c_data
            ) {
                throw new Exception( "Control target may not be higher than the total count of Subject Rows" ) ;
            }
            elseif ( is_numeric( $control ) && $control == 0 ) {
                throw new Exception( "Control target may not be Zero(0)" ) ;
            }
            elseif ( !is_numeric( $control ) && $control != "all" ) {
                throw new Exception( "\"$control\" Is not a valid control target." ) ;
            }
        }


        if ( $control == 'all' ) {
            return ;
        }
        elseif ( is_numeric( $control ) ) {
            return array_slice( $this->data_set , 0 , $control ) ;
        }
        elseif ( isset( $row ) ) {
            $row-- ;
            $this->data_set = $this->data_set[$row] ;
        }
        elseif ( isset( $start ) && isset( $range ) ) {
            $start-- ;
            $this->data_set = array_slice( $this->data_set , $start , $range ) ;
        }

    }




}


 ?>
