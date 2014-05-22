ALTER TABLE import_url_information ADD area_id INTEGER NULL;
ALTER TABLE import_url_information ADD CONSTRAINT import_url_information_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);

ALTER TABLE import_url_history ADD area_id INTEGER NULL;
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_area_id FOREIGN KEY (area_id) REFERENCES area_information(id);


