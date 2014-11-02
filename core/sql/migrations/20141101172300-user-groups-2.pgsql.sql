
CREATE TABLE user_has_no_editor_permissions_in_site (
	site_id INTEGER NOT NULL,
	user_account_id INTEGER NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(site_id, user_account_id, added_at)
);
ALTER TABLE user_has_no_editor_permissions_in_site ADD CONSTRAINT user_has_no_editor_permissions_in_site_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE user_has_no_editor_permissions_in_site ADD CONSTRAINT user_has_no_editor_permissions_in_site_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_has_no_editor_permissions_in_site ADD CONSTRAINT user_has_no_editor_permissions_in_site_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_has_no_editor_permissions_in_site ADD CONSTRAINT user_has_no_editor_permissions_in_site_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);
