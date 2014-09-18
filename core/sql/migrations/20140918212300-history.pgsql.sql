ALTER TABLE tag_history ALTER COLUMN is_deleted DROP DEFAULT;
ALTER TABLE tag_history ALTER COLUMN is_deleted DROP NOT NULL;
ALTER TABLE tag_history ALTER COLUMN title DROP NOT NULL;

ALTER TABLE venue_history ALTER COLUMN is_deleted DROP DEFAULT;
ALTER TABLE venue_history ALTER COLUMN is_deleted DROP NOT NULL;

ALTER TABLE area_history ALTER COLUMN is_deleted DROP DEFAULT;
ALTER TABLE area_history ALTER COLUMN is_deleted DROP NOT NULL;
ALTER TABLE area_history ALTER COLUMN title DROP NOT NULL;
ALTER TABLE area_history ALTER COLUMN country_id DROP NOT NULL;

ALTER TABLE event_history ALTER COLUMN start_at DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN end_at DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN is_deleted DROP DEFAULT;
ALTER TABLE event_history ALTER COLUMN is_deleted DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN timezone DROP DEFAULT;
ALTER TABLE event_history ALTER COLUMN timezone DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN is_virtual DROP DEFAULT;
ALTER TABLE event_history ALTER COLUMN is_virtual DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN is_physical DROP DEFAULT;
ALTER TABLE event_history ALTER COLUMN is_physical DROP NOT NULL;
ALTER TABLE event_history ALTER COLUMN is_cancelled DROP DEFAULT;
ALTER TABLE event_history ALTER COLUMN is_cancelled DROP NOT NULL;

ALTER TABLE curated_list_history ALTER COLUMN is_deleted DROP DEFAULT;
ALTER TABLE curated_list_history ALTER COLUMN is_deleted DROP NOT NULL;

ALTER TABLE import_url_history ALTER COLUMN is_enabled DROP DEFAULT;
ALTER TABLE import_url_history ALTER COLUMN is_enabled DROP NOT NULL;
