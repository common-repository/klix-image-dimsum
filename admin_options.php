<?php

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:                                    
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

namespace tv\klix\optimize\image;
//use tv\klix\optimize\image as my;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'debug.php';
//require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'etc.php';

use tv\klix\optimize\debug as debug;

if (is_admin()) {   
   add_action('admin_init', 'tv\klix\optimize\image\admin_init');
   add_action('admin_menu', 'tv\klix\optimize\image\admin_add_options_page');
}

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'klix_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted (UNINSTALLED)
/*
function delete_plugin_options() {
	$tmp = get_option('klix_options');//Database call
	debug\log('Klix deleting options from DB ' . var_dump($tmp));
	if (delete_option('klix_options'))
	   	debug\log('Klix deleting options from DB ' . var_dump($tmp));
}
*/


// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'klix_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

function defaults() 
{
   global $defaultDirectory;
   if (defined(WP_CONTENT_DIR))
      $defaultDirectory = WP_CONTENT_DIR . 'kliximagedimsum';
   else
      $defaultDirectory = ABSPATH . 'wp-content/' . 'kliximagedimsum';
   
    $defaults = array( "isenabled" => "0",
		      "cache_directory" => $defaultDirectory,
		    "image_type" => "0",
		    "image_compression" => "90",
		    "content_type" => "0",
		    "isgrep"=>"0",
		    "image_grep" => "/wp-image/",
		    "isjpg" => "1",
		    "ispng" => "1",
		    "isgif" => "0",
		    "isdebug" => "0",
		    "debug_level" => "1",
		    "run_level" => "10",
		    "render_content_home" => "1",
		    "render_content_posts" => "1",
		    "render_content_pages" => "0",
		    "render_content_header" => "0",
		    "render_content_footer" => "0",
		    "allow_promo" => "1"
    );
   debug\log('Klix Setting defaults as ' . var_export($defaults, true));
   return $defaults;
}

// Define default option settings
/*
function add_defaults() {
   debug\log("**Adding options**");
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
*/


// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'klix_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function admin_init(){
   //echo "Running admin_options.klix_init";
   //	register_setting( 'klix_plugin_options', 'klix_options', 'klix_validate_options' );
   register_setting( 'klix_plugin_options', 'klix_options', 'tv\klix\optimize\image\validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'klix_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function admin_add_options_page() {
   add_options_page('Klix Image DimSum', 'Klix Image DimSum', 'manage_options', __FILE__, 'tv\klix\optimize\image\klix_render_form');
   //   wp_enqueue_script('thickbox');	
   //   wp_enqueue_style('thickbox');	
	
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function klix_render_form() {
	?>
		       
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Klix Image DimSum</h2>
		<p>Dimsum are bite-sized portions of Cantonese food, often served in steamer 
		baskets or on small plates. Dimsum restaurants usually have the dishes fully cooked and ready-to-eat. Servers go around the restaurant offering the snacks to customers. 
		</p>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('klix_plugin_options'); ?>
			<?php $options = get_option('klix_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
			
				<!-- Is Enabled Control -->
				<tr valign="top">
				   <th scope="row">Turn on/off</th>
					<td>
						<label><input name="klix_options[isenabled]" type="hidden" value="0" ><input name="klix_options[isenabled]" type="checkbox" value="1" <?php if (isset($options['isenabled'])) { checked('1', $options['isenabled']); } ?> /> On</label><br />
<span style="color:#666666;margin-left:2px;">Turn on or off rather than activate or deactivate.</span>
					</td>
				</tr>

					   <tr valign="top" style="border-top:#dddddd 1px solid;"><td></td></tr>
				
				<tr valign="top">
				   <th scope="row">Content Filter</th>
					<td>
						<label><input name="klix_options[render_content_home]" type="hidden" value="0" ><input name="klix_options[render_content_home]" type="checkbox" value="1" <?php if (isset($options['render_content_home'])) { checked('1', $options['render_content_home']); } ?> /> Home</label>
						<label><input name="klix_options[render_content_posts]" type="hidden" value="0" ><input name="klix_options[render_content_posts]" type="checkbox" value="1" <?php if (isset($options['render_content_posts'])) { checked('1', $options['render_content_posts']); } ?> /> Posts</label>
<!--						<label><input name="klix_options[render_content_pages]" type="hidden" value="0" ><input name="klix_options[render_content_pages]" type="checkbox" value="1" <?php if (isset($options['render_content_pages'])) { checked('1', $options['render_content_pages']); } ?> /> Pages</label>
-->						<label><input name="klix_options[render_content_header]" type="hidden" value="0" ><input name="klix_options[render_content_header]" type="checkbox" value="1" <?php if (isset($options['render_content_header'])) { checked('1', $options['render_content_header']); } ?> /> Header</label>
						<label><input name="klix_options[render_content_footer]" type="hidden" value="0" ><input name="klix_options[render_content_footer]" type="checkbox" value="1" <?php if (isset($options['render_content_footer'])) { checked('1', $options['render_content_footer']); } ?> /> Footer</label>
						<br/><span style="color:#666666;margin-left:2px;">Images in these selected content types will be DimSummed.
<br/>Tips: Home receives the greatest number of hits. Header and Footer may contain GIF images (branded logos, etc) that are already small and optimized.
						</span>
					</td>
				</tr>
				

				<tr valign="top">
				   <th scope="row">Image Filter</th>
					<td>
<label><input name="klix_options[isjpg]" type="hidden" value="0" ><input name="klix_options[isjpg]" type="checkbox" value="1" <?php if (isset($options['isjpg'])) { checked('1', $options['isjpg']); } ?> /> JPG/JPEG </label>
						<label><input name="klix_options[ispng]" type="hidden" value="0" ><input name="klix_options[ispng]" type="checkbox" value="1" <?php if (isset($options['ispng'])) { checked('1', $options['ispng']); } ?> /> PNG </label>
						<label><input name="klix_options[isgif]" type="hidden" value="0" ><input name="klix_options[isgif]" type="checkbox" value="1" <?php if (isset($options['isgif'])) { checked('1', $options['isgif']); } ?> /> GIF </label>
<br/><span style="color:#666666;margin-left:2px;">Images with the selected extension types will be DimSummed.
<br/>Tip: GIFs are often already small.</span>
					</td>
				</tr>

				<tr>
					<th scope="row">PCRE (GREP) Filter</th>
					<td>
<label><input name="klix_options[isgrep]" type="hidden" value="0" ><input name="klix_options[isgrep]" type="checkbox" value="1" <?php if (isset($options['isgrep'])) { checked('1', $options['isgrep']); } ?> /> On</label>
					       
						 <label><input type="text" size="57" name="klix_options[image_grep]" value="<?php echo $options['image_grep']; ?>" /><label><br />
   <span style="color:#666666;margin-left:2px;">Filter images using comma-seperated PCRE (perl regular expression) strings. 
<br/>Tips: Use braces but no quotes, /example/. Empty will target all images.
   Target certain sized images, WP only images, external websites, particular directories: /size-full/, /wp-image/, /wp-content/
<br/>Default is <b>Off</b> and <b>/wp-image/</b></span>
					</td>
				</tr>
				
			       <tr valign="top" style="border-top:#dddddd 1px solid;"><td></td></tr>

				<!-- Image type Radio Button Group -->
				<tr valign="top">
					<th scope="row">Target Image Type</th>
					<td>
					   <label><input name="klix_options[image_type]" type="radio" value="0" <?php checked('0', $options['image_type']); ?> /> Original <span style="color:#666666;margin-left:30pt;">[Retain the original image type. Recommended for graphical integrity.]</span></label><br />
					      <label><input name="klix_options[image_type]" type="radio" value="2" <?php checked('2', $options['image_type']); ?> /> JPEG  <span style="color:#666666;margin-left:39pt;">[Convert images to jpeg. Optimal for speed.]</span></label><br />
<!--						 <label><input name="klix_options[image_type]" type="radio" value="3" <?php checked('3', $options['image_type']); ?> /> PNG <span style="color:#666666;margin-left:44pt;">[Convert images to png.]</span></label><br />
						    <label><input name="klix_options[image_type]" type="radio" value="1" <?php checked('1', $options['image_type']); ?> /> GIF <span style="color:#666666;margin-left:50pt;">[Convert images to gif.]</span></label><br />
-->						    
						<span style="color:#666666;">Klix DimSum can not only optimize size, but also convert image type. 
						<br/>Tips: PNG images offer better image quality, but can be 2 to 5 times a comparable JPEG in size. 
						JPEG are smaller in size, thus offering faster downloads and higher speed rating. 
						<br/>Default is <b>Original<b/></span>
					</td>
				</tr>


				<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row">Target Image Quality</th>
					<td>
						<select name='klix_options[image_compression]'>
							<option value='100' <?php selected('100', $options['image_compression']); ?>>100%</option>
							<option value='99' <?php selected('99', $options['image_compression']); ?>>99%</option>
							<option value='98' <?php selected('98', $options['image_compression']); ?>>98%</option>
							<option value='97' <?php selected('97', $options['image_compression']); ?>>97%</option>
							<option value='96' <?php selected('96', $options['image_compression']); ?>>96%</option>
							<option value='95' <?php selected('95', $options['image_compression']); ?>>95%</option>
							<option value='94' <?php selected('94', $options['image_compression']); ?>>94%</option>
							<option value='93' <?php selected('93', $options['image_compression']); ?>>93%</option>
							<option value='92' <?php selected('92', $options['image_compression']); ?>>92%</option>
							<option value='91' <?php selected('91', $options['image_compression']); ?>>91%</option>
							<option value='90' <?php selected('90', $options['image_compression']); ?>>90%</option>
							<option value='89' <?php selected('89', $options['image_compression']); ?>>89%</option>
							<option value='87' <?php selected('87', $options['image_compression']); ?>>87%</option>
							<option value='85' <?php selected('85', $options['image_compression']); ?>>85%</option>
							<option value='80' <?php selected('80', $options['image_compression']); ?>>80%</option>
							<option value='75' <?php selected('75', $options['image_compression']); ?>>75%</option>
							<option value='70' <?php selected('70', $options['image_compression']); ?>>70%</option>
							<option value='65' <?php selected('65', $options['image_compression']); ?>>65%</option>
							<option value='60' <?php selected('60', $options['image_compression']); ?>>60%</option>
							<option value='55' <?php selected('55', $options['image_compression']); ?>>55%</option>
							<option value='50' <?php selected('50', $options['image_compression']); ?>>50%</option>
							<option value='45' <?php selected('45', $options['image_compression']); ?>>45%</option>
							<option value='40' <?php selected('40', $options['image_compression']); ?>>40%</option>
							<option value='35' <?php selected('35', $options['image_compression']); ?>>35%</option>
							<option value='30' <?php selected('30', $options['image_compression']); ?>>30%</option>
							<option value='25' <?php selected('25', $options['image_compression']); ?>>25%</option>
							<option value='20' <?php selected('20', $options['image_compression']); ?>>20%</option>
						</select>
						<br/><span style="color:#666666;margin-left:2px;">Image quality determines file size. 
					   <br/>Tips: Choose 65% for lower-quality but small (and thus speedy) DimSum, 99% for higher-quality but larger (slow) DimSum. 
					   100% is not recommended, images are often larger than original. Recommended use by IJG (Independent JPEG Group) is 75%. 
					   Note that you are compressing a compressed image, thus a good balance of size and quality is between 90% and 96%. 
					   Experiment with Debug turned on.
					  <br/>Default is <b>90%</b></span>
					</td>
				</tr>

				<tr>
					<th scope="row">Target Directory</th>
					<td>
					   <input type="text" size="57" name="klix_options[cache_directory]" value="<?php echo $options['cache_directory']; ?>" /><br />
					   <span style="color:#666666;margin-left:2px;">Images are saved (cached) in this directory. 
					      <br/>Warning: Do not often change Directory; images will be Re-DimSummed if not found.
<br/> Default is <b><?php global $defaultDirectory; echo $defaultDirectory; ?></b></span>
					</td>
				</tr>
				
				<!-- Content type -->
<!--
				<tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>				

-->
				<!-- Is Debug Control -->
			       <tr valign="top" style="border-top:#dddddd 1px solid;"><td></td></tr>
				
				   <th scope="row">Debug on/off</th>
					<td>
						<label><input name="klix_options[isdebug]" type="hidden" value="0" ><input name="klix_options[isdebug]" type="checkbox" value="1" <?php if (isset($options['isdebug'])) { checked('1', $options['isdebug']); } ?> /> Debug On</label><br />
					<span style="color:#666666;margin-left:2px;">Debug output appears on the page. Debug Re-DimSums images and reveals them in their native format and size.</span>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Debug level</th>
					<td>
						<select name='klix_options[debug_level]'>
							<option value='1' <?php selected('1', $options['debug_level']); ?>>1</option>
							<option value='2' <?php selected('2', $options['debug_level']); ?>>2</option>
							<option value='3' <?php selected('3', $options['debug_level']); ?>>3</option>
							<option value='4' <?php selected('4', $options['debug_level']); ?>>4</option>
							<option value='5' <?php selected('5', $options['debug_level']); ?>>5</option>
							<option value='6' <?php selected('6', $options['debug_level']); ?>>6</option>
							<option value='7' <?php selected('7', $options['debug_level']); ?>>7</option>
							<option value='8' <?php selected('8', $options['debug_level']); ?>>8</option>
							<option value='9' <?php selected('9', $options['debug_level']); ?>>9</option>
							<option value='10' <?php selected('10', $options['debug_level']); ?>>10</option>
						</select>
	    <br/><span style="color:#666666;margin-left:2px;">Show less or more amount of debug information. 1 is less. 10 is all.
<br/>Default is <b>1<b/></span>
					</td>
				</tr>

				<!-- Run level Select Drop-Down Control -->			       
				<tr valign="top">
					<th scope="row">Run level</th>
					<td>
						<select name='klix_options[run_level]'>
							<option value='-1' <?php selected('-1', $options['run_level']); ?>>-1</option>
							<option value='0' <?php selected('0', $options['run_level']); ?>>0</option>
							<option value='1' <?php selected('1', $options['run_level']); ?>>1</option>
							<option value='2' <?php selected('2', $options['run_level']); ?>>2</option>
							<option value='3' <?php selected('3', $options['run_level']); ?>>3</option>
							<option value='4' <?php selected('4', $options['run_level']); ?>>4</option>
							<option value='5' <?php selected('5', $options['run_level']); ?>>5</option>
							<option value='6' <?php selected('6', $options['run_level']); ?>>6</option>
							<option value='7' <?php selected('7', $options['run_level']); ?>>7</option>
							<option value='8' <?php selected('8', $options['run_level']); ?>>8</option>
							<option value='9' <?php selected('9', $options['run_level']); ?>>9</option>
							<option value='10' <?php selected('10', $options['run_level']); ?>>10</option>
							<option value='11' <?php selected('11', $options['run_level']); ?>>11</option>
						</select>
	    <br/><span style="color:#666666;margin-left:2px;">Klix DimSum uses WP action hooks, and so do many other plugins. Most plugins use 10. Change the run level to encourage compatibility. '1' runs early, but is more likely to miss other plugin content. '10' runs late, but is more likely to conflict with a plugin.
<br/>Default is <b>10<b/></span>
					</td>
				</tr>

		       <tr valign="top" style="border-top:#dddddd 1px solid;"><td></td></tr>				
				   <th scope="row">Street Cred</th>
					<td>
						<label><input name="klix_options[allow_promo]" type="hidden" value="0" ><input name="klix_options[allow_promo]" type="checkbox" value="1" <?php if (isset($options['allow_promo'])) { checked('1', $options['allow_promo']); } ?> />On/Off</label><br />
					<span style="color:#666666;margin-left:2px;">Kindly recognize our hard work in making your site faster and better.</span>
					</td>
				</tr>

				
		  <tr>
			  <th scope="row">Persistence</th>
			<td>
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</td>
		  </tr>
   
		 <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>				

		 <!-- Debug Control -->
<!--		 
		 <tr valign="top" style="border-top:#dddddd 1px solid;">
		 <th scope="row">Preview with Debug</th>
		 <td>
		     <input class="button-primary" onclick="window.open('/?preview=1&preview_iframe=1&iskdsdebug=true', 'Klix Image DimSum Preview', 'height=800,width=1000,toolbar=no,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes')" title="Klix Image DimSum Preview with Debug" type="button" value="Preview" />  
		     <br/><span style="color:#666666;margin-left:2px;">Debug output appears on the page. Shows new images and reveals them old ones in their native format and size. 
<br/>Tips: Save settings first. Overwrites images in directory. Use to compare quality levels. Will also write to log if WP_DEBUG set to true</span>
		  </td>
		  </tr>
-->		  

		</table>

		</form>

	       <table class="form-table">
		
	       </table>
			<table class="form-table">
			
			<tr valign="top" style="border-top:#dddddd 1px solid;">
			<td colspan="2"><div style="margin-top:10px;">				

<h2>Suggested Tools</h2>

The following websites and tools help you speed up your site, and select the ideal settings.

<h4>Web Page analysis</h4>

<p><strong><a target="_blank" href="http://www.webpagetest.org/">WebPageTest</a> - </strong> 
An excellent site that provides a detailed analysis of your page load performance, HTTP headers as well as a comparison against an optimization checklist. Pick East/West US, Canada and some Europe and Asian.</p> 

<p><strong><a target="_blank" href="http://www.showslow.com/">Show Slow</a> - </strong> 
Show Slow monitors website performance metrics. Regression analysis permits comparison from change to change. Piggybacks off the results of YSlow, Page Speed and dynaTrace AJAX.</p> 

<p><strong><a target="_blank" href="http://browsermob.com/monitoring">BrowserMob</a> - </strong> 
A tool that provides alerts for poor website performance.</p> 

<h4>Browser Plugins and Helpers</h4>

<p><strong><a target="_blank" href="http://developer.yahoo.com/yslow/">Yahoo! YSlow</a> - </strong> 
An excellent Firefox/Firebug Add-on that analyzes web pages and suggests ways to improve their performance.</p> 

<p><strong><a target="_blank" href="http://msfast.myspace.com/">MySpace Performance Tracker</a> - </strong> 
An Internet Explorer plugin that helps optimize web page performance by analysing and measuring problem areas.</p> 

<p><strong><a target="_blank" href="http://www.alphaworks.ibm.com/tech/pagedetailer">IBM Page Detailer</a> - </strong> 
An obscure yet useful tool that assesses web page performance and provides details include the timing, size, and identity of each item in a page.</p> 

<p><strong><a target="_blank" href="http://www.microsoft.com/downloads/details.aspx?FamilyID=119f3477-dced-41e3-a0e7-d8b5cae893a3&displaylang=en">Microsoft VRTA</a> - </strong> 
A tool that visualizes web page download, identifies areas for performance improvements, and recommends solutions. </p> 


<h2>Suggested Reading</h2>

<h4>Image Quality</h4>

<p><strong><a  target="_blank" href="http://code.google.com/speed/articles/optimizing-images.html">Google paper on Optimizing images</a> - </strong> 
A fast read that explains why you should use Klix DimSum, with JPG/JPEG images. Shows a common comparison of image sizes and quality.
Basically, their claim, "Simple improvements can drastically decrease your download size, without diminishing the site's quality."
</p> 
				
				</td></tr>
</table>

<!--
		<p style="margin-top:15px;">
			<p style="font-style: italic;font-weight: bold;color: #26779a;">If you have found this starter kit at all useful, please consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XKZXD2BHQ5UB2" target="_blank" style="color:#72a1c6;">donation</a>. Thanks.</p>
			<span><a href="http://www.facebook.com/PressCoders" title="Our Facebook page" target="_blank"><img style="border:1px #ccc solid;" src="<?php echo plugins_url(); ?>/wp-content-filter/images/facebook-icon.png" /></a></span>
			&nbsp;&nbsp;<span><a href="http://www.twitter.com/dgwyer" title="Follow on Twitter" target="_blank"><img style="border:1px #ccc solid;" src="<?php echo plugins_url(); ?>/wp-content-filter/images/twitter-icon.png" /></a></span>
			&nbsp;&nbsp;<span><a href="http://www.presscoders.com" title="PressCoders.com" target="_blank"><img style="border:1px #ccc solid;" src="<?php echo plugins_url(); ?>/wp-content-filter/images/pc-icon.png" /></a></span>
		</p>
-->

	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function validate_options($input) {
   debug\log('Validating options');
	 // strip html from textboxes
	$input[''] =  wp_filter_nohtml_kses($input['cache_directory']); // Sanitize textbox input (strip html tags, and escape characters)
	return $input;
}

/*
add_filter( 'plugin_action_links', 'klix_plugin_action_links', 10, 2 );

// Display a Settings link on the main Plugins page. Aha. 
function klix_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$klix_links = '<a href="'.get_admin_url().'options-general.php?page=plugin-imageresize/admin_options.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $klix_links );
	}

	return $links;
}
*/
// ------------------------------------------------------------------------------
// SAMPLE USAGE FUNCTIONS:
// ------------------------------------------------------------------------------
// THE FOLLOWING FUNCTIONS SAMPLE USAGE OF THE PLUGINS OPTIONS DEFINED ABOVE. TRY
// CHANGING THE DROPDOWN SELECT BOX VALUE AND SAVING THE CHANGES. THEN REFRESH
// A PAGE ON YOUR SITE TO SEE THE UPDATED VALUE.
// ------------------------------------------------------------------------------

// As a demo let's add a paragraph of the select box value to the content output
//add_filter( "the_content", "klix_add_content", 1 );

//Set the variables this way...

//This can be run by all users.
add_action("init", "tv\klix\optimize\image\set_variables", 1);

//All global variables are declared here?!
function set_variables() {
   
   global $isEnabled, $isDebug, $imageDirectory, $imageURL;
   global $imageType, $imageCompression, $runLevel;
   global $isRenderContentHome, $isRenderContentPost, $isRenderContentPage, $isRenderContentHeader, $isRenderContentFooter;
   global $isGrep, $imageGrep, $imageFilter;
   global $defaultDirectory;
   
   global $debugLevel;
   global $allowPromo;
   
   $options = get_option('klix_options');
   
   if (defined(WP_CONTENT_DIR))
      $defaultDirectory = WP_CONTENT_DIR . 'kliximagedimsum';
   else
      $defaultDirectory = ABSPATH . 'wp-content/' . 'kliximagedimsum';
     
   if (!$options || !is_array($options))
      $options = defaults();      
      
   $isEnabled = intval($options['isenabled']);   // issetdef($options, 'isenabled', '0');// $options['isenabled'] ;
   
   $isDebug = intval($options['isdebug']);
   
   $debugLevel = intval($options['debug_level']);	//NEW
   
   $allowPromo = intval($options['allow_promo']);	//NEW
   
   $imageDirectory = $options['cache_directory'];
   
   $imageType = intval($options['image_type']);
   
   $isGrep = intval($options['isgrep']);
   $imageGrep = explode(',',$options['image_grep']);//comma seperated values, if any
		
   if ($options['isjpg']) $imageFilter = 'jpg|jpeg';
   if ($options['ispng']) $imageFilter .= ($imageFilter ? '|':''). 'png';
   if ($options['isgif']) $imageFilter .= ($imageFilter ? '|':'').'gif';
      
   $imageCompression = intval($options['image_compression']);
   
   $isRenderContentHome = intval($options['render_content_home']);
   
   $isRenderContentPost = intval($options['render_content_posts']);
   
   $isRenderContentHeader = intval($options['render_content_header']);
   
   $isRenderContentFooter = intval($options['render_content_footer']);   
   
   $runLevel = $options['run_level'];

   debug\log('Setting new options: '.var_export($options, true));

   //   echo "$isEnabled $isDebug $imageDirectory $imageType $imageCompression $runLevel";
   //debug\info("Settings isEnabled:$isEnabled isDebug:$isDebug directory:$imageDirectory");
   //debug\info("Settings runlevel:$runLevel imagetype:$imageType quality:$imageCompression");
   //debug\info("Settings grep:". var_export($imageGrep, true));   
   debug\log("Image directory is set as $imageDirectory");
   
   if (substr($imageDirectory, -1) != DIRECTORY_SEPARATOR)	//if directory doesnt end with '/
      $imageDirectory = $imageDirectory . DIRECTORY_SEPARATOR;
      
   if (is_admin() && $imageDirectory )//only run as admin
      initDirectory($imageDirectory);

   //Replace the ABSPATH with the siteurl for the html replaced string
   $site = get_site_url();
   if (substr($site, -1) != DIRECTORY_SEPARATOR)//if directory doesnt end with '/
      $site .= DIRECTORY_SEPARATOR;
   
   $imageURL = str_replace(ABSPATH, $site, $imageDirectory);
   debug\log("Image url variable is set as $imageURL;", 4);	
}

//   if (array_key_exists($array, $key);
function issetdef($array, $key, $default)
{
   if (isset($array[$key]))
      return $array[$key];
   else
      return $default;
}




