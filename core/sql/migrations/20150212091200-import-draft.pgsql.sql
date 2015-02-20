
ALTER TABLE import_url_information ADD is_manual_events_creation BOOLEAN DEFAULT '0' NOT NULL;

ALTER TABLE import_url_history ADD is_manual_events_creation BOOLEAN  NULL;

ALTER TABLE import_url_history ADD is_manual_events_creation_changed SMALLINT DEFAULT '0' NOT NULL;

UPDATE import_url_history SET is_manual_events_creation = '0';
