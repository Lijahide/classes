<?php

class UpdateQuery extends Query implements LoadQuery {

  protected $table ;
  protected $index ;
  protected $where_clause ;
  protected $data_arr ;
  protected $constant_fields ;

  protected $n_affected_rows ;
  protected $new_data ;


  /////////////////////////////////////////////

  public function __construct( array $param_arr ) {

      $this->dynamic_assignment( $param_arr ) ;

      /////////////////////

      $q = $this->write_query() ;

      $this->query_str = htmlspecialchars( $q ) ;
  }


  /////////////////////////////////////////////

  public function validate_result() {

      $this->n_affected_rows = $this->get_affected_records() ;

      $this->get_affected_records( $this->index ) ;

      if ( !isset( $this->new_data ) ) {
          throw new Exception(
            "Something Went Wrong While Retrieving the New Data"
          ) ;
      }

      $differences =
      array_diff( $this->data_arr , $this->new_data[0] ) ;

      if ( count( $differences ) > 0 ) {

          $msg = "It Appears the data did not get properly Loaded" ;
          $this->logger->add_entry( 'warning' , $msg , $this->data_arr ) ;
          throw new Exception( $msg ) ;
      }

      elseif ( count( $differences ) == 0 ) {

          $msg = "Data was Successfully Loaded" ;
          $this->logger->add_entry( 'info' , $msg , $this->new_data ) ;
          return TRUE ;
      }

  }

  ///////////////////////////////////////////////////////
  // internal methods

  public function write_query() {

      foreach ( $this->data_arr AS $key => $value ) {
          $set_clause[] = "$key = '$value'" ;
      }
      $set_clause = implode( ",\n\t" , $set_clause ) ;

      $q =
      " UPDATE
            $this->table
        SET
            $set_clause
        WHERE
            $this->where_clause
      " ;

      return $q ;
  }


  /////////////////////////////////////////////

  public function run( bool $permission = TRUE ) {

      $this->validate_conditions() ;

      $this->execute( $permission ) ;
  }


  /////////////////////////////////////////////

  private function get_affected_records() {

      $fields = array_keys( $this->data_arr ) ;
      $fields = implode( ",\n" , $fields ) ;

      $q =
      " SELECT
            $fields
        FROM
            $this->table
        WHERE
            $this->where_clause
      " ;

      try {
          $r = get_query( $q , TRUE ) ;
          $this->n_affected_rows = get_num_rows( $r ) ;
          $this->updated_data = get_all( $r ) ;

          return TRUE ;
      }
      catch ( Throwable $e ) {
          $this->logger->add_entry( "error" , "Something went wrong during your Update. We could not Validate it's success" , "<pre>$q</pre>" ) ;

          throw $e ;
      }

  }

  /////////////////////////////////////////////

    private function validate_conditions() {

        assert( isset( $this->table ) ) ;
        assert( isset( $this->data_arr ) ) ;
        assert( count( $this->data_arr ) > 0 ) ;


        if ( !is_array( $this->data_arr ) ) {
          throw new Exception( "Supplied Data Must be an Array" ) ;
        }

        $this->data_arr = array_filter( $this->data_arr ) ;
        if ( count( $this->data_arr ) == 0 ) {
          throw new Exception( "The Supplied Array is Empty" ) ;
        }

        if ( !isset( $this->where_clause ) ) {
          throw new Exception( "It would be unwise to UPDATE without a WHERE clause" ) ;
        }

    }




}

?>
