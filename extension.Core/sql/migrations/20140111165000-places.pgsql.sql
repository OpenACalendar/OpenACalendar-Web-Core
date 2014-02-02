
CREATE TABLE area_information (
	id SERIAL,
	site_id INTEGER NOT NULL,
	slug INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	description TEXT NULL,
	country_id INTEGER NOT NULL,
	parent_area_id INTEGER NULL,
	is_deleted boolean default '0' NOT NULL,
	cache_area_has_parent_generated boolean default '0' NOT NULL,
	cached_future_events INTEGER DEFAULT 0 NOT NULL,
	cached_max_lat REAL NULL,
	cached_max_lng REAL NULL,
	cached_min_lat REAL NULL,
	cached_min_lng REAL NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE area_information ADD CONSTRAINT area_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE area_information ADD CONSTRAINT area_information_country_id FOREIGN KEY (country_id) REFERENCES country(id);
ALTER TABLE area_information ADD CONSTRAINT area_information_parent_area_id FOREIGN KEY (parent_area_id) REFERENCES area_information(id);
CREATE UNIQUE INDEX area_information_slug ON area_information(site_id, slug);

CREATE TABLE area_history (
	area_id INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	description TEXT NULL,
	country_id INTEGER NOT NULL,
	parent_area_id INTEGER NULL,
	is_deleted boolean default '0' NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(area_id,created_at)
);
ALTER TABLE area_history ADD CONSTRAINT area_history_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
ALTER TABLE area_history ADD CONSTRAINT area_history_country_id FOREIGN KEY (country_id) REFERENCES country(id);
ALTER TABLE area_history ADD CONSTRAINT area_history_parent_area_id FOREIGN KEY (parent_area_id) REFERENCES area_information(id);

CREATE TABLE cached_area_has_parent (
	area_id INTEGER,
	has_parent_area_id INTEGER,
	PRIMARY KEY (area_id,has_parent_area_id)
);
ALTER TABLE cached_area_has_parent ADD CONSTRAINT cached_area_has_parent_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
ALTER TABLE cached_area_has_parent ADD CONSTRAINT cached_area_has_parent_has_parent_area_id FOREIGN KEY (has_parent_area_id) REFERENCES area_information(id);

ALTER TABLE venue_information ADD area_id INTEGER NULL;
ALTER TABLE venue_information ADD CONSTRAINT venue_information_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);

ALTER TABLE venue_history ADD area_id INTEGER NULL;
ALTER TABLE venue_history ADD CONSTRAINT venue_history_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
