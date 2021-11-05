<?php


abstract class FormElement {

    protected $attr_arr ;

    //////////////////////////////////////////////////

    public function __construct( array $param_arr ) {

        $this->validate_construction( $param_arr ) ;

        $this->dynamic_assignment( $param_arr ) ;
    }

    //////////////////////////////////////////////////

    abstract function validate_construction( array $param_arr ) ;

    //////////////////////////////////////////////////

    abstract public function render() ;

    //////////////////////////////////////////////////

    protected function dynamic_assignment( $param_arr ) {
        foreach ( $param_arr AS $prop => $value ) {
            assert( property_exists( self , $prop ) , "Property: $prop does not exist" ) ;
            $this->$prop = $value ;
        }
    }

    //////////////////////////////////////////////////

    protected function populate_attributes() {
        foreach ( $this->attr_arr AS $attr => $value ) {
            $attr_arr[$attr] = "$attr='$value'" ;
        }
        return implode( "\n\t" , $attr_arr ) ;
    }

}

?>
