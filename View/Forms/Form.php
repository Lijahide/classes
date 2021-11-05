<?php


class Form {

    private $attr_arr ; // array of html attributes
    private $elem_arr ; // array of objects

    //////////////////////////////////////////////////

    public function __construct( array $attributes , array $elements  ) {

        $this->attr_arr = $attributes ;
        $this->elem_arr = $elements ;
    }

    /////////////////////////////////////////////

    public function render() {

        $output =
        "<form
            $this->attr_str
         >
        " ;


        foreach ( $this->hidden_input_arr AS $i => $value ) {
            $output .= $value ;
        }

        foreach ( $this->input_arr AS $i => $element ) {
            $output .= $element ;
        }

        $output .= "</form>" ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////





    ////////////////////////////////////////////////////////////////


    public function hidden_input( $arr ) {

        foreach ( $arr AS $attr => $value ) {
            $attr_arr[] = "$attr='$value'" ;
        }

        $attr_str = implode( "\n\t" , $attr_arr ) ;

        $output =
        " <input
              $attr_str
          >
        " ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////


    public function input_label( $for , $label ) {

        $output = "<label for='$for'>$label</label>" ;
        $output .= "<br>" ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////


    public function form_select( $name , $default , $arr , ...$extras ) {

        $output =
        " <select
            class='form-field'
            style='width:315px;'
            name='$name'
        " ;

        if ( !isset( $extras ) ) {

            $output .= "\n\t>" ;
        }

        else {

            if ( isset( $extras ) ) {

                foreach ( $extras AS $i => $extra ) {

                    $output .= "\n\t$extra" ;
                }
            }

            $output .= "\n\t>\n" ;
        }

        $output .= "<option value=''>$default</option>\n" ;

        foreach ( $arr AS $value => $inner_html ) {

            $output .=
            " <option value='$value'>
                $inner_html
              </option>\n
            " ;
        }

        $output .= "</select>" ;
        $output .= "<br><br>" ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////


    public function form_datalist( $id , $arr ) {

        $output = "<datalist id='$id'>" ;

        foreach ( $arr AS $value => $inner_html ) {

            $output .=
            " <option>
                $inner_html
              </option>\n
            " ;
        }

        $output .= "</datalist>" ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////


    public function submit_button( $name , $value , ...$extras ) {

        $output =
        " <div class='form_submit'>
            <input
              class='button'
              type='submit'
              name='$name'
              value='$value'
        " ;


        if ( isset( $extras ) ) {

            foreach ( $extras AS $i => $extra ) {

                $output .= "\n\t$extra" ;
            }
        }

        $output .= "\n>" ;

        $output .= " </div>" ;

        return $output ;
    }


    ////////////////////////////////////////////////////////////////


    public function add_div( $content , $class , $style = NULL ) {

        $output =
        " <div class='$class' $style>
            $content
          </div>
        " ;

        return $output ;
    }


}

 ?>
