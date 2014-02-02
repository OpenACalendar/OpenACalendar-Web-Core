
CREATE TABLE site_quota_information (
	id SERIAL,
	title VARCHAR(255) NOT NULL,
	code VARCHAR(255) NOT NULL,
	max_new_events_per_month INT NOT NULL DEFAULT 1,
	max_new_groups_per_month INT NOT NULL DEFAULT 1,
	max_new_venues_per_month INT NOT NULL DEFAULT 1,
	max_countries INT NOT NULL DEFAULT 1,
	max_media_mb INT NOT NULL DEFAULT 1,
	PRIMARY KEY(id)
);
CREATE UNIQUE INDEX site_quota_code ON site_quota_information(code);

INSERT INTO site_quota_information (title,code,max_new_events_per_month,max_new_groups_per_month,max_new_venues_per_month,max_countries,max_media_mb) 
	VALUES ('Basic','BASIC',1000,1000,1000,1000,1000);

ALTER TABLE site_information ADD site_quota_id INT NULL;
ALTER TABLE site_information ADD CONSTRAINT site_information_site_quota_id  FOREIGN KEY (site_quota_id) REFERENCES site_quota_information(id);

UPDATE site_information SET site_quota_id = (SELECT id FROM site_quota_information WHERE code='TRIAL');

