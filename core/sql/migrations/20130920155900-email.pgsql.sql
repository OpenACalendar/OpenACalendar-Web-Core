
CREATE TABLE send_email_information (
	id SERIAL,
	site_id INTEGER,
	slug INTEGER,
	subject VARCHAR(255),
	send_to TEXT,
	introduction TEXT,
	is_event_view_calendar BOOLEAN DEFAULT '1',
	event_html TEXT,
	event_text TEXT,
	days_into_future SMALLINT NULL,
	timezone VARCHAR(255) DEFAULT 'Europe/London'  NOT NULL,
	created_at timestamp without time zone NOT NULL,
	created_by INTEGER NOT NULL,
	sent_at timestamp without time zone NULL,
	sent_by INTEGER NULL,
	discarded_at timestamp without time zone NULL,
	discarded_by INTEGER NULL,
	PRIMARY KEY(id)
);
CREATE UNIQUE INDEX send_email_information_slug ON group_information(site_id, slug);

ALTER TABLE send_email_information ADD CONSTRAINT send_email_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE send_email_information ADD CONSTRAINT send_email_information_created_by FOREIGN KEY (created_by) REFERENCES user_account_information(id);
ALTER TABLE send_email_information ADD CONSTRAINT send_email_information_sent_by FOREIGN KEY (sent_by) REFERENCES user_account_information(id);
ALTER TABLE send_email_information ADD CONSTRAINT send_email_information_discarded_by FOREIGN KEY (discarded_by) REFERENCES user_account_information(id);

CREATE TABLE send_email_has_event (
	send_email_id  INTEGER,
	event_id INTEGER,
	PRIMARY KEY(send_email_id, event_id)
);
ALTER TABLE send_email_has_event ADD CONSTRAINT send_email_has_event_send_email_id FOREIGN KEY (send_email_id) REFERENCES send_email_information(id);
ALTER TABLE send_email_has_event ADD CONSTRAINT send_email_has_event_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);
