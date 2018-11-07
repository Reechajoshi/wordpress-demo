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

$post_lang = $_GET['postLanguage'];
if( ! $post_lang ) {
    $post_lang = $gts_plugin->config->target_languages[0];
}

?>

<script type="text/javascript">
    function ajax_delete_post(postId, postTitle) {
        if ( confirm('Are you sure you want to delete the translated post "' + postTitle + "'?")) {
            var data  = {
                action: 'gts_delete_translated_post',
                id: postId
            };

            jQuery.post(ajaxurl, data, function(response) {
                window.location.reload();
            });
        }
    }
</script>

<h2>Translated Posts</h2>

<p>
    Here, you can remove translations of posts that you don't want to be displayed in your blog.
</p>

<p>
    To edit your posts, please go to the <a href="http://<?php echo $gts_plugin->config->api_host . ($gts_plugin->config->api_port == 80 ? '' :  (':' . $gts_plugin->config->api_port)) ?>/api/">GTS Website</a>.
</p>

<div>
    <form id="gts_template_options_form" method="GET" action="<?php echo remove_query_arg( 'postLanguage', $_SERVER['PHP_SELF'] . '?page=' . $_GET['page'] ) ?>">
        Change Language : <select name="postLanguage" onchange="window.location.href='<?php echo remove_query_arg( 'postLanguage', $_SERVER['PHP_SELF'] . '?page=' . $_GET['page'] ) ?>&postLanguage=' + this.options[this.selectedIndex].value;">
            <?php foreach ( $gts_plugin->config->target_languages as $code ) {
                $lang = com_gts_Language::get_by_code( $code );
                ?>
                <option value="<?php echo $code ?>"<?php if ($code == $post_lang) echo ' selected'?>><?php echo $lang->englishName ?></option>
            <?php } ?>
        </select>
    </form>
</div>

<p/>

<div style="text-align: center">
    <table class="widefat">
        <thead>
        <tr>
            <th scope="col" class="manage-column">Post ID</th>
            <th scope="col" class="manage-column">Post Title</th>
            <th scope="col" class="manage-column">Translated Title</th>
            <th scope="col" class="manage-column">Last Modified</th>
            <th scope="col" class="manage-column">Remove</th>
        </tr>
        </thead>
        <tbody style="text-align: left">
        <?php foreach($gts_plugin->get_translated_blog_post_metadata( $post_lang ) as $tpost) { ?>
        <tr class="active<?php if ($i++ % 2 == 1) echo ' second' ?>">
            <td><a href="<?php echo get_permalink( $tpost->local_id) ?>"><?php echo $tpost->local_id ?></a></td>
            <td><?php echo htmlentities( get_post($tpost->local_id)->post_title, ENT_COMPAT, 'UTF-8' ); ?></td>
            <td><?php echo htmlentities( $tpost->post_title, ENT_COMPAT, 'UTF-8' ); ?></td>
            <td><?php echo htmlentities( $tpost->modified_time, ENT_COMPAT, 'UTF-8' ); ?></td>
            <td><a href="javascript:ajax_delete_post(<?php echo $tpost->id ?>, '<?php echo preg_replace( '/\'/', '\\\'', $tpost->post_title ) ?>');">Remove</a></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>