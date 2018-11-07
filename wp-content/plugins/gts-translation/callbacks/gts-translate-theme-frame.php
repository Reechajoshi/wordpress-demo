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

// because we don't have WP protecting us here and because translating templates is
// an expensive operation, don't want to leave this open to the outside world.  require
// that there's a user logged-in.
auth_redirect();

// and now we're going to make sure that they have permissions to be doing this, too
if( !current_user_can( 'manage_options' ) ) {
    wp_redirect('404.php', 404 );
}


// this isn't part of the standard PHP install, but we'll want to
// tell apache not to gzip if that's how it's configured.
if( function_exists( 'apache_setenv' ) ) {
    @apache_setenv('no-gzip', 1);
}

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);

?>
<html>
<head>

    <link rel="stylesheet" type="text/css" href="../wordpress/css/smoothness/jquery-ui-1.7.3.custom.css"/> 
    <script type="text/javascript" src="../wordpress/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="../wordpress/js/jquery-ui-1.7.3.custom.min.js"></script>
    <script type="text/javascript">
        var $jq = jQuery.noConflict();

        function resetFrame() {
            $jq('#finishedContainer').hide();
            $jq('#translate_theme_iframe', parent.document).hide();
            $jq('#translate_theme_button', parent.document).show();
        }
    </script>

</head>
<body bgcolor="white">
<div id="progressContainer">
    <div id="progressbar" style="width:300px; height: 16px"></div>

    <p id="translating_text">Translating theme file <span id="translating_file"></span>...</p>
    <p id="finished_text" style="display:none; background-color:green">Translated!</p>
</div>
<div id="finishedContainer" style="display: none">
    <p style="text-align: center">
        <span id="finishedMessage"></span></br>
        <input type="button" value="OK" onclick="resetFrame()"/>
    </p>
</div>
</body>
</html>

<script type="text/javascript">
    $jq('#progressbar').progressbar({ value: 0 });

    function finish( msg, success ) {

        $jq('#finishedMessage').text(msg);

        if(!success) {
            $jq('#finishedMessage').css('color', '#ff3333'); 
        }

        $jq('#progressContainer').hide();
        $jq('#finishedContainer').show();
    }
</script>

<?php

global $gts_plugin;
$num_files = count( $gts_plugin->get_template_filenames() );
$num_translated = 0;

function callback_update_dom( $theme, $file ) { ?>
    <script type="text/javascript">
        $jq('#translating_file').html('<?php echo $file ?>');
    </script>
<?php
}

function callback_update_count( $theme, $file ) {
    global $num_translated;
    $num_translated++;  ?>

    <script type="text/javascript">
        $jq('#progressbar').progressbar( "option", "value", <?php global $num_translated, $num_files; echo ($num_translated / $num_files) * 100; ?>);
    </script>
<?php
}

try {
    $gts_plugin->translate_current_theme( 'callback_update_dom', 'callback_update_count' );
    ?>

    <script type="text/javascript">
        finish('Translated Successfully!', true);
    </script>
    <?php
}
catch(Exception $e) {
    ?>

    <script type="text/javascript">
        finish('Translation failed : <?php echo preg_replace("/\\'/", "'", $e->getMessage()) ?>', false);
    </script>
    <?php
}