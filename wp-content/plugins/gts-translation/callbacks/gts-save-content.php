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


require_once '../GtsPlugin.php';

global $gts_plugin;

// this is a whitelist of allowed posters.  when an incoming request comes in, the
// first thing we'll do is make sure that it's one of our authorized hosts.  if it's not,
// then this script will bail and we won't try to do anything with the post data.
$ALLOWED_POSTERS = array(
    '82.80.235.29', // translate.gts-translation.com'
    '82.80.235.38', // old translate.gts-translation.com  (todo - remove this before launch?)
    '127.0.0.1',
);

$remote_addr = $_SERVER['REMOTE_ADDR'];
if( !in_array( $remote_addr, $ALLOWED_POSTERS) ) {
    echo "ERROR : unauthorized host ($remote_addr)";
    return;
}


// now, to make sure nobody is trying to do nasty things and post tons of data here,
// we're going to limit the amount of text we pull in.  the limit here is 256KB + 16 KB fudge
// for the transmission format which should be more than enough for whatever blog post
// we'll be dealing with.  anything else in the post stream is dropped.
define( 'MAX_POST_SIZE', pow( 2, 18 ) + pow( 2, 14 ) );

$xml_text = trim( file_get_contents( 'php://input', MAX_POST_SIZE ) );


// finally, make sure that the signature on the request matches a signature we compute
// using the content and our API key.  if it doesn't match, then the request isn't
// official and we toss it.
$sig = trim( $_SERVER['HTTP_X_GTS_SIGNATURE'] );
$our_sig = strtolower( sha1( $gts_plugin->config->api_key . '~' . $xml_text ) );

if( $sig == $our_sig ) {
    $xml = com_gts_ApiClient::parse_api_result( $xml_text, 'gts-save-content.php' );

    if( $xml->translationResult->translations->blogPost ) {
        $gts_plugin->process_translated_post( $xml->translationResult );
    }
    else if( $xml->translationResult->translations->terms ) {
        $gts_plugin->process_translated_terms( $xml->translationResult );
    }
    else if( $xml->translationResult->translations->options ) {
        $gts_plugin->process_translated_options( $xml->translationResult );
    }
    else {
        echo ("ERROR : not able to process input ($xml_text)");
        return;
    }

    echo "OK";
}
else {
    echo "ERROR : signature mismatch ($sig, $our_sig)";
}

?>