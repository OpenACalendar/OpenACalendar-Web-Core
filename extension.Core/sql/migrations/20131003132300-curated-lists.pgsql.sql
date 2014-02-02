
CREATE TABLE curated_list_information (
	id SERIAL NOT NULL,
	site_id INTEGER NULL,
	slug INTEGER NOT NULL,
	title VARCHAR(255),
	description TEXT,
	is_deleted boolean default '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE curated_list_information ADD CONSTRAINT curated_list_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
CREATE UNIQUE INDEX curated_list_information_slug ON curated_list_information(site_id, slug);


CREATE TABLE curated_list_history (
	curated_list_id INTEGER NOT NULL,
	title VARCHAR(255),
	description TEXT,
	is_deleted boolean default '0' NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(curated_list_id,created_at)
);
ALTER TABLE curated_list_history ADD CONSTRAINT curated_list_history_id FOREIGN KEY (curated_list_id) REFERENCES curated_list_information(id);
ALTER TABLE curated_list_history ADD CONSTRAINT curated_list_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);


CREATE TABLE user_in_curated_list_information (
	user_account_id INTEGER NOT NULL,
	curated_list_id INTEGER NOT NULL,
	is_owner BOOLEAN DEFAULT '0' NOT NULL, 
	is_editor BOOLEAN DEFAULT '0' NOT NULL, 
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,curated_list_id)
);
ALTER TABLE user_in_curated_list_information ADD CONSTRAINT user_in_curated_list_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_in_curated_list_information ADD CONSTRAINT user_in_curated_list_information_curated_list_id FOREIGN KEY (curated_list_id) REFERENCES curated_list_information(id);

CREATE TABLE event_in_curated_list (
	curated_list_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NOT NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(curated_list_id,event_id,added_at)
);
ALTER TABLE event_in_curated_list  ADD CONSTRAINT event_in_curated_list_curated_list_id FOREIGN KEY (curated_list_id) REFERENCES curated_list_information(id);
ALTER TABLE event_in_curated_list  ADD CONSTRAINT event_in_curated_list_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);
ALTER TABLE event_in_curated_list  ADD CONSTRAINT event_in_curated_list_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE event_in_curated_list  ADD CONSTRAINT event_in_curated_list_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

