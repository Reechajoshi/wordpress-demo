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


require_once "XMLWireType.php";
require_once "WireTypes.php";


class com_gts_ApiClientException extends Exception {}

class com_gts_RemoteApiErrorException extends com_gts_ApiClientException {}
class com_gts_MissingPayloadException extends com_gts_ApiClientException {}
class com_gts_MalformedResponseException extends com_gts_ApiClientException {}


class com_gts_ApiClient {


    var $api_host;
    var $api_port;
    var $blog_id;
    var $api_access_key;


    function __construct( $api_host, $api_port, $blog_id, $api_access_key ) {
        $this->api_host = $api_host;
        $this->api_port = $api_port;
        $this->blog_id = $blog_id;
        $this->api_access_key = $api_access_key;
    }


    function get_api_response( $service, $data, $synchronous ) {
        $request = new com_gts_BlogTranslatorApiRequest( $this->blog_id, $data, $synchronous );
        return $this->do_post_request( $service, $request->to_utf8_string() );
    }


    function do_post_request( $service, $data )
    {

        $url = $this->get_api_url($service);
        if(GTS_TEST) {
            echo "calling url : $url\n";
        }

        // we're doing the API call as a straight HTTP post.  i had been using stream_context_create,
        // but some versions of PHP out in the wild require headers to be a single string and others
        // an array...with no acceptance of one by the other.  it's terrible...PHP is a joke sometimes.
        // so...we're reading and writing straight to the wire.
        $headers = array(
            "POST /api/$service HTTP/1.0",
            'Host: ' . $this->api_host,
            'User-agent: ' . $this->get_user_agent(),
            'Content-type: text/xml; charset=utf-8',
            'Content-length: ' . strlen( $data ),
            'X-Gts-Signature: ' . sha1( $data . $this->api_access_key ),
            'X-Gts-Plugin-Version: ' . GTS_PLUGIN_VERSION,
        );

        $fp = @fsockopen( $this->api_host, $this->api_port );
        if(!$fp) {
            throw new com_gts_ApiClientException("Unable to connect to $url");
        }

        foreach ( $headers as $header ) {
            @fwrite( $fp, $header . "\r\n" );
        }
        @fwrite( $fp, "\r\n" );
        @fwrite( $fp, $data );
        @fwrite( $fp, "\r\n\r\n" );


        // in our ghetto wire impl, we'll ignore headers.  when we come across
        // the first blank \r\n, we'll start recording the result.  so lame...stupid PHP.
        $append = false;
        $response = "";
        while ( $str = @fgets( $fp, 4096) ) {
            if( $append ) {
                $response .= $str;
            }
            else {                                      
                $append = $str == "\r\n";
            }
        }

        @fclose( $fp );

        if ( !$response ) {
            throw new com_gts_ApiClientException("Unable to read data from $url");
        }

        if(GTS_TEST) {
            echo "api response : \n$response\n";
        }

        return $this->parse_api_result($response, $url);
    }


    function get_api_url($service) {
        return "http://$this->api_host:$this->api_port/api/$service";
    }


    static function parse_api_result($response, $url) {

        $xml = @simplexml_load_string($response);
        if(!$xml) {
            throw new com_gts_MalformedResponseException("problem parsing response from $url");
        }

        if($xml->error) {
            throw new com_gts_RemoteApiErrorException((string) $xml->error->message);
        }

        if(!$xml->payload) {
            throw new com_gts_MissingPayloadException("missing payload in response from $url");
        }

        return $xml->payload;
    }


    function get_user_agent() {
        return 'GtsTranslationBlogTranslator/' . GTS_PLUGIN_VERSION . ' (' . $this->get_user_agent_additional_info() . ')';
    }

    function get_user_agent_additional_info() {

        global $wp_version; // WP specific!

        $php_version = phpversion();
        $simplexml_version = phpversion('simplexml');

        return "PHP=$php_version,SimpleXML=$simplexml_version,WordPress=$wp_version";
    }
}