ALTER TABLE country ADD address_code_label VARCHAR(255) NULL;

ALTER TABLE venue_information ADD address TEXT NULl;
ALTER TABLE venue_information ADD address_code VARCHAR(255) NULL;

ALTER TABLE venue_history ADD address TEXT NULl;
ALTER TABLE venue_history ADD address_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD address_code VARCHAR(255) NULL;
ALTER TABLE venue_history ADD address_code_changed SMALLINT DEFAULT '0' NOT NULL;

