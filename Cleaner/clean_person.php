<?php







/**
 *
 */
trait PersonCleaner {


    ////////////////////////////////////////////////////////////


    function clean_phone( $value ) {

            // remove whitespace from both ends of the string
        $value = trim( $value ) ;

        if ( strlen( $value ) != 10 ) {

                // remove all NON-DIGIT characters from the phone number
            $value = preg_replace( "/[^\d]/i" , "" ,  $value ) ;


            if ( strlen( $value ) != 10 ) {

                if ( preg_match( '/1(\d{10})/' , $value ) ) {

                    $value = substr( $value , 1 ) ;
                }
            }

        }


            // a valid number should have 10 characters
        if ( strlen( $value ) != 10 ) {

            $value = "" ;
        }
        else {

                // group the sections of the number together
            $phone_parts[] = substr( $value , 0 , 3 ) ;
            $phone_parts[] = substr( $value , 3 , 3 ) ;
            $phone_parts[] = substr( $value , 6 ) ;

                // reformat and combine the numbers into a correct number
            $value = implode( "-" , $phone_parts ) ;
        }

        return $value ;
    }



    ////////////////////////////////////////////////////////////

      // a name field that contains the names of two people
    static function split_people( $name ) {

          // cobuyer found
      if ( preg_match( "/[\&\/]| and /" , $name ) ) {

              // use REGEX pattern to split into array
          $name_arr = preg_split( "/[\&\/]| and /" , $name ) ;

          $c_name = count( $name_arr ) ;

              // there shouldnt ever be 3 buyer names...
          if ( $c_name > 2 ) {

              throw new Error( "Unexpected name scenario" ) ;
          }

              // expected result sort them into their places
          elseif ( $c_name == 2 ) {

              return $name_arr ;
          }

      }
      else {

          return $name ;
      }

    }



    /////////////////////////////////////////////



    static function match_names( $first , $last ) {

      foreach ( $first AS $i => $value ) {

          $people[$i][first_name] = $value ;
      }

      if ( !is_array( $last ) ) {

          for ( $i=0; $i < 2 ; $i++ ) {

              $people[$i][last_name] = $last ;
          }
      }
      else {

          foreach ( $last AS $i => $value ) {

              $people[$i][last_name] = $value ;
          }
      }

      return $people ;
    }



    /////////////////////////////////////////////



    function filter_extra_names() {

      $name_fields = array( "first_name" , "last_name" ) ;


      foreach ( $name_fields AS $i => $field ) {

          $name = $this->$field ;

          $name = trim( $name ) ;

              // multiple names found
          if ( preg_match( "/\s|\-|[jJsS][rR]|\sI{2, }/" , $name ) ) {

                  // check for generation marker or suffix
              if ( preg_match( "/[jJsS][rR]|\sI{2, }/" , $name ) ) {

                      // I dont care about name Generation or Suffix
                  continue ;
              }

                  // split into array according to REGEX pattern
              $sub_names = preg_split( "/\s/" , $name ) ;

              foreach ( $sub_names AS $i_name => $sub_name ) {

                      // keep first name as main, skip iteration
                  if ( $i_name == 0 ) {

                      $this->$field = $sub_name ;

                      continue ;
                  }

                  $length = strlen( $sub_name ) ;

                      // I dont care about Initials
                  if ( $length == 1 ) {

                      continue ;
                  }

                    // add secondary name to list
                  $this->extra_names[] = $sub_name ;
              }

          }
          else {

              continue ;
          }

      }

    }



    /////////////////////////////////////////////



}









 ?>
