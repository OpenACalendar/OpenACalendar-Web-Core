
CREATE TABLE group_in_curated_list (
	curated_list_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NOT NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(curated_list_id,group_id,added_at)
);
ALTER TABLE group_in_curated_list  ADD CONSTRAINT group_in_curated_list_curated_list_id FOREIGN KEY (curated_list_id) REFERENCES curated_list_information(id);
ALTER TABLE group_in_curated_list  ADD CONSTRAINT group_in_curated_list_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
ALTER TABLE group_in_curated_list  ADD CONSTRAINT group_in_curated_list_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE group_in_curated_list  ADD CONSTRAINT group_in_curated_list_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

