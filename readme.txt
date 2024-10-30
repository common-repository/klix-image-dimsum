=== Plugin Name ===
Contributors: adrianboston
Donate link: http://klix.tv/imagedimsum
Tags: pagerank, rank, speed, fast, cache, image, resize, optimize, png, jpg, jpeg, gif, thumbnail, cache, pagespeed
Requires at least: 2.7
Tested up to: 3.2.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Addicted to speed. Squeeze out a better Google ranking with no effort. Optimize image content for viewing by the end user.

== Description ==

Addicted to speed. Heck, who isn't. Squeeze out better performance with little effort. And get a shot at a higher Google ranking.

Search engines, notably Google introduced speed in their ranking algorithm a few years ago. Yet, the average site runs above 10 seconds to the user. Big guns like Facebook, Twitter and Google  deliver their site in under 2 seconds to the user. Yours should shoot for the same performance.

A lot of sites suffer in speed tests because large images really slows down the delivery of a website. Specifically, many sites serve huge images when they're viewed as tiny thumbnails by the user. Caching can significantly speed up WP, but if you're delivering elephants then caching elephants can only do so much.

DimSum turns those big elephant images into ones that are small and quality-controlled. And it does so in real-time.

This plugin resizes all blog images (home, post and pages) to their tagged width and height, and changes the compression for a perfect balance between quality and speed as seen fit by you, the site owner. It makes elephants into bite-sized dimsum whenever possible.

Results include not only increased performance but also increased site aesthetics and ease of use.

A) Performance issues include faster page delivery. Speed increases range from minor to significant, depending on the mis-matching of underlying file to that image seen by the user in the browser. It's particularly useful for front pages that don't use official thumbnail features but embed smaller representations of larger images. Optimizing large content is often the best way of increasing page delivery. As a corollary, DimSum decreases bandwidth usage which is important for (cloud) users who pay per usage.

B) Ease of use improvements are significant. Use an image without regard for its size. Which means no endless sizing in Photoshop or GIMP. One image is resized to perfectly suit each different page use in real-time.
Moreso, don't rely on the few thumbnail sizes given to you by your theme. Generate any size you want without disturbing your site delivery time.
Save in the highest quality in Photoshop/Gimp. Upload and then downgrade in DimSum until a perfect tradeoff between quality and size is found. And, in WordPress, select the perfect size to match your content, using the 90%, 80%, 70%, 60% ... scaling in the post area.
Finally, within seconds crank up or down the compression quality setting to suit the number of visitors (and thus server load) your site receives to better address your customer's needs -- until you upgrade to a faster server. Because one drowned server can ruin your day.

C) In the aesthetic department, images are not stretched out beyond their viewable resolution. In fact, images are perfect WYSIWYG between image on the filesystem and image in the browser, in terms of resolution and size. Nevertheless, nothing can increase quality beyond that found in the original file, so use the highest quality in the first upload.

== Installation ==

1. Upload `klix-image-dimsum.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the Klix Image DimSum admin panel and enable it.
1. Flush your cache if needed.

== Frequently Asked Questions ==

= How do I verify that DimSum is working? =

One of the following tests will verify a complete run:

* Run your page, check the HTML source. Do a find/search for the string '.dimsum.' in the HTML source. 

* Turn on Debug in the DimSum options (Not WP_DEBUG in the wp-config.php file). A verbose output will follow in each page footer. *Warning:* And please note that this debug mode is public and it lists information such as directory names. 

= What are the system requirements? =

System requirements are few:

1. DimSum needs the PHP library, GD for image processing. It does a check for GD during installation. See http://www.php.net/manual/en/book.image.php. GD may need to be added to your site.
1. DimSum leans heavily on the Perl Compatible Regular Expression (PCRE) functions for parsing HTML text. That is usually included in installs.
1. DimSum needs Themes that use the 'width' and 'height' attribute in the image tag. It is rendered useless, without those two attributes. Make sure to include it when designing your theme.
ie. `<img src="image.jpg" width="200" height="150">`

= How much faster are DimSum pages =

Results will vary. We made the plugin for our site, and it decreased the home page size from 450KB to about 220KB under our favorite theme. -- with no visible difference at all. That move shaved off around 3 seconds of delivery time to the user. Add a caching plugin, and it got even better. We started at 7 seconds, dropped to under 4 seconds with DimSum and then dropped to under 2.7 seconds with a caching plugin.
DimSum savings are greatest with a large disparity between underlying image width/height and HTML width and height attributes.
Themes that use the original or largest image will see big savings, those that use thumbnail images will see less significant speed savings. Images on pages will vary, depending on the use of the 90%, 80%, 70% image scale in posts. I hope you know the interface im speaking of.

= What about the trade-offs =

DimSum conducts regular expression on an HTML page, searching for 'img' tags along with 'width=xx', 'height=yy', plucks those values out and spanks them. That lengthy process is followed by a string replace. All add up to an expensive operation.
Despite all that work, a significant time savings can be had when the disparity between original and optimized is great. Why is that. Because performance to user is different than server performance. More content means slower delivery. Remember the elephants.

But we strongly suggest using DimSum along with a caching plugin of some sort for optimal performance. Page caching will avoid that expensive regular expression operation per user. 

= Will DimSum work with caching plugins? =

Yes, but not always. It is compatible with various components of a caching plugin, but not all. WP is getting complex after all.

That's why we added a huge Debug log and we have added a 'Run level' option to help you find compatibility with your caching plugin. A low number runs early and the high number runs after other plugins.
Those two features help you find compatibility with your caching plugin. 
*Note:* DimSum should run after most other plugins, but before caching plugins. 

* WP Cache: It seems to be fully compatible.
* WP Total Cache: Incompatibilities appeared when using the extended disk paging. Basic paging seems compatible.
* WP Super Cache: Incompatibilities appeared but page caching was fine because of its use of WP Cache under the hood.

We suggest disabling caching, play around with DimSum, check out the different debug outputs, and get it stable. And then enable caching again.

Our site succesfully uses the following combination: DimSum, WP Cache and Keep-Alive and Max-Age for browser caching. We also 'prime' our site using a simple combo of wget and crontab.

= Will DimSum work with Google's mod_pagespeed. It seems redundant with the'rewrite_images' function? =

Yes, its works perfectly with mod_pagespeed. Most importantly, DimSum was designed for those users without no or incomplete control over server configurations. DimSum gives you better control over your shared server situation.

== Screenshots ==

1. None at this point.

== Changelog ==

= 1.0 =
* Initial version

== Upgrade Notice ==

Nothing to upgrade at this point.

