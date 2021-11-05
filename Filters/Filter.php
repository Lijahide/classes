<?php



class Filter
{

    private $function_map ;
    private $data ;

    ///////////////////////////////////////////////////////

    public function __construct( array $param_arr )
    {
        foreach ( $param_arr AS $prop => $value ) :
            assert( isset( $value ) ) ;
            $this->$prop = $value ;
        endforeach ;
    }


    ///////////////////////////////////////////////////////

    public function filter( $data ) {

        foreach ( $this->function_map AS $func => $param_arr ) :
            $param_arr['data'] = $data ;
            $data = $this->$func( $param_arr ) ;
        endforeach ;

        return $data ;
    }


    ///////////////////////////////////////////////////////

    public function register_data_filter( callable $func )
    {

    }

    ///////////////////////////////////////////////////////

    /**
     * Given a specific key, update the $data property to have unique values of that specific column. Meaning for that key, there will be no duplicated data.
     *
     * @param string $col: The string of the array key you would like to have unique values.
    */
    public function unique_column( string $col ) {

    }

    ///////////////////////////////////////////////////////

    public function filter_columns( array $arg_arr )
    {
        extract( $arg_arr ) ;
        assert( isset( $field_map ) ) ;
        assert( isset( $data ) ) ;

        if ( !is_array( $data[0] ) )
        {
            foreach ( $field_map AS $local_name => $foreign_name ) :
                $arr[$local_name] = $data[$foreign_name] ;
            endforeach ;
            return $arr ;
        }
        else {
            foreach ( $data AS $i => $row ) :
                $data[$i] =
                $this->filter_columns( [
                    "data" => $row , "field_map" => $field_map
                ] ) ;
            endforeach ;
            return $data ;
        }

        return FALSE ;
    }

    ///////////////////////////////////////////////////////

    public function filter_unqualified_rows( array $arg_arr )
    {
        extract( $arg_arr ) ;
        assert( isset( $req_columns ) ) ;
        assert( isset( $data ) ) ;

        if ( !is_array( $data[0] ) )
        {
            $data = array_filter( $data ) ;
            foreach ( $req_columns AS $i => $col ) :
                if ( $data[$col] == "" ) {
                    unset( $data[$i] ) ;
                }
            endforeach ;
            return $data ;
        }
        else
        {
            foreach ( $req_columns AS $c => $col ) :
                foreach ( $data AS $i => $row ) :
                    if ( $data[$i][$col] == "" ) {
                        unset( $data[$i] ) ;
                    }
                endforeach ;
            endforeach ;
            return $data ;
        }

        return FALSE ;
    }

}

?>
