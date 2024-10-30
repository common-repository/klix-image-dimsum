<?php

namespace tv\klix\optimize\debug;

//$info;

//Print to screen in html table <table><tr><td> format
function info($str, $level = 1) {
   global $isDebug;
   global $debugLevel;
   
   if ($isDebug && $level <= $debugLevel) {
      log('Level: '.$level . ' Message:' . $str);	//call normal logx

      global $info;      
      $info .= '<tr><td>' . $level . '</td><td>' . $str . '</td></tr>';
   }
}

//Goes to regular log
function log( $message ) {
 if( WP_DEBUG === true ){
   if( is_array( $message ) || is_object( $message ) ){
      error_log( 'Klix DimSum: ' . var_export( $message, true ) );
   } else {
      error_log( 'Klix DimSum: ' . $message );
   }
 }
}


?>