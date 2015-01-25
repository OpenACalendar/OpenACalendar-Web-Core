CREATE TABLE contact_support (
	id SERIAL,
	subject VARCHAR(255) NULL,
	message TEXT NULL,
	email VARCHAR(255) NULL,
	user_account_id INTEGER,
	ip VARCHAR(255) NULL,
	browser TEXT NULL,
	created_at timestamp without time zone NOT NULL,
	is_spam_manually_detected boolean default '0' NOT NULL,
	is_spam_honeypot_field_detected boolean default '0' NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE contact_support ADD CONSTRAINT contact_support_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);


