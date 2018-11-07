<?php

/*
 * Copyright (c) 2010, Localization Technologies (LT) LLC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *     * Neither the name of Localization Technologies (LT) LLC nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL LOCALIZATION TECHNOLOGIES (LT) LLC BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once("GtsLinkRewriter.php");
require_once("GtsWidgetTranslator.php");


class GtsPluginWordpress extends GtsPlugin {

    // matches standard 2 character ISO codes or the semi-formal
    // zh-XXX chinese language codes.  optional group is non-capturing
    // so that this doesn't wreak havoc with other regexs it may be
    // embedded within.
    static $LANGUAGE_CODE_REGEX = "[a-z]{2}(?:\\-[A-Z]{3})?";

    static $TRANSLATE_OPTIONS = array(
        'blogname',
        'blogdescription',
    );


    var $wpdb;
    var $skip_config_check;

    var $link_rewriter;
    var $widget_translator;
    var $theme_language;


    function __construct() {
        parent::__construct();

        global $wpdb;
        $this->wpdb = $wpdb;

        $this->link_rewriter = new GtsLinkRewriter();
        $this->widget_translator = new GtsWidgetTranslator();

        // HACK ALERT:  the problem we're running into here is that the theme gets selected
        // before the query is parsed.  therefore, we have to pull out the language pre-emptively.
        // for reasons not completely understood at this time, the term substitution gets borked
        // if we set the language variable, so we have a bad fake variable set here.  hack hack hack!
        //
        // note that we use get_option b/c wp_rewrite hasn't initialized yet, and we won't
        // be able to switch off values in there.
        if( get_option( 'permalink_structure') ) {
            if( preg_match('/\/language\/(' . GtsPluginWordpress::$LANGUAGE_CODE_REGEX . ')\//', $_SERVER['REQUEST_URI'], $matches)) {
                $this->theme_language = $matches[1];
            }

            if( $this->target_hostname ) {
                $this->theme_language = $this->language;
            }
        }
        else {
            $this->theme_language = $_GET['language'];
        }

        // now that all that is squared away, see if we need to redirect to a virtual host.  if a virtual host is
        // set, we want to avoid serving up content from any other hostname so that for SEO purposes we don't have
        // the same page hosted in two places.
        if( $this->theme_language ) {

            $virtual_host = $this->config->target_hostnames[$this->theme_language];
            if( $virtual_host && $virtual_host != $_SERVER['HTTP_HOST'] ) {

                $https = $_SERVER['HTTPS'] == 'on';
                $server_port = $_SERVER['SERVER_PORT'];

                $protocol = 'http' . ($https ? 's' : '');
                $port = (!$https && $server_port == 80) || ($https && $server_port == 443) ? '' : ":$server_port";

                $url = "Location: $protocol://$virtual_host$port" . $_SERVER['REQUEST_URI'];
                header($url, true, 301);
                die();
            }
        }
    }


    function update_language_from_wp_query( $wp_query ) {
        if(!$this->language) {
            $this->language = $wp_query->query_vars[GtsLinkRewriter::$LANG_PARAM];
			$this->fix_page_query($wp_query);
            
            // now that we've read the language variable, we unset it.  otherwise, it breaks
            // the static home page feature b/c WP thinks the query isn't empty.
            unset($wp_query->query_vars[GtsLinkRewriter::$LANG_PARAM]);
        }
		
		
    }
	
	function fix_page_query($wp){
		/**
		 * [category_name] => muestra-pagina
            [pagename] => /sample-page/yellow-page
            [gts_pagepath] => /sample-page/yellow-page
		 */
		global $wpdb;
		global $wp_rewrite;
		
		
		if( //the fix is for this permalink structure only
			$wp_rewrite->permalink_structure == '/%category%/%postname%/' 
			//category_name should be set but nothing else
			&& isset($wp->query_vars['category_name']) 
			&& !isset($wp->query_vars['name']) 
			&& !isset($wp->query_vars['page_name']) 
			&& !isset($wp->query_vars['gts_pagepath'])
			//should be a name without slashes
			&& !preg_match("/\//", $wp->query_vars['category_name'])
			//should not be a real category
			&& !term_exists($wp->query_vars['category_name'])
		  )
		{
			
			$page_name = $wp->query_vars['category_name'];
			
			$lang = $this->language;
			
			$page_name = GtsUtils::orig_page_name($page_name, $lang);
			
			$page_name = "/$page_name";
			
			$wp->query_vars = array('pagename' => $page_name);
			
		}
		else {
			$rule = 'language/([a-z]{2}(?:\-[A-Z]{3})?)/(.+?)/([^/]+)(/[0-9]+)?/?$';
			if(
				$wp_rewrite->permalink_structure == '/%postname%/' 
				&& '404' == $wp->query_vars['error']
				&& preg_match("#$rule#", $wp->request, $matches)
				&& count($matches)>=4
			)
			{
				
				$this->language = $matches[1];
				$page_names = array_slice($matches, 2);
				
				foreach($page_names as $page_name){
					$orig_slug = GtsUtils::orig_page_name($page_name, $this->language);
					$orig_path .= "/" . $orig_slug;
				}
				
				$wp->query_vars = array(
										'page' => '',
										'name' => '',
										'pagename' => $orig_path, 
										);
				$wp->query_string = '';
				$wp->matched_rule = $rule;
				
			}
		}
		
	}


    function activate_plugin() {

        try {
            $this->notify_plugin_activation();
        }
        catch(Exception $e) {
            $this->queue_info_notification( "Unable to reactivate plugin", "Unable to contact GTS API.  Please deactivate and try again later.");
        }

        $this->link_rewriter->flush_rewrite_rules();
        $this->widget_translator->on_activation();

        if( !is_dir( GTS_THEME_DIR ) ) {
            if( $this->is_plugin_directory_writable() ) {
                mkdir( GTS_THEME_DIR );
            }
        }
    }

    function deactivate_plugin() {

        $this->link_rewriter->flush_rewrite_rules();
        $this->unschedule_cron_jobs();

        try {
            $this->notify_plugin_deactivation();
        }
        catch(Exception $e) {
            if( GTS_DEBUG_MODE ) {
                $this->queue_info_notification( "Unable to deactivate plugin", "Unable to contact GTS API.");
            }
        }
    }


    public static function uninstall_gts_plugin() {

        if( GTS_DEBUG_MODE || WP_UNINSTALL_PLUGIN ) {

            global $wpdb;

            $config = get_option( GTS_OPTION_NAME );
            $blog_id = $config[ 'blog_id' ];
            $api_key = $config[ 'api_key' ];
            $api_host = $config[ 'api_host' ];
            $api_port = $config[ 'api_port' ];

            if ( $blog_id && $api_key ) {

                if( !$api_host ) {
                    $api_host = GTS_DEFAULT_API_HOST;
                    $api_port = GTS_DEFAULT_API_PORT;
                }

                try {
                    $api_client = new com_gts_ApiClient( $api_host, $api_port, $blog_id, $api_key );
                    $api_client->get_api_response( 'killBlog', '', true );
                }
                catch(Exception $e) {
                    if( GTS_DEBUG_MODE ) {
                        echo $e->getTraceAsString();
                    }
                }
            }

            delete_option( GTS_OPTION_NAME );
            delete_option( GTS_THEME_OPTION_NAME );
            delete_option( GTS_AUTHORIZATION_OPTION_NAME );
            delete_option( GTS_DB_INITIALIZED_OPTION_NAME );

            foreach ( GtsDbSchema::$gts_db_schema as $table ) {
                $matches = array();
                preg_match( '/create\s+table\s+(\S+)/i', $table, $matches );
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . $matches[1] );
            }
        }
    }


    function register_plugin_hooks() {

        register_activation_hook( __FILE__, array($this, 'activate_plugin') );
        register_deactivation_hook( __FILE__, array($this, 'deactivate_plugin') );

        if(is_admin()) {
            $this->register_admin_filters();
            $this->register_admin_actions();
        }

        $this->register_filters();
        $this->register_actions();

        // register widget translation plugin filters and actions
        $this->widget_translator->init();
        
        $this->link_rewriter->register_plugin_hooks();
    }


    function register_admin_actions() {

        add_action( 'admin_init', array($this, 'register_settings'));
        add_action( 'admin_menu', array($this, 'create_admin_menu'));

        add_action( 'publish_post', array($this, 'translate_post_id'), 1 );
        add_action( 'publish_page', array($this, 'translate_post_id'), 1 );

        // these have to run after the term cache is cleared.
        add_action( 'created_term', array($this, 'translate_term_id'), 1, 3 );
        add_action( 'edited_term', array($this, 'translate_term_id'), 1, 3 );


        if (!$this->config->plugin_initialized && GTS_MENU_NAME != $_GET['page'] ) {
            $this->send_info_notification(__('GTS is almost ready to translate your blog'), sprintf(__('Please visit the <a href="admin.php?page=%1$s">configuration page</a> to get started.'), GTS_MENU_NAME));
        }

        $save_config = $this->send_notifications( $this->config->info_messages, 'info' );
        $save_config |= $this->send_notifications( $this->config->error_messages, 'error' );

        if($save_config) {
            $this->save_config();
        }

        foreach ( GtsPluginWordpress::$TRANSLATE_OPTIONS as $option ) {
            add_action( "add_option_$option", array( $this, 'translate_option_on_add', 1, 2 ) );
            add_action( "update_option_$option", array( $this, "translate_option_on_update_$option"), 1, 2 );
        }


        add_action( 'wp_ajax_gts_delete_translated_post', array($this, 'delete_translated_blog_post'));

        if ( GTS_DEBUG_MODE ) {
            add_action( 'wp_ajax_gts_kill_blog', array( get_class( $this ), 'uninstall_gts_plugin') );
            add_action( 'wp_ajax_gts_update_cached_languages', array( $this, 'fetch_and_cache_available_languages' ) );
            add_action( 'wp_ajax_gts_update_cached_mofiles', array( $this, 'fetch_and_cache_mofiles' ) );
        }
    }


    function ensure_db_current() {

        if ( !get_option( GTS_DB_INITIALIZED_OPTION_NAME ) || $this->config->plugin_version != GTS_PLUGIN_VERSION ) {

            global $wpdb;

            foreach ( GtsDbSchema::$gts_db_schema as $table ) {
                $table = preg_replace( '/(create\s+table\s+)(\S+)/i', '${1}' . $wpdb->prefix . '${2}', $table, 1 );
                $this->create_db_table( $table );
            }

            $this->config->plugin_version = GTS_PLUGIN_VERSION;
            $this->save_config();

            update_option( GTS_DB_INITIALIZED_OPTION_NAME, true );


            // HACK!  this changes this function more to an "onUpgrade" than just
            // making sure the DB is current.
            wp_schedule_single_event( time(), GTS_FETCH_MOFILES_CRONJOB );
        }
    }


    function get_cached_available_languages() {
        return get_option( GTS_CACHED_LANGUAGES_OPTION_NAME );
    }

    function cache_available_languages( $languages ) {
        update_option( GTS_CACHED_LANGUAGES_OPTION_NAME, $languages );
    }


    function translate_option_on_add( $name, $value ) {
        $this->translate_named_option( $name, $value );
    }

    function translate_option_on_update_blogname( $old, $new) {
        $this->translate_named_option( 'blogname', $new );
    }

    function translate_option_on_update_blogdescription( $old, $new) {
        $this->translate_named_option( 'blogdescription', $new );
    }


    /**
     * handles the fact that certain WP options are html-escaped (including the quote marks) in the DB.
     * if we don't unescape them before passing them on to the renite API, then the translation gets messy.
     * @param  $name
     * @param  $value
     * @return void
     */
    function translate_named_option( $name, $value ) {

        switch($name) {
            case 'blogname':
            case 'blogdescription':
                $value = wp_specialchars_decode( $value, ENT_QUOTES );
                break;
        }

        return parent::translate_named_option( $name, $value );
    }



    function filter_autogenerated_themes( $theme ) {
        return !preg_match( '/^gts_autogenerated (.*?)\.[a-z]{2}$/', $theme['Name'] );
    }


    function register_admin_filters() {
    }
	
	function sv_init(){
		GtsUtils::log(__METHOD__);
		$this->sv_fix();
	}
	
	function sv_wp_loaded(){
		GtsUtils::log(__METHOD__);
		$this->sv_fix();
	}
	
	function sv_fix(){
		global $wp;
		GtsUtils::log(__METHOD__);
		GtsUtils::log($wp);
	}

    function register_actions() {
    	
		/*
		 * $actions = array(
'muplugins_loaded',
'plugins_loaded',
'sanitize_comment_cookies',
'setup_theme',
'load_textdomain',
'after_setup_theme',
'auth_cookie_malformed',
'set_current_user',
'init',
'widgets_init',
//'register_sidebar',
//'wp_register_sidebar_widget',
'wp_loaded',
'parse_request',
'send_headers',
'parse_query',
'pre_get_posts',
'posts_selection',
'wp',
'template_redirect',
'get_header',
'wp_head',
'wp_enqueue_scripts',
'wp_print_styles',
'wp_print_scripts',
'get_template_part_loop',
'loop_start',
'the_post',
'loop_end'
);
		 */
		//add_action( 'init' , array($this, 'sv_init') );
		//add_action( 'wp_loaded' , array($this, 'sv_wp_loaded') );
		
		
		
		
        add_action( 'parse_request' , array($this, 'update_language_from_wp_query') );

        add_action( 'widgets_init', create_function('', 'return register_widget("GTS_LanguageSelectWidget");') );

        add_action( 'the_post' , array($this, 'substitute_translated_posts'), 1 );
        add_action( 'the_posts' , array($this, 'substitute_translated_posts'), 1 );

        add_action( 'get_term', array($this, 'substitute_translated_term'), 1 );
        add_action( 'get_terms', array($this, 'substitute_translated_terms'), 1 );

        add_action( 'wp_get_object_terms', array($this, 'substitute_translated_terms'), 1 );

        add_action( 'wp_head', array($this, 'add_link_rel_elements') );

        if( $this->config->auto_detect_language ) {
            add_action( 'init', array($this, 'intercept_widget_requests' ) );
            add_action( 'wp_head', array($this, 'add_lang_detection_script') );
        }


        // only register our theme directories when we're not in admin view.  otherwise, it will
        // clutter up the view.
        //
        // theme translation requires WP 2.9 so that we can keep our thenes out of the main
        // wp-content directory.
        if( function_exists( 'register_theme_directory') && $this->config->use_translated_theme ) {
            register_theme_directory( GTS_THEME_DIR );
        }

        // cronjobs...
        add_action( GTS_FETCH_LANGUAGES_CRONJOB, array($this, 'fetch_and_cache_available_languages' ) );
        add_action( GTS_FETCH_MOFILES_CRONJOB, array($this, 'fetch_and_cache_mofiles' ) );
        add_action( 'wp', array( $this, 'schedule_cron_jobs' ) );
    }


    function register_filters() {

        add_filter( 'locale', array($this, 'get_translation_locale'), 9999999999 );  // important that this is called *LAST*
        add_filter( 'load_textdomain_mofile', array($this, 'rewrite_mofile_path'), 9999999999, 2 );  // this one too!

        add_filter( 'the_title', array($this, 'get_translated_title'), 1, 2 );
        add_filter( 'page_title', array($this, 'get_translated_title'), 1, 2 );

        add_filter( 'bloginfo', array($this, 'filter_translated_bloginfo'), 1, 2 );

        global $wp_version;
        if( preg_match( '/^2\./', $wp_version ) || preg_match( '/^3\.[0-3]/', $wp_version ) ) {
            add_filter( 'template', array($this, 'substitute_translated_template'), 1 );
            add_filter( 'stylesheet', array($this, 'substitute_translated_stylesheet'), 1 );
        }

        add_filter( 'get_pages', array($this, 'substitute_translated_posts'), 1 );

        add_filter( 'option_home', array( $this, 'replace_hostname_if_available' ), 1 );
        add_filter( 'option_siteurl', array( $this, 'replace_hostname_if_available' ), 1 );

        add_filter( 'posts_join', array( $this, 'add_posts_join_criteria' ), 1 );
        add_filter( 'posts_search', array( $this, 'add_posts_search_criteria' ), 1);

        add_filter( 'comment_excerpt', array( $this, 'set_comment_text_direction') );
        add_filter( 'comment_text',  array( $this, 'set_comment_text_direction') );
    }



    function schedule_cron_jobs() {

        foreach( array( GTS_FETCH_LANGUAGES_CRONJOB, GTS_FETCH_MOFILES_CRONJOB ) as $cron ) {
            if( !wp_next_scheduled( $cron ) ) {
                wp_schedule_event( time(), 'daily', $cron );
            }
        }
    }

    function unschedule_cron_jobs() {

        foreach( array( GTS_FETCH_LANGUAGES_CRONJOB, GTS_FETCH_MOFILES_CRONJOB ) as $cron ) {
            if( $time = wp_next_scheduled( $cron ) ) {
                wp_unschedule_event( $time, $cron );
            }
        }
    }


    function add_link_rel_elements() {

        // todo - hack alert... probably need to factor out the get_current_url_for_language functionality.
        $widget = new GTS_LanguageSelectWidget();

        $all_langs = array();
        array_push( $all_langs, $this->config->source_language );
        if( $this->config->target_languages ) {
            foreach( $this->config->target_languages as $lang ) {
                array_push( $all_langs, $lang );
            }
        }

        $selected_lang = $this->language;
        if( !$selected_lang ) {
            $selected_lang = $this->config->source_language;
        }

        echo "<!-- GTS Plugin Version " . GTS_PLUGIN_VERSION  . " -->\n";
        foreach( $all_langs as $lang ) {
            if( $lang != $selected_lang ) {
                echo "<link rel=\"alternate\" hreflang=\"$lang\" href=\"" . $widget->get_current_url_for_language( com_gts_Language::get_by_code( $lang) ) . "\" />\n";
            }
        }
    }


    /**
     * when a request comes in to change the language explicitly (e.g. via the widget)
     * we remove the gtsLanguageSource parameter from the URL, set our skipAutoDetect
     * cookie, and redirect w/out the language source param (for bookmarking).
     */
    function intercept_widget_requests() {
        if( $_GET['gtsLanguageSource'] ) {
            setcookie( 'gts_skipAutoDetect', 'yes' );
            wp_redirect( remove_query_arg( 'gtsLanguageSource' ) );
            exit;
        }
    }


    function add_lang_detection_script() {

        $accept_lang = null;
        if( preg_match( '/^([a-z]{2}(-[A-Z]{3})?)/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches ) ) {

            $accept_lang = $matches[1];
            $message = com_gts_Language::get_by_code( $accept_lang )->localizationStrings[ 'AutoDetectionSwitchDialog' ];

            // sanitize the message for insertion into the JS block as a string literal.
            // backslashes are not allowed, and we fix up the quotes.
            $message = str_replace( '\\', '\\\\', $message );
            $message = str_replace( '\'', '\\\'', $message );

            if( ! $_COOKIE['gts_skipAutoDetect'] ) {
                echo <<<EOL
            <!-- GTS Language Detection Script -->
            <script type="text/javascript">

              // if there's an existing on load function already, need to store
              // it aside to make sure we don't break template's functionality.
              var gts_existingOnLoad = window.onload;

              window.onload = function() {

                if(gts_existingOnLoad) {
                  gts_existingOnLoad();
                }

                var gts_acceptLang = '$accept_lang';
                var gts_message = '$message';
                var gts_links = document.getElementsByTagName('link');

                if(document.cookie.indexOf('gts_skipAutoDetect') < 0) {
                  for(i = 0; i < gts_links.length; i++) {
                    if(gts_links[i].rel == 'alternate' && gts_links[i].hreflang == gts_acceptLang) {
                      if(confirm(gts_message)) {
                        window.location.href = gts_links[i].href;
                      }
                      else {
                        document.cookie = 'gts_skipAutoDetect=yes';
                      }
                    }
                  }
                }
              };
            </script>
EOL;
            }
        }
    }


    function add_posts_join_criteria( $join ) {
        
        if( $this->language ) {

            global $wpdb;
            $wp_table = $wpdb->prefix . "posts";
            $tp_table = $wpdb->prefix . 'gts_translated_posts';
            return "$join INNER JOIN $tp_table ON ($tp_table.local_id = $wp_table.id AND $tp_table.language = '". $this->language. "')";
        }

        return $join;
    }

    function add_posts_search_criteria( $join ) {

        if( $join && $this->language ) {
            $join = preg_replace( '/_posts\.post_title/', '_gts_translated_posts.post_title', $join );
            $join = preg_replace( '/_posts\.post_content/', '_gts_translated_posts.post_body', $join );
        }

        return $join;
    }


    function get_translation_locale( $locale ) {

        // locale is loaded before query parameters are parsed, so like with the theme,
        // we have to detect the language prior to parsing params.
        if ( $this->theme_language && $this->theme_language != $this->config->source_language ) {

            $lang_by_code = com_gts_Language::get_by_code($this->theme_language);

            // this is also a good time to go about setting the text direction...
            global $text_direction;
            $text_direction = $lang_by_code->textDirection;

            return $lang_by_code->wordpressLocaleName;
        }

        return $locale;
    }


    function set_comment_text_direction( $text ) {

        global $text_direction;
        if( !strcasecmp( $text_direction, 'rtl' ) && !preg_match( '/^(ar)|(he)/', WPLANG ) ) {
            $text = "<span dir=\"ltr\">$text</span>";
        }

        return $text;
    }

    
    function rewrite_mofile_path( $mofile, $domain ) {

        if( preg_match('/\/([a-z]{2}(_[A-Z]{2})?\.mo)$/', $mofile, $matches ) ) {

            $best_mofile = $this->get_best_mofile( $this->theme_language, $domain );
            if( $best_mofile ) {
                return $best_mofile;
            }
        }

        return $mofile;
    }


    function get_best_mofile( $language, $domain = 'default' ) {

        global $wp_version;
        $mofile = com_gts_Language::get_by_code( $language )->wordpressLocaleName . ".mo";

        $mo_dir_base = GTS_I18N_DIR;
        if( file_exists( $mo_dir_base ) && is_dir( $mo_dir_base ) ) {

            $newfile = $mo_dir_base . "/$wp_version/$domain/$mofile";

            // best case : we have the version file!
            if( file_exists( $newfile ) ) {
                return $newfile;
            }

            // next best : walk down the version folders until we find one...
            $wp_versions = GtsUtils::list_directory( $mo_dir_base );
            if( sizeof( $wp_versions ) > 0 ) {
                rsort( $wp_versions );

                foreach ( $wp_versions as $version ) {
                    $newfile = $mo_dir_base . "/$version/$domain/$mofile";
                    if( file_exists( $newfile ) ) {
                        return $newfile;
                    }
                }
            }
        }

        return FALSE;
    }


    function fetch_and_cache_mofiles() {

        foreach( $this->config->target_languages as $language ) {

            $this->download_mofile( $language );

            global $wp_version;
            if( preg_match( '/^2\./', $wp_version ) ) {
                $this->download_mofile( $language, 'kubrick' );
            }
            else {
                $this->download_mofile( $language, 'twentyten' );
                if( preg_match( '/^3\.[2-9]/', $wp_version ) ) {
                    $this->download_mofile( $language, 'twentyeleven' );
                }
            }
        }
    }


    function download_mofile( $language, $domain = "default" ) {

        global $wp_version;
        $locale_name = com_gts_Language::get_by_code( $language )->wordpressLocaleName;

        $svn_host = 'svn.automattic.com';
        $svn_url = "/wordpress-i18n/$locale_name";
        $svn_messages_dir = "messages" . ( $domain != "default" ? "/$domain" : "");

        $mofile = GTS_I18N_DIR . "/$wp_version/$domain/$locale_name.mo";
        if( file_exists( $mofile ) ) {
            if( $this->is_valid_mofile( $mofile ) ) {
                return TRUE;
            }

            @unlink( $mofile );
        }

        $tags_fp = $this->url_get_stream( $svn_host, 80, "$svn_url/tags/" );
        $tags = @stream_get_contents( $tags_fp );
        @fclose( $tags_fp );

        if( $tags ) {

            if( preg_match_all( '/href="(\d+(\.\d+)+)\/?\"/', $tags,  $match_versions ) ) {

                // we're only going to bother looking at 2.9+.  all translated locales really should
                // have an file for at the very least 2.9.  if not, we can always fall back to trunk.
                $versions = array();
                foreach( $match_versions[1] as $version ) {
                    if( preg_match( '/^[3-9]/', $version ) || preg_match( '/^2\.9/', $version ) ) {
                        array_push( $versions, $version );
                    }
                }

                rsort( $versions );

                $i = 0;
                $num_versions = sizeof( $versions );
                global $wp_version;

                while( $i < $num_versions && strcmp( $versions[$i], $wp_version ) > 0 ) {
                    $i++;
                }

                // try going backward, then forward...  hopefully we get something!
                $prioritized_versions = array_merge( array_slice( $versions, $i ), array_reverse( array_slice( $versions, 0, $i ) ) );

                foreach ( $prioritized_versions as $version ) {
                    if( $this->download_mofile_to_i18n_dir( $svn_host, "$svn_url/tags/$version/$svn_messages_dir/$locale_name.mo", $domain, $version, "$locale_name.mo" ) ) {
                        return TRUE;
                    }
                }
            }
        }

        return $this->download_mofile_to_i18n_dir( $svn_host, "$svn_url/trunk/$svn_messages_dir/$locale_name.mo", $domain, "0.0", "$locale_name.mo" );
    }


    function download_mofile_to_i18n_dir( $svn_host, $svn_url, $dirname, $version, $filename ) {

        $i18n_base = GTS_I18N_DIR . "/$version/$dirname";

        $mo_stream = $this->url_get_stream( $svn_host, 80, $svn_url );
        if( !$mo_stream ) {
            return FALSE;
        }

        if( ! file_exists( $i18n_base ) ) {
            GtsUtils::mkdir_dash_p( $i18n_base );
        }

        $fh = @fopen( "$i18n_base/$filename", 'w' );
        if ( !$fh ) {
            fclose( $mo_stream );
            throw new Exception("Unable to write .mo files!");
        }

        stream_copy_to_stream( $mo_stream, $fh );

        fclose( $mo_stream );
        fclose( $fh );

        // check the headers to make sure that we have a .mofile
        return $this->is_valid_mofile( $filename );
    }


    function is_valid_mofile( $filename ) {

        $valid = FALSE;
        $fh = @fopen( $filename, 'r' );

        if ( $fh ) {

            $bytes = fread( $fh, 4 );
            if( $bytes !== FALSE ) {

                $unpacked = unpack( "Lmagic", $bytes );
                $magic = $unpacked["magic"];

                // this is borrowed from WP code...checks the magic header of the
                // .mo file to make sure that it at least has the right prefix.

                // The magic is 0x950412de
                // bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
                $magic_little = (int) - 1794895138;
                $magic_little_64 = (int) 2500072158;
                // 0xde120495
                $magic_big = ((int) - 569244523) & 0xFFFFFFFF;
                $valid = ( $magic_little == $magic || $magic_little_64 == $magic || $magic_big == $magic );
            }
        }

        @fclose( $fh );

        return $valid;
    }


    // this is a workaround to deal with hosts where file_get_contents doesn't work for URLs
    // due to security constraints...
    function url_get_stream( $host, $port, $url ) {

        $fp = @fsockopen( $host, $port );

        if( $fp ) {
            @fwrite( $fp, "GET $url HTTP/1.1\r\n" );
            @fwrite( $fp, "Host: $host\r\n" );
            @fwrite( $fp, "\r\n" );

            // in our ghetto wire impl, we'll ignore headers.  when we come across
            // the first blank \r\n, we'll return the result.  we'll also check
            // the very first line for the HTTP status and return false if not 200.
            $first = true;
            while ( $str = @fgets( $fp, 4096) ) {

                if( $first ) {
                    if( !preg_match( '/^HTTP\/\d+\.\d+ 200/', $str, $matches ) ) {
                        return false;
                    }
                    $first = false;
                }

                if ( $str == "\r\n" ) {
                    return $fp;
                }
            }
        }

        return $fp;
    }


    function is_i18n_directory_writable() {
        return $this->is_plugin_directory_writable() || ( is_dir( GTS_I18N_DIR ) && is_writable( GTS_I18N_DIR ) );
    }


    function replace_hostname_if_available( $url ) {

        if ( $this->language ) {

            $target_hostname = $this->config->target_hostnames[$this->language];
            if ( $target_hostname ) {

                $parts = parse_url( $url );
                if( $parts ) {
                    return $parts['scheme'] . '://' . $target_hostname . $parts['path'] .
                            ( $parts['query'] ? '?' . $parts['query'] : '') .
                            ( $parts['fragment'] ? '#' . $parts['fragment'] : '')
                            ;
                }
            }
        }

        return $url;
    }


    function load_config() {

        $config = new GtsConfig();
        $config_class = new ReflectionClass( get_class( $config ) );

        $config_option = get_option( GTS_OPTION_NAME );

        if($config_option) {
            foreach( $config_option as $key => $value ) {
                if( $config_class->hasProperty($key) ) {
                    $config_class->getProperty($key)->setValue( $config , $value );
                }
            }
        }

        return $config;
    }


    function save_config() {

        $config_array = array();
        $config_class = new ReflectionClass( get_class( $this->config ) );

        foreach ( $config_class->getProperties() as $property ) {
            $property_value = $property->getValue( $this->config );
            if ( $property_value ) {
                $config_array[$property->getName()] = $property_value;
            }
        }

        if( count( $config_array ) > 0 ) {
            $this->skip_config_check = true;
            update_option( GTS_OPTION_NAME , $config_array );
            $this->skip_config_check = false;
        }
        else {
            delete_option( GTS_OPTION_NAME );
        }
    }


    function create_db_table( $sql ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // HACK ALERT!  this is b/c WP isn't handling index creation properly.  it's duplicating indexes,
        // and that's running into mysql bugginess with text indices.  plus, it means that every time we
        // do the upgrade, it's creating duplicate indexes, and that's just silly.  for now, we'll just
        // drop all indexes...could probably be smarter in the future, but these tables are expected to
        // be sufficiently small that it shouldn't be too much effort to just recreate the indexes.
        if( preg_match('/create\s+table\s+(\S+)/i', $sql, $matches ) ) {
            global $wpdb;

            $indexes_to_drop = array();
            $table_name = $matches[1];

            foreach ( $wpdb->get_results( "SHOW INDEX FROM $table_name") as $row ) {
                if( $row->Key_name != "PRIMARY" && !in_array( $row->Key_name, $indexes_to_drop ) ) {
                    array_push( $indexes_to_drop , $row->Key_name );
                }
            }

            if( sizeof( $indexes_to_drop ) > 0 ) {
                $query = "ALTER TABLE $table_name ";
                foreach ( $indexes_to_drop as $index ) {
                    $query .= " DROP INDEX $index,";
                }
            }

            $wpdb->query( substr( $query, 0, strlen( $query ) - 1 ) );
        }

        dbDelta($sql);
    }

    function get_blog_post( $id ) {
        return get_post($id);
    }

    function get_blog_post_terms( $id ) {

        $terms = array();
        foreach( get_taxonomies( array(), 'objects' ) as $taxonomy ) {
            if ( in_array( 'post', $taxonomy->object_type ) ) {
                $terms = array_merge( $terms, wp_get_post_terms( $id, $taxonomy->name, array( "fields" => "all" ) ) );
            }
        }

        return $terms;
    }

    function get_blog_term( $id, $taxonomy ) {
        return get_term( $id, $taxonomy );
    }


    function get_translated_named_option( $id, $language ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM " . $this->wpdb->prefix . "gts_translated_options WHERE local_id = %s AND language = %s",
                $id, $language
            )
        );
    }


    function save_translated_named_option( $translated_option ) {
        $option_is_widget = in_array($translated_option->name, GtsWidgetTranslator::getOptions());
        if($option_is_widget)
        {
            $translated_option->value = serialize($this->widget_translator->processTranslatedOption($translated_option->value));
        }
        
        $columns = array(
            "foreign_id" => $translated_option->id,
            "name" => $translated_option->name,
            "value" => $translated_option->value,
        );

        $ids = array(
            "local_id" => $translated_option->remoteId,
            "language" => $translated_option->language,
        );

        $this->wpdb_upsert($this->wpdb->prefix ."gts_translated_options", $columns, $ids);
    }



    function get_translated_blog_post( $id, $language ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM " . $this->wpdb->prefix . "gts_translated_posts WHERE local_id = %s AND language = %s",
                $id, $language
            )
        );
    }


    function get_translated_blog_post_by_slug( $slug, $language ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM " . $this->wpdb->prefix . "gts_translated_posts WHERE post_slug = %s AND language = %s",
                $slug, $language
            )
        );
    }

    function get_translated_blog_post_metadata( $lang_code ) {
        return $this->wpdb->get_results(
            'SELECT id, local_id, foreign_id, post_title, post_slug, language, modified_time ' .
                    'FROM ' . $this->wpdb->prefix . 'gts_translated_posts ' .
                    'WHERE language = \'' . $lang_code . '\' ' .
                    'ORDER BY modified_time DESC'
        );
    }

    function save_translated_blog_post( $translated_post ) {

        $translated_post->slug = $this->sanitize_slug( $translated_post->slug, $translated_post->language );

        $columns = array(
            "foreign_id" => $this->get_attribute_value( $translated_post, 'id' ),
            "post_title" => $translated_post->title,
            "post_excerpt" => $translated_post->excerpt,
            "post_body" => $translated_post->body,
            "post_slug" => $translated_post->slug,
        );

        $ids = array(
            "local_id" => $translated_post->remoteId,
            "language" => $translated_post->language,
        );

        $id_fmts = array(
            "local_id" => "%d",
        );

        $this->wpdb_upsert($this->wpdb->prefix ."gts_translated_posts", $columns, $ids, $id_fmts);
    }

    function delete_translated_blog_post( $id ) {
        $this->wpdb->query('DELETE FROM ' . $this->wpdb->prefix . 'gts_translated_posts WHERE id = ' . ((int) $_POST['id']));
    }



    function sanitize_slug( $slug, $language ) {

        $langObj = com_gts_Language::get_by_code( $language );
        if( $langObj->latin ) {
            $slug = sanitize_title( $slug );
        }
        else {
            $slug = preg_replace( '/([[:punct:]]|[[:space:]])+/', '-', $slug );
        }

        return $slug;
    }


    function get_translated_blog_term( $name, $language ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM " . $this->wpdb->prefix . "gts_translated_terms WHERE local_name = %s AND language = %s",
                $name, $language
            )
        );
    }

    function get_translated_blog_term_by_slug( $name, $language ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM " . $this->wpdb->prefix . "gts_translated_terms WHERE slug = %s AND language = %s",
                $name, $language
            )
        );
    }

    function save_translated_blog_term( $translated_term ) {

        $translated_term->slug = $this->sanitize_slug( $translated_term->slug, $translated_term->language );

        $columns = array(
            "foreign_id" => $this->get_attribute_value( $translated_term, 'id' ),
            "name" => $translated_term->term,
            "slug" => $translated_term->slug,
            "description" => $translated_term->description,
        );

        $ids = array(
            "local_name" => $translated_term->remoteId,
            "language" => $translated_term->language,
        );

        $id_fmts = array(
            "local_name" => "%d"
        );

        $this->wpdb_upsert($this->wpdb->prefix ."gts_translated_terms", $columns, $ids, $id_fmts);
    }


    function wpdb_upsert($table, $columns, $ids, $id_formats = array()) {

        $select_where = "";
        $select_binds = array();

        foreach (array_keys($ids) as $id) {
            if($select_where) {
                $select_where .= " AND ";
            }

            $select_where .= ("$id = " . (array_key_exists($id, $id_formats) ? $id_formats[$id] : "%s"));
            array_push($select_binds, $ids[$id]);
        }


        $found = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $select_where", $select_binds
            )
        );

        if($found) {
            $this->wpdb->update($table, $columns, $ids);
        }
        else {
            $now = $this->wpdb->get_row( "select now()", ARRAY_N );
            $this->wpdb->insert($table, array_merge($columns, $ids, array("created_time" => $now[0] )));
        }
    }


    function filter_translated_bloginfo( $bloginfo, $show ) {

        if( $show == 'name' || $show == 'description' ) {
            $show = "blog$show";
        }

        if( $this->language ) {
            switch( $show ) {
                case 'language':
                    return com_gts_Language::get_by_code( $this->theme_language )->wordpressLocaleName;
                case 'blogname':
                case 'blogdescription':
                    $translated_option = $this->get_translated_named_option( $show, $this->language );
                    if( $translated_option ) {
                        return $translated_option->value;
                    }
            }
        }

        return $bloginfo;
    }




    function register_settings() {
        register_setting(GTS_SETTING_GROUP, GTS_OPTION_NAME, array($this, 'validate_settings' ));
        register_setting(GTS_THEME_SETTING_GROUP, GTS_THEME_OPTION_NAME, array($this, 'validate_theme_settings' ));
    }


    function validate_settings( $input ) {

        if($this->skip_config_check) {
            return $input;
        }

        $input[GTS_SETTING_API_KEY] = preg_replace('/\s+/m', '', $input[GTS_SETTING_API_KEY] );
        $input[GTS_SETTING_API_KEY] = preg_replace('/(=+[^=]+=+)/', '', $input[GTS_SETTING_API_KEY] );

        $valid_key = true;
        if( $input[GTS_SETTING_API_KEY] != $this->config->api_key ) {
            $this->validate_api_key( $input[GTS_SETTING_API_HOST], $input[GTS_SETTING_API_PORT], $input[GTS_SETTING_API_KEY] );
        }

        // do a slight transform on the languages...
        $languages = array();
        foreach (com_gts_Language::$OUTPUT_LANGUAGES as $lang) {
            if($input[GTS_SETTING_TARGET_LANGUAGES][$lang->code]) {
                array_push($languages, $lang->code);
            }
        }

        if( $input[GTS_SETTING_TARGET_LANGUAGES] != $this->config->target_languages ) {
            try {
                $input[GTS_SETTING_TARGET_LANGUAGES] = $this->save_configured_languages( $languages );
                $this->link_rewriter->flush_rewrite_rules();
                $rewrite_rules_flushed = true;

                // whenever the languages change, make sure our .mo files are up to date!
                wp_schedule_single_event( time(), GTS_FETCH_MOFILES_CRONJOB );
            }
            catch(Exception $e) {
                $this->queue_info_notification( "Unable to set languages", "Unable to contact GTS API.  Will keep old values");
                $input[GTS_SETTING_TARGET_LANGUAGES] = $this->config->target_languages;
            }
        }
        $this->config->target_languages = $input[GTS_SETTING_TARGET_LANGUAGES];

        if(count($input[GTS_SETTING_TARGET_LANGUAGES]) == 0) {
            $this->queue_info_notification(__('No Languages Selected'), __('You should select something!'));
        }

        if ( !$rewrite_rules_flushed && $this->config->target_hostnames != $input[GTS_SETTING_TARGET_HOSTS] ) {
            $this->link_rewriter->flush_rewrite_rules();
        }

        // not sure why, but this is getting a boolean true even when unchecked.  this
        // double-check fixes it.
        $input[GTS_SETTING_SYNCHRONOUS] = "on" == $input[GTS_SETTING_SYNCHRONOUS];
        $input[GTS_SETTING_AUTO_DETECT_LANG] = "on" == $input[GTS_SETTING_AUTO_DETECT_LANG];
        $input[GTS_SETTING_USE_THEME] = "on" == $input[GTS_SETTING_USE_THEME];

        // if our form input is valid, then go ahead and toggle this setting...
        // note that it's sticky so that once set, we don't send the user back to the
        // plugin splash screen.
        $this->config->plugin_initialized = $this->config->plugin_initialized || ( $valid_key && count( $languages ) > 0 );

        // this is a little bit of magic to make sure that our config object gets properly
        // restored.  anything that's not a part of the user-configurable portion will get
        // copied into our saved config object.
        $config_class = new ReflectionClass( get_class( $this->config ) );
        foreach ( $config_class->getProperties() as $property ) {
            switch( $property->getName() ) {
                // these first few are only available in debug mode.
                // otherwise, they always have to be the default value.
                case GTS_SETTING_BLOG_ID:
                case GTS_SETTING_API_HOST:
                case GTS_SETTING_API_PORT: if ( !GTS_DEBUG_MODE ) break;

                case GTS_SETTING_API_KEY:
                case GTS_SETTING_TARGET_HOSTS:
                case GTS_SETTING_TARGET_LANGUAGES:
                case GTS_SETTING_AUTO_DETECT_LANG:
                case GTS_SETTING_SYNCHRONOUS:
                    break;
                default:
                    $input[ $property->getName() ] = $property->getValue( $this->config );
            }
        }

        return $input;
    }

    function validate_theme_settings( $input ) {

        $ticked = 'on' == $input;

        $this->config->use_translated_theme = $ticked;
        $this->save_config();

        return $ticked;
    }


    function create_admin_menu() {
        add_menu_page( 'GTS Plugin Settings', 'GTS Settings', 'manage_options', GTS_MENU_NAME, array($this, 'settings_page') );

        global $wp_version;
        if( preg_match( '/^2\./', $wp_version ) || preg_match( '/^3\.[0-3]/', $wp_version ) ) {
            add_submenu_page( GTS_MENU_NAME, 'GTS Theme Settings', 'Translate Theme', 'manage_options', GTS_MENU_NAME . '-theme', array($this, 'settings_theme_page') );
        }

        add_submenu_page( GTS_MENU_NAME, 'GTS Manage Translated Posts', 'Manage Posts', 'manage_options', GTS_MENU_NAME . '-posts', array($this, 'settings_posts_page') );
        add_submenu_page( GTS_MENU_NAME, 'GTS Localization Status', 'Localization Status', 'manage_options', GTS_MENU_NAME . '-localization', array($this, 'settings_localization_page') );
    }

    function settings_page() {
        $this->include_page_or_splash( 'options' );
    }

    function settings_theme_page() {
        $this->include_page_or_splash( 'theme', true );
    }

    function settings_posts_page() {
        $this->include_page_or_splash( 'translated-posts', true );
    }

    function settings_localization_page() {
        $this->include_page_or_splash( 'localization', true );
    }

    function include_page_or_splash( $page, $not_available_yet = false ) {
        if(!$this->config->plugin_initialized && !$_GET['initialize']) {
            if ( $not_available_yet ) {
                include("pages/gts-options-notavailable.php");
            }
            else {
                include("pages/gts-options-splash.php");
            }
        }
        else {
            include("pages/gts-settings-$page.php");
        }
    }


    function send_info_notification($heading, $text) {
        $this->send_wp_notification($heading, $text, "info");
    }

    function send_error_notification($heading, $text) {
        $this->send_wp_notification($heading, $text, "error");
    }

    function send_wp_notification($heading, $text, $type) {

        $html = "<div id=\"gts-warning-$heading\" class=\"" . ($type == "info" ? "updated" : "error") . " fade\">"
                . "<p><strong>" . __($heading) . "</strong>: " . __($text) . "</p></div>";

        add_action("admin_notices", create_function("", "echo '". preg_replace('/\'/', "\\'", $html) .  "\n';"));
    }


    function send_notifications( &$messages, $type ) {

        $count = 0;

        while( $message = array_shift( $messages ) ) {
            $line = explode("|", $message);
            $count++;

            if($type == 'error') {
                $this->send_error_notification($line[0], $line[1]);
            }
            else {
                $this->send_info_notification($line[0], $line[1]);
            }
        }

        return $count;
    }



    function get_translated_title( $title, $post_id = "" ) {

        if( !$post_id ) {
            return $title;
        }

        if( $post_id instanceof StdClass ) {
            $post_id = $post_id->ID;
        }

        $post = $this->get_translated_blog_post( $post_id, $this->language );

        // easy case...the post is valid and we return the title!
        if( $post ) {
            return $post->post_title;
        }

        // less easy case...  this is a fix for WP menus, which do some really retarded
        // things with copying the data from the post around and sending the category through
        // here.  it's ridiculous...
        if( !$post && !in_the_loop() && is_nav_menu_item( $post_id ) ) {

            $post_type = get_post_meta( $post_id, '_menu_item_object' );
            $original_post_id = get_post_meta( $post_id, '_menu_item_object_id' );

            if( $post_type[0] == 'page' ) {
                $post = $this->get_translated_blog_post( $original_post_id[0] , $this->language );
                if( $post ) {
                    return $post->post_title;
                }
            }
            else if( $post_type[0] == 'category' ) {
                $category = $this->get_translated_blog_term( $original_post_id[0], $this->language );
                if( $category ) {
                    return $category->name;
                }
            }

        }

        return $title;
    }



    function substitute_translated_template( $template ) {
        return $this->get_translated_theme_attribute( 'Template', $template );
    }

    function substitute_translated_stylesheet( $stylesheet ) {
        return $this->get_translated_theme_attribute( 'Stylesheet', $stylesheet );
    }

    function get_translated_theme_attribute( $attribute, $if_not_found ) {

        // making sure to use the theme_language and not the normal language due to wordpress wonkiness
        if($this->theme_language) {

            $current = get_current_theme();

            // depending on whether the theme has a package, we have different replace conditions here.
            if( strpos( $current, ' ') ) {
                $translated_theme = preg_replace( '/(\S+)\s+(.*)/', 'gts_autogenerated $2', $current) . ".$this->theme_language";
            }
            else {
                $translated_theme = "gts_autogenerated $current.$this->theme_language";
            }

            if( ($theme = get_theme( $translated_theme ) ) && $theme[$attribute] ) {
                return $theme[$attribute];
            }
        }

        return $if_not_found;
    }



    function add_autogenerated_theme_dir( $dirs ) {
        return $dirs;
    }



    function translate_current_theme( $pre_file_callback = null, $post_file_callback = null ) {

        $theme = get_theme( get_current_theme() );

        $this->prepare_theme_for_translation( $theme );

        foreach ( $this->get_template_filenames( $theme ) as $file ) {

            if( $pre_file_callback ) {
                $pre_file_callback( $theme, $file );
            }

            $this->translate_template_file( $theme, $file );

            if( $post_file_callback ) {
                $post_file_callback( $theme, $file );
            }
        }
    }


    function prepare_theme_for_translation( $theme ) {
        foreach ( $this->config->target_languages as $lang ) {
            $template_dir = GTS_THEME_DIR . '/' . basename( $theme['Template Dir'] ) . ".$lang";
            if ( !file_exists( $template_dir ) ) {
                GtsUtils::mkdir_dash_p( $template_dir );
            }

            GtsUtils::copy_directory( $theme['Template Dir'], $template_dir );
            GtsUtils::copy_directory( $theme['Stylesheet Dir'], $template_dir );
        }
    }

    function get_template_filenames( $theme = null ) {

        if( !$theme ) {
            $theme = get_theme( get_current_theme() );
        }

        $files = array_merge( $theme['Template Files'], $theme['Stylesheet Files'] );
        array_walk( $files, array( $this, 'array_walk_get_relative_name') );

        return $files;
    }

    function array_walk_get_relative_name( &$file ) {
        $file = str_replace( WP_CONTENT_DIR . '/themes', '', $file );
    }


    function translate_template_file( $theme, $file ) {

        $template_request = new com_gts_BlogTemplate();

        $template_request->language = $this->config->source_language;

        $template_request->theme = $theme['Name'];
        $template_request->path = $file;
        $template_request->text = stream_get_contents( fopen( WP_CONTENT_DIR . '/themes' . $file, 'r' ) );

        $template_request->remoteId = $template_request->theme . ':' . $template_request->path;

        $response = $this->do_api_call( 'translateTemplate' , $template_request, true );

        if ( $response ) {

            foreach ( $response->translationResult->translations->blogTemplate as $template ) {

                $template_file = GTS_THEME_DIR . '/' . $template->path;
                $template_dir = dirname( $template_file );

                if ( !file_exists( $template_dir ) ) {
                    GtsUtils::mkdir_dash_p( $template_dir );
                }

                $fh = @fopen( $template_file, 'w' );
                if ( !$fh ) {
                    throw new Exception("Unable to write template files!");
                }

                fwrite( $fh, $template->text );
                fclose( $fh );
            }
        }
    }

    function is_plugin_directory_writable() {
        return is_writable( GTS_PLUGIN_DIR );
    }

    function is_plugin_theme_directory_writable() {
        return $this->is_plugin_directory_writable() || ( is_dir( GTS_THEME_DIR ) && is_writable( GTS_THEME_DIR ) );
    }
}



function gtsenv_is_wp_loaded() {
    return defined('WP_PLUGIN_URL');
}


// find and configure the wp runtime if we're not being called from within a WP
// request.  this block must be executed in the global scope in order to succeed.
// if decomped into a function, it will not work...so all vars are namespaced.
if(!gtsenv_is_wp_loaded()) {

    // first start going up the directory tree until we find a copy of the wp-load.php file.
    // this will be the common case when running in a webserver.
    foreach( explode(PATH_SEPARATOR, get_include_path() ) as $gtsenv_include_dir ) {

        $gtsenv_script_file = $_SERVER['SCRIPT_FILENAME'];
        $gtsenv_offset = -1;

        while(($idx = strrpos( $gtsenv_script_file, DIRECTORY_SEPARATOR, $gtsenv_offset )) > 0) {
            $gtsenv_filename = substr( $gtsenv_script_file, 0, $idx + 1) . 'wp-load.php';
            $gtsenv_offset = ($idx - 1) - strlen($gtsenv_script_file);

            if(@file_exists($gtsenv_filename)) {
                require_once $gtsenv_filename;
                break;
            }
        }
    }

    // if we still haven't been able to find our file, then try to pull the file from our include path.
    // this will be the case when we're running from the IDE or unit tests.
    if( !gtsenv_is_wp_loaded() ) {

        $gtsenv_filename = $gtsenv_include_dir . DIRECTORY_SEPARATOR . 'wp-load.php';

        if(@file_exists($gtsenv_filename)) {
            require_once $gtsenv_filename;
        }
    }

    // todo - should provide an option to specify a path if PLUGINDIR is not under ABSPATH?    
    if(!gtsenv_is_wp_loaded()) {
        die('unable to find wp config...');
    }
}


/**
 * now that the WP runtime is loaded up, we'll define all our constants.
 */

define( 'GTS_PLUGIN_NAME', 'gts-translation' );
define( 'GTS_PLUGIN_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . GTS_PLUGIN_NAME );
define( 'GTS_PLUGIN_URL', trailingslashit( WP_PLUGIN_URL ) . basename( GTS_PLUGIN_DIR) );

define( 'GTS_I18N_DIR', GTS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'i18n' );

$plugin_data = get_file_data( GTS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'Gts.php', array( 'Name' => 'Plugin Name', 'Version' => 'Version' ) );
define( 'GTS_PLUGIN_VERSION' , $plugin_data['Version'] );

define( 'GTS_THEME_DIR_NAME', 'translated_themes' );
define( 'GTS_THEME_DIR', GTS_PLUGIN_DIR . DIRECTORY_SEPARATOR . GTS_THEME_DIR_NAME  );

define( 'GTS_OPTION_NAME' , 'gts_plugin_config');
define( 'GTS_THEME_OPTION_NAME' , 'gts_plugin_config_theme');
define( 'GTS_AUTHORIZATION_OPTION_NAME', 'gts_plugin_authorization' );
define( 'GTS_DB_INITIALIZED_OPTION_NAME' , 'gts_database_initialized');
define( 'GTS_CACHED_LANGUAGES_OPTION_NAME', 'gts_cached_languages' );

// names of menus and such for the wp-admin interface.
define( 'GTS_MENU_NAME', 'gts-settings' );
define( 'GTS_SETTING_GROUP', 'gts-plugin-settings' );
define( 'GTS_THEME_SETTING_GROUP', 'gts-plugin-settings-templates' );

// these are names of individual settings that are keys in the associative
// array that wordpress passes around for our main option.
define( 'GTS_SETTING_BLOG_ID', 'blog_id' );
define( 'GTS_SETTING_API_KEY', 'api_key' );
define( 'GTS_SETTING_API_HOST', 'api_host' );
define( 'GTS_SETTING_API_PORT', 'api_port' );
define( 'GTS_SETTING_TARGET_LANGUAGES', 'target_languages' );
define( 'GTS_SETTING_TARGET_HOSTS', 'target_hostnames' );
define( 'GTS_SETTING_SYNCHRONOUS', 'synchronous' );
define( 'GTS_SETTING_AUTO_DETECT_LANG', 'auto_detect_language' );
define( 'GTS_SETTING_USE_THEME', 'use_translated_theme' );

define( 'GTS_FETCH_LANGUAGES_CRONJOB', 'gts_cron_fetch_languages' );
define( 'GTS_FETCH_MOFILES_CRONJOB', 'gts_cron_fetch_mofiles' );


?>
