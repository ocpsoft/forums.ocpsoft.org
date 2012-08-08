<?php
/*
Plugin Name: Google Analytics
Plugin URI: http://boakes.org/analytics
Description: This plugin makes it simple to add Google Analytics to your WordPress blog and/or your BBPress forum. 
Author: Rich Boakes
Version: 0.68
Author URI: http://boakes.org
License: GPL

0.30 - * added external link tracking
0.40 - * added code comments and re-jigged so
         others can more easily contribute
	    * added version comment
		 * outbound comment-author links are
		   also now tracked
0.50 - * Switched to a format which should
         behave nicely when run under PHP5 -
			testing by People with PHP5 is needed!
0.51 - * Changed javaScript to use onclick (not
         onClick) for validation purposes.
0.60 - * Now works with bbPress - just set the UAString
         to the one that appears in your analytics
			script.
0.61 - * If the plugin is installed in wordpress AND you
         have BBPress configured to load wordpress, then
         the plugin will (when also installed in bbpress)
         use the UAString from the WPDB.  If you want to use
         a different key, set $wp_uastring_takes_precedence
         to false;
0.65 - * Inclusion of UDN based on $_SERVER['HTTP_HOST']
         for WPMU goodness - set $includeUDN to true to enable
			this capability.
0.66 - * Moved the script to wp_footer so page loads are not
         dependent on the speed of the google server.
0.67 - * Security fix for multiple author blogs.
0.68 - * Allow longer UA strings. 
*/

$uastring = "UA-5083245-1";
$wp_uastring_takes_precedence = true;
$includeUDN = false;

/*
 * Admin User Interface
 */
if ( ! class_exists( 'GA_Admin' ) ) {

	class GA_Admin {

		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_submenu_page('plugins.php', 'Google Analytics Configuration', 'Google Analytics', 1, basename(__FILE__), array('GA_Admin','config_page'));
			}
		} // end add_GA_config_page()

		function config_page() {
			global $uastring;
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the UA string.'));
				check_admin_referer();
				$uastring = $_POST['uastring'];
				update_option('analytics_uastring', $uastring);
			}
			$mulch = ($uastring=""?"##-#####-#":$uastring);
	
			?>
			<div class="wrap">
				<h2>Google Analytics Configuration</h2>
				<p>Google Analytics is a statistics service provided
					free of charge by Google.  This plugin simplifies
					the process of including the <em>basic</em> Google
					Analytics code in your blog, so you don't have to
					edit any PHP. If you don't have a Google Analytics
					account yet, you can get one at 
					<a href="https://www.google.com/analytics/home/">analytics.google.com</a>.</p>

				<p>In the Google interface, when you "Add Website 
					Profile" you are shown a piece of JavaScript that
					you are told to insert into the page, in that script is a 
					unique string that identifies the website you 
					just defined, that is your User Account string
					(it's shown in <strong>bold</strong> in the example below).</p>
				<tt>&lt;script src="http://www.google-analytics.com/urchin.js" type="text/javascript"&gt;<br />&lt;/script&gt;<br />&lt;script type="text/javascript"&gt;<br />_uacct = "<strong><?php echo($mulch);?></strong>";<br />urchinTracker();<br />&lt;/script&gt;<br /></tt>

				<p>Once you have entered your User Account String in
				   the box below your pages will be trackable by
					Google Analytics.</p>
				
				<form action="" method="post" id="analytics-conf" style="margin: auto; width: 25em; ">
					<h3><label for="uastring">Analytics User Account</label></h3>
					<p><input id="uastring" name="uastring" type="text" size="20" maxlength="40" value="<?php echo get_option('analytics_uastring'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /></p>
					<p class="submit"><input type="submit" name="submit" value="Update UA String &raquo;" /></p>
				</form>

			</div>
			<?php
			$opt = get_option('analytics_uastring');
			if (isset($opt)) {
				if ($opt == "") {
					add_action('admin_footer', array('GA_Admin','warning'));
				} else {
					if (isset($_POST['submit'])) {
						add_action('admin_footer', array('GA_Admin','success'));
					}
				}
			} else {
				add_action('admin_footer', array('GA_Admin','warning'));
			}

		} // end config_page()

		function success() {
			echo "
			<div id='analytics-warning' class='updated fade-ff0000'><p><strong>Congratulations! You have just activated Google Analytics - take a look at the source of your blog pages and search for the word 'urchin' to see how your pages have been affected.</p></div>
			<style type='text/css'>
			#adminmenu { margin-bottom: 7em; }
			#analytics-warning { position: absolute; top: 7em; }
			</style>";
		} // end analytics_warning()

		function warning() {
			echo "
			<div id='analytics-warning' class='updated fade-ff0000'><p><strong>Google Analytics is not active.</strong> You must <a href='plugins.php?page=googleanalytics.php'>enter your UA String</a> for it to work.</p></div>
			<style type='text/css'>
			#adminmenu { margin-bottom: 6em; }
			#analytics-warning { position: absolute; top: 7em; }
			</style>";
		} // end analytics_warning()

	} // end class GA_Admin

} //endif


/**
 * Code that actually inserts stuff into pages.
 */
if ( ! class_exists( 'GA_Filter' ) ) {
	class GA_Filter {

		function analytics_cats() {
      	global $dir, $post;
		      foreach (get_the_category($post->ID) as $cat) {
      		 	$profile = get_option('analytics_'.$cat->category_nicename);
		         if ($profile != "") {
						return $profile;
					}
      		}
			return '';
		} //end analytics_cats()

		function spool_analytics() {
			global $uastring, $post, $version;

			// check if there's a post level profile
			// and if so, use it.
			if (function_exists("get_post_meta")) {
				$ua = get_post_meta($post->ID, $uakey);
				if ($ua[0] != "") {
					GA_Filter::spool_this($ua);
					return;
				}
			}
			
			// use the default channel if there is 
			if ($uastring != "") {
				GA_Filter::spool_this($uastring);
				return;
			}

			// if we get here there is a problem
			echo("<!-- The plugin is enabled but no channel account number is available. -->\n");
		} // end spool_analytics()

		/*
		 * Insert the tracking code into the page
		 */
		function spool_this($ua) {
			global $version, $includeUDN;

			echo "<script type=\"text/javascript\">";
			echo "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");";
			echo "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));";
			echo "</script>";
			echo "<script type=\"text/javascript\"> try { ";
			echo "var pageTracker = _gat._getTracker('" .$ua. "'); pageTracker._trackPageview(); } catch(err) {}";
			echo "</script>";
		}

		/* Create an array which contians:
		 * "domain" e.g. boakes.org
		 * "host" e.g. store.boakes.org
		 */
		function ga_get_domain($uri){

			$hostPattern = "/^(http:\/\/)?([^\/]+)/i";
			$domainPattern = "/[^\.\/]+\.[^\.\/]+$/";

			preg_match($hostPattern, $uri, $matches);
			$host = $matches[2];
			preg_match($domainPattern, $host, $matches);
			return array("domain"=>$matches[0],"host"=>$host);    

		}

		/* Take the result of parsing an HTML anchor ($matches)
		 * and from that, extract the target domain.  If the 
		 * target is not local, then when the anchor is re-written
		 * then an urchinTracker call is added.
		 *
		 * the format of the outbound link is definedin the $leaf
		 * variable which must begin with a / and which may 
		 * contain multiple path levels:
		 * e.g. /outbound/x/y/z 
		 * or which may be just "/"
		 *
		 */
		function ga_parse_link($leaf, $matches){
			global $origin ;
			$target = GA_Filter::ga_get_domain($matches[3]);
			$coolbit = "";
			if ( $target["domain"] != $origin["domain"]  ){
				$coolBit .= "onclick=\"javascript:urchinTracker ('".$leaf."/".$target["host"]."');\"";
			} 
			return '<a href="' . $matches[2] . '//' . $matches[3] . '"' . $matches[1] . $matches[4] . ' '.$coolBit.'>' . $matches[5] . '</a>';    
		}

		function ga_parse_article_link($matches){
			return GA_Filter::ga_parse_link("/outbound/article",$matches);
		}

		function ga_parse_comment_link($matches){
			return GA_Filter::ga_parse_link("/outbound/comment",$matches);
		}

		function the_content($text) {
			static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
			$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_article_link'),$text);
			return $text;
		}

		function comment_text($text) {
			static $anchorPattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
			$text = preg_replace_callback($anchorPattern,array('GA_Filter','ga_parse_comment_link'),$text);
			return $text;
		}

		function comment_author_link($text) {
	
			static $anchorPattern = '(.*href\s*=\s*)[\"\']*(.*)[\"\'] (.*)';
			ereg($anchorPattern, $text, $matches);
			if ($matches[2] == "") return $text;
	
			$target = GA_Filter::ga_get_domain($matches[2]);
			$coolbit = "";
			$origin = GA_Filter::ga_get_domain($_SERVER["HTTP_HOST"]);
			if ( $target["domain"] != $origin["domain"]  ){
				$coolBit .= " onclick=\"javascript:urchinTracker('/outbound/commentauthor/".$target["host"]."');\" ";
			} 
			return $matches[1] . "\"" . $matches[2] . "\"" . $coolBit . $matches[3];    
		}
	} // class GA_Filter
} // endif

$version = "0.61";
$uakey = "analytics";

if (function_exists("get_option")) {
	if ($wp_uastring_takes_precedence) {
		$uastring = get_option('analytics_uastring');
	}
} 

$mulch = ($uastring=""?"##-#####-#":$uastring);
$gaf = new GA_Filter();
$origin = $gaf->ga_get_domain($_SERVER["HTTP_HOST"]);

if (!function_exists("add_GA_config_page")) {
} //endif

// adds the menu item to the admin interface
add_action('admin_menu', array('GA_Admin','add_config_page'));

// adds the footer so the javascript is loaded
add_action('wp_footer', array('GA_Filter','spool_analytics'));
// adds the footer so the javascript is loaded
add_action('bb_foot', array('GA_Filter','spool_analytics'));

// filters alter the existing content
add_filter('the_content', array('GA_Filter','the_content'), 99);
add_filter('the_excerpt', array('GA_Filter','the_content'), 99);
add_filter('comment_text', array('GA_Filter','comment_text'), 99);
add_filter('get_comment_author_link', array('GA_Filter','comment_author_link'), 99);

?>
