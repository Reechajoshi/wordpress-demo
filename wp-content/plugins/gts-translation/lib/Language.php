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


class com_gts_Language {

    public static $ALL_LANGUAGES;
    public static $ALL_LANGUAGE_CODES;

    public static $INPUT_LANGUAGES;
    public static $INPUT_LANGUAGE_CODES;

    public static $OUTPUT_LANGUAGES;
    public static $OUTPUT_LANGUAGE_CODES;


    var $code;
    var $name;
    var $englishName;
    var $input;
    var $output;
    var $latin;
    var $recentlyAdded;
    var $displayCountryCode;
    var $wordpressLocaleName;
    var $textDirection;

    var $localizationStrings;

    function __construct( $code, $name, $englishName, $input, $output, $latin, $recentlyAdded,  $displayCountryCode, $wordpressLocaleName, $textDirection, $localizationStrings ) {
        $this->code = $code;
        $this->name = $name;
        $this->englishName = $englishName;
        $this->input = $input;
        $this->output = $output;
        $this->latin = $latin;
        $this->recentlyAdded = $recentlyAdded;
        $this->displayCountryCode = $displayCountryCode;
        $this->wordpressLocaleName = $wordpressLocaleName;
        $this->textDirection = $textDirection;
        $this->localizationStrings = $localizationStrings;
    }

    function __toString() {
        return get_class( $this ) . ":$this->code";
    }

    function get_type_name() {
        return "language";
    }

    static function set_arrays( &$langs, &$codes, $input ) {

        $langs = array();
        $codes = array();

        if( is_array( $input ) ) {
            foreach ( $input as $lang ) {
                array_push( $langs, $lang );
                array_push( $codes, $lang->code );
            }
        }
    }

    public static function get_by_code( $code ) {
        foreach ( com_gts_Language::$ALL_LANGUAGES as $lang ) {
            if( $lang->code == $code ) {
                return $lang;
            }
        }
    }

    static function filter_lang_input( $lang ) {
        return $lang->input;
    }

    static function filter_lang_output( $lang ) {
        return $lang->output;
    }
}
