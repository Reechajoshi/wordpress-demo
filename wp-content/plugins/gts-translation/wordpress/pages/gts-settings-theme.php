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

$supports_translated_theme = function_exists('register_theme_directory');
$themes_enabled = get_option( GTS_THEME_OPTION_NAME );

?>

<h2>Translate Theme</h2>

<p style="width: 70%">Use this page to translate your blog's theme's template files.  Since translating PHP files can be tricky, it's possible
that the translation process mangles your PHP code.  If that happens, simply untick the "Use Translated Theme" checkbox.</p>

<p style="width: 80%">Please read the FAQ to get the most out of this feature.</p>

<?php if(!$supports_translated_theme) { ?>
<div class="updated">
    Translating your blog's theme requires WordPress version 2.9.0 or later. Your version
    is <?php global $wp_version; echo $wp_version; ?>
</div>
<?php } else if( !$gts_plugin->is_plugin_theme_directory_writable() ) { ?>
<div class="updated">
    If your plugin directory were <a href="http://codex.wordpress.org/Changing_File_Permissions">writable</a>, you
    could take advantage of translating your blog's theme. <br/>
    <code><?php echo is_dir(GTS_THEME_DIR) ? GTS_THEME_DIR : GTS_PLUGIN_DIR; ?></code>
</div>
<?php } else { ?>
<form id="gts_options_form" method="post" action="options.php">
<?php settings_fields( GTS_THEME_SETTING_GROUP ); ?>
    <table class="form-table">
        <tr valign="top">
            <td scope="row">Translate Themes</td>
            <td>
                <iframe name="translate_theme_iframe" id="translate_theme_iframe" style="display: none" width="400" height="75"
                        scrolling="true"></iframe>
                <input id="translate_theme_button" type="button" value="Translate Theme"
                       onclick="jQuery('#translate_theme_iframe').show(); document.getElementById('translate_theme_iframe').src='<?php echo WP_PLUGIN_URL ?>/gts-translation/callbacks/gts-translate-theme-frame.php'; jQuery(this).hide(); "/>
            </td>
        </tr>
        <tr>
            <td scope="row">Use Translated Theme</td>
            <td><input type="checkbox"
                       name="<?php echo GTS_THEME_OPTION_NAME ?>"<?php if($themes_enabled) echo ' checked';?>/>
            </td>
        </tr>
    </table>

    <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
    </p>

</form>

<?php } ?>