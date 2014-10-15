ALTER TABLE site_history ALTER COLUMN title DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN slug DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN slug_canonical DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_web_robots_allowed DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_web_robots_allowed DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_closed_by_sys_admin DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_closed_by_sys_admin DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_listed_in_index DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_listed_in_index DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN prompt_emails_days_in_advance DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN prompt_emails_days_in_advance DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_map DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_map DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_importer DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_importer DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_curated_list DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_curated_list DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_virtual_events DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_virtual_events DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_physical_events DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_physical_events DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_group DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_group DROP NOT NULL;

ALTER TABLE site_history ALTER COLUMN is_feature_tag DROP DEFAULT;
ALTER TABLE site_history ALTER COLUMN is_feature_tag DROP NOT NULL;
