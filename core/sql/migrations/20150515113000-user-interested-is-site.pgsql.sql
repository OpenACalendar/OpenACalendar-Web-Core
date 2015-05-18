
CREATE TABLE user_interested_in_site_information (
	user_account_id INTEGER,
	site_id INTEGER,
	is_interested BOOLEAN DEFAULT '0' NOT NULL,
	is_not_interested BOOLEAN DEFAULT '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id)
);
ALTER TABLE user_interested_in_site_information ADD CONSTRAINT user_interested_in_site_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_interested_in_site_information ADD CONSTRAINT user_interested_in_site_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
