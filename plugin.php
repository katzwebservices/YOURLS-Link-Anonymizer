<?php
/*
Plugin Name: Link Anonymizer
Plugin URI: http://www.seodenver.com/link-anonymizer/
Description: Anonymously visit links in YOURLS, including referring sites and original URLs.
Version: 1.0
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com
*/

if(!function_exists('yourls_add_filter') || !defined('YOURLS_SITE')) { return; }

class KWS_Yourls_Anonymizer {

	public function __construct() {
		yourls_register_plugin_page( 'anonymizer', 'Anonymizer', array(&$this, 'admin_page'));
		yourls_add_filter( 'html_link', array(&$this, 'filter'), 999);
		yourls_add_action('html_footer', array(&$this, 'script'), 999);
	}

	public function script() {
		?>
		<style>
			a.anon {
				margin-left: .4em;
				height: 16px;
				width: 16px;
				display: inline-block;
				overflow: hidden;
				text-indent: -999px;
				opacity: .6;
				background: transparent url(<?php echo yourls_plugin_url( dirname( __FILE__ ) ); ?>/key_go_bw.png) left center no-repeat;
			}
			a.anon:hover {
				opacity: .9;
				background-image: url(<?php echo yourls_plugin_url( dirname( __FILE__ ) ); ?>/key_go.png);
			}
		</style>
		<script>
		$("#main_table td.url > a").after(function() {
			return $('<a class="anon" title="Visit this link anonymously">[anon]</a>').attr("href", '<?php echo $this->get_root(); ?>'+$(this).attr('href'));
		});
		</script>
		<?php
	}

	/**
	 * Filter the display of the URL in the admin.
	 */
	public function filter($link) {

		$root = $this->get_root();

		// Don't modify the internal links
		if(strpos($link, YOURLS_SITE)) { return $link; }

		if(preg_match('/https\:\/\//ism',$link)) {
			$anonlink = str_replace('href="https://', 'href="'.$root.'https://', $link);
		} else {
			$anonlink = str_replace('href="http://', 'href="'.$root.'http://', $link);
		}
			$anonlink = preg_replace('/(.*)>(.*?)\<\/a\>/ism', '$1 class="anon">[anon]</a>', $anonlink);

		return $link.' '.$anonlink;
	}

	/**
	 * Get the anonymizer service URL from the settings. If it's not set,
	 * use https://linkonym.appspot.com/
	 * @return string URL base for the anonymizer service.
	 */
	function get_root() {
		// Get value from database
        $root = yourls_get_option( 'anonymizer_root' );
        if(empty($root)) { $root = 'https://linkonym.appspot.com/?'; }
        return $root;
	}

	// Update option in database
	function update_option() {
	        if(isset($_POST['anonymizer_root'])) {
				yourls_update_option( 'anonymizer_root', yourls_sanitize_url($_POST['anonymizer_root']));
	        }
	}

	/**
	*
	* Display admin page
	*
	*/
	function admin_page() {
	        // Check if a form was submitted
	        if( isset( $_POST['anonymizer_root'] ) )
	                $this->update_option();

	        $root = $this->get_root();

	?>
		<style type="text/css">
			 .description { color:#555; font-style:italic; }
			 #ga_settings { font-size: 120%; }
			 #ga_settings h4 {
			 	margin-bottom: .25em;
			 }
			 .submit {
			 	margin-top: .5em;
			 	padding-top: .9em;
			 	border-top: 1px solid #ccc;
			 	padding-bottom: 1em;
			 }
			 .submit input {
			 	font-size: 14px!important;
			 }
			 #more_tracking_code_info {
			 	display: none;
			 	padding:5px 15px 5px 15px;
			 	background: #fcfcfc;
			 	border-top: 2px solid #ccc;
			 }
			 label.borderbottom {
			 	border-bottom: 1px dotted;
			 	cursor: pointer;
			 }
			 #anonymizer_root {display: block;}
			 .req { color: red; }
		</style>

		<p>Visit links anonymously in YOURLS by clicking the <img src="<?php echo yourls_plugin_url( dirname( __FILE__ ) ); ?>/key_go_bw.png" width="16" height="16" alt="[anon]" /> link next to a link.</p>

		<h2>Anonymizer Settings</h2>

		<form method="post">
		    <h4>Structure for anonymous links: </h4>
	         <p>The following will be placed before the outgoing link in the admin:
	         	<label for="anonymizer_root"><input type="text" id="anonymizer_root" name="anonymizer_root" value="<?php echo $root; ?>" size="60" /></label>
	         </p>
   	        <div style="clear:both"></div>
	        <div class="submit">
		        <input style="display:block;" type="submit" value="Update Settings">
	        </div>
	    </form>

   			 <h4>Here are some more anonymizer options:</h4>
			 <p>Note: these services are not affiliated with this plugin.</p>
	        <ul>
				<li><code>http://nullrefer.com/?</code></li>
				<li><code>http://blankrefer.com/?</code></li>
				<li><code>http://refhide.com/?</code></li>
				<li><code>http://anonym.to/?</code></li>
				<li><code>http://anonym2.com/?</code></li>
				<li><code>http://knil.ws/?url=</code></li>
			</ul>

	<?php
	}

}
$KWS_Yourls_Anonymizer = new KWS_Yourls_Anonymizer;