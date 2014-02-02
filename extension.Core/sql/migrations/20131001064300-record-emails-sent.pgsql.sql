
CREATE TABLE user_watches_site_prompt_email (
	user_account_id INTEGER NOT NULL,
	site_id INTEGER NOT NULL,
	sent_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id,sent_at)
);

ALTER TABLE user_watches_site_prompt_email ADD CONSTRAINT user_watches_site_prompt_email_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_site_prompt_email ADD CONSTRAINT user_watches_site_prompt_email_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE user_watches_site_notify_email (
	user_account_id INTEGER NOT NULL,
	site_id INTEGER NOT NULL,
	sent_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,site_id,sent_at)
);

ALTER TABLE user_watches_site_notify_email ADD CONSTRAINT user_watches_site_notify_email_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_site_notify_email ADD CONSTRAINT user_watches_site_notify_email_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE user_watches_group_prompt_email (
	user_account_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	sent_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,group_id,sent_at)
);

ALTER TABLE user_watches_group_prompt_email ADD CONSTRAINT user_watches_group_prompt_email_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_group_prompt_email ADD CONSTRAINT user_watches_group_prompt_email_site_id FOREIGN KEY (group_id) REFERENCES group_information(id);

CREATE TABLE user_watches_group_notify_email (
	user_account_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	sent_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,group_id,sent_at)
);

ALTER TABLE user_watches_group_notify_email ADD CONSTRAINT user_watches_group_notify_email_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_watches_group_notify_email ADD CONSTRAINT user_watches_group_notify_email_site_id FOREIGN KEY (group_id) REFERENCES group_information(id);
