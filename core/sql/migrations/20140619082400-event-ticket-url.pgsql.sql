ALTER TABLE event_information ADD ticket_url VARCHAR(255) NULL;
ALTER TABLE event_history ADD ticket_url VARCHAR(255) NULL;
ALTER TABLE event_history ADD   ticket_url_changed SMALLINT DEFAULT '0' NOT NULL;
UPDATE event_history SET ticket_url_changed = -1;
