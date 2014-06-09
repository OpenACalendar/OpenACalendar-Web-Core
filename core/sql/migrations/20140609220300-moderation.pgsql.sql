ALTER TABLE event_information ADD approved_at timestamp without time zone NULL;
ALTER TABLE event_history ADD approved_at timestamp without time zone NULL;

ALTER TABLE group_information ADD approved_at timestamp without time zone NULL;
ALTER TABLE group_history ADD approved_at timestamp without time zone NULL;

ALTER TABLE venue_information ADD approved_at timestamp without time zone NULL;
ALTER TABLE venue_history ADD approved_at timestamp without time zone NULL;

ALTER TABLE area_information ADD approved_at timestamp without time zone NULL;
ALTER TABLE area_history ADD approved_at timestamp without time zone NULL;

ALTER TABLE event_in_group ADD addition_approved_at timestamp without time zone NULL;
ALTER TABLE event_in_group ADD removal_approved_at timestamp without time zone NULL;

