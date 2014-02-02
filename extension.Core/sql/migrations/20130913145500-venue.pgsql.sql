
CREATE TABLE venue_information (
	id SERIAL,
	site_id INTEGER,
	slug INTEGER,
	title VARCHAR(255),
	description TEXT,
	lat REAL,
	lng REAL,
	country_id INTEGER,
	is_deleted boolean default '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE venue_information ADD CONSTRAINT venue_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE venue_information ADD CONSTRAINT venue_information_country_id FOREIGN KEY (country_id) REFERENCES country(id);
CREATE UNIQUE INDEX venue_information_slug ON venue_information(site_id, slug);


CREATE TABLE venue_history (
	venue_id INTEGER,
	title VARCHAR(255),
	description TEXT,
	lat REAL,
	lng REAL,
	country_id INTEGER,
	is_deleted boolean default '0' NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(venue_id,created_at)
);
ALTER TABLE venue_history ADD CONSTRAINT venue_history_id FOREIGN KEY (venue_id) REFERENCES venue_information(id);
ALTER TABLE venue_history ADD CONSTRAINT venue_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE venue_history ADD CONSTRAINT venue_history_country_id FOREIGN KEY (country_id) REFERENCES country(id);

ALTER TABLE event_information ADD venue_id INTEGER NULL;
ALTER TABLE event_information ADD CONSTRAINT event_information_venue_id FOREIGN KEY (venue_id) REFERENCES venue_information(id);

ALTER TABLE event_history ADD venue_id INTEGER NULL;
ALTER TABLE event_history ADD CONSTRAINT event_history_venue_id FOREIGN KEY (venue_id) REFERENCES venue_information(id);
