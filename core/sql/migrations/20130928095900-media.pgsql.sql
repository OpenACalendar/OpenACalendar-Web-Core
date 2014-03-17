CREATE TABLE media_information (
	id SERIAL NOT NULL,
	site_id INTEGER,
	slug INTEGER,
	is_file_lost boolean default '0' NOT NULL,
	storage_size INTEGER NULL,
	created_by_user_account_id INTEGER NOT NULL,
	created_at timestamp without time zone NOT NULL,
	deleted_by_user_account_id INTEGER NULL,
	deleted_at timestamp without time zone NULL,
	PRIMARY KEY(id)
);
ALTER TABLE media_information ADD CONSTRAINT media_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX media_information_slug ON media_information(site_id, slug);
ALTER TABLE media_information ADD CONSTRAINT media_information_created_by_user_account_id FOREIGN KEY (created_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE media_information ADD CONSTRAINT media_information_deleted_by_user_account_id FOREIGN KEY (deleted_by_user_account_id) REFERENCES user_account_information(id);


CREATE TABLE site_profile_media_information (
	site_id INTEGER NOT NULL,
	logo_media_id INTEGER NULL,
	PRIMARY KEY(site_id)
);
ALTER TABLE site_profile_media_information ADD CONSTRAINT site_profile_media_information_logo_media_id FOREIGN KEY (logo_media_id) REFERENCES media_information(id);
ALTER TABLE site_profile_media_information ADD CONSTRAINT site_profile_media_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE site_profile_media_history (
	site_id INTEGER,
	logo_media_id INTEGER NULL,
	user_account_id INTEGER NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(site_id, created_at)
);
ALTER TABLE site_profile_media_history ADD CONSTRAINT site_profile_media_history_logo_media_id FOREIGN KEY (logo_media_id) REFERENCES media_information(id);
ALTER TABLE site_profile_media_history ADD CONSTRAINT site_profile_media_history_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE site_profile_media_history ADD CONSTRAINT ssite_profile_media_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

ALTER TABLE site_information DROP image_file_md5;
ALTER TABLE site_history DROP image_file_md5;
