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


class com_gts_BlogTranslatorApiRequest extends com_gts_XMLWireType {

    var $blogId;
    var $synchronous;
    var $payload;

    function __construct( $blogId, $payload, $synchronous ) {
        $this->blogId = $blogId;
        $this->synchronous = $synchronous ? "true" : "false";
        $this->payload = $payload;
    }

    function get_type_name() {
        return "blogTranslatorApiRequest";
    }

    function add_attributes( $xml ) {
        $xml->addAttribute( "apiVersion", "1.0" );
    }

    function is_polymorphic( $property_name ) {
        return "payload" == $property_name;
    }
}


class com_gts_Options extends com_gts_XMLWireType {

    var $options = array();

    function get_type_name() {
        return "options";
    }

    protected function to_simplexml_internal( $xml ) {
        foreach($this->options as $option) {
            $option->to_simplexml_internal($xml->addChild("option"));
        }
    }
}

class com_gts_BlogOption extends com_gts_XMLWireType {

    var $remoteId;
    var $name;
    var $value;

    function __construct( $name, $value ) {
        $this->remoteId = $this->name = $name;
        $this->value = $value;
    }

    function get_type_name() {
        return "blogOption";
    }
}


class com_gts_BlogPosts extends com_gts_XMLWireType {

    var $posts = array();

    function get_type_name() {
        return "blogPosts";
    }

    protected function to_simplexml_internal( $xml ) {
        foreach($this->posts as $post) {
            $post->to_simplexml_internal($xml->addChild("blogPost"));
        }
    }
}


class com_gts_BlogPost extends com_gts_XMLWireType {

    var $remoteId;
    var $language;

    var $author;
    var $title;
    var $excerpt;
    var $body;
    var $slug;

    var $tags = array();


    function get_type_name() {
        return "blogPost";
    }


    function get_inner_element_name( $name, $entry ) {
        if( "tags" == $name ) {
            return "tag";
        }
    }
}


class com_gts_BlogTerm extends com_gts_XMLWireType {

    var $remoteId;
    var $term;
    var $slug;
    var $description;

    function get_type_name() {
        return "blogTerm";
    }
}



class com_gts_Terms extends com_gts_XMLWireType {

    var $terms = array();

    function get_type_name() {
        return "terms";
    }

    protected function to_simplexml_internal( $xml ) {
        foreach($this->terms as $term) {
            $term->to_simplexml_internal($xml->addChild("term"));
        }
    }
}


class com_gts_Languages extends com_gts_XMLWireType {

    var $languages = array();

    function get_type_name() {
        return "languages";
    }

    protected function to_simplexml_internal( $xml ) {
        foreach( $this->languages as $language ) {
            $xml->addChild( "language" )->addChild( "code", $language->code );
        }
    }
}



class com_gts_BlogTemplate extends com_gts_XMLWireType {

    var $remoteId;
    var $language;

    var $theme;
    var $path;
    var $text;

    function get_type_name() {
        return "blogTemplate";
    }
}



class com_gts_EchoType extends com_gts_XMLWireType {

    var $string;

    function __construct( $string ) {
        $this->string = $string;
    }

    function get_type_name() {
        return "echo";
    }
}
