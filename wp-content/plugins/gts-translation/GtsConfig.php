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

require_once("lib/Language.php");


define( 'GTS_DEFAULT_API_HOST', in_array( $_SERVER['SERVER_NAME'], array( 'wp.local', 'localhost', '127.0.0.1', 'sven.local', 'wpmayor.local', 'gts-plugin.local') ) ? '127.0.0.1' : 'translate.gts-translation.com' );
define( 'GTS_DEFAULT_API_PORT', GTS_DEFAULT_API_HOST == '127.0.0.1' ? 8080 : 80 );
define( 'GTS_DEFAULT_SECURE_API_PORT', GTS_DEFAULT_API_HOST == '127.0.0.1' ? 8443 : 443 );


class GtsConfig {

    var $blog_id;
    var $api_key;
    var $api_host = GTS_DEFAULT_API_HOST;
    var $api_port = GTS_DEFAULT_API_PORT;
    var $secure_api_port = GTS_DEFAULT_SECURE_API_PORT;

    var $source_language = "en";
    var $target_languages;
    var $target_hostnames = array();

    var $info_messages = array();
    var $error_messages = array();

    var $plugin_version; // = GTS_PLUGIN_VERSION;
    var $plugin_initialized;

    var $synchronous;
    var $use_translated_theme;
    var $auto_detect_language = true;
}
