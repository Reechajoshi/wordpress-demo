<?php
/**
 * Footer
 * @package by Theme Record
 * @auther: MattMao
*/


#
#Theme load google analytics
#
function  theme_load_google_analytics() 
{	
	global $tr_config;

	if($tr_config['analytics']) { echo stripslashes($tr_config['analytics']) ."\n"; }
}

add_action('wp_footer', 'theme_load_google_analytics');


#
#Copyright
#
if ( !function_exists( 'theme_site_copyright' ) )
{

	function theme_site_copyright() 
	{
	global $tr_config;
	?>
	<?php if($tr_config['copyright']) : ?>
		<p><?php echo $tr_config['copyright']; ?></p>
	<?php endif; ?>
	<?php
	}

}



#
#Bottom Menu
#
function theme_bottom_wp_nav() 
{
	$args = array( 
		'container' => 'nav',
		'container_id' => 'bottom-menu', 
		'container_class' =>'bottom-menu-container', 
		'menu_class' => 'bottom-menu-class clearfix', 
		'theme_location' => 'bottom menu',
		'after' => '<span>&middot;</span>',
		'depth' => 1
	);
	wp_nav_menu($args); 
}



#
#Footer Widgets
#
if ( !function_exists( 'theme_footer_widgets' ) )
{

	function theme_footer_widgets() 
	{
		global $tr_config;

		$enable_footer_widgets = $tr_config['enable_widgets'];
		$columns = $tr_config['widgets_column'];
		$first_col = ' col-first';
		switch($columns)
		{
			case 1: $class = 'col-1-1'; break;
			case 2: $class = 'col-2-1'; break;
			case 3: $class = 'col-3-1'; break;
			case 4: $class = 'col-4-1'; break;
		}

		if($columns == '1')
		{
			if ( ! is_active_sidebar( 'footer-widget-area-1' ))
			return;
		}
		elseif($columns == '2')
		{
			if ( ! is_active_sidebar( 'footer-widget-area-1' )
				&& ! is_active_sidebar( 'footer-widget-area-2' )
			)
			return;
		}
		elseif($columns == '3')
		{
			if ( ! is_active_sidebar( 'footer-widget-area-1' )
				&& ! is_active_sidebar( 'footer-widget-area-2' )
				&& ! is_active_sidebar( 'footer-widget-area-3' )
			)
			return;
		}
		elseif($columns == '4')
		{
			if ( ! is_active_sidebar( 'footer-widget-area-1' )
				&& ! is_active_sidebar( 'footer-widget-area-2' )
				&& ! is_active_sidebar( 'footer-widget-area-3' )
				&& ! is_active_sidebar( 'footer-widget-area-4' )
			)
			return;
		}

		if($enable_footer_widgets == true)
		{
			echo '<div class="footer-widgets-area">'."\n";
			echo '<div class="col-width clearfix">'."\n";
			for ($i = 1; $i <= $columns; $i++)
			{
				echo '<div class="'.$class.$first_col.'">'."\n";
				dynamic_sidebar('footer-widget-area-'.$i);
				echo '</div>'."\n";
				$first_col = '';
			}
			echo '</div>'."\n";
			echo '</div><!--end-->'."\n";
		}
	}

}



#
#Footer Contact Info
#
if ( !function_exists( 'theme_footer_contact_info' ) )
{
	function theme_footer_contact_info() 
	{
	global $tr_config;
	?>
	<?php if($tr_config['enable_footer_contact_info'] == true) : ?>
	<div class="footer-contact-info">
	<ul class="clearfix col-width">
	<?php if($tr_config['footer_address']) : ?><li class="address"><?php echo $tr_config['footer_address']; ?></li><?php endif; ?>
	<?php if($tr_config['footer_phone']) : ?><li class="phone"><?php echo $tr_config['footer_phone']; ?></li><?php endif; ?>
	<?php if($tr_config['footer_email']) : ?><li class="email"><a href="mailto:<?php echo $tr_config['footer_email']; ?>"><?php echo $tr_config['footer_email']; ?></a></li><?php endif; ?>
	</ul>
	</div>
	<?php endif; ?>
	<?php
	}
}




#
#Social Networking
#
if ( !function_exists( 'theme_social_networking' ) )
{
	function theme_social_networking() 
	{
		global $tr_config;

		?>
		<div id="social-networking">
		<ul class="clearfix">
		<?php if($tr_config['twitter']): ?><li><a href="<?php echo $tr_config['twitter']; ?>" id="twitter" rel="external">twitter</a></li><?php endif; ?>
		<?php if($tr_config['facebook']): ?><li><a href="<?php echo $tr_config['facebook']; ?>" id="facebook" rel="external">facebook</a></li><?php endif; ?>
		<?php if($tr_config['dribbble']): ?><li><a href="<?php echo $tr_config['dribbble']; ?>" id="dribbble" rel="external">dribbble</a></li><?php endif; ?>
		<?php if($tr_config['flickr']): ?><li><a href="<?php echo $tr_config['flickr']; ?>" id="flickr" rel="external">flickr</a></li><?php endif; ?>
		<?php if($tr_config['linkedin']): ?><li><a href="<?php echo $tr_config['linkedin']; ?>" id="linkedin" rel="external">linkedin</a></li><?php endif; ?>
		<?php if($tr_config['google']): ?><li><a href="<?php echo $tr_config['google']; ?>" id="google" rel="external">google</a></li><?php endif; ?>
		<?php if($tr_config['vimeo']): ?><li><a href="<?php echo $tr_config['vimeo']; ?>" id="vimeo" rel="external">vimeo</a></li><?php endif; ?>
		<?php if($tr_config['picasa']): ?><li><a href="<?php echo $tr_config['picasa']; ?>" id="picasa" rel="external">picasa</a></li><?php endif; ?>
		<?php if($tr_config['feed']): ?><li><a href="<?php echo $tr_config['feed']; ?>" id="feed" rel="external">feed</a></li><?php endif; ?>
		</ul>
		</div>
		<?php
	}
}




#
#Theme load drop menu js
#
if ( !function_exists('theme_load_footer_js') )
{
	function  theme_load_footer_js() 
	{	
		echo '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&#038;"></script>'."\n";
	}

	add_action('wp_footer', 'theme_load_footer_js');
}

?>