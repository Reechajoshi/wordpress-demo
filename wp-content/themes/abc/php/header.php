<?php
/**
 * @package by Theme Record
 * @auther: MattMao
*/

if(!is_admin())
{
	add_action('wp_print_styles', 'theme_load_styles');
	add_action('wp_print_scripts', 'theme_load_scripts');
}


#
#Add classes for body
#
function body_layout_class($classes) 
{
	$front_page = get_option('show_on_front');

	if(is_front_page() && $front_page == 'page')
	{
		$classes[] = 'template-front-page'; 
	}
	elseif(is_home() && $front_page == 'posts')
	{
		$classes[] = 'template-front-posts'; 
	}

	return $classes;
}

add_filter('body_class','body_layout_class');



#
# Styles For The Front
#
function  theme_load_styles() 
{	
	global $tr_config;
	$enable_responsive = $tr_config['enable_responsive'];
	$favicon = $tr_config['favicon'];
	$site_name_family = $tr_config['site_name_family'];
	$menu_family = $tr_config['menu_family'];
	$hgroup_family = $tr_config['hgroup_family'];
	$breadcrumbs_family = $tr_config['breadcrumbs_family'];
	$page_header_family = $tr_config['page_header_family'];
	$meta_family = $tr_config['meta_family'];
	$slogan_family = $tr_config['slogan_family'];
	$price_family = $tr_config['price_family'];
	$read_more_family = $tr_config['read_more_family'];
	$pagination_family = $tr_config['pagination_family'];
	$form_family = $tr_config['form_family'];
	$copyright_family = $tr_config['copyright_family'];
	$google_apis = $tr_config['google_apis'];

	if($site_name_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$site_name_family.'" type="text/css" />',"\n"; }
	if($menu_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$menu_family.'" type="text/css" />',"\n"; }
	if($hgroup_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$hgroup_family.'" type="text/css" />',"\n"; }
	if($breadcrumbs_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$breadcrumbs_family.'" type="text/css" />',"\n"; }
	if($page_header_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$page_header_family.'" type="text/css" />',"\n"; }
	if($meta_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$meta_family.'" type="text/css" />',"\n"; }
	if($slogan_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$slogan_family.'" type="text/css" />',"\n"; }
	if($price_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$price_family.'" type="text/css" />',"\n"; }
	if($read_more_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$read_more_family.'" type="text/css" />',"\n"; }
	if($pagination_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$pagination_family.'" type="text/css" />',"\n"; }
	if($form_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$form_family.'" type="text/css" />',"\n"; }
	if($copyright_family != 'disabled') { echo '<link rel="stylesheet"  href="http://fonts.googleapis.com/css?family='.$copyright_family.'" type="text/css" />',"\n"; }
	if($google_apis) { echo $google_apis."\n"; }
	if($favicon) { echo '<link rel="shortcut icon" href="'.$favicon.'" />',"\n"; }

	wp_register_style('style', THEME_URI.'/style.css', false, THEME_VERSION, 'screen');
	wp_register_style('shortcode', ASSETS_URI.'/css/shortcode.css', false, THEME_VERSION, 'screen');
	wp_register_style('widget', ASSETS_URI.'/css/widget.css', false, THEME_VERSION, 'screen');
	wp_register_style('flexslider', ASSETS_URI.'/css/flexslider.css', false, THEME_VERSION, 'screen');
	wp_register_style('fancybox', ASSETS_URI.'/css/fancybox.css', false, THEME_VERSION, 'screen');
	wp_register_style('video-js', ASSETS_URI.'/css/video-js.css', false, THEME_VERSION, 'screen');
	wp_register_style('audio-player', ASSETS_URI.'/css/audio-player.css', false, THEME_VERSION, 'screen');
	wp_register_style('responsive', ASSETS_URI.'/css/responsive.css', false, THEME_VERSION, 'screen');
	wp_enqueue_style('style');
	wp_enqueue_style('shortcode');
	wp_enqueue_style('widget');
	wp_enqueue_style('flexslider');
	wp_enqueue_style('fancybox');
	wp_enqueue_style('video-js');
	wp_enqueue_style('audio-player');
	if($enable_responsive == 'yes') 
	{ 
		wp_enqueue_style('responsive'); 
	}
}




#
# JavaSrcipts For The Front
#
function  theme_load_scripts() 
{
	global $tr_config;
	$enable_responsive = $tr_config['enable_responsive'];
	$button_format_bg_color = $tr_config['button_format_bg_color'];
	$button_format_hover_bg_color = $tr_config['button_format_hover_bg_color'];
	$button_read_more_bg_color = $tr_config['button_read_more_bg_color'];
	$button_read_more_hover_bg_color = $tr_config['button_read_more_hover_bg_color'];
	$button_pagenation_bg_color = $tr_config['button_pagenation_bg_color'];
	$button_pagenation_hover_bg_color = $tr_config['button_pagenation_hover_bg_color'];
	$button_submit_bg_color = $tr_config['button_submit_bg_color'];
	$button_submit_hover_bg_color = $tr_config['button_submit_hover_bg_color'];
	$footer_copyright_icon_bg_color = $tr_config['footer_copyright_icon_bg_color'];

	$speed = $tr_config['slideshow_speed'];
	$duration = $tr_config['slideshow_duration'];
	$animation = $tr_config['slideshow_animation'];
	$auto_show = $tr_config['enable_slideshow_auto'];
	$direction_nav = $tr_config['enable_slideshow_directionnav'];
	$control_nav = $tr_config['enable_slideshow_controlnav'];
	$pause_play = $tr_config['enable_slideshow_pauseplay'];


	echo "<script type='text/javascript'>"."\n";
	echo "/* <![CDATA[ */"."\n";
	echo "var globalVars = {"."\n";
	echo "	'post_format_bgcolor':'".$button_format_bg_color."',"."\n";
	echo "	'post_format_hover_bgcolor':'".$button_format_hover_bg_color."',"."\n";
	echo "	'read_more_bgcolor':'".$button_read_more_bg_color."',"."\n";
	echo "	'read_more_hover_bgcolor':'".$button_read_more_hover_bg_color."',"."\n";
	echo "	'single_post_pagenation_bgcolor':'".$button_pagenation_bg_color."',"."\n";
	echo "	'single_post_pagenation_hover_bgcolor':'".$button_pagenation_hover_bg_color."',"."\n";
	echo "	'comment_submit_bgcolor':'".$button_submit_bg_color."',"."\n";
	echo "	'comment_submit_hover_bgcolor':'".$button_submit_hover_bg_color."',"."\n";
	echo "	'send_message_bgcolor':'".$button_submit_bg_color."',"."\n";
	echo "	'send_message_hover_bgcolor':'".$button_submit_hover_bg_color."',"."\n";
	echo "	'footer_media_bgcolor':'".$footer_copyright_icon_bg_color."',"."\n";
	echo "	'slideshow_speed':'".$speed."',"."\n";
	echo "	'slideshow_duration':'".$duration."',"."\n";
	echo "	'slideshow_animation':'".$animation."',"."\n";
	echo "	'slideshow_auto_show':".$auto_show.","."\n";
	echo "	'slideshow_direction_nav':".$direction_nav.","."\n";
	echo "	'slideshow_control_nav':".$control_nav.","."\n";
	echo "	'slideshow_pause_play':".$pause_play.""."\n";
	echo "};"."\n";
	echo "/* ]]> */"."\n";
	echo "</script>"."\n";

	wp_register_script( 'jquery-min-1.7.1', ASSETS_URI. '/js/jquery-1.7.1.min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-html5', ASSETS_URI. '/js/jquery-html5-min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-respond', ASSETS_URI. '/js/jquery-respond.min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-easing', ASSETS_URI. '/js/jquery-easing.js', false, THEME_VERSION );
	wp_register_script( 'jquery-cookie', ASSETS_URI. '/js/jquery-cookie.js', false, THEME_VERSION );
	wp_register_script( 'jquery-mobilemenu', ASSETS_URI. '/js/jquery-mobilemenu.js', false, THEME_VERSION );
	wp_register_script( 'jquery-animate-colors', ASSETS_URI. '/js/jquery-animate-colors-min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-ddsmoothmenu', ASSETS_URI. '/js/jquery-ddsmoothmenu.js', false, THEME_VERSION );
	wp_register_script( 'jquery-lavalamp', ASSETS_URI. '/js/jquery-lavalamp.js', false, THEME_VERSION );
	wp_register_script( 'jquery-quicksand', ASSETS_URI. '/js/jquery-quicksand.js', false, THEME_VERSION );
	wp_register_script( 'jquery-flexslider', ASSETS_URI. '/js/jquery-flexslider-min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-fancybox', ASSETS_URI. '/js/jquery-fancybox.js', false, THEME_VERSION );
	wp_register_script( 'jquery-jcarousel', ASSETS_URI. '/js/jquery-jcarousel-min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-placeholder', ASSETS_URI. '/js/jquery-placeholder.js', false, THEME_VERSION );
	wp_register_script( 'jquery-video', ASSETS_URI. '/js/jquery-video.min.js', false, THEME_VERSION );
	wp_register_script( 'jquery-audio', ASSETS_URI. '/js/jquery-audioplayer.js', false, THEME_VERSION );
	wp_register_script( 'template', ASSETS_URI. '/js/template.js', false, THEME_VERSION );
	wp_deregister_script( 'jquery' );
	wp_enqueue_script('jquery-min-1.7.1');
	wp_enqueue_script('jquery-html5');
	wp_enqueue_script('jquery-respond');
	wp_enqueue_script('jquery-easing');
	wp_enqueue_script('jquery-cookie');
	if($enable_responsive == 'yes') 
	{
		wp_enqueue_script('jquery-mobilemenu');
	}
	wp_enqueue_script('jquery-animate-colors');
	wp_enqueue_script('jquery-ddsmoothmenu');
	wp_enqueue_script('jquery-lavalamp');
	wp_enqueue_script('jquery-quicksand');
	wp_enqueue_script('jquery-flexslider');
	wp_enqueue_script('jquery-fancybox');
	wp_enqueue_script('jquery-jcarousel');
	wp_enqueue_script('jquery-placeholder');
	wp_enqueue_script('jquery-video');
	wp_enqueue_script('jquery-audio');
	wp_enqueue_script('template');
	if ( is_singular() && !is_front_page() && get_option( 'thread_comments' ) == true ) 
	{ 
		wp_enqueue_script( 'comment-reply' ); 
	}
}



#
#Top Menu
#
function theme_top_wp_nav() 
{
	global $tr_config;

	$args = array( 
		'container' => 'nav',
		'container_id' => 'top-menu', 
		'container_class' =>'ddsmoothmenu', 
		'menu_class' => 'drop-menu',
		'fallback_cb' => 'theme_top_wp_page', 
		'theme_location' => 'top menu',
		'walker' => new theme_description_walker(),
		'depth' => $tr_config['depth']
	);
	wp_nav_menu($args); 
}


#
#Top Page Menu
#
function theme_top_wp_page() 
{
	global $tr_config;

	$args = array(
		'title_li' => '0',
		'sort_column' => 'menu_order',
		'link_before'  => '<strong>',
		'link_after'   => '</strong>',
		'depth' => $tr_config['depth']
	);

	echo '<nav id="top-menu" class="ddsmoothmenu">'."\n";
	echo '<ul class="drop-menu">'."\n";
	wp_list_pages($args); 
	echo '</ul>'."\n";
	echo '</nav>'."\n";
}


#
# Description Walker
#
class theme_description_walker extends Walker_Nav_Menu
{
      function start_el(&$output, $item, $depth, $args)
      {
          //maximum length of description: increase if you want to allow longer description texts
	     $maxlength = 60;
	     
	     $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	
	     $class_names = $value = '';
	
	     $classes = empty( $item->classes ) ? array() : (array) $item->classes;
	
	     $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
	     $class_names = ' class="' . esc_attr( $class_names ) . '"';
	
	     $output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';
	
	     $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	     $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	     $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	     $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		 $attributes .= !empty( $item->description ) && strlen( $item->description ) <= $maxlength ? ' data-description="' . esc_attr( $item->description ) .'"' : '';	
		 
		 $prepend = '<strong>';
	     $append = '</strong>';

	     if($depth != 0)
	     {
	     	$description = $append = $prepend = "";
	     }
	
	     $item_output = $args->before;
	     $item_output .= '<a'. $attributes .'>';
	     $item_output .= $args->link_before.$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append.$args->link_after;
	     $item_output .= '</a>';
	     $item_output .= $args->after;
	
	     $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
}


/*
#
# Description Walker
#
class theme_description_walker extends Walker_Nav_Menu
{
      function start_el(&$output, $item, $depth, $args)
      {
          //maximum length of description: increase if you want to allow longer description texts
	     $maxlength = 60;
	     
	     $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	
	     $class_names = $value = '';
	
	     $classes = empty( $item->classes ) ? array() : (array) $item->classes;
	
	     $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
	     $class_names = ' class="' . esc_attr( $class_names ) . '"';
	
	     $output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';
	
	     $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	     $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	     $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	     $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
	     
	     
	     $prepend = '<strong>';
	     $append = '</strong>';
	     $description  = "";
	     if(! empty( $item->description ) )
	     {
	     	if(strlen($item->description) < $maxlength)
	     	{
	     		$description  = '<span class="primary-menu-description">'.esc_attr( $item->description ).'</span>';
	     	}
	     }
	     
	     
	     if($depth != 0)
	     {
	     	$description = $append = $prepend = "";
	     }
	    
	
	     $item_output = $args->before;
	     $item_output .= '<a'. $attributes .'>';
	     $item_output .= $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
	     $item_output .= $description.$args->link_after;
	     $item_output .= '</a>';
	     $item_output .= $args->after;
	
	     $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
}
*/



#
#Site Logo & Name
#
function theme_site_name() 
{
global $tr_config;
?>
<?php if($tr_config['enable_site_name'] == true): ?>
<div class="site-name">
<h1><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
<?php elseif($tr_config['logo']): ?>
<div class="site-logo">
<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><img src="<?php echo $tr_config['logo']; ?>" /></a>
<?php endif; ?>

<?php if($tr_config['enable_site_desc'] == true): ?>
<p><?php if($tr_config['site_desc']) { echo $tr_config['site_desc']; } else { bloginfo( 'description' ); } ?></p>
<?php endif; ?>
</div>
<?php
}



#
# Fixed WP Title
#
function theme_filter_wp_title( $title, $separator ) 
{
	if ( is_feed() )
		return $title;

	global $paged, $page;

	if ( is_search() ) {
		$title = sprintf( __( 'Search results for %s',  'TR' ), '"' . get_search_query() . '"' );
		if ( $paged >= 2 )
			$title .= " $separator " . sprintf( __( 'Page %s',  'TR' ), $paged );
			$title .= " $separator " . get_bloginfo( 'name', 'display' );
		return $title;
	}

	$title .= get_bloginfo( 'name', 'display' );

	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $separator " . $site_description;

	if ( $paged >= 2 || $page >= 2 )
		$title .= " $separator " . sprintf( __( 'Page %s',  'TR' ), max( $paged, $page ) );

	return $title;
}

if (!class_exists('All_in_One_SEO_Pack') && !class_exists('WPSEO_Frontend')) {
	add_filter( 'wp_title', 'theme_filter_wp_title', 10, 2 );
}



#
# SEO for the theme
#
function theme_wp_seo($separator) 
{
	$site_name = get_bloginfo( 'name', 'display' );
	$title = get_meta_option('seo_title');
	$keywords = stripslashes(get_meta_option('seo_keywords'));
	$description = stripslashes(get_meta_option('seo_description'));

	$output = ''; 

	if (!class_exists('All_in_One_SEO_Pack') && !class_exists('WPSEO_Frontend'))
	{
		if(is_singular())
		{
			if($keywords || $description) {
				$output .= '<!--SEO Meta-->'."\n"; 
			}
			if($keywords) { 
				$output .= '<meta name="keywords" content="'.$keywords.'" />'."\n"; 
			}
			if($description) { 
				$output .= '<meta name="description" content="'.$description.'" />'."\n"; 
			}
		}

		$output .= "\n"; 
		$output .= '<!--Title-->'."\n";
		$output .= '<title>'; 
		if(is_singular() && $title)
		{
			$output .= $title.' '.$separator.' '.$site_name; 
		}
		else
		{
			ob_start(); wp_title($separator, true, 'right'); $output .= ob_get_clean();
		}
		$output .= '</title>'."\n"; 
	}
	else
	{
		$output .= '<!--Title-->'."\n";
		$output .= '<title>'; 
		ob_start(); wp_title($separator, true, 'right'); $output .= ob_get_clean();
		$output .= '</title>'."\n";
	}

	return $output;
}
?>
