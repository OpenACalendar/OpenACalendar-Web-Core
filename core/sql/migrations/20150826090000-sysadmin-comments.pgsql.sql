
CREATE TABLE sysadmin_comment_information (
	id SERIAL,
	user_account_id INTEGER NULL,
	comment TEXT NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE sysadmin_comment_information ADD CONSTRAINT sysadmin_comment_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

CREATE TABLE sysadmin_comment_about_user (
	sysadmin_comment_id INTEGER NOT NULL,
	user_account_id INTEGER NOT NULL,
	PRIMARY KEY(sysadmin_comment_id,user_account_id)
);
ALTER TABLE sysadmin_comment_about_user ADD CONSTRAINT sysadmin_comment_about_user_sysadmin_comment_id FOREIGN KEY (sysadmin_comment_id) REFERENCES sysadmin_comment_information(id);
ALTER TABLE sysadmin_comment_about_user ADD CONSTRAINT sysadmin_comment_about_user_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
