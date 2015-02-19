
CREATE TABLE user_watches_area_information (
	user_account_id INTEGER,
	area_id INTEGER,
	is_watching BOOLEAN DEFAULT '0' NOT NULL,
	is_was_once_watching BOOLEAN DEFAULT '0' NOT NULL,
	last_notify_email_sent timestamp without time zone NULL,
	last_prompt_email_sent timestamp without time zone NULL,
	last_watch_started timestamp without time zone NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,area_id)
);
ALTER TABLE user_watches_area_information ADD CONSTRAINT user_watches_area_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_area_information ADD CONSTRAINT user_watches_area_information_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);


CREATE TABLE user_watches_area_stop (
	user_account_id INTEGER,
	area_id INTEGER,
	access_key character varying(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,area_id,access_key)
);
ALTER TABLE user_watches_area_stop ADD CONSTRAINT user_watches_area_stop_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_area_stop ADD CONSTRAINT user_watches_area_stop_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
