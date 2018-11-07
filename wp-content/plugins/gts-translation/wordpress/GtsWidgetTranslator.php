<?php

/*
 * Copyright (c) 2012, Localization Technologies (LT) LLC
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

class GtsWidgetTranslator
{

    const TRANSLATED_OPTION_PREFIX = 'gts_widgets_%s_';
    
    // define hook prefixes and method prefixes
    public static function getMethodPrefixes()
    {
        return array(
            'filter_option' => 'option_',
            'action_add_option' => 'add_option_',
            'action_update_option' => 'update_option_',
        );
    }

    // define the relevant options list
    public static function getOptions()
    {
        return array(
            'widget_archives',
            'widget_calendar',
            'widget_categories',
            'widget_links',
            'widget_meta',
            'widget_nav_menu',
            'widget_pages',
            'widget_recent-comments',
            'widget_recent-posts',
            'widget_rss',
            'widget_search',
            'widget_tag_cloud',
            'widget_text',
            'widget_widget_twentyevelen_ephemera',
        );
    }

    public function __call($name, $args)
    {
        // get the calling name and filter out the prefix
        foreach (self::getMethodPrefixes() as $methodPrefix => $hookName)
        {
            if (preg_match('/^' . $methodPrefix . '_/', $name))
            {
                $name = preg_replace('/^(' . $methodPrefix . '_)/', '', $name);

                return call_user_func( array($this, $methodPrefix), $name, $args);
            }
        }
    }

    public function init()
    {
        // get list of options
        $options = self::getOptions();

        foreach ($options as $optionName)
        {
            // add a hook filter
            foreach (self::getMethodPrefixes() as $methodPrefix => $hookPrefix)
            {
                // a filter
                if (preg_match('/^filter_/', $methodPrefix))
                {
                    // only add this filter if we are NOT in admin
                    if (!is_admin())
                    {
                        add_filter($hookPrefix . $optionName, array($this, $methodPrefix . '_' . $optionName), 10, 2);
                    }
                }
                elseif (preg_match('/^action_/', $methodPrefix))
                { // an action
                    add_action($hookPrefix . $optionName, array($this, $methodPrefix . '_' . $optionName), 10, 2);
                }
            }
        }
    }

    // translate the option
    public function translateOption($optionName, $optionValue)
    {
        global $gts_plugin;
        // create xml to send for translation
        $translateXml = $this->encapsulateOption($optionName, $optionValue);
        //translate_named_option translate the xml
        $gts_plugin->translate_named_option($optionName, $translateXml);
    }
    
    public function processTranslatedOption($resultXml, $originalOption = null)
    {
        //transform xml back to options array
        
        $translatedOptions = $this->unencapsulateOption($resultXml);
        foreach($translatedOptions as $optionName => $translatedOption)
        {
            if(!$originalOption)
            {
                $originalOption = get_option($optionName);
            }
            
            if($originalOption)
            {
                foreach($originalOption as $key => $optionVal)
                {
                    if(isset($translatedOptions[$optionName][$key]) && is_array($optionVal))
                    {
                                
                        foreach($optionVal as $optionKey => $value)
                        {
                            if(isset($translatedOptions[$optionName][$key][$optionKey]))
                            {
                                $originalOption[$key][$optionKey] = (string) $translatedOptions[$optionName][$key][$optionKey];
                            }
                        }
                    }
                }
            }
        }
        
        if($originalOption)
        {
            return $originalOption;
        }
        else
        {
            return $resultXml;
        }
        
    }
    
    // encapsulate the options array and return XML
    public function encapsulateOption($name, $option)
    {
        $xml = '<html><body id="gts_widget">';
        
        if(is_array($option))
        {
            foreach ($option as $key => $value)
            {
                // translate the title
                if (isset($value['title']))
                {
                    $xml .= '<div name="'.$name.'" id="'.$key.'" key="title" class="text">';
                    $xml .= htmlentities($value['title']);
                    $xml .= '</div>';
                }
                // translate the text if any
                if (isset($value['text']))
                {
                    $xml .= '<div name="'.$name.'" id="'.$key.'" key="text" class="html">';
                    $xml .= $value['text'];
                    $xml .= '</div>';
                }

            }
        }
        
        $xml .= '</body></html>';
        return $xml;
    }
    
    
    // open the xml and return array
    public function unencapsulateOption($xml)
    {
        $options = array();
        $num = preg_match_all('/<div name="(.*)" id="(.*)" key="(.*)" class="(.*)">(.*)<\/div>/sU', $xml, $matches, PREG_SET_ORDER);
        if($num)
        {
            // iterate over each tag
            foreach($matches as $key => $match)
            {
                list(, $name, $id, $key, $class, $value) = $match;
                if(!isset($options[$name]))
                {
                    $options[$name] = array();
                }

                if(!isset($options[$name][$id]))
                {
                    $options[$name][$id] = array();
                }

                if(!isset($options[$name][$id][$key]))
                {
                    if($class == 'html')
                    {
                        $options[$name][$id][$key] = $value;
                    }
                    elseif($class == 'text')
                    {
                        $options[$name][$id][$key] = html_entity_decode($value);
                    }
                }
            }
        }
        
        return $options;
        
    }


    // filter hook for get option
    public function filter_option($name, $args)
    {
        global $gts_plugin;
        $conf = isset($args[0]) && is_array($args[0]) ? $args[0] : array();
        $translated_option = $gts_plugin->get_translated_named_option( $name, $gts_plugin->language );

        if( $translated_option && isset($translated_option->value)) 
        {
            $optionValue = unserialize($translated_option->value);
            
            if(is_array($optionValue))
            {
                
                return $optionValue;
            }
            else
            {
                return $conf;
            }
        }
        else
        {
            return $conf;
        }
    }

    // action hook for add_option
    public function action_add_option($name, $args)
    {
        // call update_option
        return $this->action_update_option($name, $args);
    }

    // action hook for update_option
    public function action_update_option($name, $args)
    {
        // extract the arguments of the callback
        list(, $newValues) = $args;
        
        // translate the option
        $this->translateOption($name, $newValues);

        return $newValues;
    }
    
    // hook function for activation of plugin
    public function on_activation()
    {
        // HACK!  this should be fixed to run via cron.
        global $gts_plugin;
        if(!$gts_plugin)
        {
            $gts_plugin = new GtsPluginWordpress();

            $gts_plugin->ensure_db_current();
            $gts_plugin->construct_api_client();
            $gts_plugin->load_available_languages();
        }

        $widgetOptions = array();
        // translate all the current options for widgets
        foreach(self::getOptions() as $optionName)
        {
            // get the option value
            $widgetOptions[$optionName] = get_option($optionName);
        }
        // multi translate the widget options
        foreach($widgetOptions as $optionName => $optionValue)
        {
            $this->translateOption($optionName, $optionValue);
        }
    }
}
