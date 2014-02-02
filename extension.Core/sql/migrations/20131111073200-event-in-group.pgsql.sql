
CREATE TABLE event_in_group (
	group_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	is_main_group boolean NOT NULL,
	added_by_user_account_id INTEGER NULL,
	added_at timestamp without time zone NOT NULL,
	removed_by_user_account_id INTEGER NULL,
	removed_at timestamp without time zone NULL,
	PRIMARY KEY(group_id,event_id,added_at)
);
ALTER TABLE event_in_group  ADD CONSTRAINT event_in_group_group_id FOREIGN KEY (group_id) REFERENCES group_information(id);
ALTER TABLE event_in_group  ADD CONSTRAINT event_in_group_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);
ALTER TABLE event_in_group  ADD CONSTRAINT event_in_group_added_by_user_account_id FOREIGN KEY (added_by_user_account_id) REFERENCES user_account_information(id);
ALTER TABLE event_in_group  ADD CONSTRAINT event_in_group_removed_by_user_account_id FOREIGN KEY (removed_by_user_account_id) REFERENCES user_account_information(id);

INSERT INTO event_in_group (event_id, group_id, added_by_user_account_id, added_at, is_main_group) SELECT id, group_id, null, created_at, 't'  FROM event_information WHERE group_id IS NOT NULL;

ALTER TABLE event_information DROP group_id;
ALTER TABLE event_history DROP group_id;

