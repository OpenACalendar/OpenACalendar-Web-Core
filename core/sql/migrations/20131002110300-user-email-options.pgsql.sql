ALTER TABLE user_account_information ADD is_email_watch_notify boolean default '1' NOT NULL;
ALTER TABLE user_account_information ADD is_email_watch_prompt boolean default '1' NOT NULL;
ALTER TABLE user_account_information ADD email_upcoming_events VARCHAR(1) default 'w' NOT NULL;

