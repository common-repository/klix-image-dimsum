<?php
 
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/
 
namespace tv\klix\optimize\image;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'etc.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'debug.php';

use tv\klix\optimize\debug as debug;//a mere convenience?!

/**
* Get which version of GD is installed, if any.
*
* Returns the version (1 or 2) of the GD extension.
*/
function gdVersion($user_ver = 0)
{
    if (! extension_loaded('gd')) { return; }
    static $gd_ver = 0;
    // Just accept the specified setting if it's 1.
    if ($user_ver == 1) { $gd_ver = 1; return 1; }
    // Use the static variable if function was called previously.
    if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
    // Use the gd_info() function if possible.
    if (function_exists('gd_info')) {
        $ver_info = gd_info();
        preg_match('/\d/', $ver_info['GD Version'], $match);
        $gd_ver = $match[0];
        return $match[0];
    }
    // If phpinfo() is disabled use a specified / fail-safe choice...
    if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        if ($user_ver == 2) {
            $gd_ver = 2;
            return 2;
        } else {
            $gd_ver = 1;
            return 1;
        }
    }
    // ...otherwise use phpinfo().
    ob_start();
    phpinfo(8);
    $info = ob_get_contents();
    ob_end_clean();
    $info = stristr($info, 'gd version');
    preg_match('/\d/', $info, $match);
    $gd_ver = $match[0];
    return $match[0];
} // End gdVersion()


class SimpleImage {
 
   var $image;
   var $image_type;
   var $image_width;
   var $image_height;
   
   var $target;
   var $path;
   var $filename;
   var $extension;
   
   /*   
   function __construct($path, $file, $ext, $target) {
      $this->path = $path;
      $this->filename = $file;
      $this->extension = $ext;
      $this->target = $target;//0 = original, 1=jpeg, 2=png, 3=gif
   }

   function load() {
	 return $this->load($this->path . DIRECTORY_SEPARATOR . $this->filename . DIRECTORY_SEPARATOR . $this->extension);
   }
*/ 
   
   function loadWithFilename($filename) {
      debug\info("SimpleImage::loadWithFilename: $filename", 4);
      $error = FALSE;
       
      $image_info = getimagesize($filename);//Not only size but also image type(jpeg/gif/etc
      
      if ($image_info) {//Success or fail?
       
	 $this->image_width = $image_info[0];
	 $this->image_height = $image_info[1];//unreliable may equal 0
	 $this->image_type = $image_info[2];//unreliable may equal 0
	 
	     if( $this->image_type == IMAGETYPE_JPEG ) {
		  $this->image = imagecreatefromjpeg($filename);
	       } elseif( $this->image_type == IMAGETYPE_GIF ) {
	  
		  $this->image = imagecreatefromgif($filename);
	       } elseif( $this->image_type == IMAGETYPE_PNG ) {
	  
		  $this->image = imagecreatefrompng($filename);
	       }            
	       
	 if ($this->image == FALSE) {
		 debug\info("Error. SimpleImage:load with file $filename error:$error");
		 return FALSE;
	 }
	 else {
	    debug\info("Success. SimpleImage:load with file $filename", 3);
		 return TRUE;
	 }
	    
      }
      else {
	 debug\info("Error. SimpleImage:load with file $filename error:$error", 1);
	 return FALSE; 
      }
   
   }
   
   function save($compression=75, $permissions=null) {
      debug\info("SimpleImage::save compress $compression; permiss:$permission;", 4);
      $newExt = image_type_to_extension_withdefault($this->target, true, $this->ext);//target is 

      return $this->saveAs($this->path . $this->filename . $this->$newExt, $type, $compression, $permissions);   
   
   }
   
   function saveWithPathFilenameExt($path, $file, $ext, $target, $compression=75, $permissions=null)
   {
      debug\info("SimpleImage:saveWithFilename image path:$path; file:$file ext:$ext $target type: $image_type; compression:$compression; permission:$permissions", 4);
      $newExt = image_type_to_extension_withdefault($target, TRUE, $ext);//target is 
      return $this->saveAs($this->path . $this->filename . $this->$newExt, $target, $compression, $permissions);   
   }
   
   
   
   function saveWithFilename($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null)
   {
      debug\info("SimpleImage:saveAs image file: $filename; type: $image_type; compression:$compression; permission:$permissions", 4);
            
      $error = FALSE;
      
      if ($image_type == 0) {
	 $t = pathinfo($filename, PATHINFO_EXTENSION);
	 $image_type = extension_to_image_type($t);
	 debug\info("Zero type. Should now match extension: $image_type", 4);
      }
	       
      //      if ($image_type == 0) {
	 //$image_type = extension_to_image_type(pathinfo($filename, PATHINFO_EXTENSION));
      //}
      
      if( $image_type == IMAGETYPE_JPEG ) {
         $error = imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         $error = imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         $error = imagepng($this->image,$filename);
      }
      
      if ($error == TRUE && $permissions != null) {    
	    $error = chmod($filename,$permissions);
      }
      
      //      if ($error == FALSE)
      //      {
      //      	 debug\info("ERROR. SimpleImage:save with file $SimpleImage:save image file: $filename; type: $image_type; compression:$compression; permission:$permissions");
      //}
      return $error; 
   }
   
   function output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image);
      }
   }
   
   function getWidth() {
 
      return imagesx($this->image);
   }
   
   function getHeight() {
 
      return imagesy($this->image);
   }
   
   function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
 
   function resizeWithMargin($width, $height, $margin=0.0) {
      debug\info("Resize with margin $margin", 4);
      if ($margin <= 0.0 || $margin >= .99 || $this->width <= 0 || $this->height <= 0) 
	 return $this->resize($width, $height);
	 
      if (($width < $this->width * (1.-$margin)) || ($height < $this->$height * (1.-$margin)  ))
	 return $this->resize($width, $height);
   }
   
   function resize($width,$height) {
      debug\info("SimpleImage:resize attempting to resize: $width x $height", 4);
      
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      
      $this->image = $new_image;
      
      if ($this->image != FALSE) {
	 debug\info('Success. SimpleImage:resize. old size (could be blank):' . $this->width . ' x ' .  $this->height . " new:$width x $height", 4);	
	 return TRUE;
      }
      else {
	 debug\info("ERROR. SimpleImage:resize. file $width x $height");	
	 return FALSE;
      }
   }
   
   function resizeWithAlpha($w,$h) {
      debug\info("SimpleImage:resize attempting to resize: $w x $h");
            
      $img = $this->image;
      
      $original_transparency = -1;
      
      if (!imageistruecolor($img)) {	 
	   debug\log("image is not true color");	 
	$original_transparency = imagecolortransparent($img);
	   debug\log("transparency is >=0 $original_transparency", 4);
	//we have a transparent color
	if ($original_transparency >= 0) {
		 debug\log("Inside making trans $original_transparency", 4);	      
	     //get the actual transparent color
	     $rgb = imagecolorsforindex($img, $original_transparency);	     
	     $original_transparency = ($rgb['red'] << 16) | ($rgb['green'] << 8) | $rgb['blue'];
	     //change the transparent color to black, since transparent goes to black anyways (no way to remove transparency in GIF)
	     imagecolortransparent($img, imagecolorallocate($img, 0, 0, 0));
	}      
      } 
      
      $truecolor = imagecreatetruecolor($w, $h);

      imagealphablending($img, false);
      imagesavealpha($img, true);
	 //      imagecopy($truecolor, $img, 0, 0, 0, 0, $w, $h);
      //imagecopymerge($truecolor, $img, 0, 0, 0, 0, $w, $h, 100);
      
      imagecopyresampled($truecolor, $this->image, 0, 0, 0, 0, $w, $h, $this->getWidth(), $this->getHeight());
     
      //      imagedestroy($img);
      //      $img = $truecolor;
      debug\log("transparency is >=0 $original_transparency");
     
     //remake transparency (if there was transparency)
     if ($original_transparency >= 0) {
       imagealphablending($truecolor, false);
       imagesavealpha($truecolor, true);
       for ($x = 0; $x < $w; $x++)
	 for ($y = 0; $y < $h; $y++)
	   if (imagecolorat($truecolor, $x, $y) == $original_transparency)
	      imagesetpixel($truecolor, $x, $y, 127 << 24);
     }

      imagedestroy($this->image);//garbage previous image.
      $this->image = $truecolor;
      
      if ($this->image != FALSE) {
	 debug\info('Success. SimpleImage:resize. old size (could be blank):' . $this->width . ' x ' .  $this->height . " new:$width x $height", 4);	
	 return TRUE;
      }
      else {
		 debug\info("ERROR. SimpleImage:resize. file $width x $height");	
		 return FALSE;
      }
   }
   
 
}
?>
