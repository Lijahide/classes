<?php

/**
 *
 */
Class Cleaner {


    private $data ;

    ///////////////////////////////////////////////////////

    public function clean( $data ) {

        foreach ( $data AS $key => $value ) {

            if ( preg_match( "/phone/" , $key ) ) {
                $data[$key] = $this->clean_phone( $value ) ;
            }

            elseif ( preg_match( "/address/" , $key ) ) {
                $data[$key] = $this->clean_address( $value ) ;
            }

            elseif ( preg_match( "/date/" , $key ) ) {
                $data[$key] = $this->reformat_date( "Y-m-d" , $value ) ;
            }

            elseif ( preg_match( "/zip/" , $key ) ) {
                $data[$key] = $this->clean_integer( $value ) ;
            }

        }

        return $data ;
    }


    /////////////////////////////////////////////


    // reformat a date or array of dates to a specified format using the PHP date keys

    static function reformat_date( string $format , $input ) {

        if ( !is_array( $input ) )
        {
            if ( $input == "" )
            {
                return $input ;
            }
            return date( $format , strtotime( $input ) ) ;
        }
        else
        {
            foreach ( $input AS $key => $value ) :
                $output[$key] = self::reformat_date( $format , $value ) ;
            endforeach ;
            return $output ;
        }

        return FALSE ;
    }


    //////////////////////////////////////////////////////////////
    // strip whitespace and escape special chars from a name or an array of names

    static function clean_string( $input ) {

        if ( !is_array( $input ) ) {

            $output = trim( $input ) ;

            if ( strpos( '\'' , $output ) ) {

                $output = addslashes( $output ) ;
            }

            return $output ;
        }
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::clean_string( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }

    ////////////////////////////////////////
    // basic integer cleaning

    static function clean_integer( $input ) {

        if ( !is_array( $input ) ) {

            return preg_replace( "/[^\d]/" , "" , $input ) ;
        }
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::clean_integer( $value ) ;
            }
        }

        return FALSE ;
    }

    ////////////////////////////////////////
    // basic cleaning of an entire array | integers and strings

    static function clean_type( $input ) {

        if ( !is_array( $input ) ) {

            if ( is_numeric( $input ) ){

                $output = self::clean_integer( $input ) ;
            }
            else {

                $output = self::clean_string( $input ) ;
            }

            return $output ;
        }
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::clean_type( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }


    ////////////////////////////////////////////////////////////
    // expand abbreviations | Standardize Capitalization

    static function clean_address( $input ) {

            // base scenario ( single string )
        if ( !is_array( $input ) ) {

            // list of common address abbreviations
            // https://wiki.acstechnologies.com/display/ACSDOC/Common+Approved+Address+Abbreviations

            $address_abrv = array(
                "avenue"     => "ave"  , "center"    => "ctr" ,
                "boulevard"  => "blvd" , "circle"    => "cir" ,
                "court"      => "ct"   , "drive"     => "dr"  ,
                "expressway" => "expy" , "heights"   => "hts" ,
                "highway"    => "hwy"  , "island"    => "is"  ,
                "junction"   => "jct"  , "lake"      => "lk"  ,
                "lane"       => "ln"   , "mountain"  => "mtn" ,
                "parkway"    => "pkwy" , "place"     => "pl"  ,
                "plaza"      => "plz"  , "ridge"     => "rdg" ,
                "road"       => "rd"   , "square"    => "sq"  ,
                "street"     => "st"   , "station"   => "sta" ,
                "terrace"    => "ter"  , "trail"     => "trl" ,
                "turnpike"   => "tpke" , "valley"    => "vly" ,
                "way"        => "wy"   , "apartment" => "apt" ,
                "room"       => "rm"   , "suite"     => "ste" ,
                "north"      => "n"    , "east"      => "e"   ,
                "south"      => "s"    , "west"      => "w"   ,
                "northeast"  => "ne"   , "northwest" => "nw"  ,
                "southeast"  => "se"   , "southwest" => "sw"
            ) ;

            $input = preg_replace( "/[\.\,]/" , '' , $input ) ;
            $input = preg_replace( "/\-/" , ' ' , $input ) ;
            $input = trim( $input ) ;
            $input = strtolower( $input ) ;

                // replace abbreviations with their full words
            foreach ( $address_abrv AS $full_word => $abrv ) {

                $input = " $input " ;

                if ( preg_match( "/\s$abrv([\.;,'])?\s/i" , $input ) ) {

                    $input = preg_replace(
                        "/\s$abrv\s/" ,
                        " $full_word " ,
                        $input
                    ) ;
                }

                $input = trim( $input ) ;
            }

            $input = ucwords( $input ) ;
            $output = $input ;

            return $output ;
        }
            // recursion
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::clean_address( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }


    ////////////////////////////////////////////////////////////
    // standardize phone formatting

    static function clean_phone( $input ) {

        if ( !is_array( $input ) ) {

                // remove whitespace from both ends of the string
            $input = trim( $input ) ;

            if ( strlen( $input ) != 10 ) {

                    // remove all NON-DIGIT characters from the phone number
                $input = preg_replace( "/[^\d]/i" , "" ,  $input ) ;


                if ( strlen( $input ) != 10 ) {

                    if ( preg_match( '/1(\d{10})/' , $input ) ) {

                        $input = substr( $input , 1 ) ;
                    }
                }

            }

                // a valid number should have 10 characters
            if ( strlen( $input ) != 10 ) {

                $input = "" ;
            }
            else {

                    // group the sections of the number together
                $phone_parts[] = substr( $input , 0 , 3 ) ;
                $phone_parts[] = substr( $input , 3 , 3 ) ;
                $phone_parts[] = substr( $input , 6 ) ;

                    // reformat and combine the numbers into a correct number
                $output = implode( "-" , $phone_parts ) ;

                return $output ;
            }

        }
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::clean_phone( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
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

    ////////////////////////////////////////

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


    ////////////////////////////////////////////////////////////
    // split single-field names into two fields ( |$name| -> |$name1|$name2| )

    static function split_names( array $keys , string $input ) : array {

        if ( !is_array( $input ) ) {

                // multiple names found
            if ( preg_match( "/\s|\-/" , $name ) ) {

                    // split into array according to REGEX pattern
                $sub_names = preg_split( "/\s/" , $name ) ;

                    // first value is the first name
                $output[$i][] = array_shift( $sub_names ) ;

                    // last value is the last name
                $sub_names = array_reverse( $sub_names ) ;
                $output[$i][] = array_shift( $sub_names ) ;

                    // unset initials
                if ( count( $sub_names ) != 0 ) {

                    foreach ( $sub_names AS $key => $value ) {

                        if ( strlen( $value ) == 1 ) {

                            unset( $sub_names[$key] ) ;
                        }
                    }
                }

                    // anything left is extra
                $output[$i][] = $sub_names ;
            }
            else {

                return $input ;
            }


        }



        foreach ( $input AS $key => $name ) {

            $output[$i] = array_combine( $keys , $output[$i] ) ;
        }

        return $output ;
    }

    ////////////////////////////////////////

    static function filter_names( $input ) {

        if ( !is_array( $input ) ) {

            if ( str_word_count( $input ) > 1 ) {

                $output = str_word_count( $input , 1 ) ;
            }
            else {

                $output = $input ;
            }

            return $output ;
        }

    }

    ////////////////////////////////////////
    // remove generational markers ( i.e Jr, Sr, II ,IV )

    static function remove_generations( $input ) {

            // string
        if ( !is_array( $input ) ) {

            $input = " $input " ;

            if ( preg_match( "/(\s[js]r\s)|(\sI{2, }\s)/i" , $input ) ) {

                $output =
                preg_replace( "/(\s[js]r\s)|(\sI{2, }\s)/i" , "" , $input ) ;

                return trim( $output ) ;
            }
            else {

                return trim( $input ) ;
            }
        }

        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::remove_generations( $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }


    /////////////////////////////////////////////


    static function remove_initials( $input ) {

        if ( !is_array( $input ) ) {

            $input = " $input " ;

            if ( preg_match( "/\s[a-z]\.?\s/i" , $input ) ) {

                $output =
                preg_replace( "/\s[a-z]\.?\s/i" , "" , $input ) ;

                return trim( $output ) ;
            }
            else {

                return trim( $input ) ; ;
            }
        }

        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::remove_initials( $value ) ;
            }

            return $output ;
        }

        return FALSE ;

    }


    ////////////////////////////////////////


    static function convert_romans( $str ) {

        $roman_arr = array(
          "I" => 1 ,
          "II" => 2 ,
          "III" => 3 ,
          "IV" => 4 ,
          "V" => 5 ,
          "VI" => 6
        ) ;

        foreach ( $roman_arr AS $roman => $int ) {

            if ( preg_match( "/\s$roman$/" , $str ) ) {

                return preg_replace( "/\s$roman$/" , $int , $str ) ;
            }
        }
    }


    ////////////////////////////////////////////////////////////

    static function convert_seconds( $format , $input ) {

        if ( !is_array( $input ) ) {

            return gmdate( $format , $input ) ;
        }
        else {

            foreach ( $input AS $key => $value ) {

                $output[$key] = self::convert_seconds( $format , $value ) ;
            }

            return $output ;
        }

        return FALSE ;
    }


    //////////////////////////////////////////////////

    private function is_date( $date , $format = 'Y-m-d H:i:s' ) {

        $d = DateTime::createFromFormat( $format , $date ) ;
        return $d && $d->format( $format ) == $date ;
    }


    //////////////////////////////////////////////////

    private function is_phone_num( $number ) {

        $clean_num = preg_replace( "/\D/" , '' , $number ) ;

        if ( strlen( $clean_num ) == 11 ) {
            $clean_num = preg_replace( "/^1/" , '' , $clean_num ) ;
        }

        if ( strlen( $clean_num ) == 10 ) {
            $return = TRUE ;
        }
        else {
            $return = FALSE ;
        }

        return $return ;
    }


}

 ?>
