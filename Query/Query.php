<?php

require_once "classes/Logger/Log.php" ;

abstract class Query {

    protected $statement ;
    protected $result ;
    protected $table ;

    protected $log ;


    abstract public function __construct( array $param_arr ) ;


    ///////////////////////////////////////////////////////

    public function __get( string $str ) {
        if ( !property_exists( $this , $str ) ) {
            return FALSE ;
        }
        return $this->$str ;
    }


    ///////////////////////////////////////////////////////

    public function __set( string $property , $value ) {
        $this->$property = $value ;
    }


    ///////////////////////////////////////////////////////

    public function test() {

        $q_test = "EXPLAIN $this->statement" ;
        $content = $this->statement ;

        try {
            $r_test = get_query( $q_test , TRUE ) ;

            $this->log->
            add_entry( 'info' , 'Successful Query Test' , $content ) ;

            return TRUE ;
        }
        catch ( Throwable $e ) {
            $this->log->
            add_entry( 'error', 'Failed Query Test<br>' . error_msg( $e ) , $content ) ;

            return FALSE ;
        }

    }


    ///////////////////////////////////////////////////////

    public function run( bool $permission = TRUE ) {
        $this->execute( $permission ) ;
    }


    ///////////////////////////////////////////////////////

    protected function execute( bool $permission = TRUE ) {

        if ( $permission == FALSE ) {

            $this->log->
            add_entry( "notice" , "We Were Not Given Permission to Execute this Query, a test-run was executed instead." ) ;

            return $this->test() ;
        }

        try {
            $this->result = get_query( $this->statement , TRUE ) ;
        }
        catch ( Throwable $e ) {
            throw $e ;
        }

        if ( boolval( $this->result ) == TRUE ) {
            $this->log->add_entry( 'success' , 'Successful Query' , $this->statement ) ;
        }
        elseif ( boolval( $this->result ) == FALSE ) {
            $this->log->add_entry( 'error' , 'Failed Query' , $this->statement ) ;
        }

        return boolval( $this->result ) ;
    }


    ///////////////////////////////////////////////////////

    protected function dynamic_assignment( array $param_arr ) {

        foreach ( $param_arr AS $prop => $value ) {
            $this->$prop = $value ;
        }
    }


    ///////////////////////////////////////////////////////

    protected function recursive_addslashes( $input ) {

        if ( !is_array( $input ) ) {
            return addslashes( $input ) ;
        }
        elseif ( is_array( $input ) ) {
            foreach ( $input AS $key => $value ) {
                $output[$key] = $this->recursive_addslashes( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }

}

/////////////////////////////////////////////////////////////////

/*
INPUTS:
    • $table:
          Database Table associated to the data
    • $data_arr:
          Array where keys are table column names and values are the associated values
    • $where_clause:
          The WHERE clause used for the query. Only Required when $type is UPDATE.
    • $index:
          OPTIONAL; Index Column of the Selected Table; Used in Retrieving the Record after LOAD.

INTERNAL PROPERTIES:
    • $query_str:
          The fully formed, dynamically created query string itself; Will always only be an INSERT or UPDATE query.
    • $result:
          MySQLi "Result" Object;
    • $n_affected_rows:
          The number of records INSERTED or UPDATED.
    • $inserted_id:
          The DB ID for the record that was INSERTED
    • $new_data:
          The Array of record(s) returned by the validate Query.


CLIENT-AVAILABLE METHODS:
    The following are methods that are built to be the code client interface to this class.

    • __construct():
        Constructor method, called upon creation of new object;
        This method is where the client will enter the INPUT properties.
        The majority of this class' error handling happens here;
        Properties are populated;
        The $query_str property is created here using the methods: make_insert_query() & make_update_query(). Depending on the $type property.
    • __get():
        Standard __get() functionality;
        INPUT a *string*, being the name of the property you want to access.
        OUTPUT is the value of the requested property.
    • run():
        Executes the Internally stored Query String.
        INPUT permission to execute the query. Must evaluate to a boolean. This allows external user to conditionally run a query. Helpful for testing and validation.
        Executes query and creates & stores a report message in an internal property, about the success or failure of the query.
    • validate():
          Performs a SELECT query to retrieve the record that was just inserted or the record(s) that were updated.
          This is essentially a double-check to make sure the LOAD query was truly successful.
          It also allows us to compare the new record(s) with the old records to determine if the proper fields were truly updated/inserted with their respective values.
*/



 ?>
