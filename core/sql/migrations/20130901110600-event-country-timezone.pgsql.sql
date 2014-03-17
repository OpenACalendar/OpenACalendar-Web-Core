ALTER TABLE event_information ADD country_id INT NULL;
ALTER TABLE event_history ADD country_id INT NULL;

ALTER TABLE event_information ADD CONSTRAINT event_information_country_id FOREIGN KEY (country_id) REFERENCES country(id);
ALTER TABLE event_history ADD CONSTRAINT event_history_country_id FOREIGN KEY (country_id) REFERENCES country(id);

ALTER TABLE event_information ADD timezone VARCHAR(255) DEFAULT 'Europe/London'  NOT NULL;
ALTER TABLE event_history ADD timezone  VARCHAR(255) DEFAULT 'Europe/London' NOT NULL;
