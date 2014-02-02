
CREATE TABLE group_information (
	id SERIAL,
	site_id INTEGER,
	slug INTEGER,
	title VARCHAR(255),
	description TEXT,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE group_information ADD CONSTRAINT group_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX group_information_slug ON group_information(site_id, slug);


CREATE TABLE group_history (
	group_id INTEGER,
	title VARCHAR(255),
	description TEXT,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(group_id,created_at)
);
ALTER TABLE group_history ADD CONSTRAINT group_history_id FOREIGN KEY (group_id) REFERENCES group_information(id);
ALTER TABLE group_history ADD CONSTRAINT group_history_user_account_d FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

ALTER TABLE event_information ADD group_id INTEGER NULL;
ALTER TABLE event_information ADD CONSTRAINT event_information_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);

ALTER TABLE event_history ADD group_id INTEGER NULL;
ALTER TABLE event_history ADD CONSTRAINT event_history_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
