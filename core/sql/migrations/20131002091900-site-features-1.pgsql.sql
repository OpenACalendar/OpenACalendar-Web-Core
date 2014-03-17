ALTER TABLE site_information ADD is_feature_map boolean default '0' NOT NULL;
ALTER TABLE site_information ADD is_feature_importer boolean default '0' NOT NULL;
ALTER TABLE site_information ADD is_feature_curated_list boolean default '0' NOT NULL;

ALTER TABLE site_history ADD is_feature_map boolean default '0' NOT NULL;
ALTER TABLE site_history ADD is_feature_importer boolean default '0' NOT NULL;
ALTER TABLE site_history ADD is_feature_curated_list boolean default '0' NOT NULL;

