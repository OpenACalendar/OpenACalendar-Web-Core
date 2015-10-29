
CREATE TABLE sysadmin_comment_about_site (
	sysadmin_comment_id INTEGER NOT NULL,
	site_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,site_id)
);
ALTER TABLE sysadmin_comment_about_site ADD CONSTRAINT sysadmin_comment_about_site_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_site ADD CONSTRAINT sysadmin_comment_about_site_site_account_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE sysadmin_comment_about_event (
	sysadmin_comment_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,event_id)
);
ALTER TABLE sysadmin_comment_about_event ADD CONSTRAINT sysadmin_comment_about_event_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_event ADD CONSTRAINT sysadmin_comment_about_event_event_account_id FOREIGN KEY (event_id) REFERENCES event_information(id);

CREATE TABLE sysadmin_comment_about_group (
	sysadmin_comment_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,group_id)
);
ALTER TABLE sysadmin_comment_about_group ADD CONSTRAINT sysadmin_comment_about_group_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_group ADD CONSTRAINT sysadmin_comment_about_group_group_account_id FOREIGN KEY (group_id) REFERENCES group_information(id);

CREATE TABLE sysadmin_comment_about_area (
	sysadmin_comment_id INTEGER NOT NULL,
	area_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,area_id)
);
ALTER TABLE sysadmin_comment_about_area ADD CONSTRAINT sysadmin_comment_about_area_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_area ADD CONSTRAINT sysadmin_comment_about_area_area_account_id FOREIGN KEY (area_id) REFERENCES area_information(id);

CREATE TABLE sysadmin_comment_about_venue (
	sysadmin_comment_id INTEGER NOT NULL,
	venue_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,venue_id)
);
ALTER TABLE sysadmin_comment_about_venue ADD CONSTRAINT sysadmin_comment_about_venue_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_venue ADD CONSTRAINT sysadmin_comment_about_venue_venue_account_id FOREIGN KEY (venue_id) REFERENCES venue_information(id);

CREATE TABLE sysadmin_comment_about_media (
	sysadmin_comment_id INTEGER NOT NULL,
	media_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,media_id)
);
ALTER TABLE sysadmin_comment_about_media ADD CONSTRAINT sysadmin_comment_about_media_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_media ADD CONSTRAINT sysadmin_comment_about_media_media_account_id FOREIGN KEY (media_id) REFERENCES media_information(id);
