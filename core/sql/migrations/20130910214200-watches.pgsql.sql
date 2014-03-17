
CREATE TABLE user_watches_site_information (
	user_account_id INTEGER,
	site_id INTEGER,
	is_watching BOOLEAN DEFAULT '0' NOT NULL, 
	is_was_once_watching BOOLEAN DEFAULT '0' NOT NULL, 
	last_notify_email_sent timestamp without time zone NULL,
	last_prompt_email_sent timestamp without time zone NULL,
	last_watch_started timestamp without time zone NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id)
);
ALTER TABLE user_watches_site_information ADD CONSTRAINT user_watches_site_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_site_information ADD CONSTRAINT user_watches_site_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);


CREATE TABLE user_watches_group_information (
	user_account_id INTEGER,
	group_id INTEGER,
	is_watching BOOLEAN DEFAULT '0' NOT NULL, 
	is_was_once_watching BOOLEAN DEFAULT '0' NOT NULL, 
	last_notify_email_sent timestamp without time zone NULL,
	last_prompt_email_sent timestamp without time zone NULL,
	last_watch_started timestamp without time zone NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,group_id)
);
ALTER TABLE user_watches_group_information ADD CONSTRAINT user_watches_group_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_group_information ADD CONSTRAINT user_watches_group_information_site_id FOREIGN KEY (group_id) REFERENCES group_information(id);

