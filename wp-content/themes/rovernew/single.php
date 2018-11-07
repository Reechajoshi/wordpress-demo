<?php
/**
 * Single
 * @package by Theme Record
 * @auther: MattMao
 */
get_header();

#Get options
global $tr_config;
$enable_date = $tr_config['enable_blog_date'];
$enable_category = $tr_config['enable_blog_categories'];
$enable_author = $tr_config['enable_blog_author'];
$enable_comments = $tr_config['enable_blog_comments'];

#Get meta
$post_format = get_meta_option('blog_type');
?>
<div id="main" class="right-side clearfix">

<article id="content">
	<?php if (have_posts()) : the_post(); ?>
	<div class="post-blog">
	<div class="post post-blog-single post-<?php echo $post_format; ?> clearfix" id="post-<?php the_ID(); ?>">
	<div class="post-meta clearfix">
		<div class="link"><a href="<?php the_permalink(); ?>" title="" rel="bookmark"><?php the_title(); ?></a></div>
		<div class="entry-header">
			<h2 class="title"><?php the_title(); ?></h2>
			<p class="entry-header-meta meta">
			<?php if($enable_date == true) : ?><b><?php _e('Date', 'TR'); ?>:</b><?php the_time( get_option('date_format') ); ?><span>&#8211;</span><?php endif; ?>
			<?php if($enable_category == true) : ?><b><?php _e('Posted', 'TR'); ?>:</b><?php the_category(', '); ?><span>&#8211;</span><?php endif; ?>
			<?php if($enable_comments == true) : ?><b><?php _e('Comments', 'TR'); ?>:</b><?php comments_popup_link(0, 1, '%'); ?><span>&#8211;</span><?php endif; ?>
			<?php if($enable_author == true) : ?><b><?php _e('Author', 'TR'); ?>:</b><?php the_author_posts_link(); ?><span>&#8211;</span><?php endif; ?>
			<?php edit_post_link( __('Edit', 'TR'), '', '<span>&#8211;</span>' ); ?>
			</p>
		</div>
	</div>
	<!--end meta-->

	<div class="post-entry clearfix">
	<?php 
	switch($post_format)
	{
		case 'image':
		theme_content_image();
		break;

		case 'slideshow':
		theme_content_gallery();
		break;

		case 'audio':
		theme_content_audio();
		break;

		case 'video':
		theme_content_video();
		break;

		case 'link':
		 theme_content_link();
		break;

		case 'quote':
		theme_content_quote();
		break;
	}
	?>
	<div class="post-format"><?php the_content(); ?></div>
	<?php echo get_the_term_list( get_the_ID(), 'post_tag', '<div class="post-tags"><b>'. __('Tags', 'TR') .':</b>', ' , ', '</div>' ); ?>
	</div>
	<!--end entry-->
	</div>
	</div>
	<!--End Blog Single-->
	<div class="post_share rail" style="position: relative; left: 0px; top: 0px; margin-top: -6px; margin-bottom: 4px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; width: 562px; height: 28px; margin-left: 0px">  <div style='float: left; margin: 2px 0px 0px 0px;'><iframe src="http://www.facebook.com/plugins/like.php?href=<?php the_permalink();?>&amp;layout=button_count&amp;show_faces=false&amp;width=80&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:80px; height:21px;" allowTransparency="true"></iframe></div>  <div style='float: left; margin:2px 0px 0px 10px; height:21px;'><g:plusone size="medium"></g:plusone><script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script></div>  <div style='float: left; margin: 2px 0px 0px -10px;'><script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>    <a href="http://twitter.com/share" class="twitter-share-button"       data-url="<?php the_permalink(); ?>"       data-via="wpbeginner"       data-text="<?php the_title(); ?>"       data-related="twitterusername:Description of the User"       data-count="horizontal">Tweet</a></div>  <div style='float: left; margin: 2px 0px 0px 5px;'><script type="text/javascript"> (function() { var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0]; s.type = 'text/javascript'; s.async = true; s.src = 'http://widgets.digg.com/buttons.js'; s1.parentNode.insertBefore(s, s1); })(); </script> <a class="DiggThisButton DiggCompact"></a></div>  <div style='float: left; margin: 2px 0px 0px 10px;'><script src="http://www.stumbleupon.com/hostedbadge.php?s=1"></script></div>  <div style='float: left; margin: 2px 0px 0px 10px;'><script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script><a href="http://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&media=<?php if(function_exists('the_post_thumbnail')) echo wp_get_attachment_url(get_post_thumbnail_id()); ?>&description=<?php echo get_the_title(); ?>" class="pin-it-button" count-layout="horizontal"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
	</div>  </div>
	<div style="clear:both;"></div>
	
	<div class="post_share rail" style="position: relative; left: 0px; top: 0px; margin-bottom: 4px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; width: 562px; height: 28px; margin-left: 0px"> 
	
		<div style='float: left; margin: 2px 0px 0px 0px;'>
			<script type="text/javascript" charset="utf-8">
				(function(){
				  var _w = 120 , _h = 50;
				  var param = {
					url:location.href,
					type:'6',
					count:'', 
					appkey:'', 
					title:'', 
					pic:'', 
					ralateUid:'', 
					language:'zh_cn', 
					dpc:1
				  }
				  var temp = [];
				  for( var p in param ){
					temp.push(p + '=' + encodeURIComponent( param[p] || '' ) )
				  }
				  document.write('<iframe allowTransparency="true" frameborder="0" scrolling="no" src="http://service.weibo.com/staticjs/weiboshare.html?' + temp.join('&') + '" width="'+ _w+'" height="'+_h+'"></iframe>')
				})()
			</script>
		</div>
		
		<div style='float: left; margin: 2px 0px 0px 0px;'>
			<script type="text/javascript" charset="utf-8">
				(function(){
				 var kx_width = 130;
				 var kx_height = 50;
				 var param = {
				  url:location.href,
				  content:'', 
				  pic:'', 
				  starid:'',
				  aid:'', 
				  showcount:1,
				  style:3
				 } 
				 var arr = [];
				 for( var tmp in param ){
				 arr.push(tmp + '=' + encodeURIComponent( param[tmp] || '' ) )
				 }
				 document.write('<iframe allowTransparency="true" frameborder="0" scrolling="no" src="http://www.kaixin001.com/rest/records.php?'+arr.join('&')+'" width="'+kx_width+'" height="'+kx_height+'"></iframe>')
				})()
			</script>
		</div>
		 
		<div style='float: left; margin: 2px 0px 0px 0px;'>
			 <script type="text/javascript" charset="utf-8">
			(function(){
				var p = [], w=100, h=65,
				lk = {
					url:''||location.href, 
					title:''||document.title,
					description:'', 
					image:'' 
				};
				for(var i in lk){
					p.push(i + '=' + encodeURIComponent(lk[i]||''));
				}
				document.write('<iframe scrolling="no" frameborder="0" allowtransparency="true" src="http://www.connect.renren.com/like/v2?'+p.join('&')+'" style="width:'+w+'px;height:'+h+'px;"></iframe>');
			})();
			</script>
		</div>
	
	</div>
	
	<?php if(comments_open()) { comments_template( '', true ); } ?>

	<?php endif; ?>

</article>
<!--End Content-->

<?php theme_sidebar('blog');?>

</div>
<!-- #main -->
<?php get_footer(); ?>

