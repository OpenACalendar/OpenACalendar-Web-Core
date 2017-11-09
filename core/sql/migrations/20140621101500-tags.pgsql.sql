ALTER TABLE site_information ADD is_feature_tag boolean default '0' NOT NULL;
ALTER TABLE site_history ADD is_feature_tag boolean default '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_tag_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE site_history SET is_feature_tag_changed = -1;

CREATE TABLE tag_information (
	id SERIAL,
	site_id INTEGER NOT NULL,
	slug INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	description TEXT NULL,
	is_deleted boolean default '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	approved_at timestamp without time zone NULL,
	PRIMARY KEY(id)
);
ALTER TABLE tag_information ADD CONSTRAINT tag_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX tag_information_slug ON tag_information(site_id, slug);

CREATE TABLE tag_history (
	tag_id INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	title_changed SMALLINT DEFAULT '0' NOT NULL,
	description TEXT NULL,
	description_changed SMALLINT DEFAULT '0' NOT NULL,
	is_deleted boolean default '0' NOT NULL,
	is_deleted_changed SMALLINT DEFAULT '0' NOT NULL,
	user_account_id INTEGER,
	is_new SMALLINT DEFAULT '0',
	created_at timestamp without time zone NOT NULL,
	approved_at timestamp without time zone NULL,
	PRIMARY KEY(tag_id,created_at)
);
ALTER TABLE tag_history ADD CONSTRAINT tag_history_tag_id FOREIGN KEY (tag_id) REFERENCES tag_information(id);
ALTER TABLE tag_history ADD CONSTRAINT tag_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

CREATE TABLE event_has_tag (
	tag_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	addition_approved_at timestamp without time zone NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	removal_approved_at timestamp without time zone NULL,
	PRIMARY KEY(tag_id,event_id,added_at)
);
ALTER TABLE event_has_tag ADD CONSTRAINT event_has_tag_tag_id FOREIGN KEY (tag_id) REFERENCES tag_information(id);
ALTER TABLE event_has_tag ADD CONSTRAINT event_has_tag_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);
ALTER TABLE event_has_tag ADD CONSTRAINT event_has_tag_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE event_has_tag ADD CONSTRAINT event_has_tag_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

