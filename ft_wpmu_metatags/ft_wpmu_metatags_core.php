<?php
/*
Plugin Name: WPMU Custom Meta Tags
Plugin URI: http://fullthrottledevelopment.com/wpmu-meta-tags
Description: Allows you to control the meta description and meta keywords for the site as well as individual blogs
Author: Glenn Ansley for FullThrottle Development
Version: 1.0
Author URI: http://glennansley.com
License: GPL
*/ 

if ( !class_exists('FT_WPMU_MetaTags') ){
	Class FT_WPMU_MetaTags{
		
		var $blogName = "";
		var $blogKeywords = "";
		var $blogDescription = "";
		var $siteDesc = "";
		var $siteKeywords = "";
		
		//this method adds the meta tags to the head of the webpage
		function metaTags(){
			$this->blogName = get_option('blogname');
			$this->blogKeywords = htmlentities(stripslashes(get_option('ft_wpmu_metatags_blog_keywords')));
			$this->blogDescription = htmlentities(stripslashes(get_option('ft_wpmu_metatags_blog_description')));
			$this->siteKeywords = htmlentities(stripslashes(get_site_option('ft_wpmu_metatags_site_keywords')));
			$this->siteDescription = htmlentities(stripslashes(get_site_option('ft_wpmu_metatags_site_description')));
			$this->siteDescription = str_replace('%blog_name%',$this->blogName,$this->siteDescription);
			?>
			<meta name="description" content="<?php echo $this->blogDescription." ".$this->siteDescription;?>" />
			<meta name="keywords" content="<?php echo $this->siteKeywords;?>, <?php echo $this->blogKeywords;?>" />
			<?php
		}
		
		//this method adds the GUI for the site and blog admins to enter their keywords and descriptions.
		function menuLinks(){
			add_options_page('MetaTag Options', 'MetaTags', 1, plugin_basename(__FILE__), array( &$this, 'printMetaTagsPage' ) );
			add_submenu_page('wpmu-admin.php', 'Site MetaTags', 'Site MetaTags', 9, plugin_basename(__FILE__) , array( &$this , 'printMetaTagsPage') );
		}
		
		//this method prints the actual page
		function printMetaTagsPage(){
			if ( isset($_POST['update_site_metatags'] ) ){
				$ft_mu_metaTags = $this->updateSiteMetaTags();
			}elseif ( isset($_POST['update_blog_metatags'] ) ) {
				$ft_mu_metaTags = $this->updateBlogMetaTags();
			}
			?>
			<div class='wrap'>
				<h2>Meta Tag Options</h2>
				<?php
				if ( is_site_admin() && strpos( $_SERVER['REQUEST_URI'] , 'wpmu-admin.php' ) ){
					$this->siteDescription = get_site_option( 'ft_wpmu_metatags_site_description' );
					$this->siteKeywords = get_site_option( 'ft_wpmu_metatags_site_keywords' );
					if( $ft_mu_metaTags ){
						if( $ft_mu_metaTags['description'] ){ echo "<div id='message' class='error fade'>".$ft_mu_metaTags['description']."</div>";}
						if( $ft_mu_metaTags['keywords'] ){ echo "<div id='message' class='error fade'>".$ft_mu_metaTags['keywords']."</div>";}
 						if( $ft_mu_metaTags['success'] ){ echo "<div id='message' class='updated fade'>".$ft_mu_metaTags['success']."</div>";}
					}
					?>
					<p>The information entered below will appear as meta tags on every blog along with the individual blog administrator's settings.</p>
					<form name="siteMetaTags" action="" method="post">
						<p>
							Site Meta Description:<br />
							<textarea name="ft_wpmu_metatags_site_description" id="site_description" cols="50" rows="10"><?php echo htmlentities(stripslashes($this->siteDescription));?></textarea>
						</p>
						<p>	
							Site Meta Keywords (comma seperated):<br />
							<input type="text" name="ft_wpmu_metatags_site_keywords" id="site_keywords" size="52" value="<?php echo htmlentities(stripslashes($this->siteKeywords));?>"/>
						</p>
						<p class="submit">
							<input type="submit" name="update_site_metatags" id="submitbutton" value="submit" />
						</p>
					</form>
					
					<?php
				}else{
					$this->blogDescription = get_option( 'ft_wpmu_metatags_blog_description' );
					$this->blogKeywords = get_option( 'ft_wpmu_metatags_blog_keywords' );
					if( $ft_mu_metaTags ){
						if( $ft_mu_metaTags['description'] ){ echo "<div id='message' class='error fade'>".$ft_mu_metaTags['description']."</div>";}
						if( $ft_mu_metaTags['keywords'] ){ echo "<div id='message' class='error fade'>".$ft_mu_metaTags['keywords']."</div>";}
 						if( $ft_mu_metaTags['success'] ){ echo "<div id='message' class='updated fade'>".$ft_mu_metaTags['success']."</div>";}
					}
					?>
					<p>The information entered below will appear as meta tags. It helps search engines to index your site more efficently.</p>
					<form name="blogMetaTags" action="" method="post">
						<p>
							Blog Meta Description:<br />
							<textarea name="ft_wpmu_metatags_blog_description" id="blog_description" cols="50" rows="10"><?php echo htmlentities(stripslashes($this->blogDescription));?></textarea>
						</p>
						<p>	
							Blog Meta Keywords (comma seperated):<br />
							<input type="text" name="ft_wpmu_metatags_blog_keywords" id="blog_keywords" size="52" value="<?php echo htmlentities(stripslashes($this->blogKeywords));?>"/>
						</p>
						<p class="submit">
							<input type="submit" name="update_blog_metatags" id="submitbutton" value="submit" />
						</p>
					</form>
				<?php
				}
				?>
			</div>
			<?php
		}
		
		//this method process the form and updates the tables for the Site Meta Tags
		function updateSiteMetaTags(){
			global $wpdb;
			$error = FALSE;
			if ( !isset($_POST['ft_wpmu_metatags_site_description'] ) || $_POST['ft_wpmu_metatags_site_description'] == '' ){
				$error = TRUE;
				$return['description'] = 'Meta description cannot be left blank';
			}else{
				update_site_option('ft_wpmu_metatags_site_description', $_POST['ft_wpmu_metatags_site_description']);
			}
			
			if ( !isset($_POST['ft_wpmu_metatags_site_keywords'] ) || $_POST['ft_wpmu_metatags_site_keywords'] == '' ){
				$error = TRUE;
				$return['keywords'] = 'Meta keywords cannot be left blank';
			}else{
				update_site_option('ft_wpmu_metatags_site_keywords', $_POST['ft_wpmu_metatags_site_keywords']);
			}
			
			if( $error == FALSE ){ $return['success'] = 'MetaTags Updated';}
			
			return $return;
			
		} 

		//this method process the form and updates the tables for the  Blog Meta Tags
		function updateBlogMetaTags(){
			global $wpdb;
			$error = FALSE;
			if ( !isset($_POST['ft_wpmu_metatags_blog_description'] ) || $_POST['ft_wpmu_metatags_blog_description'] == '' ){
				$error = TRUE;
				$return['description'] = 'Meta description cannot be left blank';
			}else{
				update_option('ft_wpmu_metatags_blog_description', $_POST['ft_wpmu_metatags_blog_description']);
			}
			
			if ( !isset($_POST['ft_wpmu_metatags_blog_keywords'] ) || $_POST['ft_wpmu_metatags_blog_keywords'] == '' ){
				$error = TRUE;
				$return['keywords'] = 'Meta keywords cannot be left blank';
			}else{
				update_option('ft_wpmu_metatags_blog_keywords', $_POST['ft_wpmu_metatags_blog_keywords']);
			}
			
			if( $error == FALSE ){ $return['success'] = 'MetaTags Updated';}
			
			return $return;
			
		}
	}
}

if ( class_exists( 'FT_WPMU_MetaTags' ) ){
	if ( !isset($ft_wpmu_MetaTags) ) {
		$ft_wpmu_metatags = New FT_WPMU_MetaTags();
	}
	
	if ( isset($ft_wpmu_metatags) ) {
		add_action( 'wp_head' , array( &$ft_wpmu_metatags, 'metaTags') );
		add_action( 'admin_menu' , array( &$ft_wpmu_metatags, 'menuLinks' ) );
	}
}

?>