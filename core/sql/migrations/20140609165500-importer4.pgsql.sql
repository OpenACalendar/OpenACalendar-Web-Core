CREATE TABLE imported_event (
	id SERIAL NOT NULL,
	import_url_id INTEGER NULL,
	import_id VARCHAR(255) NULL,
	title VARCHAR(255) NULL,
	description TEXT NULL,
	start_at timestamp without time zone NULL,
	end_at timestamp without time zone NULL,
	is_deleted boolean NOT NULL DEFAULT false,
	url VARCHAR(255) NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);
ALTER TABLE imported_event ADD CONSTRAINT imported_event_import_url_id FOREIGN KEY (import_url_id) REFERENCES import_url_information(id);

CREATE TABLE imported_event_is_event (
	imported_event_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(imported_event_id, event_id)
);
ALTER TABLE imported_event_is_event ADD CONSTRAINT imported_event_is_event_imported_event_id FOREIGN KEY (imported_event_id) REFERENCES imported_event(id);
ALTER TABLE imported_event_is_event ADD CONSTRAINT imported_event_is_event_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);




