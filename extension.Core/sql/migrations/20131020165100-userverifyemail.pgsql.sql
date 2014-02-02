				
				

CREATE TABLE user_account_verify_email (
	user_account_id INTEGER NOT NULL,
	email VARCHAR(255) NOT NULL,
	access_key VARCHAR(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	verified_at timestamp without time zone NULL,
	PRIMARY KEY(user_account_id, access_key)
);
ALTER TABLE user_account_verify_email ADD CONSTRAINT user_account_verify_email_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
