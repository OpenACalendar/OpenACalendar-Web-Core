
CREATE TABLE country_in_site_information (
	site_id INTEGER,
	country_id INTEGER,
	is_in BOOLEAN DEFAULT '0' NOT NULL, 
	is_previously_in BOOLEAN DEFAULT '0' NOT NULL, 
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(site_id,country_id)
);
ALTER TABLE country_in_site_information ADD CONSTRAINT country_in_site_information_country_id FOREIGN KEY (country_id) REFERENCES country(id);
ALTER TABLE country_in_site_information ADD CONSTRAINT country_in_site_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

