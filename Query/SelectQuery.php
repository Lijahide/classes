<?php


require_once "classes/Query/Query.php" ;

class SelectQuery extends Query {

    protected $path ;

    protected $field_list ;
    protected $where ;
    protected $orderby ;
    protected $limit ;

    protected $query_params ;
    protected $parameter_fields ;

    protected $num_rows ;
    protected $result_arr ;


    public function __construct( array $param_arr ) {

        $this->validate_construction( $param_arr ) ;

        $this->dynamic_assignment( $param_arr ) ;
    }


    ////////////////////////////////////////////////////////////

    public function write_query( array $param_arr = NULL ) {

        if ( isset( $param_arr ) )
        {
            $this->dynamic_assignment( $param_arr ) ;
        }
        if ( isset( $this->query_params ) )
        {
            $this->query_params =
            $this->recursive_addslashes( $this->query_params ) ;
            extract( $this->query_params ) ;
        }

        assert( isset( $this->field_list ) ) ;

        $field_list = implode( " ,\n\t" , $this->field_list ) ;
        $q = require $this->path ;


        if ( isset( $this->where ) ) {
            $q .= "\n" . $this->where ;
        }
        if ( isset( $this->orderby ) ) {
            $q .= "\nORDER BY \n\t" . $this->orderby ;
        }
        if ( isset( $this->limit ) ) {
            $q .= "\nLIMIT " . $this->limit ;
        }

        $this->statement = $q ;
    }


    ////////////////////////////////////////////////////////////

    public function run( bool $permission = TRUE ) {

        try {
            $this->execute( $permission ) ;
            $this->num_rows = get_num_rows( $this->result ) ;
            $this->result_arr = get_all( $this->result ) ;
            return TRUE ;
        }
        catch ( Throwable $e ) {
            throw $e ;
            return FALSE ;
        }
    }


    ////////////////////////////////////////////////////////////

    private function validate_construction( array $param_arr ) {

        // everything you pass as a parameter, should have a value.
        foreach ( $param_arr AS $param => $value ) {
            assert( property_exists( "SelectQuery" , $param ) , "Property '$param', Does not exist" ) ;
            assert( isset( $param_arr[$param] ) ) ;
        }

        extract( $param_arr ) ;

        assert( isset( $path ) ) ;
        assert( file_exists( $path ) ) ;
    }


    ////////////////////////////////////////////////////////////

    private function regexp_opt_apost( $input ) {

        if ( is_numeric( $input ) ) {
            return $input ;
        }

        // no work needs to be done if there isnt an apostrophe
        if ( !preg_match( "/'/" , $input ) ) {
            return $input ;
        }

        // main process
        $output = preg_replace( "/'/" , "'?" , $input ) ;
        return $output ;
    }

}


/*
Architecture Goal:

Queries should also be developed as reusable modules/components of any given project.

You will *always* need that query again!

We should have a folder Queries that are written at a basic level so that they can be easily extended and used in any given project.

This is very similar to our new concept and architecture strategy of using front end templates.

When you MUST write a new query in a project. Try to write the query with it's purpose in mind, NOT with the current project in mind! It should be able to be used BY a project.

This encourages and enables loose coupling and high cohesion, while also maximizing flexibility and organization.

The task is to create a Class that allows the implementation to use a pre-existing query, and to easily extend it based on the requirements of the current project.

This will include:
    Testing the query before execution.

    A SELECT Query Class should allow for extended
        Filtering
        Ordering
        Pagination
*/


?>
