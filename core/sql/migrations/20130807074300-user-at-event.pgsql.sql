CREATE TABLE user_at_event_information (
	user_account_id INTEGER,
	event_id INTEGER,
	is_plan_attending BOOLEAN DEFAULT '0' NOT NULL, 
	is_plan_maybe_attending BOOLEAN DEFAULT '0' NOT NULL,  
	is_plan_public BOOLEAN DEFAULT '0' NOT NULL, 
	created_at timestamp without time zone NOT NULL,
	PRIMARY KEY(user_account_id,event_id)
);

ALTER TABLE user_at_event_information ADD CONSTRAINT user_at_event_information_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
ALTER TABLE user_at_event_information ADD CONSTRAINT user_at_event_information_event_id FOREIGN KEY (event_id) REFERENCES event_information(id);


