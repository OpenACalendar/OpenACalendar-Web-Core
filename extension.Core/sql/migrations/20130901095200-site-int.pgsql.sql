ALTER TABLE site_information ADD cached_is_multiple_timezones boolean default '0' NOT NULL;
ALTER TABLE site_information ADD cached_is_multiple_countries boolean default '0' NOT NULL;
ALTER TABLE site_information ADD cached_timezones TEXT NULL;

