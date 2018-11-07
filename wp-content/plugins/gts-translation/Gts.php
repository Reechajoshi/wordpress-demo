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


/*

Plugin Name: GTS Translation
Plugin URI: http://gts-translation.com/
Description: This plugin is guaranteed to drive more international traffic to your site by providing high quality translation, and SEO of your translated content.
Version: 1.2.2
Author: Steve van Loben Sels
Author URI: http://gts-translation.com/

Requires WP 3.0+, PHP 5.1.3+

*/


require_once 'GtsPlugin.php';
require_once 'wordpress/GtsWidgets.php';


//
// we only want to register the activation hooks when we're being called as part of the wordpress
// runtime and not in callback land.  what happens in a callback is that we end up in a chicken/egg
// problem because the env is loading up wp before the env is actually created.  accordingly, we'll
// have a null env object when wp in turns loads up this file.
//
// effectively, it's not a problem that we won't have plugin hooks in our callbacks.  the callbacks
// exist only for persisting our translated data, so they shouldn't be relying on any of the hooks
// or filters in the wp runtime.  if they do, then that means we've got a design problem!
//
if ( $gts_plugin ) {

    $gts_plugin->register_plugin_hooks();

    register_activation_hook( 'gts-translation/Gts.php' , array($gts_plugin, 'activate_plugin') );
    register_deactivation_hook( 'gts-translation/Gts.php' , array($gts_plugin, 'deactivate_plugin') );
}


/**
 * gets the homepage link for the blog with language information appended.
 * @param bool $echo if set (by default), will echo the url to stdout.
 * @return string
 */
function gts_get_homepage_link( $echo = true ) {
    global $gts_plugin;

    $link = $gts_plugin->link_rewriter->add_language_parameter( trailingslashit( get_option( 'home' ) ) );

    if( $echo ) {
        echo $link;
    }
    else {
        return $link;
    }
}

?>
