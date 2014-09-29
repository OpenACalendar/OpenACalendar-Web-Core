
ALTER TABLE import_url_information ADD approved_at timestamp without time zone NULL;
ALTER TABLE import_url_history ADD approved_at timestamp without time zone NULL;

ALTER TABLE import_url_history ADD group_id_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE import_url_history SET group_id_changed  = -1;
