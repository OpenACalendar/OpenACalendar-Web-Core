CREATE TABLE incoming_link (
	id SERIAL,
	site_id INTEGER NULL,
	extension_id VARCHAR(255) NOT NULL,
	type VARCHAR(255) NOT NULL,
	source_url TEXT NULL,
	target_url TEXT NULL,
	reporter_useragent TEXT NULL,
	reporter_ip INET NULL,
	is_verified BOOLEAN DEFAULT '0' NOT NULL,
	data TEXT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE incoming_link ADD CONSTRAINT incoming_link_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
