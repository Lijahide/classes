<?php



abstract class Merger {

    private $entity_arr ;
    private $table ;
    private $table_list ;
    private $keep ;
    private $throw ;

    private $internal_status ;
    private $internal_message ;

    ////////////////////////////////////////////////////////////

    public function __construct( $param_arr ) {

        // dynamic assignment
        foreach ( $param_arr AS $prop => $value ) {
            $this->$prop = $value ;
        }
    }


    ///////////////////////////////////

    public function verify_matches() {

    }

    ///////////////////////////////////

    public function compare( $fields_arr , $arr1 ,  $arr2 ) {

    }


    ///////////////////////////////////

    public function multi_merge() {


    }


    ///////////////////////////////////

    public function merge() {

        $q =
        " UPDATE
              $this->main_table
          SET
              $this->main_merge_str
          WHERE
              $this->key_field = '$this->keep[id]'
        " ;

    }


    ///////////////////////////////////

    protected function r_compare() {

    }



}




////////////////////////////////////////////////////////
// Duplicates
////////////////////////////////////////////////////////

if ( $section == "duplicates" ) {

    //  Verify Duplicates

    if ( $n_verify == 2 ) {

            // we need at least 2 different fields to be similar to identify a true match / duplicate
        include "transform/verify_merge.php" ;


        if ( $verify_merge == TRUE ) {

            $dup_section = "merge" ;
        }
            // accounts could not be verified as duplicates
        else {

              // compare whims data to the duplicates
            include "transform/compare_whims.php" ;

            if ( $whims_match == TRUE ) {

                $dup_section = "update" ;
            }
            else {

                $dup_section = "insert" ;
            }

        }

        if ( $_POST[override_merge] == "Merge" ) {

            $dup_section = "merge" ;
        }



        if ( $dup_section == "merge" ) {

                // condense and combine the two records
            include "transform/merge_dups.php" ;

                // merge buyers table records
            include "load/merge_dups.php" ;

                // merge buyer records of other connecting tables
            require "load/multi_merge_dups.php" ;

                // mark secondary account as duplicate, update to "inactive"
            include "load/throw_dups.php" ;
        }
        else {


            if ( $_POST[override_merge] == "" ) {

                    // manually ovveride the process, merge the rows
                include "form_override_merge.php" ;
            }



            if ( $dup_section == "insert" ) {

                // insert a new record, give this record the whims_id
                include "load/insert_buyer.php" ;
            }


            if ( $dup_section == "update" ) {

              include "create_update.php" ;
              include "load/update_buyer.php" ;
            }

        }

    }


    //////////////////////////////////////////
    // Mark Duplicates
    //////////////////////////////////////////

    elseif ( $n_verify > 2 ) {

            // mark buyer records as duplicates to be handled outside this project
        require "load/mark_dups.php" ;
    }

}


 ?>
