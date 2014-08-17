ALTER TABLE event_information ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE event_information ADD CONSTRAINT event_information_is_duplicate_of_id FOREIGN KEY (is_duplicate_of_id) REFERENCES event_information(id);
ALTER TABLE event_history ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE event_history ADD   is_duplicate_of_id_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE event_history SET is_duplicate_of_id_changed = -1;

ALTER TABLE group_information ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE group_information ADD CONSTRAINT group_information_is_duplicate_of_id FOREIGN KEY (is_duplicate_of_id) REFERENCES group_information(id);
ALTER TABLE group_history ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE group_history ADD   is_duplicate_of_id_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE group_history SET is_duplicate_of_id_changed = -1;

ALTER TABLE venue_information ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE venue_information ADD CONSTRAINT venue_information_is_duplicate_of_id FOREIGN KEY (is_duplicate_of_id) REFERENCES venue_information(id);
ALTER TABLE venue_history ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE venue_history ADD   is_duplicate_of_id_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE venue_history SET is_duplicate_of_id_changed = -1;

ALTER TABLE area_information ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE area_information ADD CONSTRAINT area_information_is_duplicate_of_id FOREIGN KEY (is_duplicate_of_id) REFERENCES area_information(id);
ALTER TABLE area_history ADD is_duplicate_of_id INTEGER NULL;
ALTER TABLE area_history ADD   is_duplicate_of_id_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE area_history SET is_duplicate_of_id_changed = -1;
