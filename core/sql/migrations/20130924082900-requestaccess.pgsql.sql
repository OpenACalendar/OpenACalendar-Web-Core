ALTER TABLE site_information ADD is_request_access_allowed boolean default '0' NOT NULL;
ALTER TABLE site_information ADD request_access_question TEXT NULL;
ALTER TABLE site_history ADD is_request_access_allowed boolean default '0' NOT NULL;
ALTER TABLE site_history ADD request_access_question TEXT NULL;


CREATE TABLE site_access_request (
	id SERIAL NOT NULL,
	site_id INTEGER NOT NULL,
	user_account_id INTEGER NOT NULL,
	answer TEXT NULL,
	created_at timestamp without time zone NOT NULL,
	created_by INTEGER NOT NULL,
	granted_at timestamp without time zone NULL,
	granted_by INTEGER NULL,
	rejected_at timestamp without time zone NULL,
	rejected_by INTEGER NULL,
	PRIMARY KEY(id)
);

ALTER TABLE site_access_request ADD CONSTRAINT site_access_request_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE site_access_request ADD CONSTRAINT site_access_request_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE site_access_request ADD CONSTRAINT site_access_request_created_by FOREIGN KEY (created_by) REFERENCES user_account_information(id);
ALTER TABLE site_access_request ADD CONSTRAINT site_access_request_granted_by FOREIGN KEY (granted_by) REFERENCES user_account_information(id);
ALTER TABLE site_access_request ADD CONSTRAINT site_access_request_rejected_by FOREIGN KEY (rejected_by) REFERENCES user_account_information(id);

