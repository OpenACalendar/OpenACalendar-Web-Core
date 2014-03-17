ALTER TABLE import_url_result ADD is_success BOOLEAN NOT NULl;
ALTER TABLE import_url_result ADD message VARCHAR(255);
ALTER TABLE import_url_information  RENAME COLUMN  enabled TO is_enabled;
ALTER TABLE import_url_history  RENAME COLUMN  enabled TO is_enabled;

