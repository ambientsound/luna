--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;





CREATE TABLE users (
    id serial PRIMARY KEY NOT NULL,
    username character varying(128) NOT NULL UNIQUE,
    email character varying(128) UNIQUE,
    password character varying(34),
    enabled boolean,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    lastlogin timestamp with time zone,
    activity timestamp with time zone,
    logincount integer DEFAULT 0
);

CREATE TABLE roles (
    role character varying(32) PRIMARY KEY NOT NULL,
    inherit character varying(32) references roles on delete cascade
);

CREATE TABLE users_roles (
    id serial NOT NULL,
    user_id integer references users on delete cascade,
    role character varying(32) references roles on delete cascade
);

CREATE TABLE privileges (
    id serial PRIMARY KEY NOT NULL,
    user_id integer references users on delete cascade,
    role character varying(32) references roles on delete cascade,
    resource_type character varying(32) NOT NULL,
    resource_id integer NOT NULL,
    created timestamp with time zone,
    modified timestamp with time zone,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    privilege character varying(32)
);

CREATE TABLE pages (
    id serial PRIMARY KEY NOT NULL,
    lft integer NOT NULL,
    rgt integer NOT NULL,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    publish_from timestamp with time zone,
    publish_to timestamp with time zone,
    published boolean default false,
    nodetype character varying(32),
    template character varying(64) NOT NULL,
    slug character varying(128) NOT NULL,
    title character varying(128) NOT NULL,
    metadesc character varying(512),
    spider_sitemap boolean default true,
    spider_index boolean default true,
    spider_follow boolean default true,
    body text
);

CREATE TABLE folders (
    id serial PRIMARY KEY NOT NULL,
    lft integer NOT NULL,
    rgt integer NOT NULL,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    name character varying(128) NOT NULL
);

CREATE TABLE files (
    id serial PRIMARY KEY NOT NULL,
    folder_id integer references folders on delete set null,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    filename character varying(256) NOT NULL UNIQUE,
    mimetype character varying(64),
    size character varying(16),
    title character varying(256) NOT NULL,
    alt character varying(512)
);

CREATE TABLE pages_files (
    id serial PRIMARY KEY NOT NULL,
    page_id integer not null references pages on delete cascade,
    file_id integer not null references files on delete cascade,
    createdby integer references users on delete restrict,
    created timestamp with time zone
);

CREATE TABLE galleries (
    id serial PRIMARY KEY NOT NULL,
    folder_id integer references folders on delete restrict,
    page_limit integer not null default 1,
    viewmode character varying(64) not null default 'thumbnails',
    size_thumbnails character varying(64) not null default 'small',
    size_flow character varying(64) not null default 'original'

);

CREATE TABLE galleries_files (
    id serial PRIMARY KEY NOT NULL,
    gallery_id integer not null references galleries on delete cascade,
    file_id integer not null references files on delete cascade,
    position integer not null default 0,
    createdby integer references users on delete restrict,
    created timestamp with time zone
);

CREATE TABLE options (
    key character varying(128) PRIMARY KEY NOT NULL,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    value character varying(1024)
);

CREATE TABLE stickers (
    id serial PRIMARY KEY NOT NULL,
    page_id integer references pages on delete cascade,
    createdby integer references users on delete restrict,
    modifiedby integer references users on delete restrict,
    created timestamp with time zone,
    modified timestamp with time zone,
    key character varying(64) NOT NULL,
    value character varying(512)
);
