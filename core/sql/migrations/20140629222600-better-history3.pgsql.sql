ALTER TABLE curated_list_history ADD   is_new SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE curated_list_history ADD title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE curated_list_history ADD description_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE curated_list_history ADD is_deleted_changed SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE import_url_history ADD   is_new SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE import_url_history ADD title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE import_url_history ADD is_enabled_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE import_url_history ADD expired_at_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE import_url_history ADD country_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE import_url_history ADD area_id_changed SMALLINT DEFAULT '0' NOT NULL;
