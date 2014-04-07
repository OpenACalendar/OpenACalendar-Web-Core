ALTER TABLE import_url_information ADD country_id INTEGER NULL;
ALTER TABLE import_url_information ADD CONSTRAINT import_url_information_country_id FOREIGN KEY (country_id) REFERENCES country(id);

ALTER TABLE import_url_history ADD country_id INTEGER NULL;
ALTER TABLE import_url_history ADD CONSTRAINT import_url_history_country_id FOREIGN KEY (country_id) REFERENCES country(id);
