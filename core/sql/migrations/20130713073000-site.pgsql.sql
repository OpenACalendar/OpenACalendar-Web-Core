
CREATE TABLE site_information (
	id SERIAL,
	title VARCHAR(255) NOT NULL,
	slug VARCHAR(255) NULL,
	slug_canonical VARCHAR(255) NULL,
	created_at timestamp without time zone NOT NULL,
	description_text TEXT NULL,
	footer_text TEXT NULL,
	is_web_robots_allowed boolean default '1' NOT NULL,
	is_closed_by_sys_admin boolean default '0' NOT NULL,
	closed_by_sys_admin_reason TEXT NULL,
	PRIMARY KEY(id)
);
CREATE UNIQUE INDEX site_slug ON site_information(slug);


CREATE TABLE site_history (
	site_id INTEGER,
	title VARCHAR(255) NOT NULL,
	slug VARCHAR(255) NOT NULL,
	slug_canonical VARCHAR(255) NOT NULL,
    user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	description_text TEXT NULL,
	footer_text TEXT NULL,
	is_web_robots_allowed boolean default '1' NOT NULL,
	is_closed_by_sys_admin boolean default '0' NOT NULL,
	closed_by_sys_admin_reason TEXT NULL,
	PRIMARY KEY(site_id, created_at)
);
ALTER TABLE site_history ADD CONSTRAINT site_history_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE site_history ADD CONSTRAINT site_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

