
CREATE TABLE user_account_remember_me (
	user_account_id INTEGER NOT NULL,
	access_key VARCHAR(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id, access_key)
);
ALTER TABLE user_account_remember_me ADD CONSTRAINT user_account_remember_me_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
