ALTER TABLE event_information ADD is_cancelled boolean default '0' NOT  NULL;
ALTER TABLE event_history ADD is_cancelled boolean default '0' NOT  NULL;
ALTER TABLE event_history ADD   is_cancelled_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE event_history SET is_cancelled_changed = -1;
