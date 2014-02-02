CREATE TABLE event_recur_set (
	id SERIAL,
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(id)
);


ALTER TABLE event_information ADD event_recur_set_id INTEGER NULL;
ALTER TABLE event_information ADD CONSTRAINT event_information_event_recur_set_id FOREIGN KEY (event_recur_set_id) REFERENCES event_recur_set(id);



