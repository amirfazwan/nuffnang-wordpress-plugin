<?php
/*
Plugin Name: Nuffnang
Plugin URI: http://zeo.unic.net.my/notes/nuffnang-wordpress-plugin/
Description: Allows you to add <a href="http://www.nuffnang.com.my">Nuffnang</a>'s Ad Units into your blog. Configure from <a href="plugins.php?page=nuffnang.php">Nuffnang Configuration</a> page. To use, add <code>&lt;?php if (function_exists('nuffnang')) nuffnang('type', 'echo'); ?&gt;</code> in your template.
Version: 2.0
Author: Safirul Alredha
Author URI: http://zeo.unic.net.my
License: GPL
*/

$nuffnang_bid = get_option('nuffnang_bid');

function nuffnang_header() {
	global $nuffnang_bid;
	
	if ( empty($nuffnang_bid) )
		return;
		
	echo '<script type="text/javascript"> nuffnang_bid = "' . $nuffnang_bid . '"; </script>';
}

add_action('wp_head','nuffnang_header');

function nuffnang($type='', $echo = 1) {
	global $nuffnang_bid;
	
	if ( empty($nuffnang_bid) )
		return;
	
		switch($type) {
			case 'leaderboard':
				$output .= "lb.js";
				$format .= 'leaderboard';
				break;
			case 'rectangle':
				$output .= "lr.js";
				$format .= 'rectangle';
				break;
			case 'skyscraper':
			default:
				$output .= "ss.js";
				$format .= 'skyscraper';
				break;		
		}
	
		$ads = '
		<!-- nuffnang -->
		<div class="nuffnang-'. $format .'">
		<script type="text/javascript" src="http://synad2.nuffnang.com.my/' . $output .'"></script>
		</div>
		<!-- nuffnang -->
		';
	
		if ($echo)
			echo $ads;
	
		return $ads;	
}

function nuffnang_add_to_content($content) {
	global $post, $posts;
	if ( is_single() || $post == $posts[0] && !is_page() && !is_feed() ) {
		$content .= nuffnang('rectangle', 0);
	} 
	return $content;
}

if ( get_option('nuffnang_addtocontent') == '1' )
	add_filter('the_content', 'nuffnang_add_to_content');

function widget_nuffnang_init() {
	global $nuffnang_bid;
	
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') || empty($nuffnang_bid) )
		return;

	function widget_nuffnang($args) {
		extract($args);

		$options = get_option('widget_nuffnang');
		$title = $options['title'];
		
		echo $before_widget . $before_title . $title . $after_title;
		nuffnang();
		echo $after_widget;
	}

	function widget_nuffnang_control() {
		$options = $newoptions = get_option('widget_nuffnang');
				
		if ( $_POST['nuffnang-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['nuffnang-title']));
		}
		
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_nuffnang', $options);
		}	

		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		echo '<p style="text-align:right;"><label for="nuffnang-title">' . __('Title:') . ' <input style="width: 250px;" id="nuffnang-title" name="nuffnang-title" type="text" value="' . $title . '" /></label></p>
		<input type="hidden" id="nuffnang-submit" name="nuffnang-submit" value="1" />';
	}		

	register_sidebar_widget('Nuffnang', 'widget_nuffnang');
	register_widget_control('Nuffnang', 'widget_nuffnang_control', 300, 100);
}

function nuffnang_conf() {
 	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));
			
		$nuffnang_bid = strip_tags(stripslashes($_POST['nuffnang_bid']));
			
		update_option('nuffnang_bid', $nuffnang_bid);
		
		if ( isset($_POST['nuffnang_addtocontent']) )
			update_option('nuffnang_addtocontent', '1');
		else
			update_option('nuffnang_addtocontent', '0');
				
		echo "<div id='message' class='updated fade'><p><strong>" . __('Options saved.') . "</strong></p></div>";
	}
	?>
		<form action="" method="post">
		<div class="wrap">
			<h2><?php _e('Nuffnang Options'); ?></h2>
			<p class="submit" style="float:right">
				<input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" />
			</p>
			<fieldset class='options'>
				<label for="nuffnang_bid"><?php _e('Your <code>nuffnang_bid</code>:'); ?></label>
					<input type="text" size="40" name="nuffnang_bid" id="nuffnang_bid" value="<?php echo get_option('nuffnang_bid'); ?>" />
					<p><?php _e('<code>nuffnang_bid</code> can be found at Nuffnang\'s <a href="https://www.nuffnang.com.my/blogger/add_ad.php" target="_blank">Add Ads</a> page.'); ?></p>
					<p><label><input name="nuffnang_addtocontent" id="nuffnang_addtocontent" value="1" type="checkbox" <?php if ( get_option('nuffnang_addtocontent') == '1' ) echo ' checked="checked" '; ?> /> <?php _e('Automatically insert Nuffnang\'s <strong>Large Rectangle</strong> Ad unit after post.'); ?></label></p>						
					<h3><?php _e('Usage:'); ?></h3>
					<p><code>&lt;?php if (function_exists('nuffnang')) nuffnang('type', 'echo'); ?&gt;</code></p>
					<h3>Examples:</h3>
					<p><code>&lt;?php if (function_exists('nuffnang')) nuffnang('leaderboard'); ?&gt;</code></p>
					<h3><?php _e('Parameters:'); ?></h3>
					<dl>
						<dt><code>type</code></dt>
						<dd><em>(string)</em>
							<ul>
								<li><code>'leaderboard'</code></li>
								<li><code>'rectangle'</code></li>
								<li><code>'skyscraper'</code> (<?php _e('Default'); ?>)</li>
							</ul>
						</dd>
						<dt><code>echo</code></dt>
						<dd><em>(boolean)</em> <?php _e('Display (<code>TRUE</code>) or return them for use by PHP (<code>FALSE</code>). Defaults to <code>TRUE</code>.'); ?>
							<ul>
								<li><code>1</code> (<code>TRUE</code> - <?php _e('Default'); ?>)</li>
								<li><code>0</code> (<code>FALSE</code>)</li>
							</ul>
						</dd>
					</dl>
					<h3><?php _e('CSS Selectors:'); ?></h3>
					<dl>
						<dt>Leaderboard (728 x 90)</dt>
						<dd><code>.nuffnang-leaderboard { ... }</code></dd>
						<dt>Large Rectangle (336X280)</dt>
						<dd><code>.nuffnang-rectangle { ... }</code></dd>
						<dt>Skyscraper (160X900)</dt>
						<dd><code>.nuffnang-skyscraper { ... }</code></dd>
					</dl>
			</fieldset>	
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" />
			</p>
		</div>
		</form>
<?php
}

function nuffnang_config_page() {
	if ( function_exists('add_submenu_page') ) {
		add_submenu_page('plugins.php', __('Nuffnang Configuration'), __('Nuffnang Configuration'), 'manage_options', basename(__FILE__), 'nuffnang_conf');
	}
}

add_action('admin_menu', 'nuffnang_config_page'); 
add_action('init', 'widget_nuffnang_init');
?>