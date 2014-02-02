
CREATE TABLE user_in_site_information (
	user_account_id INTEGER,
	site_id INTEGER,
	is_owner BOOLEAN DEFAULT '0' NOT NULL, 
	is_administrator BOOLEAN DEFAULT '0' NOT NULL, 
	is_editor BOOLEAN DEFAULT '0' NOT NULL, 
	is_interested BOOLEAN DEFAULT '0' NOT NULL, 
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id)
);
ALTER TABLE user_in_site_information ADD CONSTRAINT user_in_site_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_in_site_information ADD CONSTRAINT user_in_site_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

