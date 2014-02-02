ALTER TABLE event_information ADD is_virtual boolean default '0' NOT NULL;
ALTER TABLE event_history ADD is_virtual boolean default '0' NOT NULL;

ALTER TABLE event_information ADD is_physical boolean default '1' NOT NULL;
ALTER TABLE event_history ADD is_physical boolean default '1' NOT NULL;

ALTER TABLE site_information ADD is_feature_virtual_events boolean default '0' NOT NULL;
ALTER TABLE site_history ADD is_feature_virtual_events boolean default '0' NOT NULL;

ALTER TABLE site_information ADD is_feature_physical_events boolean default '1' NOT NULL;
ALTER TABLE site_history ADD is_feature_physical_events boolean default '1' NOT NULL;
