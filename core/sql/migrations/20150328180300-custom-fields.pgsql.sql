CREATE TABLE event_custom_field_definition_information (
	id SERIAL,
	site_id INTEGER NOT NULL,
	key VARCHAR(255) NOT NULL,
	extension_id VARCHAR(255) NOT NULL,
	type VARCHAR(255) NOT NULL,
	label VARCHAR(255) NOT NULL,
	is_active BOOLEAN NOT NULL default '1',
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE event_custom_field_definition_information ADD CONSTRAINT event_custom_field_definition_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX event_custom_field_definition_information_key_unique ON event_custom_field_definition_information(site_id, key);

CREATE TABLE event_custom_field_definition_history (
	event_custom_field_definition_id  INTEGER NOT NULL,
	key VARCHAR(255) NULL,
	key_changed SMALLINT DEFAULT '0' NOT NULL,
	extension_id VARCHAR(255) NULL,
	extension_id_changed SMALLINT DEFAULT '0' NOT NULL,
	type VARCHAR(255) NULL,
	type_changed SMALLINT DEFAULT '0' NOT NULL,
	label VARCHAR(255) NULL,
	label_changed SMALLINT DEFAULT '0' NOT NULL,
	user_account_id INTEGER NULL,
	is_active BOOLEAN NULL,
	is_active_changed SMALLINT DEFAULT '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY (event_custom_field_definition_id,created_at)
);
ALTER TABLE event_custom_field_definition_history ADD CONSTRAINT event_custom_field_definition_history_event_custom_field_definition_id FOREIGN KEY (event_custom_field_definition_id) REFERENCES event_custom_field_definition_information(id);
ALTER TABLE event_custom_field_definition_history ADD CONSTRAINT event_custom_field_definition_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

ALTER TABLE site_information ADD cached_event_custom_field_definitions TEXT;

ALTER TABLE event_information ADD custom_fields TEXT NULL;

ALTER TABLE event_history ADD custom_fields TEXT NULL;
ALTER TABLE event_history ADD custom_fields_changed TEXT NULL;
