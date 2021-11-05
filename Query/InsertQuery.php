<?php

require_once "classes/Query/Query.php" ;
require_once "classes/Query/LoadQuery.php" ;

class InsertQuery extends Query implements LoadQuery {

    protected $data_arr ;
    protected $constant_fields ;

    private $inserted_id ;
    private $new_data ;

    /////////////////////////////////////////////

    public function __construct( array $param_arr ) {

        // guard clauses / assertions

        assert( isset( $param_arr['table'] ) ) ;

        /////////////////////

        $this->dynamic_assignment( $param_arr ) ;

    }


    /////////////////////////////////////////////

    /**
     * Validate that the row(s) were inserted properly
     *
     * Check that the Query actually worked successfuly, and that the supplied fields and values were populated correctly.
     * There should be no difference between the supplied array, and the array we pull from the database.
     *
     * @param void
     * @return ?idk yet... REVIEW
   */
    public function validate_result() {

        $this->inserted_id = get_inserted_id() ;

        assert( isset( $this->inserted_id ) ) ;

        $differences =
        array_diff( $this->data_arr , $this->new_data[0] ) ;

        if ( count( $differences ) > 0 ) {

            $this->logger->add_entry( 'warning' , 'It appears the data did not insert properly' , $this->statement ) ;

            $return = FALSE ;
        }
        elseif ( count( $differences ) == 0 ) {

            $this->logger->add_entry( 'success' , 'Data was Successfully Inserted' ) ;

            $return = TRUE ;
        }

        return $return ;
    }


    ///////////////////////////////////////////////////////
    // Internal methods


    /**
     * Dynamically write an INSERT statement using an array.
     *
     * Given an array, where the keys represent table column names, and the values represent the values, we create a list of column names and a list of values, this allows us to dynamically create an insert query.
     *
     * NOTE: The keys in the supplied array MUST match the names of the table column completely.
     *
     * @param void
     * @return string
   */
    public function write_query( $param_arr = NULL ) {

        if ( isset( $param_arr ) ) {
            $this->dynamic_assignment( $param_arr ) ;
        }

        if ( count( array_filter( $this->data_arr ) ) == 0 ) {
            throw new Exception( "No data supplied, cannot insert" ) ;
        }

        $this->data_arr =
        $this->recursive_addslashes( $this->data_arr ) ;

        $this->data_arr =
        $this->merge_constant_fields( $this->data_arr ) ;

        if ( count( array_filter( $this->data_arr ) ) == 0 ) {
            throw new Exception( "something went wrong while creating your insert query" ) ;
        }


        if ( !is_array( $this->data_arr[0] ) ) {
            $this->assoc_insert() ;
        }
        else {
            $this->multi_row_insert() ;
        }

        $q =
        " INSERT INTO
              $this->table
              ( `$this->fields_list` )
          VALUES
              $this->values_list
        " ;

        $this->statement = htmlspecialchars( $q ) ;
    }


    ////////////////////////////////////////////////////////////

    private function assoc_insert() {

        $this->data_arr = array_filter( $this->data_arr ) ;

        $fields_arr = array_keys( $this->data_arr ) ;
        $c_fields = count( $fields_arr ) ;
        $fields_list = implode( "` , \t\n`" , $fields_arr ) ;

        $values_arr = array_values( $this->data_arr ) ;
        $c_values = count( $values_arr ) ;
        $values_list = implode( "' ,  \t\n'" , $values_arr ) ;
        $values_list = "( '$values_list' )" ;

        if ( $c_fields != $c_values ) {
            throw new Exception( "Field Count and Value Count must match" ) ;
        }

        $this->fields_list = $fields_list ;
        $this->values_list = $values_list ;
    }


    ////////////////////////////////////////////////////////////

    /**
     * Dynamically write a multiple-row INSERT statement.
     *
     * There is a slight difference in the syntax to insert multiple rows into the database, the automation of this requires us to create a new function.
     *
     * @param type var Description
     * @return return type
   */
    private function multi_row_insert() {

        if ( count( $this->data_arr ) == 1 )
        {
            $this->data_arr[0] = array_filter( $this->data_arr[0] ) ;
        }

        $fields_arr = array_keys( $this->data_arr[0] ) ;
        $c_column = count( $fields_arr ) ;
        $this->fields_list = implode( "` , `" , $fields_arr ) ;


        foreach ( $this->data_arr AS $i => $row ) {

            $c_row = count( $row ) ;
            if ( $c_column != $c_row ) {
                throw new Exception( "Field count and Value count must match" ) ;
            }

            $this->data_arr = array_filter( $this->data_arr ) ;

            $values_arr = array_values( $row ) ;
            $values_list = implode( "' , '" , $values_arr ) ;
            $row_values = "( '$values_list' )" ;
            $value_clause[] = $row_values ;
        }

        $this->values_list = implode( " ,\n\t" , $value_clause ) ;
    }



    /////////////////////////////////////////////

    private function merge_constant_fields( $data ) {

        if ( !isset( $this->constant_fields ) ) {
            return $data ;
        }

        if ( !is_array( $data[0] ) ) {
            $data = array_merge( $data , $this->constant_fields ) ;
            return $data ;
        }
        else {
            foreach ( $data AS $i => $row ) :
                $data[$i] =
                array_merge( $row , $this->constant_fields ) ;
            endforeach ;
            return $data ;
        }

        return FALSE ;
    }


    /////////////////////////////////////////////

    /**
     * Use the $inserted_id to query for the most recently inserted record.
     *
     * Part of Validating that an INSERT query ran successfully, is verifying that it actually inserted, its a little redundant, so this function will run under the $this->validate() method, which has to be manually executed by the programmer in the implementation.
     *
     * TODO change so that it returns boolean, have it pass it's message to a logging object.
     *
     * @param void
     * @return boolean
   */
    private function get_inserted_row() {

        $fields = array_keys( $this->data_arr ) ;
        $fields = implode( "," , $fields ) ;

        if ( $this->type == 'INSERT' ) {

            if ( isset( $primary_key ) ) {

                $this->where_clause =
                "$primary_key = '$this->inserted_id'" ;
            }
            else {

                $this->where_clause = "id = '$this->inserted_id'" ;
            }
        }

        $q =
        " SELECT
              $fields
          FROM
              $this->table
          WHERE
              $this->where_clause
        " ;

        $r = get_query( $q ) ;
        $n = get_num_rows( $r ) ;
        $arr = get_all( $r ) ;

        $this->n_affected_rows = $n ;
        $this->new_data = $arr ;

        if ( $n == 0 || boolval( $r ) == FALSE ) {

            $message = "Something went wrong during your $this->type process. We could not Validate it's success" ;
            $content = "<pre>$q</pre>" ;
            $color = "red" ;
            $return = FALSE ;
        }
        else {

            $return = TRUE ;
        }

        $this->validate_message =
        collapsible( $message , $content , $color ) ;

        return $return ;
    }


    /////////////////////////////////////////////

}



?>
