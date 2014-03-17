
CREATE TABLE user_account_information (
	id SERIAL,
	username VARCHAR(255) NULL,
	username_canonical VARCHAR(255) NULL,
	email VARCHAR(255) NULL,
	email_canonical VARCHAR(255) NULL,
	password_hash VARCHAR(255) NOT NULL,
	is_email_verified boolean default '0' NOT NULL,
	email_verify_code VARCHAR(255) NULL,
	email_verify_last_sent_at  timestamp without time zone NULL,
	is_editor boolean default '1' NOT NULL,
	is_system_admin boolean default '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
/** null email and username so can delete accounts later **/
CREATE UNIQUE INDEX user_account_information_username_canonical ON user_account_information(username_canonical);
CREATE UNIQUE INDEX user_account_information_email_canonical ON user_account_information(email_canonical);

CREATE TABLE user_account_reset (
	user_account_id INTEGER NOT NULL,
	access_key VARCHAR(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	reset_at timestamp without time zone NULL,
	PRIMARY KEY(user_account_id, access_key)
);
ALTER TABLE user_account_reset ADD CONSTRAINT user_account_reset_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
