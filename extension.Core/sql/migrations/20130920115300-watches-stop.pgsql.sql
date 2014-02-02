
CREATE TABLE user_watches_site_stop (
	user_account_id INTEGER,
	site_id INTEGER,
	access_key character varying(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id,access_key)
);
ALTER TABLE user_watches_site_stop ADD CONSTRAINT user_watches_site_stop_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_site_stop ADD CONSTRAINT user_watches_site_stop_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE user_watches_group_stop (
	user_account_id INTEGER,
	group_id INTEGER,
	access_key character varying(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,group_id,access_key)
);
ALTER TABLE user_watches_group_stop ADD CONSTRAINT user_watches_group_stop_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_group_stop ADD CONSTRAINT user_watches_group_stop_site_id FOREIGN KEY (group_id) REFERENCES group_information(id);
