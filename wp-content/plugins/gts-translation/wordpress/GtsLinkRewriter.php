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

class GtsLinkRewriter {

    static $LANG_PARAM = 'gts_lang';
    static $PAGEPATH_PARAM = 'gts_pagepath';


    var $original_tag;
    var $original_term;
    var $original_category_id;
    var $original_category_name;


    static $INTERESTING_OPTIONS = array(
        'tag_base',
        'category_base',
        GTS_OPTION_NAME,
    );

    static $SIMPLE_LINKS = array(
        //'attachment_link', - this is a compound one...depends on the post link
        'author_link',
        'author_feed_link',
        'day_link',
        'feed_link',
        'month_link',
        //'page_link', - taking care of this one explicitly
        'year_link',
        'category_link',
        //'category_feed_link',  - this is a compound one...depends on the category_link
        'tag_link',
        'term_link',
    );


    function register_plugin_hooks() {

        global $wp_version;

        $flush_callback = array( $this, 'flush_rewrite_rules' );

        if(is_admin()) {
            foreach ( GtsLinkRewriter::$INTERESTING_OPTIONS as $option ) {
                add_filter( "add_option_$option", $flush_callback );
                add_filter( "update_option_$option", $flush_callback );
            }
        }

        add_filter( 'query_vars', array( $this, 'add_query_params'), 1 );

        add_filter( 'rewrite_rules_array', array( $this, 'append_rewrite_rules' ), 9999999999 );  // we want to be last last last!

        add_action( 'request' , array($this, 'fix_term_parameters'), 1 );

        // these work together with the filters in GtsPluginWordpress to change the hostname.
        // they're used at different times and places in WP (though i think they're trying to fix that in
        // some release...we still have to support 2.9+).  if we have version 3+, we'll use the siteurl filter
        // so that we also get the path we're modifying so we can avoid rewriting admin links.  in 2.X, it looks
        // like admin links go a different route, so we don't need to worry about them.  what a pain!
        add_filter( 'option_home', array($this, 'add_language_to_home'), 1 );
        add_filter( preg_match( '/^[12]\./', $wp_version ? 'option_' : '') + 'siteurl', array($this, 'add_language_to_home'), 2 );

        add_filter( 'post_link', array($this, 'rewrite_post_link'), 1, 2 );
        add_filter( 'page_link', array($this, 'rewrite_page_link'), 1, 2 );

        foreach ( GtsLinkRewriter::$SIMPLE_LINKS as $filter_name ) {
            add_filter( $filter_name, array($this, 'add_language_parameter') );
        }
    }


    function add_language_to_home( $link, $path = "" ) {

        global $gts_plugin, $wp_rewrite;
        if( $gts_plugin->language && $wp_rewrite->permalink_structure && !preg_match( '/^\/?wp\-admin\//', $path) ) {
            $link = untrailingslashit($link) . '/language/' . $gts_plugin->language;
        }

        return $link;
    }

    function add_language_parameter( $link ) {

        global $gts_plugin;
        if($gts_plugin->language) {
            return $this->insert_param( $link, 'language', $gts_plugin->language );
        }

        return $link;
    }


    function add_language_and_term_id_parameters( $link, $id ) {

        global $gts_plugin, $wp_rewrite;
        if($gts_plugin->language) {

            if($id instanceof StdClass) {
                $id = $id->ID;
            }


            $query_param = null;
            if( !$wp_rewrite->permalink_structure ) {

                // if we don't have permalink support, we have to differentiate between
                // the different types of queries that come through this path.  the easiest
                // way to do that is by comparing queries with params removed.
                //
                // note that in the case of tags, we have to pass the name, so we won't
                // have the translated value in the URL.  oh well.
                if( remove_query_arg( 'tag', $link ) != $link ) {
                    $query_param = 'tag';
                    $id = $gts_plugin->do_without_language( array( $this, 'get_slug_without_language' ), $id );
                }
                else if( remove_query_arg( 'cat', $link ) != $link ) {
                    $query_param = 'cat';
                }
                else {
                    $query_param = 'p';
                }
            }

            return $this->add_language_parameter( $this->insert_param( $link, $query_param, $id, false) );
        }

        return $link;
    }

    function get_slug_without_language( $id ) {
        return get_tag( $id )->slug;
    }



    function rewrite_page_link( $link, $id ) {

        global $gts_plugin, $wp_rewrite;
        if( $id && $gts_plugin->language ) {

            $permalink = $wp_rewrite->get_page_permastruct();
            if( preg_match( '/%pagename%/', $permalink ) ) {

                $page = get_page( $id );
                $old_path = array();
                $translated_path = array();

                while( true ) {
                    $tpage = $gts_plugin->get_translated_blog_post( $page->ID, $gts_plugin->language );
                    array_unshift( $old_path, $page->post_name );
                    array_unshift( $translated_path, $tpage ? $tpage->post_slug : $page->post_name );

                    if( $page->post_parent == 0 ) {
                        break;
                    }

                    $page = get_page( $page->post_parent );
                }

                $link = str_replace( implode( '/', $old_path ), implode( '/', $translated_path), $link );
            }

            return $this->add_language_parameter( $link );
        }

        return $link;
    }


    function rewrite_post_link( $link, $post ) {

        global $gts_plugin, $wp_rewrite;
        if( $gts_plugin->language) {

            $permalink = $wp_rewrite->permalink_structure;

            // gts_translated is a handy little field we put on posts so that we know it's already
            // been swapped out.  sometimes, this filter is entered via a route where it's not possible
            // to action or filter the post content to our own (notably in the prev/next link functions...lame).
            // in those cases, we have to do a transformation on the link.
            if( preg_match( '/%postname%/', $permalink ) && ( !$post->gts_translated || $post->language != $gts_plugin->language ) ) {
                $tpost = $gts_plugin->get_translated_blog_post( $post->ID, $gts_plugin->language );
                if( $tpost ) {
                    $link = str_replace( $post->post_name, $tpost->post_slug, $link );
                }
            }

            return $this->add_language_parameter( $link );
        }

        return $link;
    }

    function insert_param( $link, $name, $value, $include_name_in_permalink = true ) {

        global $wp_rewrite;

        if ( $wp_rewrite->permalink_structure ) {

            // this is a special case...  we need to make sure that language doesn't get double-set.  it should be bubbling up
            // via the option_home filter, but this check is a failsafe to ensure it's always there.  if we find that it's
            // missing for whatever reason, we'll just add it below.  otherwise return the link as-is
            if ( $name == 'language' && strpos( $link, "/$name/$value" ) !== false ) {
                return $link;
            }

            $home = get_option('home');
            return substr( $link, 0, strlen($home) ) . ($include_name_in_permalink ? "/$name" : '') . "/$value" . substr( $link, strlen($home) );
        }

        return add_query_arg( $name, $value, $link );
    }


    function fix_term_parameters( $query_vars ) {

        // this is a bizarre case, but when using virtual hosts, the query parameter for lang isn't set.
        // i tried tracking down why it wasn't getting set to no avail, so the easiest thing to do is just
        // insert the language right here.
        global $gts_plugin, $wp_rewrite;
        if( $gts_plugin->language && !$query_vars[GtsLinkRewriter::$LANG_PARAM] ) {
            $query_vars[GtsLinkRewriter::$LANG_PARAM] = $gts_plugin->language;
        }

        // WP 3.1 compatibility (and b/w compatibile)...  starting with WP3.1, a custom taxonomy query no longer sets
        // the term query var.  but our code depends on it, so we'll just fake it.  this is a two
        // part hack b/c later we'll have to take from the term and substitute it back into the taxonomy.
        //
        // of course, WP2.9.X doesn't have the get_taxonomies method, but that's okay b/c it works without this hack.
        if( function_exists( 'get_taxonomies' ) ) {
            $taxonomies = get_taxonomies(array(), 'objects');
            foreach( $taxonomies as $taxonomy ) {
                if( !$taxonomy->_builtin && $query_vars[ $taxonomy->query_var ] && !$query_vars[ 'term' ] ) {
                    $query_vars[ 'term' ] = $query_vars[ $taxonomy->query_var ];
                }
            }
        }

        // this is for non-western character support in permalinks...  the values won't have been URL decoded
        // yet b/c WP is expecting ASCII...  only mess with values that we know can contain UTF-8 chars.
        if( $wp_rewrite->permalink_structure && $langCode = $query_vars[GtsLinkRewriter::$LANG_PARAM] ) {
            $langObj = com_gts_Language::get_by_code( $langCode );
            if( !$langObj->latin ) {
                foreach ( array( 'tag', 'name', 'category_name', 'term', 'pagename' ) as $param_name ) {
                    if ( $query_vars[ $param_name ] ) {

                        // this value *should* be url encoded.  if it came in via get parameters, then
                        // it will have been decoded already, but we also wouldn't enter into this loop!
                        $encoded_val = $query_vars[ $param_name ];
                        $query_vars[ $param_name ] = urldecode( $query_vars[ $param_name ] );

                        // more permalink madness.  when WP goes through to attempt canonicalization, it looks at the
                        // $_SERVER array for the REQUEST_URI.  it then "sanitizes" it, which for us means removing all
                        // extended UTF-8 characters.  we'll need to fix that here or risk categories not working...
                        //
                        // what's even stranger, in some cases, the value is already decoded but in others no.  i've witnessed
                        // this on systems where Russian (2 byte codes) is encoded but Japanese (3 byte codes) is not.  freaking
                        // weird...
                        $to_replace = $encoded_val != $query_vars[$param_name] ? $encoded_val : urlencode($query_vars[$param_name]);
                        $_SERVER['REQUEST_URI'] = str_replace( $to_replace, $query_vars[ $param_name ], $_SERVER['REQUEST_URI'] );
                    }
                }
            }
        }

        // HACK ALERT! : this is a special case to cover if the permalink is just the postname.  it becomes very
        // difficult to tell the difference between pages and posts in that case b/c WP gets silly about matching.
        // so, if we find an attachment, there's a decent chance that it's actually a nested page.  check for it!
        if( preg_match('/^\/%postname%(\/)?$/', $wp_rewrite->permalink_structure) && $query_vars['attachment'] ) {
            global $gts_plugin;
            if( $page = $gts_plugin->get_translated_blog_post_by_slug( $query_vars['attachment'], $query_vars['gts_lang'] ) ) {
                $query_vars['pagename'] = $this->get_page_path( get_page( $page->local_id) );
                $query_vars['attachment'] = null;
            }
        }

        // these are stashed so that the widget can later get at them without having been
        // overwritten in the query by other plugins (e.g. Simply Exclude)
        $this->original_tag = $query_vars['tag'];
        $this->original_term = $query_vars['term'];
        $this->original_category_id = $query_vars['cat'];
        $this->original_category_name = $query_vars['category_name'];

        // for simplicity sake, only track the leaf category in the case of nesting.
        $idx = strrpos( $this->original_category_name, '/' );
        if( $idx !== FALSE && $idx != strlen( $this->original_category_name ) - 1 ) {
            $this->original_category_name = substr( $this->original_category_name, $idx + 1 );
        }

        // HACK ALERT! : when we replace the slug, we'll set pagename and unset name if the post is actually a page.
        // this can happen when the peramlink is only the post name...there are conflict for more than a single-word
        // page name.  so after replacing all the slugs, we'll rewrite the pagename only if we haven't already!
        $has_name = $query_vars['name'];

        // now do replacements for any of our translated slugs.
        $this->replace_with_slug( $query_vars, 'tag' );
        $this->replace_with_slug( $query_vars, 'name' );
        $this->replace_with_slug( $query_vars, 'category_name' );
        $this->replace_with_slug( $query_vars, 'term' );

        if( !$has_name && $query_vars[GtsLinkRewriter::$LANG_PARAM] && $query_vars['pagename'] ) {
            $query_vars['pagename'] = $this->get_original_pagename( $query_vars['pagename'], $query_vars['gts_lang'] );
        }

        // this one is always set regardless of language.  we use it later to pick up pages because wordpress
        // rewrites the pagename param to be just the last portion, which can't easily be used for lookups.
        if( $query_vars['pagename'] ) {
            $query_vars[GtsLinkRewriter::$PAGEPATH_PARAM] = $query_vars['pagename'];
        }

        // WP 3.1 custom taxonomy part 2...  now that we have the original term text, overwrite the custom
        // taxonomy's query variable value with the source text.
        if( isset( $taxonomies ) ) {
            foreach( $taxonomies as $taxonomy ) {
                if( !$taxonomy->_builtin && $query_vars[ $taxonomy->query_var ] && $query_vars[ 'term' ] ) {
                    $query_vars[ $taxonomy->query_var ] = $query_vars[ 'term' ];
                }
            }
        }

        return $query_vars;
    }


    function get_original_pagename( $translated_page, $language ) {

        global $gts_plugin;

        $original_path = array();

        foreach ( explode( '/', $translated_page ) as $part ) {
            if( $part ) {

                $tpost = $gts_plugin->get_translated_blog_post_by_slug( $part, $language );
                if( $tpost ) {
                    $post = get_page( $tpost->local_id );
                }
                else {
                    $post = get_page_by_title( $part );
                }

                $original_path[] = $post ? $post->post_name : $part;
            }
        }

        return implode( '/', $original_path );
    }



    function replace_with_slug( &$query_vars, $param_name ) {

        global $gts_plugin;

        // this method gets called from code points before and after the variables are bound.
        // if before, then we need to go to the theme language variable, even though that could
        // in theory be wrong b/c it's not the *final* value.
        if(! $language = $gts_plugin->language ) {
            $language = $gts_plugin->theme_language;
        }

        if( $language && $param_value = $query_vars[$param_name] ) {

            if( $param_name == 'name' ) {
                if( $translated_post = $gts_plugin->get_translated_blog_post_by_slug( $param_value, $language ) ) {
                    if( $original_post = get_post( $translated_post->local_id) ) {
                        if( $original_post->post_type == 'page' ) {
                            $query_vars['name'] = null;
                            $query_vars['pagename'] = $this->get_page_path( $original_post );
                        }
                        else {
                            $query_vars[$param_name] = $original_post->post_name;
                        }
                    }
                }
            }
            else {

                // to catch the nested category case.
                if( $param_name == 'category_name' ) {
                    $param_value = $this->original_category_name;
                }

                // todo - need to distinguish between category and tag in case translations collide.
                if( $translated_term = $gts_plugin->get_translated_blog_term_by_slug( $param_value, $language ) ) {
                    $query_vars[$param_name] = $this->get_original_slug( $translated_term->local_name );
                }
            }
        }
    }

    function get_page_path( $page ) {

        if( $page->post_parent ) {
            $prefix = $this->get_page_path( get_page( $page->post_parent ) );
        }

        return "$prefix/" . $page->post_name;
    }


    function get_original_slug( $id ) {
        global $wpdb;
        return $wpdb->get_var("SELECT slug FROM " . $wpdb->prefix . "terms WHERE term_id = $id" );
    }



    function add_query_params( $query_vars ) {
        array_push( $query_vars , GtsLinkRewriter::$LANG_PARAM );
        return $query_vars;
    }

    function append_rewrite_rules( $rules ) {

        global $wp_rewrite;

        // only bother messing with this stuff if we have permalinks enabled.
        // otherwise, we just do like WP and return the empty rules array.
        if( $wp_rewrite->permalink_structure ) {

            $lang_regex = '(' . GtsPluginWordpress::$LANGUAGE_CODE_REGEX . ')';
            $wp_rewrite->add_rewrite_tag('%'.GtsLinkRewriter::$LANG_PARAM.'%', $lang_regex, GtsLinkRewriter::$LANG_PARAM . '=' );

            // todo - option-ize and localize this prefix.
            // todo - also localize tag/category in url.
            $lang_prefix = 'language/%' . GtsLinkRewriter::$LANG_PARAM . '%';

            $newrules = array();


            // GENERAL NOTE : in WP < 3.1, most of the permastructs did not have a leading slash.  in 3.1, they started putting slashes on, and that of
            // course broke all of the rewrite rules.  accordingly, to keep compatibility between the two version, we have to make sure that every
            // permastruct makes sure that it has the slash...we'll do this using the leadingslashit method.

            // date rewrites come first because otherwise they get eaten by the permalink rewrite rule.  they
            // are ordered in order of least specific to most specific.
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_year_permastruct() ), EP_YEAR );
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_month_permastruct() ), EP_MONTH );
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_day_permastruct() ), EP_DAY );
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_date_permastruct() ), EP_DATE );

            // tags and categories also come before permalinks b/c they can otherwise be eaten by the attachments in the case
            // that the permalink is *just* the postname.
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_tag_permastruct() ), EP_TAGS );
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_category_permastruct() ), EP_CATEGORIES );

            // this seems to be for custom taxonomies (i couldn't find anything else under WP that uses it...).  it also needs
            // to come before the permalink b/c it will act more or less like a normal category/tag.
            foreach( $wp_rewrite->extra_permastructs as $name => $permastruct ) {
                $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $permastruct[0] ), EP_CATEGORIES );
            }

            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_author_permastruct() ), EP_AUTHORS );
            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_search_permastruct() ), EP_SEARCH );

            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->comments_base ), EP_COMMENTS, true, true, true, false );

            $newrules += $wp_rewrite->generate_rewrite_rules( $lang_prefix . $this->leadingslashit( $wp_rewrite->permalink_structure ), EP_PERMALINK );

            // this appears to be a bug in wordpress that we need to work around...  it's eating up our gts_lang variable in places relating
            // to attachments.  this hack seems to fix it, but only when we run it just after adding the permalink rules.  not sure why it has
            // to be here rather than at the end of the function, but i'm not messing around with it further.
            foreach ( $newrules as $match => $params ) {
                if( !preg_match( '/gts_lang=/', $params )) {

                    unset($newrules[$match]);

                    $match = preg_replace( '/' . preg_quote( $lang_regex ) . '/', '($1)', $match );
                    $params = $params . '&gts_lang=$matches[0]';
                    $params = preg_replace_callback( '/\$matches\[(\d+)\]/', array( $this, 'callback_reindex_match_array' ), $params);

                    $newrules[$match] = $params;
                }
            }

            // these two go last b/c it will otherwise match some of the above patterns.
            $newrules += $wp_rewrite->generate_rewrite_rule( $lang_prefix . $this->leadingslashit( $wp_rewrite->get_page_permastruct() ), EP_PAGES );
            $newrules += $wp_rewrite->generate_rewrite_rule( $lang_prefix, EP_ROOT );

            return $newrules + $rules;
        }

        return $rules;
    }

    function leadingslashit( $str ) {

        if( $str && strlen( $str ) && $str[0] != '/' ) {
            $str = "/$str";
        }

        return $str;
    }

    function callback_reindex_match_array( $matches ) {
        return '$matches[' . ($matches[1] + 1) . ']';
    }


    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
