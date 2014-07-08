ALTER TABLE media_in_group ADD addition_approved_at timestamp without time zone NULL;
ALTER TABLE media_in_group ADD removal_approved_at timestamp without time zone NULL;

ALTER TABLE media_in_venue ADD addition_approved_at timestamp without time zone NULL;
ALTER TABLE media_in_venue ADD removal_approved_at timestamp without time zone NULL;
