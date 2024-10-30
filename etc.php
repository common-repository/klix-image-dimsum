<?php

namespace tv\klix\optimize\image;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ('debug.php');
use tv\klix\optimize\debug as debug;

function isMatching($haystack, $grep)
{
   debug\info("PCRE GREP matching" . var_export($grep, TRUE) );
   
   if ( !is_array($grep) || (is_array($grep) && $grep[0]==''))//empty array or string means positive match
      return TRUE;
      
   $retValue = 0;
   foreach ($grep as $oneNeedle) 
   {
      $test = preg_match( $oneNeedle, $haystack, $garbage);
      debug\info("Testing preg_match: $oneNeedle , <textarea>$haystack</textarea> = $test");
      $retValue = $retValue | $test; //bitwise OR pattern
   }
   debug\info("Returning value $retValue (0=no match, 1=match)");
   return $retValue;
}


function extension_to_image_type($ext) 
{
   $ext = strtolower($ext);
   debug\info("extension_to_image_type $ext", 5);
   
   $types = array ( 'gif'=>1, 'jpg'=>2, 'jpeg'=>2, 'png'=>3, 'swf'=>4, 'psd'=>5, 'bmp'=>6,
	 'tiff'=>7, 'tiff'=>8, 'jpc'=>9, 'jp2'=>10, 'jpf'=>11, 'jb2'=>12, 'swc'=>13,
	 'aiff'=>14, 'wbmp'=>15, 'xbm'=>16);
      
   return $types[$ext];
}


function image_type_to_extension_withdefault($type, $dot = true, $default='jpg')
{
   $ret = image_type_to_extension($type, $dot);  
   return $ret != null ? $ret : ($dot ? '.' : '') . $default;//put dot here too.
}

if ( !function_exists('image_type_to_extension') ) 
{
    function image_type_to_extension ($type, $dot = true)
    {
        $e = array ( 1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp',
            'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
            'aiff', 'wbmp', 'xbm');

        // We are expecting an integer.
        $type = (int)$type;
        if (!$type) {
            trigger_error( '...come up with an error here...', E_USER_NOTICE );
            return null;
        }

        if ( !isset($e[$type]) ) {
            trigger_error( '...come up with an error here...', E_USER_NOTICE );
            return null;
        }

        return ($dot ? '.' : '') . $e[$type];
    }
    
}

?>
