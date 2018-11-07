<?php
/*
 * @package by Theme Record
 * @auther: MattMao
*/

if ( !function_exists('theme_latest_tweet_posts') )
{	
	function theme_latest_tweet_posts($username, $tweet_count_limit, $ignore_replies, $twitter_style_dates, $date_format, $twitter_cachetime) 
	{
		// Cache file
		$cache_file = FUNCTIONS_DIR. '/plugins/cache/tweets.txt';

		// Time that the cache was last filled.
		$cache_file_created = ((@file_exists($cache_file))) ? @filemtime($cache_file) : 0;

		// A flag so we know if the feed was successfully parsed.
		$tweet_found = false;

		// Show file from cache if still valid.
		if (time() - $twitter_cachetime < $cache_file_created) {

			$tweet_found = true;

			// Display tweets from the cache.
			@readfile($cache_file);	

		} else {

			// Fetch the RSS feed from Twitter.
			$url = "http://twitter.com/statuses/user_timeline/$username.rss";

			// Initiate the curl session
			$ch = curl_init();

			// Set the URL
			curl_setopt($ch, CURLOPT_URL, $url);

			// Removes the headers from the output
			curl_setopt($ch, CURLOPT_HEADER, 0);

			// Return the output instead of displaying it directly
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			// Execute the curl session
			$rss_feed = curl_exec($ch);

			// Close the curl session
			curl_close($ch);

			// Parse the RSS feed to an XML object.
			$rss_feed = @simplexml_load_string($rss_feed);

			if(isset($rss_feed)) {

				// Error check: Make sure there is at least one item.
				if(count($rss_feed->channel->item)) {

					// Start output buffering.
					ob_start();

					// Open the twitter wrapping element.
					$tweets = "";
					 
					$tweet_count = 0;
					$tweet_found = true;

					// Iterate over tweets.
					foreach($rss_feed->channel->item as $tweet) {

						// Twitter feeds begin with the username, "e.g. User name: Blah"
						// so we need to strip that from the front of our tweet.
						$tweet_desc = substr($tweet->description, strpos($tweet->description, ":") + 2);
						$tweet_desc = htmlspecialchars($tweet_desc);
						$tweet_first_char = substr($tweet_desc, 0, 1);

						// If we are not gnoring replies, or tweet is not a reply, process it.
						if ($tweet_first_char != '@' || $ignore_replies == false) {

							$tweet_count++;

							// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
							$tweet_desc = preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="$1">$1</a>', $tweet_desc);
							$tweet_desc = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2">@$2</a>', $tweet_desc);
							$tweet_desc = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2">#$2</a>', $tweet_desc);

							// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
							$tweet_time = strtotime($tweet->pubDate);	

							if ($twitter_style_dates){

								// Current UNIX timestamp.
								$current_time = time();
								$time_diff = abs($current_time - $tweet_time);

								switch ($time_diff) {
									case ($time_diff < 60):
										$display_time = "$time_diff seconds ago";
									break;
									case ($time_diff >= 60 && $time_diff < 3600):
										$min = floor($time_diff/60);
										$display_time = "$min minute";
										if ($min > 1) $display_time .= "s";
										$display_time .= " ago";
									break;
									case ($time_diff >= 3600 && $time_diff < 86400):
										$hour = floor($time_diff/3600);
										$display_time = "about $hour hour";
										if ($hour > 1) $display_time .= "s";
										$display_time .= " ago";
									break;
									case ($time_diff >= 86400 && $time_diff < 604800):
										$day = floor($time_diff/86400);
										$display_time = "about $day day";
										if ($day > 1) $display_time .= "s";
										$display_time .= " ago";
									break;
									case ($time_diff >= 604800 && $time_diff < 2592000):
										$week = floor($time_diff/604800);
										$display_time = "about $week week";
										if ($week > 1) $display_time .= "s";
										$display_time .= " ago";
									break;
									case ($time_diff >= 2592000 && $time_diff < 31536000):
										$month = floor($time_diff/2592000);
										$display_time = "about $month month";
										if ($month > 1) $display_time .= "s";
										$display_time .= " ago";
									break;
									case ($time_diff > 31536000):
										$display_time = "more than a year ago";
									break;

									default:
										$display_time = date($date_format, $tweet_time);
									break;
								}

							} else {

								$display_time = date($date_format, $tweet_time);

							}

							// Render the tweet.
							$tweets .= '<li>'.$tweet_desc.'<span class="date meta"><a href="'.$tweet->link.'">'.$display_time.'</a></span></li>';

						}
		 
						// If we have processed enough tweets, stop.
						if ($tweet_count >= $tweet_count_limit) break;

					}

					// Close the twitter wrapping element.
					echo $tweets;

					// Generate a new cache file.
					$file = @fopen($cache_file, 'w');

					// Save the contents of output buffer to the file, and flush the buffer. 
					@fwrite($file, ob_get_contents()); 
					@fclose($file); 
					ob_end_flush();

				}
			}
		}

		// In case the RSS feed did not parse or load correctly, show a link to the Twitter account.
		if (!$tweet_found){
			echo $tweets = '<li>'.__('Oops, our Twitter feed is unavailable at the moment', 'TR').'- <a href="http://twitter.com/'.$username.'/">'.__('Follow us on Twitter!','TR').'</a></li>';
		}
	}//end funcation
}