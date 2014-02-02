
CREATE TABLE event_information(
	id SERIAL,
	site_id INTEGER,
	slug INTEGER,
	summary VARCHAR(255),
	description TEXT,
	start_at timestamp without time zone NOT NULL,
	end_at timestamp without time zone NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE event_information ADD CONSTRAINT event_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX event_information_slug ON event_information(site_id, slug);


CREATE TABLE event_history (
	event_id INTEGER,
	summary VARCHAR(255),
	description TEXT,
	start_at timestamp without time zone NOT NULL,
	end_at timestamp without time zone NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(event_id,created_at)
);
ALTER TABLE event_history ADD CONSTRAINT event_history_id FOREIGN KEY (event_id) REFERENCES event_information(id);
ALTER TABLE event_history ADD CONSTRAINT event_history_user_account_d FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
