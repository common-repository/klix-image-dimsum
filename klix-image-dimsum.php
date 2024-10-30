<?php
/*
Plugin Name: Klix Image DimSum
Plugin URI: http://www.klix.tv/kliximagedimsum
Version: 1.0
Author: Klix.tv
Author URI: http://klix.tv/
License: GPLv2 or later
Description: Addicted to speed. Squeeze out a better Google ranking with no effort. Klix Image DimSum or 'DimSum' resizes all post images to their tagged width and height, and changes the compression for a good balance between quality and size. Results include not only increased performance but also increased site aesthetics and ease of use.

A) Performance issues include faster page delivery. Speed increases range from minor to significant, depending on the original mis-matching of file size to that seen in the browser. It's particularly useful for front pages that don't use thumbnail features but embed smaller representations of larger images. Optimizing large content is often the best way of increasing page delivery. As a corollary, DimSum decreases bandwidth usage which is important for (cloud) users who pay per usage.

B) Ease of use improvements are gained. Use an image without regard for its size. Which means no endless sizing in Photoshop or GIMP. One image is resized to perfectly suit each different page use in real-time.
Save in the highest quality in Photoshop/Gimp. Upload and then downgrade in DimSum until a perfect tradeoff between quality and size is found. And, in WordPress, select the perfect size to match your content, using the 90%, 80%, 70%, 60% or whatever scaling is needed to suit the page.
And, change the compression quality setting to suit the number of visitors (and thus server load) your site receives not just once but as part of your ongoing site maintenance.

C) In the aesthetic department, images are not stretched out beyond their viewable resolution. In fact, images are perfect WYSIWYG between image on the filesystem and image in the browser, in terms of resolution and size. Nevertheless, nothing can increase quality beyond that found in the original file, so use the highest quality in the first upload.

Compare before and after times using http://www.webpagetest.org/

/*
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as 
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace tv\klix\optimize\image;

use tv\klix\optimize\image as my;      
      
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'simpleimage.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'debug.php';

use tv\klix\optimize\debug as debug;

if (is_admin() ) {
      if ($gdv = my\gdVersion() ) {
       if ($gdv >=2) {
	   debug\log('GD version is 2 or more. TrueColor functions may be used.');
       } else {
	   debug\log('GD version is 1.  Avoid the TrueColor functions.');
       }
   } else {
       debug\log("Fatal Error. The GD extension isn't loaded.");
       return;
   }
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin_options.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'etc.php';


$numTagsConverted = 0;


//These should(have to) be in the main plugin file
if (is_admin() ) {
   register_activation_hook(__FILE__, 'tv\klix\optimize\image\add_defaults');
   register_uninstall_hook(__FILE__, 'tv\klix\optimize\image\delete_plugin_options');
}

//Uncomment the deactivation to loose options during deactivation.
//register_deactivation_hook(__FILE__, 'tv\klix\optimize\image\delete_plugin_options');

add_action('init', 'tv\klix\optimize\image\regular_init', 2);

//The following puts a variable, 'isdebug' into the query string.
//add_filter('query_vars', 'tv\klix\optimize\image\queryVariables');//runs after init

function queryVariables ($vars) {
   $vars[] = "iskdsdebug";//add debug to variables.
   debug\log($vars);
   return $vars;
}

//The following pulls out the variable, isdebug from the query string. 
//add_filter('wp', 'tv\klix\optimize\image\parseRequest'); //use 'wp' instead of 'parse_request'
function parseRequest($vars) {

   global $isDebug;
   debug\log($vars);

   //   if (get_query_var('preview') && get_query_var('iskdsdebug') )
   if (get_query_var('iskdsdebug') )
      $isDebug = get_query_var('iskdsdebug');
   
   return $vars;
}


function delete_plugin_options() {
     debug\log("***Deleting Klix Plugin Options***");
   
   //     $tmp = get_option('klix_options');//Database call
   //     debug\log('Klix deleting options from DB ' . var_dump($tmp));
     if (delete_option('klix_options')) {
		debug\log('Klix deleting options from DB ' . var_dump($tmp));
     }
}


// Define default option settings
function add_defaults() {

   debug\log("**Klix initialize plugin. Adding options**");
   
   $tmp = get_option('klix_options');//Database call
   debug\log($tmp);
   
   if (!$tmp || !is_array($tmp) )
   {   
      debug\log("**Options array is empty. Make one**");
      //	delete_option('klix_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
	$defaults = defaults();
	add_option('klix_options', $defaults);//added later
   }	
   //   update_option('klix_options', $defaults);
}


/* Hooks for major functions */
function regular_init() {
   
   global $isEnabled, $isDebug;
   global $isRenderContentHome, $isRenderContentPost, $isRenderContentHeader, $isRenderContentFooter;
   global $wp_query;
   
   debug\log("Regular init enabled:".$isEnabled . '; debug:' . $isDebug); 
      
   if ($isEnabled || $isDebug) 
   {
      global $runLevel;
      global $imageDirectory;
      
      debug\log("Inside the isenabled/isdebug $runLevel with directory, $imageDirectory");
	   
      add_action('wp_footer','tv\klix\optimize\image\wpFooter', $runLevel);
      //Perhaps RUN a cleanup on the cache? or set images to 3600.
      
      if (!$imageDirectory) {
	 debug\info("Critical. $imageDirectory is not set. Check options");
	 return;
      }
      if (!is_dir($imageDirectory)) {
	 debug\info("Critical. $imageDirectory doesnt exist. Check options or contact SysAdmin");
	 return;
      }
      if (!is_readable($imageDirectory)) {
	 debug\info("Critical. $imageDirectory is not readable. Check options or contact SysAdmin");
	 return;
      }
      
      if ($isRenderContentHome || $isRenderContentPost) {
	 add_action('loop_start', 'tv\klix\optimize\image\loopStart', $runLevel);// adding 9 might prevent clash with caching plugins
	 add_action('the_post', 'tv\klix\optimize\image\thePost', $runLevel);
	 add_action('loop_end', 'tv\klix\optimize\image\loopEnd', $runLevel);
      }
      if ($isRenderContentHeader) {
	 add_action('get_header', 'tv\klix\optimize\image\getHeader', $runLevel);
      }
      if ($isRenderContentFooter) {
	 add_action('get_footer', 'tv\klix\optimize\image\getFooter', $runLevel);	 
      }
   }
   
}


//Create a directory for the new dimsummed images
function initDirectory($dir)
{
   if (!file_exists($dir)) {
      if (! mkdir($dir, 0755))
	 debug\log('Warning. Klix DimSum Cannot mkdir directory: ' . $dir);
   }
   if (!is_dir($dir)) { 
      debug\log('Warning. Klix DimSum. ' . $imageDirectory . ' is not a directory.');
   }
   if (! is_readable($dir) )
      debug\log('Warning. Klix DimSum directory is not readable: ' . $dir);
   if (!is_writable($dir) )
      debug\log('Warning. Klix DimSum directory is not writable: ' . $dir);
}


//Called only once before loop
function loopStart() {
   debug\info('WP action hook. LoopStart', 10);   
   global $isEnabled , $isDebug;
   if ($isEnabled || $isDebug) {
   		try {
	      $errorNum = ob_start('tv\klix\optimize\image\scaleAllImagesInContent'); //call the scaling function with buffer.   
    	  if ($errorNum == FALSE) debug\info('Critical Error. Cannot start buffer');
   		} catch (Exception $e) {
   			debug\info('Error. Cannot start buffer');
   		}
   }
}

function thePost() {
   debug\info('WP action hook. thePost', 10);
   global $isEnabled , $isDebug;
   if ($isEnabled || $isDebug) {
   		try {
   		  if (ob_get_length() > 0)
		      ob_flush();
   		} catch (Exception $e) {
   			debug\info('Error. Cannot flush buffer');
   		}
	      
   }
}

function loopEnd() {
   debug\info('WP action hook. LoopEnd', 4);
   global $isEnabled , $isDebug;
   if ($isEnabled || $isDebug) {
   		try {
   		  if (ob_get_length() > 0)
		      ob_end_flush();   
   		} catch (Exception $e) {
   			debug\info('Error. Cannot end-flush buffer');
   		}
   }
}


function getHeader()
{
   debug\info('WP action hook. GetHeader', 4);
   global $isEnabled , $isDebug, $isHeader;
   if ($isEnabled || $isDebug) {
      $isHeader = true;
      try {
	      $errorNum = ob_start('tv\klix\optimize\image\scaleAllImagesInContent'); //call the scaling function with buffer.   
	      if ($errorNum == FALSE) debug\info('Critical Error. Cannot start buffer');
	      else ob_end_flush();
      } catch (Exception $e) {
      }
      $isHeader = false;
   }
   debug\info('WP action hook. GetHeader Done', 4);   
}


function getFooter()
{
   debug\info('WP action hook. GetFooter', 4);
   global $isEnabled , $isDebug, $isHeader;
   
   if ($isEnabled || $isDebug) {
      $isFooter = true;
      try {
	      $errorNum = ob_start('tv\klix\optimize\image\scaleAllImagesInContent'); //call the scaling function with buffer.   
	      if ($errorNum == FALSE) debug\info('Critical Error. Cannot start buffer');
	      else ob_end_flush();
      } catch (Exception $e) {
      }
      $isFooter = false;
   }
   debug\info('WP action hook. GetFooter Done', 4);   
}


function isHeader()
{
   global $isHeader;
   return $isHeader;
}

function isFooter()
{
   global $isFooter;
   return $isFooter;
}

function wpFooter() {
   global $isEnabled, $isDebug, $imageDirectory, $imageURL, $imageType, $imageCompression, $runLevel;
   global $numTagsConverted;
   global $info;
   global $isRenderContentHome, $isRenderContentPost, $isRenderContentHeader, $isRenderContentFooter;
    
   global $debugLevel;
   
   //   if (is_preview() && $isDebug) { 
   if ($isDebug) { 
	 echo '<div><table><th>Debug Level. 1=Norm</th><th>Klix Image DimSum Info</th>';
	 echo '<tr><td>1</td><td>(General)Site Path: ' . ABSPATH .';</td></tr>';	 
	 echo '<tr><td>1</td><td>(General)Site URL: ' . get_site_url() .';</td></tr>';	 
	 echo "<tr><td>1</td><td>On/Off: $isEnabled;  (0=no,1=yes) Debugging: $isDebug;  (0=no,1=yes) </td></tr>";	 
	 echo "<tr><td>1</td><td>DimSum Directory: $imageDirectory;</td></tr>";	 
	 echo "<tr><td>1</td><td>DimSum URL: $imageURL;</td></tr>";	 
	 echo "<tr><td>1</td><td>Image type: $imageType;  (possible values: 0=original, 2=JPEG)</td></tr>";	 
	 echo "<tr><td>1</td><td>Image compression: $imageCompression; (possible range: 20%-100%)</td></tr>";

	 echo"<tr><td>1</td><td>Checking Content filter. home: $isRenderContentHome post: $isRenderContentPost header:$isRenderContentHeader footer:$isRenderContentFooter</td></tr>";   

	 echo "<tr><td>1</td><td>Run level: $runLevel; (possible values: -1 to 11) </td></tr>";
	 echo "<tr><td>1</td><td>Debug level: $debugLevel; (possible values: 1 to 10) </td></tr>";
	 echo $info;	 
	 echo "<tr><td>1</td><td>Optimized images on this page: $numTagsConverted </td></tr>";
	 echo '</table></div>';	 
    }
    
    global $allowPromo;
    if ($allowPromo) {
	    echo '<p style="visibility: hidden">Page image optimized by <a href="http://klix.tv/">klix.tv</a></p>';
    }
}

/* 
 * return content with image urls pointing to image resized to match the w/h attributes
 */
function scaleAllImagesInContent($content) {
   global $imageDirectory;

   if (!$imageDirectory && !is_dir($imageDirectory) && !is_readable($imageDirectory) ) 
   {
      debug\info("Critical. $imageDirectory is not readable. Contact SysAdm");
      return $content;
   }
   
   global $post;//not needed ?.
   global $numTagsConverted;

   global $imageCompression;
   global $imageType;
   global $isDebug;
   global $isGrep, $imageGrep, $imageFilter;
   global $imageDir, $imageURL;
   
   global $isRenderContentHome, $isRenderContentPost, $isRenderContentHeader, $isRenderContentFooter;  
   
   debug\info('Filter. Content Type, Home:'. is_home() . ' Post:'. is_single() . ' or Page:'. is_page() . ' or header:' . isHeader() . ' or footer: ' . isFooter(), 4 );
      
   //   if ( (is_home() && $isRenderContentHome) || (is_single() && isRenderContentPost) || (is_page() && isRenderContentPage) )
   if ( (is_home() && $isRenderContentHome) || (is_single() && $isRenderContentPost) || (isHeader() && $isRenderContentHeader) || (isFooter() && $isRenderContentFooter) )// || (is_front_page() && isRenderContentHome) )
   {
	debug\info ('Filter. Examining Post with ID: ' . get_the_ID(), 1 );
	
	$matchesAll = array();
	$matches1 = $matches2 = $matches3 = array();
	
        preg_match_all('/<img(?:.+?)src="(.+?)"(?:[^>]+?)>/', $content, $matchesAll); //check for 'src='

        debug\info('Peek. Searching for <img> tag. <textarea>'. var_export($matchesAll, TRUE) .'</textarea>', 3);
	$x=-1;
	
	foreach($matchesAll[0] as $matches2)
      	{
	   $x++;
	   $grid_img = '';
	   $w = $h = 0;
	      	   
	   debug\info("Peek. $x. Positive match. <textarea>". var_export($matches2, TRUE) .'</textarea>', 2);
	   debug\info("Peek. $x. Associated src tag <textarea>". $matchesAll[1][$x] .'</textarea>', 2);

	   debug\info("Filter. Image type: preg_match('/src=.+($imageFilter)/'", 2);
	   if (!preg_match('/'. $imageFilter . '/', $matchesAll[1][$x], $null)) {
	      debug\info("Fail. Image filter.", 2);
	      continue;
	   }
	   debug\info("Pass. Image filter", 2);

	   //check for matching strings in the imageGrep array
	   if ($isGrep) {
	      debug\info('Filter. PCRE grep. preg_match(' . var_export($imageGrep, TRUE), 2);
	      if (!isMatching($matches2, $imageGrep))  {
		 debug\info("Fail. PCRE grep filter", 2);
		 continue;
	      }	      
	      debug\info("Pass. PCRE grep filter", 2);
	   } else {
	      debug\info('Filter. PCRE Grep not on. Automatically pass', 2);
	   }

		$img_url = ($matchesAll[1][$x]) ? $matchesAll[1][$x] : '';//This is the url
		debug\info("Found image: $img_url");
		if ($img_url) 
		{
			// first, try to get attributes
			$matches_w = $matches_h = array();
			preg_match('/width="([0-9]+)"/', $matches2, $matches_w);
			preg_match('/height="([0-9]+)"/', $matches2, $matches_h);
			
			if ($matches_w[1] and $matches_h[1]) 
			{
			   $w = $matches_w[1]; $h = $matches_h[1];
			   debug\info("Found width/height: $w x $h", 2);
			 	
			  if ($w || $h) //Both width and height needed. Usually not an issue in Wordpress
			  {
			     $originalFileName = pathinfo($img_url, PATHINFO_FILENAME); //returns xxx
			     $originalURLDir = pathinfo($img_url, PATHINFO_DIRNAME);//returns http://
			     $originalExt = pathinfo($img_url, PATHINFO_EXTENSION); //returns .jpg
			     			     
			     debug\info("Original DIR:$originalURLDir FILENAME:$originalFileName EXT:$originalExt", 2);
			     
			     $newExt = image_type_to_extension_withdefault($imageType, TRUE, $originalExt);
			     
			     //Two types of URLS needed. One for the filesystem (/var/www or c:/htdocs) , the other for the web link (http)
			     //			     $newDirectoryFilePathExt = KLX_OPTIMIZED_CONTENT_DIR . $originalFileName . '_opt-'.$w.'x'.$h . $newExt;			     			     
			     $newDirectoryFilePathExt = $imageDirectory . $originalFileName . '.dimsum.'.$w.'x'.$h . $newExt;			     			     
			     //			     $newURL = KLX_SITE_URL . $originalFileName . '_opt-'.$w.'x'.$h . $newExt;			    
			     $newURL = $imageURL . $originalFileName . '.dimsum.'.$w.'x'.$h . $newExt;			    
			     
			     debug\info("New full filename: $newDirectoryFilePathExt", 2);
			     debug\info("New URL: $newURL", 2);
			      
			     if ($newDirectoryFilePathExt) 
			     {
				$errorCode = TRUE;
				if (!file_exists($newDirectoryFilePathExt) || $isDebug) 
				{
				   debug\info("File $newDirectoryFilePathExt doesnt exist. Need to create and save file. Or debug mode is ON.", 2);
				   if (is_writeable($imageDirectory))// && is_writeable($newDirectoryFilePathExt))
				   {
				      $image = new my\SimpleImage();	   				      
				      $errorCode = $image->loadWithFilename($img_url);
				      if ($errorCode == TRUE) $errorCode = $image->resize($w,$h);
				      //				      if ($errorCode == TRUE) $errorCode = $image->resizeWithAlpha($w,$h, 0.2);
				      
				      if ($errorCode == TRUE) $errorCode = $image->saveWithFilename($newDirectoryFilePathExt, $imageType, $imageCompression);
				    }
				    else {
				    	debug\info("Directory (or new file) is not writeble $imageDirectory , $newDirectoryFilePathExt", 1);				   
					$errorCode = FALSE;
				    }

				}
				else
				   debug\info("File matching name already in path: $newDirectoryFilePathExt", 2);				   
				
				if ($errorCode == TRUE)
				{
					//Find sizes
					$uploadDir = wp_upload_dir();
					$oldDirectoryFilePathExt = $uploadDir['basedir'] . '/' . $originalFileName . '.' . $originalExt;
					debug\info("Old file: $oldDirectoryFilePathExt", 2);
					
				   //Compare sizes. Use curl for old file since we cannot guarantee directory path
//					$oldSize = filesize($oldDirectoryFilePathExt);	//Incorrect path
				    $ch = curl_init($img_url);
					$ret = curl_exec($ch);
					$oldSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
					curl_close($ch);					
					//New file
				    $ch = curl_init($newURL);
					$ret = curl_exec($ch);
					$newSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
					curl_close($ch);
					
					$sizeDiff = $oldSize - $newSize;					
//					$totalSizeDiff += $sizeDiff;									
					debug\info("Original image file: <img src=$img_url>", 1);
				   debug\info("URL comparison. Original size: $oldSize KB; New size: $newSize KB; Difference: $sizeDiff KB;", 1);
					curl_close($ch);

				
					//Finally check if new size is smaller than original (or within certain parameters)
					if ($sizeDiff > 0) {
					   	$content = str_replace($img_url, $newURL, $content);				   				   
				   		$numTagsConverted++;				   					   	
				   		debug\info("Replacing: current url string: $img_url; new url string: $newURL;", 1);
					   	debug\info("New image file <img src=$newURL>", 1);
					  	debug\info("New file created in path: $newDirectoryFilePathExt ", 2);
					} else { 
						//Remove file. NOt used.
//						try { 
							unlink($newDirectoryFilePathExt);
//						} catch (Exception $e) {
//							debug\info("Old file cannot be deleted.");
//						}						
				  		debug\info("New file larger than original. Using original image.");
					}						
					
				} else {
				   debug\info("Error. Creating new image: $errorCode");
				}
				
			     }
			     
			  } //END OF if $w/$h
			} //End of matches width[1]
		   } //End of img_url
	   } //for each match.   end of if == 'img' 	   
      }//end of ishome, ispost, etc

      return $content;
}


function replaceFileExtension($filename, $switch)
{
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      $ext = strtolower($ext);      
      
      debug\info("Extension file: $filename; switch: $switch (0=orig,1=jpg,2=png)");

      $newext = $ext;
      switch($switch)
      {
	 case 0:
	    $newext = $ext;
	    break;
	    
	 case 1:
	    $newext = 'jpg';
	    break;

	 case 2:
	    $newext = 'png';
	    break;
	    
	 case 3:
	    $newext = 'gif';
	    break;
	    
	 default:
	    $newext = 'jpg';
      }
      
      $newfile = str_replace($ext, $newext, $filename);
      return $newfile;
}


function filenameWithInsertNoExtension($filename, $insert)
{
   $path = pathinfo($filename);
   if ($path) {
      $file = $path['filename'];
      //      $ext = $path['extension'];
				   
      $newpath = '';
      if ($path) {
	 $newpath = $file . $insert;
      }
      return $newpath;
      
   } 
   else return FALSE;

}

function klix_insert_data_before_fileextension($filename, $insert)
{
   $path = pathinfo($filename);
   if ($path) {
      $file = $path['filename'];
      $ext = $path['extension'];				   
      return $file . $insert . '.' .$ext;
   } 
   else return FALSE;
}

function klix_insert_data_before_pathextension($url, $insert)
{
   $path = pathinfo($url);
   if ($path) {
   $dir = $path['dirname'];
   $file = $path['filename'];
   $ext = $path['extension'];
				
   $newpath = '';
   if ($path) {
      $newpath = $dir . DIRECTORY_SEPARATOR . $file . $insert . '.'.$ext;
   }
   return $newpath;
   } else return FALSE;
}

function klix_insert_data_before_urlextension($url, $insert)
{
   $parsed = parse_url($url);
   
   if ($parsed) {
      $newpath = FALSE;
      $path = $parsed['path']; 
   				
      if ($path) {	 
	 $newpath = klix_insert_data_before_fileextension($path, $insert);
      }
      return $newpath;
   } else return FALSE;
}

//add_action('shutdown','tv\klix\optimize\image\previewPlugin');
/*
add_action('setup_theme','tv\klix\optimize\image\previewPlugin');

function previewPlugin()
{
   debug\log('previewPlugin with ABSPATH=' . ABSPATH);
   if ( ! isset($_GET['preview']) || !isset($_GET['iskdsdebug'])) 
      return;

   include(ABSPATH.'wp-includes/theme.php');
      debug\log('calling preview_theme_ob_filter');
      $error = ob_start( 'preview_theme_ob_filter' ); 
}
*/

/*
add_action('init','tv\klix\optimize\image\previewPlugin');

function rewriteWithDebug()
{
   global $isDebug;
   if ($isDebug)
      add_rewrite_rule();
      
}
*/

//add_rewrite_rule('/'.get_site_url(). '/?','index.php?$matches[1]/?preview=1&iskdsdebug=true','top');

//Thanks be to http://www.rlmseo.com/blog/passing-get-query-string-parameters-in-wordpress-url/
// hook add_rewrite_rules function into rewrite_rules_array
/*
add_filter('rewrite_rules_array', 'tv\klix\optimize\image\add_debug_rules');

function add_debug_rules($aRules)
{
    $aNewRules = array(site'msds-pif/([^/]+)/?$' => 'index.php?pagename=msds-pif&msds_pif_cat=$matches[1]');
    $aRules = $aNewRules + $aRules;
    return $aRules;
}
*/


function add_rewrite_rules($aRules) {

   debug\info( 'Rules: ' . var_export($aRules, TRUE));
            
	 //      'src\s*=\s*"([^"]+)\.gif"'	      
	 //    $imgRules = array('/<img(?:.+?)src="(.+?)"(?:[^>]+?)>/' => '<img'
	 
   //    $aNewRules = array('msds-pif/([^/]+)/?$' => 'index.php?pagename=msds-pif&msds_pif_cat=$matches[1]');
    $aNewRules = array('msds-pif/([^/]+)/?$' => 'index.php?pagename=msds-pif&msds_pif_cat=$matches[1]');
    $aRules = $aNewRules + $aRules;
    return $aRules;
}
 
// hook add_rewrite_rules function into rewrite_rules_array
//add_filter('rewrite_rules_array', 'add_rewrite_rules');

?>
