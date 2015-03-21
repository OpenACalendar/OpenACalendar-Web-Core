CREATE TABLE site_feature_information (
  site_id  INTEGER NOT NULL,
  extension_id VARCHAR(255) NOT NULL,
  feature_id VARCHAR(255) NOT NULL,
  is_on BOOLEAN NOT NULL,
  PRIMARY KEY (site_id,extension_id,feature_id)
);

ALTER TABLE site_feature_information ADD CONSTRAINT site_feature_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);

CREATE TABLE site_feature_history (
  site_id  INTEGER NOT NULL,
  extension_id VARCHAR(255) NOT NULL,
  feature_id VARCHAR(255) NOT NULL,
  is_on BOOLEAN NOT NULL,
  is_on_changed SMALLINT DEFAULT '0' NOT NULL,
  user_account_id INTEGER,
  created_at timestamp without time zone NOT NULL,
  PRIMARY KEY (site_id,extension_id,feature_id,created_at)
);

ALTER TABLE site_feature_history ADD CONSTRAINT site_feature_information_site_id FOREIGN KEY (site_id) REFERENCES site_information(id);
ALTER TABLE site_feature_history ADD CONSTRAINT site_feature_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);


ALTER TABLE group_history ADD edit_comment TEXT NULL;
ALTER TABLE event_history ADD edit_comment TEXT NULL;
ALTER TABLE area_history ADD edit_comment TEXT NULL;
ALTER TABLE venue_history ADD edit_comment TEXT NULL;
ALTER TABLE tag_history ADD edit_comment TEXT NULL;
ALTER TABLE import_url_history ADD edit_comment TEXT NULL;
