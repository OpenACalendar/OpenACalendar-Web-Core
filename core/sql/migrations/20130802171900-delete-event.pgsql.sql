ALTER TABLE event_information ADD is_deleted boolean default '0' NOT NULL;
ALTER TABLE event_history ADD is_deleted boolean default '0' NOT NULL;
