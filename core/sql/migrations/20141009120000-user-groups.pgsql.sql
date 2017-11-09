
CREATE TABLE user_group_information (
	id SERIAL,
	title VARCHAR(255) NOT NULL,
	description TEXT NULL,
	is_deleted BOOLEAN default '0' NOT NULL,
	is_in_index BOOLEAN default '0' NOT NULL,
	is_includes_anonymous BOOLEAN default '0' NOT NULL,
	is_includes_users BOOLEAN default '0' NOT NULL,
	is_includes_verified_users BOOLEAN default '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE user_group_history (
	user_group_id  INTEGER NOT NULL,
	title VARCHAR(255),
	title_changed SMALLINT DEFAULT 0 NOT NULL,
	description TEXT NULL,
	description_changed SMALLINT DEFAULT 0 NOT NULL,
	is_deleted BOOLEAN NULL,
	is_deleted_changed SMALLINT DEFAULT 0 NOT NULL,
	is_in_index BOOLEAN NULL,
	is_in_index_changed SMALLINT DEFAULT 0 NOT NULL,
	is_includes_anonymous BOOLEAN NULL,
	is_includes_anonymous_changed SMALLINT DEFAULT 0 NOT NULL,
	is_includes_users BOOLEAN NULL,
	is_includes_users_changed SMALLINT DEFAULT 0 NOT NULL,
	is_includes_verified_users BOOLEAN NULL,
	is_includes_verified_users_changed SMALLINT DEFAULT 0 NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_group_id, created_at)
);
ALTER TABLE user_group_history ADD CONSTRAINT user_group_history_user_group_id FOREIGN KEY (user_group_id) REFERENCES user_group_information(id);
ALTER TABLE user_group_history ADD CONSTRAINT user_group_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

CREATE TABLE user_group_in_site (
	user_group_id  INTEGER NOT NULL,
	site_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(user_group_id, site_id, added_at)
);
ALTER TABLE user_group_in_site ADD CONSTRAINT user_group_in_site_user_group_id FOREIGN KEY (user_group_id) REFERENCES user_group_information(id);
ALTER TABLE user_group_in_site ADD CONSTRAINT user_group_in_site_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE user_group_in_site ADD CONSTRAINT user_group_in_site_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_group_in_site ADD CONSTRAINT user_group_in_site_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

CREATE TABLE user_in_user_group (
	user_group_id  INTEGER NOT NULL,
	user_account_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(user_group_id, user_account_id, added_at)
);
ALTER TABLE user_in_user_group ADD CONSTRAINT user_in_user_group_user_group_id FOREIGN KEY (user_group_id) REFERENCES user_group_information(id);
ALTER TABLE user_in_user_group ADD CONSTRAINT user_in_user_group_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_in_user_group ADD CONSTRAINT user_in_user_group_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_in_user_group ADD CONSTRAINT user_in_user_group_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

CREATE TABLE permission_in_user_group (
	user_group_id  INTEGER NOT NULL,
	extension_id VARCHAR(255) NOT NULL,
	permission_key VARCHAR(255) NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(user_group_id, extension_id, permission_key, added_at)
);
ALTER TABLE permission_in_user_group ADD CONSTRAINT permission_in_user_group_user_group_id FOREIGN KEY (user_group_id) REFERENCES user_group_information(id);
ALTER TABLE permission_in_user_group ADD CONSTRAINT permission_in_user_group_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE permission_in_user_group ADD CONSTRAINT permission_in_user_group_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);
