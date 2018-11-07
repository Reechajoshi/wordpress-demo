<?php
/*
 * @package by Theme Record
 * @auther: MattMao
*/

if ( !function_exists('theme_latest_tweet_posts') )
{	
	function theme_latest_tweet_posts($username, $tweet_count, $ignore_replies = true) 
	{
		
		// A flag so we know if the feed was successfully parsed
		$tweet_found = false;

		// Get tweets
		$tweets = get_transient('theme_latest_tweet_posts');

		// Show file from cache if still valid
		if ( $tweets === false  ) {

			// Fetch the RSS feed from Twitter
			$rss_feed = wp_remote_get("http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=$username");

			// Parse the RSS feed to an XML object
			$rss_feed = @simplexml_load_string( $rss_feed['body'] );

			if( !is_wp_error( $rss_feed ) && isset( $rss_feed ) ) {

				// Error check: Make sure there is at least one item
				if( count( $rss_feed->channel->item ) ) {

					// Open the twitter wrapping element
					$tweets = '<ul class="tweets-feed clearfix">';
					 
					$tweets_count = 0;
					$tweet_found = true;

					// Iterate over tweets.
					foreach( $rss_feed->channel->item as $tweet ) {

						// Twitter feeds begin with the username, "e.g. User name: Blah"
						// so we need to strip that from the front of our tweet
						$tweet_desc = substr( $tweet->description, strpos( $tweet->description, ':' ) + 2 );
						$tweet_desc = htmlspecialchars( $tweet_desc );
						$tweet_first_char = substr( $tweet_desc, 0, 1 );

						// If we are not gnoring replies, or tweet is not a reply, process it
						if ( $tweet_first_char != '@' || $ignore_replies == false ) {

							$tweets_count++;

							// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet
							$tweet_desc = preg_replace( '/(https?:\/\/[^\s"<>]+)/', '<a href="$1">$1</a>', $tweet_desc );
							$tweet_desc = preg_replace( '/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2">@$2</a>', $tweet_desc );
							$tweet_desc = preg_replace( '/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2">#$2</a>', $tweet_desc );

							// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time
							$tweet_time = strtotime( $tweet->pubDate );
							
							// Current UNIX timestamp.
							$current_time = time();
							$time_diff = abs( $current_time - $tweet_time );

							switch ( $time_diff ) {

								case ( $time_diff < 60 ):

									$display_time = $time_diff . __(' seconds ago', 'TR');

									break;

								case ( $time_diff >= 60 && $time_diff < 3600 ):

									$min = floor( $time_diff/60 );
									$display_time = $min . __(' minute', 'TR');
									if ( $min > 1 )
										$display_time .= __('s', 'TR');
									$display_time .= __(' ago', 'TR');

									break;

								case ( $time_diff >= 3600 && $time_diff < 86400 ):

									$hour = floor( $time_diff/3600 );
									$display_time = __('about ', 'TR') . $hour . __(' hour', 'TR');
									if ( $hour > 1 )
										$display_time .= __('s', 'TR');
									$display_time .= __(' ago', 'TR');

									break;

								case ( $time_diff >= 86400 && $time_diff < 604800 ):

									$day = floor( $time_diff/86400 );
									$display_time = __('about ', 'TR') . $day . __(' day', 'TR');
									if ( $day > 1 )
										$display_time .= __('s', 'TR');
									$display_time .= __(' ago', 'TR');

									break;

								case ( $time_diff >= 604800 && $time_diff < 2592000 ):

									$week = floor( $time_diff/604800 );
									$display_time = __('about ', 'TR') . $week . __(' week', 'TR');
									if ( $week > 1 )
										$display_time .= __('s', 'TR');
									$display_time .= __(' ago', 'TR');

									break;

								case ( $time_diff >= 2592000 && $time_diff < 31536000 ):

									$month = floor( $time_diff/2592000 );
									$display_time = __('about ', 'TR') . $month . __(' month', 'TR');
									if ( $month > 1 )
										$display_time .= __('s', 'TR');
									$display_time .= __(' ago', 'TR');

									break;

								case ( $time_diff > 31536000 ):

									$display_time = __('more than a year ago', 'TR');

									break;

								default:
									$display_time = date( 'F d, Y', $tweet_time );
									break;

							}
								
							// Render the tweet
							$tweets .= '<li>'.$tweet_desc.'<span class="date meta"><a href="'.$tweet->link.'">'.$display_time.'</a></span></li>';

						}
		 
						// If we have processed enough tweets, stop
						if ($tweets_count >= $tweet_count)
							break;

					}

					// Close the twitter wrapping element
					$tweets .= '</ul><!-- end .tweets-feed -->';

					// Tweets will be updated every hour
					set_transient('theme_latest_tweet_posts', $tweets, 60 * 60);

				}
				
			}

			// In case the RSS feed did not parse or load correctly, show a link to the Twitter account.
			if ( !$tweet_found )
				$tweets = '<ul class="tweets-feed"><li>'.__('Oops, our Twitter feed is unavailable at the moment', 'TR').'- <a href="http://twitter.com/'.$username.'/">'.__('Follow us on Twitter!','TR').'</a></li></ul><!-- end .tweets-feed -->';

		}

		return $tweets;

	}//end funcation
}