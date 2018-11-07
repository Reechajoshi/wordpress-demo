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

require_once "GtsConfig.php";

require_once "lib/Language.php";
require_once "lib/GtsUtils.php";

require_once "lib/ApiClient.php";
require_once "lib/WireTypes.php";

require_once "lib/db/GtsDbSchema.php";


/**
 * variable that holds a class reference to a GtsEnv impl that will implement all plugin functionality.
 * this is the ONLY variable that we're placing in the global scope.  this file is intentionally
 * coded to NOT use local variables in any case to avoid polluting whatever Blog runtime we're
 * working in.
 *
 * @global
 */
$gts_plugin;



abstract class GtsPlugin {


    var $api_client;
    var $config;
    var $synchronous = true;
    var $language;
    var $target_hostname;

    function __construct() {

        if( $_GET && array_key_exists( 'language', $_GET ) ) {
            $this->language = $_GET["language"];
        }

        $this->config = $this->load_config();

        $host = $_SERVER['SERVER_NAME'];
        $count = count( $this->config->target_languages );

        for ( $i = 0; $i < $count; $i++ ) {
            $lang = $this->config->target_languages[$i];
            $target_host = $this->config->target_hostnames[$lang];

            if ( $host == $target_host ) {
                $this->language = $lang;
                $this->target_hostname = $target_host;
                break;
            }
        }
    }



    abstract function load_config();
    abstract function save_config();


    abstract function create_db_table( $sql );

    abstract function get_blog_post( $id );

    abstract function get_blog_post_terms( $id );

    abstract function get_blog_term( $id, $taxonomy );

    abstract function get_translated_named_option( $id, $language );

    abstract function save_translated_named_option( $translated_option);



    abstract function get_translated_blog_post( $id, $language );

    abstract function save_translated_blog_post( $translated_post );

    abstract function get_translated_blog_term( $name, $language );

    abstract function save_translated_blog_term( $translated_term );



    abstract function send_info_notification( $heading, $text );

    abstract function send_error_notification( $heading, $text );




    abstract function ensure_db_current();



    abstract function get_cached_available_languages();

    abstract function cache_available_languages( $response );



    function construct_api_client() {
        $this->api_client = new com_gts_ApiClient( $this->config->api_host, $this->config->api_port, $this->config->blog_id, $this->config->api_key  );
    }


    function notify_plugin_activation() {

        // if we successfully activated, try to get the latest languages.
        $this->fetch_and_cache_available_languages();


        // if we have these values, then our blog was previously active.  in that case,
        // we need to call back home to get the blog back into the editing queue...
        if( $this->config->blog_id && $this->config->api_key ) {

            $request = new com_gts_Languages();
            foreach ( $this->config->target_languages as $code ) {
                array_push( $request->languages, com_gts_Language::get_by_code( $code ) );
            }

            $this->api_client->get_api_response( 'reactivateBlog', $request, true );
        }
    }

    function notify_plugin_deactivation() {

        // if we were previously configured, phone home to GTS to let them know not
        // to keep working on this blog's content...
        if( $this->config->blog_id && $this->config->api_key ) {
            $this->api_client->get_api_response( 'deactivateBlog', '', true );
        }
    }


    function load_available_languages() {

        // only try to load languages from the cache.  we don't want to inadvertently call
        // the remote API if this host has problems receiving data.  the daily cron job
        // should be more than enough!
        $this->initialize_available_languages( $this->get_cached_available_languages() );
    }


    function initialize_available_languages( $languages ) {

        // TODO - the language stuff should probably be factored into this class rather than have it outside.
        // it made more sense to have it that way before the API was responsible for loading languages...
        com_gts_Language::set_arrays(com_gts_Language::$ALL_LANGUAGES, com_gts_Language::$ALL_LANGUAGE_CODES, $languages );

        com_gts_Language::set_arrays(
            com_gts_Language::$INPUT_LANGUAGES,
            com_gts_Language::$INPUT_LANGUAGE_CODES,
            array_values(array_filter(com_gts_Language::$ALL_LANGUAGES, array('com_gts_Language', 'filter_lang_input')))
        );

        com_gts_Language::set_arrays(
            com_gts_Language::$OUTPUT_LANGUAGES,
            com_gts_Language::$OUTPUT_LANGUAGE_CODES,
            array_values(array_filter(com_gts_Language::$ALL_LANGUAGES, array('com_gts_Language', 'filter_lang_output')))
        );

        return $languages;
    }



    function fetch_and_cache_available_languages() {

        $response = $this->api_client->get_api_response( 'getAvailableLanguages', '', true );

        $languages = array();
        foreach ( $response->languages->language as $language_xml ) {

            $localization_strings = array();
            foreach( $language_xml->localizationStrings->localizationString as $localization_string ) {
                $localization_strings[ (string) $localization_string->key ] = (string) $localization_string->value;
            }

            $languages[] = new com_gts_Language(
                (string) $language_xml->code,
                (string) $language_xml->name,
                (string) $language_xml->englishName,
                    $language_xml->input == "true",
                    $language_xml->output == "true",
                    $language_xml->latin == "true",
                    $language_xml->recentlyAdded == "true",
                (string) $language_xml->displayCountryCode,
                (string) $language_xml->wordpressLocaleName,
                (string) $language_xml->textDirection,
                $localization_strings
            );
        }

        $this->cache_available_languages( $languages );

        return $languages;
    }



    function get_configured_languages() {

        // use the api client directly so that exceptions get propagated to the caller.
        $response = $this->api_client->get_api_response( 'getLanguages' , '', true );
        return $this->get_codes_from_language_response( $response );
    }

    function save_configured_languages( $languages ) {

        $request = new com_gts_Languages();
        foreach ( $languages as $code ) {
            array_push( $request->languages, com_gts_Language::get_by_code( $code ) );
        }

        // like above, using the api client directly...
        $response = $this->api_client->get_api_response( 'setLanguages' , $request, true );

        $response = $this->do_api_call( 'setLanguages', $request, true );
        return $this->get_codes_from_language_response( $response );
    }

    function get_codes_from_language_response( $response ) {
        $codes = array();
        foreach( $response->languages->language as $language ) {
            array_push( $codes, (string) $language->code );
        }

        return $codes;
    }


    function get_validation_code() {  // todo - WP specific
        if( ! $auth = get_option( GTS_AUTHORIZATION_OPTION_NAME ) ) {
            return false;
        }
        
        return strtolower( sha1( $auth['code'] . '~' . strtolower($auth['email']) . '~' . $this->do_without_language( array( $this, 'callback_get_home' ) ) ) );
    }

    function callback_get_home() {  // todo - WP specific
        return untrailingslashit( get_option('home') );
    }


    function do_without_language( $fn, $params = null ) {
        return $this->do_with_language( $fn, null, $params );
    }

    function do_with_language( $fn, $temp_language, $params = null ) {

        $result = $err = null;

        global $gts_plugin;
        $curr_lang = $gts_plugin->language;
        $gts_plugin->language = $temp_language;

        try {
            $result = call_user_func( $fn, $params );
        }
        catch(Exception $e) {
            $err = $e;
        }

        $gts_plugin->language = $curr_lang;

        if($err) {
            throw $err;
        }

        return $result;
    }
    

    function translate_named_option( $name, $value ) {

        $post_request = new com_gts_Options();
        array_push( $post_request->options, new com_gts_BlogOption($name, $value ) );

        $result = $this->do_api_call( 'translateOptions', $post_request, true );

        if ( $result ) {
            $this->process_translated_options( $result->translationResult );
        }
    }



    function translate_post_id( $post_id ) {
        $this->translate_post( $this->get_blog_post($post_id) );
    }

    function translate_post( $post ) {

        $post_request = $this->create_post_request( $post );
        $result = $this->do_api_call( 'translateEntry' , $post_request, $this->config->synchronous );

        if ( $result && $this->config->synchronous ) {
            $this->process_translated_post( $result->translationResult );
        }
    }


    function create_post_request( $post ) { // todo - wp specific

        $post_request = new com_gts_BlogPost();

        $post_request->remoteId = $post->ID;
        $post_request->language = $this->config->source_language;
        $post_request->title = $post->post_title;
        $post_request->excerpt = $post->post_excerpt;
        $post_request->body = $post->post_content;
        $post_request->slug = $post->post_name;

        $post_request->body = $this->escape_shortcode_tags( $post_request->body );     // todo - WP specific!

        foreach ( $this->get_blog_post_terms( $post->ID ) as $term ) {
            $post_term = new com_gts_BlogTerm();
            $post_term->remoteId = $term->term_id;
            $post_term->term = $term->name;
            $post_term->slug = $term->slug;

            array_push( $post_request->tags, $post_term );
        }

        return $post_request;
    }


    function escape_shortcode_tags( $content ) {
        $pattern = get_shortcode_regex();
        return preg_replace_callback('/'.$pattern.'/s', array($this, 'replace_shortcode_tag'), $content);
    }

    /*
     * 1/6 - An extra [ or ] to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     */
    function replace_shortcode_tag( $m ) {
        global $shortcode_tags;

        // allow [[foo]] syntax for escaping a tag
        if ( !array_key_exists($m[2], $shortcode_tags) || ($m[1] == '[' && $m[6] == ']')) {
            return $m[0];
        }

        $tag = $m[2];
        $attr = $m[3];

        // different element names based on whether the element has content.  this is important because
        // otherwise WP will have problems if we have open/close tags for repeated elements.  there are
        // known issues with their HTML parsing, and somewhere in the systran/jericho pipeline, these elements
        // get expanded into open/close tags.  therefore, we need to be able to differentiate.
        if ($m[5]) {
            return "<shortcode-content-$tag$attr>$m[5]</shortcode-content-$tag$attr>";
        }

        return "<shortcode-empty-$tag$attr$m[4]>";
    }


    function unescape_shortcode_tags( $content ) {
        return preg_replace_callback('/<(\/)?shortcode-([a-z]+)-([^ \/>]+)([^\/>]*)(\/)?>/', array($this, 'place_shortcode_tag'), $content );
    }

    function place_shortcode_tag( $m ) {

        if($m[1]) {
            return $m[2] == "empty" ? "" : "[/$m[3]]";
        }

        return "[$m[3]$m[4]$m[5]]";
    }


    function translate_term_id( $term_id, $tt_id, $taxonomy ) {
        $this->translate_term( $term_id, $this->get_blog_term( $term_id, $taxonomy ) );
    }

    function translate_term( $term_id, $term ) {

        $post_request = new com_gts_Terms();

        array_push( $post_request->terms, $this->create_term_request( $term ) );

        $result = $this->do_api_call( 'translateTerms' , $post_request, true );

        if ( $result ) {
            $this->process_translated_terms( $result->translationResult );
        }
    }

    function create_term_request( $term ) {  // TODO - wp-specific

        $post_term = new com_gts_BlogTerm();

        $post_term->remoteId = $term->term_id;
        $post_term->term = $term->name;
        $post_term->slug = $term->slug;
        $post_term->description = $term->description;

        return $post_term;
    }


    function process_translated_options( $result ) {

        foreach ( $result->translations->options as $options ) {
            foreach ( $options->option as $option ) {
                $this->save_translated_named_option($option);
            }
        }
    }

    function process_translated_post( $result ) {
        
        foreach ( $result->translations->blogPost as $translation ) {

            $translation->body = $this->unescape_shortcode_tags( $translation->body );   // TODO - wp-specific
            $this->save_translated_blog_post( $translation );
            
            foreach ( $translation->tags->tag as $tag ) {
                $this->save_translated_blog_term( $tag );
            }
        }
    }

    function process_translated_terms( $result ) {

        foreach ( $result->translations->terms as $terms ) {
            foreach( $terms->term as $term ) {
                $this->save_translated_blog_term($term);
            }
        }
    }



    function substitute_translated_posts( $posts ) {

        if($this->language) {

            $was_single = !is_array( $posts );
            if( $was_single ) {
                $posts = array( $posts );
            }

            foreach($posts as $post) {
                if( ! $post->gts_translated ) {
                    $translated_post = $this->get_translated_blog_post($post->ID, $this->language);

                    if($translated_post) {
                        $post->post_title = $translated_post->post_title;
                        $post->post_name = $translated_post->post_slug;
                        $post->post_excerpt = $translated_post->post_excerpt;
                        $post->post_content = $translated_post->post_body;
                        $post->gts_translated = true;
                    }
                }
            }
        }

        return $was_single ? $posts[0] : $posts;
    }


    function substitute_translated_terms( $terms ) {

        foreach ($terms as $term) {
            $this->substitute_translated_term($term);
        }

        return $terms;
    }

    function substitute_translated_term( $term ) {

        if($this->language) {
            $translated_term = $this->get_translated_blog_term($term->term_id, $this->language);

            if($translated_term) {
                $term->name = $translated_term->name;
                $term->slug = $translated_term->slug;
                $term->description = $translated_term->description;
            }
        }

        return $term;
    }


    function validate_api_key( $api_host, $api_port, $new_key ) {

        try {
            $new_client = new com_gts_ApiClient( $api_host, $api_port, $this->config->blog_id, $new_key );
            $result = $new_client->get_api_response("validateAccessKey", null, true );
            
            if((string) $result->booleanResult->value == "true") {
                return true;
            }

            $this->queue_error_notification("API Access Key", "Please double-check it's entered correctly");
        }
        catch(Exception $e) {
            $this->queue_error_notification("Error contacting translation server.", $e->getMessage());
        }

        return false;
    }


    function do_api_call($api_service, $api_request, $synchronous) {

        if(GTS_TEST) {
            echo $api_request->to_utf8_string() . "\n";
        }

        try {
            $response = $this->api_client->get_api_response( $api_service , $api_request, $synchronous );

            if(GTS_TEST) {
                echo $response->asXML() . "\n";
            }

            return $response;
        }
        catch(com_gts_ApiClientException $e) {

            if( $api_service == 'translateEntry' ) {
                if ( $e instanceof com_gts_RemoteApiErrorException ) {
                    $this->queue_info_notification( 'GTS', $e->getMessage() );
                }
                else {
                    $this->queue_error_notification( 'GTS Plugin was unable to contact translation server', 'Please try editing your post again, later.' );
                }
                
                $this->save_config();
            }
        }
    }


    function queue_info_notification($heading, $text) {
        array_push( $this->config->info_messages, "$heading|$text" );
    }

    function queue_error_notification($heading, $text) {
        array_push( $this->config->error_messages, "$heading|$text" );
    }



    function get_attribute_value( $xml, $name ) {
        $attrs = $xml->attributes();
        return $attrs[$name];
    }
}


define( 'GTS_TEST' , class_exists("PHPUnit_Framework_TestCase") );
define( 'GTS_DEBUG_MODE', GTS_DEFAULT_API_HOST == '127.0.0.1' );

if(GTS_TEST) {
    require_once "test/GtsPluginForTesting.php";
    $gts_plugin = new GtsPluginForTesting();
}
else {
    require_once "wordpress/GtsPluginWordpress.php";
    $gts_plugin = new GtsPluginWordpress();
}

$gts_plugin->ensure_db_current();
$gts_plugin->construct_api_client();
$gts_plugin->load_available_languages();

if(GTS_TEST) {
    echo "gts env successfully loaded...\n";
}

?>