ALTER TABLE event_information ADD area_id INT NULL;
ALTER TABLE event_history ADD area_id INT NULL;

ALTER TABLE event_information ADD CONSTRAINT event_information_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
ALTER TABLE event_history ADD CONSTRAINT event_history_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);
