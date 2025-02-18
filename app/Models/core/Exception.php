<?php

namespace App\Models\Core;

use Exception;

class DBError extends Exception {
   function __construct($e) {
      global $db, $skip_db_error;

      if ( isset($skip_db_error_exception) AND $skip_db_error_exception === TRUE ) { //Used by system_check script.
         return TRUE;
      }

      $db->FailTrans();

      //print_r($e);
      //adodb_pr($e);

      //Log database error
      if ( isset($e->message) ) {
         Debug::Text($e->message, __FILE__, __LINE__, __METHOD__,10);
      }

      if ( isset($e->trace) ) {
         $e = strip_tags( adodb_backtrace($e->trace) );
         Debug::Arr( $e, 'Exception...', __FILE__, __LINE__, __METHOD__,10);
      }

      Debug::Arr( Debug::backTrace(), ' BackTrace: ', __FILE__, __LINE__, __METHOD__,10);

      //Dump debug buffer.
      Debug::Display();
      Debug::writeToLog();
      Debug::emailLog();

      Redirect::Page( URLBuilder::getURL( array('exception' => 'DBError'), Environment::getBaseURL().'DownForMaintenance.php') );

      ob_flush();
      ob_clean();

      exit;
   }
}

class GeneralError extends Exception {
   function __construct($message) {
      global $db;

      //debug_print_backtrace();
      $db->FailTrans();

      echo "======================================================================<br>\n";
      echo "EXCEPTION!<br>\n";
      echo "======================================================================<br>\n";
      echo "<b>Error message: </b>".$message ."<br>\n";
      echo "<b>Error code: </b>".$this->getCode()."<br>\n";
      echo "<b>Script Name: </b>".$this->getFile()."<br>\n";
      echo "<b>Line Number: </b>".$this->getLine()."<br>\n";
      echo "======================================================================<br>\n";
      echo "EXCEPTION!<br>\n";
      echo "======================================================================<br>\n";

      Debug::Arr( Debug::backTrace(), ' BackTrace: ', __FILE__, __LINE__, __METHOD__,10);

      //Dump debug buffer.
      Debug::Display();
      Debug::writeToLog();
      Debug::emailLog();
      ob_flush();
      ob_clean();

      Redirect::Page( URLBuilder::getURL( array('exception' => 'GeneralError'), Environment::getBaseURL().'DownForMaintenance.php') );

      exit;
   }
}
?>
