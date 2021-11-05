<?php


require_once "classes/View/Forms/Elements/FormElement.php" ;

class InputElem extends FormElement {



    const ACCEPTED_TYPES = array(
        "button" ,
        "checkbox" , "color" ,
        "date" , "datetime-local" ,
        "email" ,
        "file" ,
        "hidden" ,
        "image" ,
        "month" ,
        "number" ,
        "password" ,
        "radio" , "range" , "reset" ,
        "search" , "submit" ,
        "tel" , "text" , "time" ,
        "url" ,
        "week"
    ) ;

    //////////////////////////////////////////////////

    public function __construct( array $attr_arr ) {

        $this->validate_construction( $attr_arr ) ;
        $this->attr_arr = $attr_arr ;
    }

    //////////////////////////////////////////////////

    public function validate_construction( array $attr_arr ) {
        foreach ( $attr_arr AS $attr => $value ) {
            assert( $value != "" , "All attributes must have a value" ) ;
        }
        assert( isset( $attr_arr['name'] ) , "Attribute 'name' MUST be set" ) ;
        assert( isset( $attr_arr['type'] ) , "Attribute 'type' MUST be set" ) ;
        assert( in_array( $attr_arr['type'] , self::ACCEPTED_TYPES ) , "Invalid 'type' attribute, '$attr_arr[type]' given" ) ;
    }

    //////////////////////////////////////////////////

    public function render() {

        $attr_str = $this->populate_attributes() ;
        $output = "<input $attr_str>" ;
        return $output ;
    }

    //////////////////////////////////////////////////




}




?>
