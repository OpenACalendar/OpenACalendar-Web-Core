ALTER TABLE site_history DROP IF EXISTS api2_application_id;
ALTER TABLE event_history DROP IF EXISTS api2_application_id ;
ALTER TABLE group_history DROP IF EXISTS api2_application_id ;
ALTER TABLE venue_history DROP IF EXISTS api2_application_id ;
ALTER TABLE area_history DROP IF EXISTS api2_application_id ;
ALTER TABLE curated_list_history DROP IF EXISTS api2_application_id ;
ALTER TABLE import_url_history DROP IF EXISTS api2_application_id ;
ALTER TABLE tag_history DROP IF EXISTS api2_application_id ;
ALTER TABLE event_has_tag DROP IF EXISTS added_by_api2_application_id ;
ALTER TABLE event_has_tag DROP IF EXISTS removed_by_api2_application_id ;

DROP TABLE IF EXISTS api2_application_user_authorisation_token CASCADE;
DROP TABLE IF EXISTS api2_application_user_token_information CASCADE;
DROP TABLE IF EXISTS api2_application_request_token CASCADE;
DROP TABLE IF EXISTS user_in_api2_application_information CASCADE;
DROP TABLE IF EXISTS api2_application_information CASCADE;
