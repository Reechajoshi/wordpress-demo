<?php
/*
 * @package by Theme Record
 * @auther: MattMao
*/

class theme_widget_tweets extends WP_Widget 
{
	#
	#Constructor
	#
	function theme_widget_tweets()
	{
		$widget_ops = array(
			'classname' => 'widget-tweets', 
			'description' => __('This widget will display a tweets section.', 'TR')
		);
		$this->WP_Widget( THEME_SLUG. '_tweets', THEME_NAME.' &raquo; Tweets', $widget_ops );
	}


	#
	#Form
	#
	function form($instance) 
	{
		$instance = wp_parse_args((array) $instance, array( 
			'title' => 'Tweets',
			'username' => '',
			'limit' => '2'
		));
		$title = strip_tags($instance['title']);
		$username = strip_tags($instance['username']);
		$limit = strip_tags($instance['limit']);
		?>
		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</div>

		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php esc_html_e('ID:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		</div>

		<div class="theme-widget-wrap">
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php esc_html_e('Limit:','TR'); ?></label>
			<input  id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</div>
		<?php
	}	


	#
	#Update & save the widget
	#
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;	
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}
		return $instance;
	}


	#
	#Prints the widget
	#
	function widget($args, $instance) 
	{
		extract($args, EXTR_SKIP);
		$title = $instance['title'];
		$username = $instance['username'];
		$limit = $instance['limit'];

		// Do you want to show replies
		$ignore_replies = true;

		// Date settings
		$twitter_style_dates = true; // e.g. "about 1 hour ago"

		// Date setting if $twitter_style_dates is false
		$date_format = 'F d, Y'; // e.g. "December 20, 2011"

		// Seconds to cache feed (1 hour)
		$cachetime = 60*60;
	?>
	<?php echo $before_widget; ?>
	<?php echo $before_title . $title . $after_title; ?>
	<?php
		echo '<ul class="clearfix">'."\n";
		theme_latest_tweet_posts($username, $limit, $ignore_replies, $twitter_style_dates, $date_format, $cachetime);
		echo '</ul>'."\n";
	?>
	<?php echo $after_widget; ?>
	<?php
	}
}

register_widget( 'theme_widget_tweets' );
?>