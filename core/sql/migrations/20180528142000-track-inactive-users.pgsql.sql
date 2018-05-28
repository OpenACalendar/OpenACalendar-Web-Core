ALTER TABLE user_account_information ADD last_website_login_at  timestamp without time zone NULL;
ALTER TABLE user_account_remember_me ADD last_used_at   timestamp without time zone NULL;
ALTER TABLE user_account_private_feed_key ADD last_used_at   timestamp without time zone NULL;
