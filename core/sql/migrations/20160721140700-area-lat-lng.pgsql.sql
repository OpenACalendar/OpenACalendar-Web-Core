
ALTER TABLE area_information ADD max_lat REAL NULL;
ALTER TABLE area_information ADD max_lng REAL NULL;
ALTER TABLE area_information ADD min_lat REAL NULL;
ALTER TABLE area_information ADD min_lng REAL NULL;

ALTER TABLE area_history ADD max_lat REAL NULL;
ALTER TABLE area_history ADD max_lng REAL NULL;
ALTER TABLE area_history ADD min_lat REAL NULL;
ALTER TABLE area_history ADD min_lng REAL NULL;

ALTER TABLE area_history ADD max_lat_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD max_lng_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD min_lat_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD min_lng_changed SMALLINT DEFAULT '0' NOT NULL;
