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


abstract class com_gts_XMLWireType {

    
    abstract function get_type_name();

    function add_attributes( $xml ) {}

    function is_polymorphic( $property_name ) {
        return false;
    }

    function get_inner_element_name( $container_name , $entry ) {

        if( $entry instanceof com_gts_XMLWireType ) {
            return $entry->get_type_name();
        }

        return "node"; 
    }

    function __toString() {
        return $this->get_type_name();
    }


    function to_utf8_string() {
        return $this->to_simplexml()->asXML();
    }


    function to_simplexml() {
        $xml = new SimpleXMLElement("<" . $this->get_type_name() . "/>");
        $this->add_attributes( $xml );
        $this->to_simplexml_internal($xml);
        return $xml;
    }


    protected function to_simplexml_internal( $xml ) {

        $reflector = new ReflectionClass(get_class($this));
        foreach( $reflector->getProperties() as $property ) {

            if( !$property->isStatic()) {

                $name = $property->getName();
                $value = $property->getValue($this);

                if( isset($value) ) {

                    if( is_array($value) ) {

                        $xmllist = $xml->addChild($name);

                        foreach( $value as $entry ) {
                            $inner_element_name = $this->get_inner_element_name( $name, $entry );

                            if( $entry instanceof com_gts_XMLWireType) {
                                $entry->to_simplexml_internal($xmllist->addChild($inner_element_name));
                            }
                            else {
                                $xmllist->addChild( $inner_element_name , $entry );
                            }
                        }
                    }
                    else if ( $value instanceof com_gts_XMLWireType ) {

                        if( $this->is_polymorphic( $property->getName()) ) {
                            $insertion_point = $xml->addChild( $property->getName() );
                            $value->to_simplexml_internal( $insertion_point->addChild( $value->get_type_name() ) );
                        }
                        else {
                            $value->to_simplexml_internal( $xml->addChild( $property->getName() ) );
                        }
                    }
                    else {
                        $xml->addChild( $property->getName() , htmlspecialchars( $property->getValue($this), ENT_NOQUOTES, 'UTF-8' ) );
                    }
                }
            }
        }
    }
}
