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

$host = $gts_plugin->config->api_host;
$port = $gts_plugin->config->secure_api_port;

$home = get_option('home');
$plugin_path = GTS_PLUGIN_URL;
if ( strpos( $plugin_path, $home ) == 0) {
    $plugin_path = substr( $plugin_path, strlen( $home ) );
}

global $current_user;
get_currentuserinfo();
$admin_email = $current_user->user_email;

if ( ! $auth = get_option( GTS_AUTHORIZATION_OPTION_NAME ) ) {
    $auth = array(
        'code' => GtsUtils::random_alphanumeric( 128 ),
        'email' => $admin_email,
    );
    update_option( GTS_AUTHORIZATION_OPTION_NAME, $auth );
}


if ( $auth['email'] != $admin_email ) {
    $auth['email'] = $admin_email;
    update_option( GTS_AUTHORIZATION_OPTION_NAME, $auth );
}

$args = array(
    'auth' => $auth['code'],
    'blogUrl' => $home,
    'blogTitle' => get_option('blogname'),
    'blogDescription' => get_option('blogdescription'),
    'pluginPath' => $plugin_path,
    'adminEmail' => $admin_email,
);

$url = "https://$host" . ( $port == 443 ? '' : ":$port") . "/api/setup/landing";


?>
<div class="wrap" style="width: 60%">

    <h2>GTS Translation</h2>

<?php if( count( com_gts_Language::$ALL_LANGUAGES ) == 0 ) {

    try {
        $gts_plugin->fetch_and_cache_available_languages();
    }
    catch(Exception $e) {}
}

if ( $e ) {

    ?>

    <p>
        In order to configure and activate the plugin, it first must download a list of available languages from GTS.  We encountered an
        error while trying to fetch this information (technical reason : <?php echo $e->getMessage() ?>).
    </p>

    <p>
        Click <a onclick="window.location.reload(); return false;" href="#">here</a> to try again.  If the problem persists, please contact
         <a href="mailto:info@gts-translation.com">info@gts-translation.com</a>.
    </p>

<?php } else { ?>

    <form id="registrationForm" method="post" action="<?php echo $url; ?>" enctype="application/x-www-form-urlencoded;charset=utf-8">
    <?php foreach ( $args as $key => $value ) { ?>
        <input type="hidden" name="<?php echo $key ?>" value="<?php echo htmlentities( $value ) ?>"/>
    <?php } ?>
    </form>

    <p>
        Before we can start translating your blog, you need to
        <a style="font-weight: bold; cursor: pointer" onclick="document.getElementById('registrationForm').submit()">register</a> with GTS.
    </p>

    <p>
        If you have previously registered and have the activation information GTS sent to you via email, please follow the
        link provided in that mail to finalize the registration process.  If you cannot locate the email, please get in touch
        with <a href="mailto:info@gts-translation.com">info@gts-translation.com</a>.
    </p>

<?php } ?>

</div>