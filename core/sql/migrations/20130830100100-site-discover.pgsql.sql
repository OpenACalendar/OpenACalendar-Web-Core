ALTER TABLE site_information ADD is_listed_in_index boolean default '1' NOT NULL;
ALTER TABLE site_history ADD is_listed_in_index boolean default '1' NOT NULL;

UPDATE site_information SET is_listed_in_index = is_web_robots_allowed;
UPDATE site_history SET is_listed_in_index = is_web_robots_allowed;
