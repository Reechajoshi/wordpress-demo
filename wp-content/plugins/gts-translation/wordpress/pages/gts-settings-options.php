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


global $gts_plugin;

$options = get_option( GTS_OPTION_NAME );

// this is a bootstrap.  the first time we land on this page, we'll
// save the default options object.
if(!$options) {
    $gts_plugin->save_config();
    $options = get_option( GTS_OPTION_NAME );
}

$options_initialized = false;
if ( $_GET['initialize'] ) {

    $auth = $_POST['auth'] ? $_POST['auth'] : $_GET['auth'];
    $blog_id = $_POST['blogId'] ? $_POST['blogId'] : $_GET['blogId'];
    $api_key = $_POST['apiKey'] ? $_POST['apiKey'] : $_GET['apiKey'];
    $languages = $_POST['languages'] ? $_POST['languages'] : $_GET['languages'];

    if( $auth && $blog_id && $api_key && $languages ) {

        if( $gts_plugin->get_validation_code() === $auth ) {

            $gts_plugin->config->blog_id = $blog_id;
            $gts_plugin->config->api_key = $api_key;
            $gts_plugin->config->target_languages = explode(',', $languages);
            $gts_plugin->config->synchronous = 'true' == ($_POST['publishImmediately'] ? $_POST['publishImmediately'] : $_GET['publishImmediately']);
            $gts_plugin->config->plugin_initialized = true;

            $gts_plugin->save_config();
            $options = get_option( GTS_OPTION_NAME );

            // clear our auth option now that we've successfully initialized...
            delete_option( GTS_AUTHORIZATION_OPTION_NAME );

            $options_initialized = true;
        }
    }

    try {
        $gts_plugin->initialize_available_languages( $gts_plugin->fetch_and_cache_available_languages() );
    }
    catch(Exception $e) {
        $gts_plugin->send_error_notification("Unable to Load Languages", "We're currently unable to load the list of supported languages...please try again later.");
    }
}
else {
    try {
        $options[GTS_SETTING_TARGET_LANGUAGES] = $gts_plugin->get_configured_languages();
    }
    catch(Exception $e) {
        $using_cached_languages = true;
    }

}


$set_languages = $options[GTS_SETTING_TARGET_LANGUAGES];
$is_debug = GTS_DEBUG_MODE || $_GET['gts_debug'];


function format_api_key( $key, $chunk = 32 ) {

    $strlen = strlen($key);

    for ( $i = 0; $i < $strlen; $i += $chunk ) {
        $result .= substr( $key, $i, min($chunk, $strlen - $i) ) . "\n";
    }

    return $result;
}

?>
<div class="wrap">
    <h2>GTS Translation</h2>

    <?php if( $options_initialized ) { ?>
        <div class="updated">
            <p>Congratulations!  Your blog has been successfully configured and is ready to start translating.</p>
            <p>There are other configuration options here that you can now set...</p>
        </div>
    <?php } ?>

    <?php if( !is_active_widget( false, false, 'gts_languageselectwidget' ) ) { ?>
        <div class="updated">
            You haven't activated the <i>GTS Language Selector</i> widget in your theme.  Users won't be able to dynamically change the language.
            To activate the widget, navigate to the <a href="widgets.php">Widgets Panel</a> and drag the <i>Gts Language Selector</i> to one
            of the available sidebars.
        </div>
    <?php } ?>

    <?php if( $using_cached_languages ) { ?>
        <div class="updated">
            The GTS API is currently not reachable...you won't be able to change language selection...
        </div>
    <?php } ?>

    <?php if( !$gts_plugin->is_plugin_theme_directory_writable() ) { ?>
        <div class="updated">
            Your plugin directory isn't <a href="http://codex.wordpress.org/Changing_File_Permissions">writable</a>, so localization
            files cannot be downloaded!<br/>
        <code><?php echo is_dir(GTS_I18N_DIR) ? GTS_I18N_DIR : GTS_PLUGIN_DIR; ?></code>
    </div>
    <?php } ?>

    <script type="text/javascript">
        var $jq = jQuery.noConflict();        
        function enableCheckboxes(form) {
            $jq(form).find('input[type=checkbox]').removeAttr('disabled');
        }
    </script>
    <form id="gts_options_form" method="post" action="options.php" onsubmit="enableCheckboxes(this);">
    <?php settings_fields( GTS_SETTING_GROUP ); ?>
        <input type="hidden" name="continue" value="true"/>
        <table class="form-table">

            <tr valign="top">
                <th scope="row">Blog ID</th>
                <td><input type="text" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_BLOG_ID) ?>" value="<?php echo $options[GTS_SETTING_BLOG_ID] ?>"/></td>
            </tr>

            <tr valign="top">
                <th scope="row">API Access Key</th>
                <td><textarea style="font-family:monospace" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_API_KEY) ?>" rows="4" cols="40"><?php echo format_api_key( $options[GTS_SETTING_API_KEY ]); ?></textarea>
                </td>
            </tr>

            <?php if ( $is_debug ) { ?>
            <tr valign="top">
                <th scope="row">API Host</th>
                <td><input type="text" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_API_HOST) ?>" value="<?php echo $options[GTS_SETTING_API_HOST] ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">API Port</th>
                <td><input type="text" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_API_PORT) ?>" value="<?php echo $options[GTS_SETTING_API_PORT] ?>" /></td>
            </tr>
            <?php } ?>

            <tr valign="top">
                <th scope="row">Languages</th>
                <td>
                    <table>
                    <?php foreach(com_gts_Language::$OUTPUT_LANGUAGES as $lang) {
                        $lang_enabled = $set_languages && in_array($lang->code, $set_languages);
                        ?>
                        <tr>
                            <td style="padding: .2em; padding-right: 1em;"><?php echo ($lang->recentlyAdded ? ' <span style="font-weight: bold">NEW!</span>&nbsp;&nbsp;' : '') . "$lang->englishName ($lang->name)" ?> : </td>
                            <td style="padding: .2em;"><input type="checkbox" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_TARGET_LANGUAGES)?>[<?php echo $lang->code?>]"<?php if($lang_enabled) echo " checked"?><?php if($using_cached_languages) echo ' disabled="disabled"'?> onchange="var elems = $jq('.virtualHost-<?php echo $lang->code ?>'); if(this.checked) elems.show(); else elems.hide();"/></td>
                            <td class="virtualHost-<?php echo $lang->code ?>" style="padding: .2em; padding-left: .3em;<?php if(!$lang_enabled) echo ' display:none;'?>">Virtual Host:</td>
                            <td class="virtualHost-<?php echo $lang->code ?>" style="padding: .2em;<?php if(!$lang_enabled) echo ' display:none;'?>"><input type="text" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_TARGET_HOSTS)?>[<?php echo $lang->code?>]" value="<?php echo $options[GTS_SETTING_TARGET_HOSTS][$lang->code] ?>"/></td>
                        </tr>
                    <?php } ?>
                    </table>
                </td>
            </tr>

            <tr valign="top">
                <td scope="row">Display machine-translated content?</td>
                <td><input type="checkbox" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_SYNCHRONOUS) ?>"<?php if($options[GTS_SETTING_SYNCHRONOUS]) echo ' checked';?>/></td>
            </tr>

            <tr valign="top">
                <td scope="row">Auto-detect browser's language and prompt to change if different?</td>
                <td><input type="checkbox" name="<?php echo sprintf('%s[%s]', GTS_OPTION_NAME, GTS_SETTING_AUTO_DETECT_LANG) ?>"<?php if($options[GTS_SETTING_AUTO_DETECT_LANG]) echo ' checked';?>/></td>
            </tr>

            <?php if ( $is_debug ) { ?>

            <script type="text/javascript">

                function ajax_update_cached_languages() {
                    jQuery.post(ajaxurl, { action: 'gts_update_cached_languages' }, function(response) {
                        alert("Languages have been updated...");
                    });
                }

                function ajax_update_cached_mofiles() {
                    jQuery.post(ajaxurl, { action: 'gts_update_cached_mofiles' }, function(response) {
                        alert(".mo files have been updated...");
                    });
                }

                function ajax_reset_plugin() {
                    if ( confirm('Are you sure you want to reset the plugin?  It will also be cleared on the backend.')) {
                        var data  = {
                            action: 'gts_kill_blog'
                        };

                        jQuery.post(ajaxurl, data, function(response) {
                            window.location.reload();
                        });
                    }
                }
            </script>

            <tr valign="top">
                <td scope="row" colspan="2"><a href="javascript:ajax_update_cached_languages();">Update Cached Language Settings</a></td>
            </tr>

            <tr valign="top">
                <td scope="row" colspan="2"><a href="javascript:ajax_update_cached_mofiles();">Update Cached .mo Files</a></td>
            </tr>

            <tr valign="top">
                <td scope="row" colspan="2"><a href="javascript:ajax_reset_plugin();">Kill this blog</a></td>
            </tr>
            <?php } ?>

        </table>

        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>



    </form>
</div>