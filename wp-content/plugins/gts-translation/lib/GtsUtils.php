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


class GtsUtils {


    static function mkdir_dash_p( $input_dir, $mode = 0775 ) {

        $dir = $input_dir;
        $to_make = array();
        while ( !file_exists( $dir ) ) {
            array_unshift( $to_make, $dir );
            $dir = dirname( $dir );
        }

        while( $make = array_shift( $to_make ) ) {
            if ( !mkdir( $make, $mode ) ) {
                return false;
            }
        }

        return true;
    }

    static function copy_directory( $src, $dest ) {

        $dir = opendir( $src );
        @mkdir( $dest );

        while( ( $file = readdir( $dir ) ) !== false ) {

            if ( $file == '.' || $file == '..') {
                continue;
            }

            $src_path = $src . DIRECTORY_SEPARATOR . $file;
            $dest_path = $dest . DIRECTORY_SEPARATOR . $file;

            if ( is_dir( $src_path ) ) {
                GtsUtils::copy_directory( $src_path, $dest_path );
            }
            else {
                GtsUtils::stream_copy( $src_path, $dest_path );
            }
        }

        closedir( $dir );
    }

    static function stream_copy($src, $dest)
    {
        $fsrc = @fopen($src,'r');
        $fdest = @fopen($dest,'w+');

        if( $fsrc && $fdest) {

            $len = stream_copy_to_stream($fsrc,$fdest);
            fclose($fsrc);
            fclose($fdest);
            return $len;
        }

        return -1;
    }

    static function list_directory( $dir, $return_hidden = false ) {

        $dirhandle = opendir( $dir );
        $filenames = array();

        if( $dirhandle ) {

            while( $entryName = readdir( $dirhandle ) ) {
                if( !("." == $entryName || ".." == $entryName) && ($return_hidden || !preg_match( '/^\./', $entryName ) ) ) {
                    array_push( $filenames, $entryName );
                }
            }

            closedir( $dirhandle );

            return $filenames;
        }

        return FALSE;
    }


    static $alpha_upper_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    static $alpha_lower_chars = 'abcdefghijklmnopqrstuvwxyz';
    static $digit_chars = '0123456789';

    static function random_alphanumeric( $len ) {

        $chars = GtsUtils::$alpha_lower_chars . GtsUtils::$alpha_upper_chars . GtsUtils::$digit_chars;

        $str = "";
        $strlen = strlen( $chars );
        for ( $i = 0; $i < $len; $i++ ) {
            $str .= $chars[mt_rand( 0 , $strlen )];
        }

        return $str;
    }
	
	static function orig_page_name($page_name, $lang){
		global $wpdb;
		
		$select_tran_page = "select * from {$wpdb->prefix}gts_translated_posts where post_slug='{$page_name}' and language='{$lang}'";
		
		$tran_page = $wpdb->get_row($select_tran_page);
		
		$orig_id = $tran_page->local_id;
		
		$select_orig_page = "select * from {$wpdb->posts} where id={$orig_id}";
		
		$orig_page = $wpdb->get_row($select_orig_page);
		
		$result = $orig_page->post_name;
		
		return $result;
	}
	
	static function log($msg){
		?>
		<div>
		<pre>
			
			<?php print_r($msg); ?>
		
		</pre>
		</div>
		<?php
	}
}
