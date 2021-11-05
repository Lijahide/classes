<?php

class Report {

    public  $title ;
    private $category ;
    private $interface ;

    private $headers ; #REVIEW

    private $pagination ;
    private $orientation = "portrait" ;
    private $style ;

    private $data_source ;

    private $required_fields = array(
        "title" , "category" , "data_source"
    ) ;

    ////////////////////////////////////////////////////////////

    public function __construct(
      $param_arr ,
      $style_arr = NULL
    ) {

        assert( is_array( $param_arr ) ) ;



        $missing_fields =
        array_diff_key( $this->required_fields , $param_arr ) ;
        $c_missing_fields = count( $missing_fields ) ;

        if ( $c_missing_fields > 0 ) {

            $missing_fields = implode( ", " , $missing_fields ) ;

            throw new Exception( "Missing $c_missing_fields Required Parameters: <br> $missing_fields" ) ;
        }

        foreach ( $param_arr AS $key => $value ) {

            $this->$key = $value ;
        }
    }

    ////////////////////////////////////////

    public function render() {

        switch ( $this->category ) {
            case 'list':
                return $this->build_list() ;
            break ;

            case 'table':
                return $this->build_table() ;
            break;

            default:
                throw new Exception( "\"$this->category\" Category is not a supported Report Format" ) ;
                return FALSE ;
            break ;
        }

    }

    ////////////////////////////////////////

    private function build_list() {



    }



}



 ?>
