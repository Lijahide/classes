<?php

require_once "classes/View/Forms/Elements/FormElement.php" ;

class SelectElem extends FormElement {

    protected $attr_arr ;

    protected $label ;
    protected $default_option ;

    protected $stylesheets ;

    protected $query ;
    protected $column_map ;


    /////////////////////////////////////////////

    public function __construct( array $param_arr ) {

        $this->validate_construction( $param_arr ) ;

        $this->dynamic_assignment( $param_arr ) ;

        $this->query->write_query() ;
        $this->query->run() ;

        $this->options_data_set =
        $this->query->__get( "result_arr" ) ;

        $this->map_columns() ;
    }

    /////////////////////////////////////////////

    public function render() {

        $attr_str = $this->populate_attributes() ;
        $output = "<select $attr_str>" ;

        $output .=
        "<option value=''>$this->default_option</option>\n" ;

        $output .= $this->populate_options() ;

        $output .= "</select>" ;

        return $output ;
    }

    ////////////////////////////////////////////////////////////

    public function validate_construction( array $param_arr ) {

        extract( $param_arr ) ;

        assert( isset( $query ) ) ;
        assert( is_a( $query , "SelectQuery" ) ) ;
        assert( is_array( $column_map ) ) ;
        assert( is_string( $column_map['value'] ) ) ;
        assert( is_string( $column_map['inner_html'] ) ) ;

        assert( is_string( $default_option ) ) ;

        assert( is_array( $attr_arr ) ) ;
        assert( isset( $attr_arr['name'] ) );

        if ( isset( $style_sheets ) ) {
            assert( is_array( $stylesheets ) ) ;
            foreach ( $stylesheets AS $i => $path ) {
                assert( file_exists( $path ) ) ;
            }
        }
    }

    ////////////////////////////////////////////////////////////

    private function map_columns() {

        $this->options_data_set = array_column(
            $this->options_data_set ,
            $this->column_map['inner_html'] ,
            $this->column_map['value']
        ) ;
    }

    /////////////////////////////////////////////

    private function populate_options() {
        foreach (
            $this->options_data_set AS $value => $inner_html
        ) {
            $options_arr .=
            " <option value='$value'>
                $inner_html
              </option>\n
            " ;
        }
        return $options_arr ;
    }

    /////////////////////////////////////////////

    protected function dynamic_assignment( $param_arr ) {
        foreach ( $param_arr AS $prop => $value ) {
            // assert( property_exists( self , $prop ) , "Property: '$prop' does not exist" ) ;
            $this->$prop = $value ;
        }
    }

}

?>
