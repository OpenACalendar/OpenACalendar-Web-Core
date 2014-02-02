
CREATE TABLE import_url_information (
	id SERIAL NOT NULL,
	site_id INTEGER NOT NULL,
	slug INTEGER NOT NULL,
	group_id INTEGER NULL,
	title VARCHAR(255),
	url VARCHAR(255),
	enabled boolean default '1' NOT NULL,
	expired_at timestamp without time zone NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE import_url_information ADD CONSTRAINT import_url_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE import_url_information ADD CONSTRAINT import_url_information_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
CREATE UNIQUE INDEX import_url_information_slug ON venue_information(site_id, slug);


CREATE TABLE import_url_history (
	import_url_id INTEGER NOT NULL,
	group_id INTEGER NULL,
	title VARCHAR(255),
	url VARCHAR(255),
	enabled boolean default '1' NOT NULL,
	expired_at timestamp without time zone NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(import_url_id,created_at)
);
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_id FOREIGN KEY (import_url_id) REFERENCES import_url_information(id);
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

CREATE TABLE import_url_result (
	import_url_id INTEGER NOT NULL,
	new_count smallint NOT NULL,
	existing_count smallint NOT NULL,
	saved_count smallint NOT NULL,
	in_past_count smallint NOT NULL,
	to_far_in_future_count smallint NOT NULL,
	not_valid_count smallint NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(import_url_id,created_at)
);
ALTER TABLE import_url_result ADD CONSTRAINT import_url_result_id FOREIGN KEY (import_url_id) REFERENCES import_url_information(id);

ALTER TABLE event_information ADD import_url_id INTEGER NULL;
ALTER TABLE event_information ADD import_id VARCHAR(255) NULL;
ALTER TABLE event_information ADD CONSTRAINT event_information_import_url_id FOREIGN KEY (import_url_id) REFERENCES import_url_information(id);
