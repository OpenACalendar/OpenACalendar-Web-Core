ALTER TABLE site_history ADD   title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   slug_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   description_text_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   footer_text_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_web_robots_allowed_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_closed_by_sys_admin_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   closed_by_sys_admin_reason_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_listed_in_index_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_map_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_importer_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_curated_list_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   prompt_emails_days_in_advance_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_virtual_events_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_physical_events_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE site_history ADD   is_feature_group_changed SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE event_history ADD   summary_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   description_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   start_at_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   end_at_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   is_deleted_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   country_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   timezone_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   venue_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   url_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   is_virtual_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   is_physical_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE event_history ADD   area_id_changed SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE group_history ADD   title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE group_history ADD   description_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE group_history ADD   url_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE group_history ADD   twitter_username_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE group_history ADD   is_deleted_changed SMALLINT DEFAULT '0' NOT NULL;

ALTER TABLE venue_history ADD   title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   description_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   lat_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   lng_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   country_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   is_deleted_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE venue_history ADD   area_id_changed SMALLINT DEFAULT '0' NOT NULL;


ALTER TABLE area_history ADD   title_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD   description_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD   country_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD   parent_area_id_changed SMALLINT DEFAULT '0' NOT NULL;
ALTER TABLE area_history ADD   is_deleted_changed SMALLINT DEFAULT '0' NOT NULL;
