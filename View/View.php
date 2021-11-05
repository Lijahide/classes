<?php




class View {

    private $content ;
    private $stylesheets ;
    private $template ;

    ////////////////////////////////////////////////////////////

    public function __construct( $param_arr ) {

        // assertions

        assert( is_array( $param_arr ) ) ;

        if ( isset( $param_arr['template'] ) ) {
            assert( is_string( $param_arr['template'] ) ) ;
        }

        if ( isset( $param_arr['content'] ) ) {
            assert( is_array( $param_arr['content'] ) ) ;
        }

        if ( isset( $param_arr['style_sheet'] ) ) {
            $style_type = gettype( $param_arr['style_sheet'] ) ;
            assert( $style_type == "string" || $style_type == "array" ) ;
        }

        // dynamic assignment
        foreach ( $param_arr AS $prop => $value ) {
            $this->$prop = $value ;
        }

        $this->template = "templates/{$this->template}" ;
    }


    ///////////////////////////////////

    public function __set( $property , $value ) {
        assert( is_string( $property ) ) ;
        $this->$property = $value ;
    }


    ///////////////////////////////////

    public function render() {

        assert( file_exists( $this->template ) ) ;

        $this->include_css() ;

        extract( $this->content ) ;

        ob_start() ;

        try {
            require( $this->template ) ;
        }
        catch ( Throwable $e ) {
            throw $e ;
        }

        $content = ob_get_contents() ;
        ob_end_clean() ;

        return $content ;
    }


    ///////////////////////////////////

    private function include_css() {

        if ( !isset( $this->stylesheets ) ) {
            return ;
        }

        foreach ( $this->stylesheets AS $i => $path ) {
            assert( file_exists( $path ) ) ;
            require "$path.css" ;
        }

        return ;
    }


}


?>
