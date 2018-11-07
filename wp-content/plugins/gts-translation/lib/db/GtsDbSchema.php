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


class GtsDbSchema {

    public static $gts_db_schema = array(

        "CREATE TABLE gts_translated_options (
            id bigint not null primary key auto_increment,
            local_id varchar(255) not null,
            foreign_id int not null,
            language char(16) not null,
            name varchar(255) not null,
            value text not null,
            created_time timestamp null default null,
            modified_time timestamp not null default now(),
            UNIQUE KEY (local_id, language)
        ) CHARACTER SET 'utf8'",


        "CREATE TABLE gts_translated_posts (
            id bigint not null primary key auto_increment,
            local_id bigint not null,
            foreign_id int not null,
            language char(16) not null,
            post_title text not null,
            post_excerpt text not null,
            post_body longtext not null,
            post_slug tinytext not null,
            created_time timestamp null default null,
            modified_time timestamp not null default now(),
            UNIQUE KEY (local_id, language),
            INDEX (post_slug(255), language)
        ) CHARACTER SET 'utf8'",


        "CREATE TABLE gts_translated_terms (
            id bigint not null primary key auto_increment,
            local_name varchar(255) not null,
            foreign_id int not null,
            language char(16) not null,
            name varchar(255) not null,
            slug varchar(255) not null,
            description text,
            created_time timestamp null default null,
            modified_time timestamp not null default now(),
            UNIQUE KEY (local_name, language),
            UNIQUE KEY (slug, language)
        ) CHARACTER SET 'utf8'"
    );
    
}

?>