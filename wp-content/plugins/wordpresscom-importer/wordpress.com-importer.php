<?php
/*
Plugin Name: WordPress.com Importer
Plugin URI:
Description: Import WordPress.com blog content to WordPress.
Author: Prasath Nadarajah
Author URI:
Version: 1.0
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if ( !defined('WP_LOAD_IMPORTERS') )
	return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}

if ( class_exists( 'WP_Importer' ) ) {
class WordPressdotcom_Import {

		private $timeout;
		private $useragent;

		function WordPressdotcom_Import() {
			$this->timeout = 300;
			$this->useragent = 'wordpressdotcom-importer';
		}

		function header() {
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>'.__('Import WordPress.com blogs', 'wordpressdotcom-importer').'</h2>';
		}

		function footer() {
			echo '</div>';
		}

		function display_authorization_menu() {

			$client_id = sanitize_text_field( $_POST['wpcom_client_id'] );
			update_option( 'wpcom_import_client_id', $client_id );
			$client_secret = sanitize_text_field( $_POST['wpcom_client_secret'] );
			update_option( 'wpcom_import_client_secret', $client_secret );

			$redirect_uri = get_admin_url() . 'admin.php?import=wordpressdotcom'; // @TODO find a better solution
			$authorization_endpoint = 'https://public-api.wordpress.com/oauth2/authorize?client_id=' . $client_id . '&redirect_uri=' .  $redirect_uri . '&response_type=code';

			?>
				<div class="narrow">

					<form action="admin.php?import=wordpressdotcom" method="post">

						<p><?php _e( 'Howdy! This importer allows you to connect to WordPress.com sites and import content' , 'wordpressdotcom-importer' ) ?></p>
						<p><?php _e( 'First you must <a href="https://developer.wordpress.com/apps/new/">create a new application</a> in WordPress.com' , 'wordpressdotcom-importer' ) ?></p>
						<p><?php _e( 'Enter the Redirect URI as follows' , 'wordpressdotcom-importer' ) ?></p>
						<p><strong><?php echo esc_html( get_admin_url() . 'admin.php?import=wordpressdotcom' ) ?></strong></p>

						<table class="form-table">

							<tr>
								<th scope="row"><label for="wpcom_client_id"><?php _e( 'Enter your client id' , 'wordpressdotcom-importer' ) ?></label></th>
								<td><input type="text" name="wpcom_client_id" id="wpcom_client_id" class="regular-text" value="<?php echo esc_html( get_option( 'wpcom_import_client_id' ) ); ?>" /></td>
							</tr>

							<tr>
								<th scope="row"><label for="wpcom_client_secret"><?php _e( 'Enter your client secret' , 'wordpressdotcom-importer' ) ?></label></th>
								<td><input type="text" name="wpcom_client_secret" id="wpcom_client_secret" class="regular-text" value="<?php echo esc_html( get_option( 'wpcom_import_client_secret' ) ); ?>" /></td>
							</tr>

						</table>

						<p class="submit">
							<input type="submit" class="button" value="<?php esc_attr_e( 'Save Changes' , 'wordpressdotcom-importer' ) ?>" />
						</p>

					</form>

					<p><?php _e( 'You need to authorize this application in WordPress.com. You will be sent back here after providing authorization' , 'wordpressdotcom-importer' ) ?></p>

					<p class="submit">
						<input type="submit" class="button" onClick="parent.location='<?php echo esc_html( $authorization_endpoint ); ?>'" value="<?php esc_attr_e( 'Authorize' , 'wordpressdotcom-importer' ) ?>" />
					</p>

				</div>
			<?php

		}

		function display_import_menu() {

			// @TODO add an option whether to import comments

			?>
				<div class="narrow">

					<form action="admin.php?import=wordpressdotcom&step=1" method="post">

						<p><?php _e( 'Howdy! If you want to import content from another site please <a href="admin.php?import=wordpressdotcom&step=2">clear the tokens</a>' , 'wordpressdotcom-importer' ) ?></p>

						<table class="form-table">

							<tr>
								<th scope="row"><label for="wpcom_import_post_types"><?php _e( 'Select post types you want to import' , 'wordpressdotcom-importer' ) ?></label></th>
								<td id="wpcom_import_post_types">
									<ul>
										<li>
											<label>
												<input type="checkbox" name="wpcom_import_post_types[]" value="post"/>
												post
											</label>
										</li>
										<li>
											<label>
												<input type="checkbox" name="wpcom_import_post_types[]" value="page"/>
												page
											</label>
										</li>
									</ul>
								</td>
							</tr>

						</table>

						<p class="submit">
							<input type="submit" class="button" value="<?php esc_attr_e( '  Import  ' , 'wordpressdotcom-importer' ) ?>" />
						</p>

					</form>

				</div>
			<?php

		}

		function set_oauth_tokens() {

			$response = wp_remote_post(
				'https://public-api.wordpress.com/oauth2/token',
				array(
					'sslverify' => false,
					'timeout' => $this->timeout,
					'user-agent' => $this->useragent,
					'body' => array (
						'client_id' => get_option( 'wpcom_import_client_id' ),
						'redirect_uri' => get_admin_url() . 'admin.php?import=wordpressdotcom',
						'client_secret' => get_option( 'wpcom_import_client_secret' ),
						'code' => $_GET['code'],
						'grant_type' => 'authorization_code'
					),
				)
			);

			$result = json_decode( $response['body'] );

			if( isset( $result->error  ) ) {

				?>
					<p>Error retrieving API tokens. <?php echo esc_html($result->error_description); ?></p>
					<p>Please try again</p>
				<?php

				$this->display_authorization_menu();

				return;

			}

			update_option( 'wpcom_import_access_token', $result->access_token );
			update_option( 'wpcom_import_blog_id', $result->blog_id  );
			update_option( 'wpcom_import_blog_url', $result->blog_url );

		}

		function import() {

			$post_types = $_POST['wpcom_import_post_types'];

			if( in_array( 'post', $post_types ) ) {

				// maximum 100 posts
				$response = wp_remote_get(
					'https://public-api.wordpress.com/rest/v1/sites/' . get_option( 'wpcom_import_blog_id' ) . '/posts/?type=post&number=100',
					array(
						'sslverify' => false,
						'timeout' => $this->timeout,
						'user-agent' => $this->useragent,
						'headers' => array (
							'authorization' => 'Bearer ' . get_option( 'wpcom_import_access_token' ),
							'Content-Type' => 'application/x-www-form-urlencoded'
						),
					)
				);

				$result = json_decode( $response['body'] );
				$no_of_posts = $result->found;
				$pages = $result->posts;

				foreach( $pages as $page ) {

					$post_ID = wp_insert_post(
						array(
							'post_date' => $page->date,
							'post_status' => $page->status,
							'post_type' => $page->type,
							'ping_status' => !empty( $page->pings_open ) ? 'open' : 'closed',
							'comment_status' => !empty( $page->comments_open ) ? 'open' : 'closed',
							'post_password' => $page->password,
							'post_excerpt' => $page->excerpt,
							'post_content' => $page->content,
							'post_title' => $page->title,
							'category' => $page->categories,
							'post_tag' => $page->tags
						)
					);

					set_post_format( $post_ID, $page->format );

				}

			}

			if( in_array( 'page', $post_types ) ) {

				// maximum 100 pages
				$response = wp_remote_get(
					'https://public-api.wordpress.com/rest/v1/sites/' . get_option( 'wpcom_import_blog_id' ) . '/posts/?type=page&number=100',
					array(
						'sslverify' => false,
						'headers' => array (
							'authorization' => 'Bearer ' . get_option( 'wpcom_import_access_token' ),
							'Content-Type' => 'application/x-www-form-urlencoded'
						),
					)
				);

				$result = json_decode( $response['body'] );
				$no_of_pages = $result->found;
				$pages = $result->posts;

				foreach( $pages as $page ) {

					$post_ID = wp_insert_post(
						array(
							'post_date' => $page->date,
							'post_status' => $page->status,
							'post_type' => $page->type,
							'ping_status' => !empty( $page->pings_open ) ? 'open' : 'closed',
							'comment_status' => !empty( $page->comments_open ) ? 'open' : 'closed',
							'post_parent' => $page->parent,
							'post_password' => $page->password,
							'post_excerpt' => $page->excerpt,
							'post_content' => $page->content,
							'post_title' => $page->title,
							'category' => $page->categories,
							'post_tag' => $page->tags
						)
					);

					set_post_format( $post_ID, $page->format );
				}

			}

			echo '<p>' . translate( 'Finished importing content.', 'wordpressdotcom-importer' ) . '</p>';
			echo '<p>' . translate( 'Number of posts ', 'wordpressdotcom-importer' ) . $no_of_posts . '</p>';
			echo '<p>' . translate( 'Number of pages ', 'wordpressdotcom-importer' ) . $no_of_pages . '</p>';
			echo '<p>' . translate( ' <a href="admin.php?import=wordpressdotcom">go back to import menu</a>', 'wordpressdotcom-importer' ) . '</p>';

		}

		function clear_oauth_tokens() {

			delete_option( 'wpcom_import_access_token' );
			delete_option( 'wpcom_import_blog_id' );
			delete_option( 'wpcom_import_blog_url' );

			echo '<p>' . translate( 'Succuessfully cleared access tokens. <a href="admin.php?import=wordpressdotcom">go back to authorization menu</a>', 'wordpressdotcom-importer' ) . '</p>';

		}

		function greet() {
			echo '<div class="narrow">';

			if( isset( $_GET['code'] ) ) {
				$this->set_oauth_tokens();
			}

			$oauth_key = get_option( 'wpcom_import_access_token' );
			if( empty( $oauth_key ) ) {
				$this->display_authorization_menu();
			} else {
				$this->display_import_menu();
			}

			echo '</div>';
		}

		function dispatch() {
			if (empty ($_GET['step']))
				$step = 0;
			else
				$step = (int) $_GET['step'];

			$this->header();

			switch ($step) {
				case 0 :
					$this->greet();
					break;
				case 1 :
					$this->import();
					break;
				case 2:
					$this->clear_oauth_tokens();
					break;
			}

			$this->footer();
		}

}

$wordpressdotcom_import = new WordPressdotcom_Import();

register_importer( 'wordpressdotcom', __( 'WordPress.com', 'wordpressdotcom-importer' ), __( 'Import content from a WordPress.com blog.', 'wordpressdotcom-importer' ), array( $wordpressdotcom_import, 'dispatch' ) );

}