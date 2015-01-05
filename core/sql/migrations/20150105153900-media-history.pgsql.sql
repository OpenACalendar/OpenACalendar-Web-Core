CREATE TABLE media_history (
	media_id INTEGER NOT NULL,
	title VARCHAR(255) NULL,
	title_changed SMALLINT DEFAULT 0 NOT NULL,
	source_text VARCHAR(255) NULL,
	source_text_changed  SMALLINT DEFAULT 0 NOT NULL,
	source_url VARCHAR(255) NULL,
	source_url_changed  SMALLINT DEFAULT 0 NOT NULL,
	user_account_id INTEGER,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(media_id, created_at)
);
ALTER TABLE media_history ADD CONSTRAINT media_history_media_id FOREIGN KEY (media_id) REFERENCES media_information(id);
ALTER TABLE media_history ADD CONSTRAINT media_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);

