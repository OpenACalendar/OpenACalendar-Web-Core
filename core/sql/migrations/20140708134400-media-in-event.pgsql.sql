
CREATE TABLE media_in_event (
	event_id INTEGER NOT NULL,
	media_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NOT NULL,
	added_at timestamp without time zone NOT NULL,
  addition_approved_at timestamp without time zone NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
  removal_approved_at timestamp without time zone NULL,
	PRIMARY KEY(event_id,media_id,added_at)
);
ALTER TABLE media_in_event  ADD CONSTRAINT media_in_event_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);
ALTER TABLE media_in_event  ADD CONSTRAINT media_in_event_media_id FOREIGN KEY (media_id) REFERENCES media_information(id);
ALTER TABLE media_in_event  ADD CONSTRAINT media_in_event_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE media_in_event  ADD CONSTRAINT media_in_event_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);
