CREATE TABLE api2_application_information (
	id SERIAL,
	user_id INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	description TEXT,
	app_token VARCHAR(255) NOT NULL,
	app_secret VARCHAR(255) NOT NULL,
	is_write_user_actions SMALLINT DEFAULT '0' NOT NULL,
	is_write_calendar SMALLINT DEFAULT '0' NOT NULL,
	is_callback_url SMALLINT DEFAULT '1' NOT NULL,
	is_callback_display SMALLINT DEFAULT '1' NOT NULL,
	is_callback_javascript SMALLINT DEFAULT '1' NOT NULL,
	allowed_callback_urls TEXT NULL,
	is_auto_approve SMALLINT DEFAULT '0' NOT NULL,
	is_all_sites SMALLINT DEFAULT '1' NOT NULL,
	is_closed_by_sys_admin boolean default '0' NOT NULL,
	closed_by_sys_admin_reason TEXT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
CREATE UNIQUE INDEX api2_application_information_app_token ON api2_application_information(app_token);
ALTER TABLE api2_application_information ADD CONSTRAINT api2_application_user_id FOREIGN KEY (user_id) REFERENCES user_account_information(id);

CREATE TABLE user_in_api2_application_information (
	api2_application_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	is_in_app SMALLINT DEFAULT '1' NOT NULL,
	is_write_user_actions SMALLINT DEFAULT '0' NOT NULL,
	is_write_calendar SMALLINT DEFAULT '0' NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(api2_application_id,user_id)
);
ALTER TABLE user_in_api2_application_information ADD CONSTRAINT user_in_api2_application_informationapi2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);
ALTER TABLE user_in_api2_application_information ADD CONSTRAINT user_in_api2_application_information_user_id FOREIGN KEY (user_id) REFERENCES user_account_information(id);


CREATE TABLE api2_application_request_token (
	api2_application_id INTEGER NOT NULL,
	request_token VARCHAR(255) NOT NULL,
	user_id INTEGER NULL,
	callback_url TEXT NULL,
	is_callback_display SMALLINT DEFAULT '0' NOT NULL,
	is_callback_javascript SMALLINT DEFAULT '0' NOT NULL,
	is_write_user_actions SMALLINT DEFAULT '0' NOT NULL,
	is_write_calendar SMALLINT DEFAULT '0' NOT NULL,
	state_from_user VARCHAR(255) NULL,
	created_at timestamp without time zone NOT NULL,
	used_at timestamp without time zone NULL,
	PRIMARY KEY(api2_application_id,request_token)
);
ALTER TABLE api2_application_request_token ADD CONSTRAINT api2_application_request_token_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);
ALTER TABLE api2_application_request_token ADD CONSTRAINT api2_application_request_token_ FOREIGN KEY (user_id) REFERENCES user_account_information(id);

CREATE TABLE api2_application_user_token_information (
	api2_application_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	user_token VARCHAR(255) NOT NULL,
	user_secret VARCHAR(255) NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(api2_application_id,user_id)
);
ALTER TABLE api2_application_user_token_information ADD CONSTRAINT api2_application_user_token_information_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);
ALTER TABLE api2_application_user_token_information ADD CONSTRAINT api2_application_user_token_information_user_id FOREIGN KEY (user_id) REFERENCES user_account_information(id);

CREATE TABLE api2_application_user_authorisation_token (
	api2_application_id INTEGER NOT NULL,
	authorisation_token VARCHAR(255) NOT NULL,
	user_id INTEGER NOT NULL,
	request_token VARCHAR(255) NULL,
	created_at timestamp without time zone NOT NULL,
	used_at timestamp without time zone NULL,
	PRIMARY KEY(api2_application_id,authorisation_token)
);
ALTER TABLE api2_application_user_authorisation_token ADD CONSTRAINT api2_application_user_authorisation_token_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);
ALTER TABLE api2_application_user_authorisation_token ADD CONSTRAINT api2_application_user_authorisation_token_ FOREIGN KEY (user_id) REFERENCES user_account_information(id);

ALTER TABLE site_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE site_history ADD CONSTRAINT site_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE event_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE event_history ADD CONSTRAINT event_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE group_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE group_history ADD CONSTRAINT group_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE venue_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE venue_history ADD CONSTRAINT venue_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE area_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE area_history ADD CONSTRAINT area_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE curated_list_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE curated_list_history ADD CONSTRAINT curated_list_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

ALTER TABLE import_url_history ADD   api2_application_id INTEGER NULL;
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_api2_application_id FOREIGN KEY (api2_application_id) REFERENCES api2_application_information(id);

