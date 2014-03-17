
CREATE TABLE media_in_group (
	group_id INTEGER NOT NULL,
	media_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NOT NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(group_id,media_id,added_at)
);
ALTER TABLE media_in_group  ADD CONSTRAINT media_in_group_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
ALTER TABLE media_in_group  ADD CONSTRAINT media_in_group_media_id FOREIGN KEY (media_id) REFERENCES media_information(id);
ALTER TABLE media_in_group  ADD CONSTRAINT media_in_group_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE media_in_group  ADD CONSTRAINT media_in_group_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

