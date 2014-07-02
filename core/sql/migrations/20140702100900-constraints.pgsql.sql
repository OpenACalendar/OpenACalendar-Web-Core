ALTER TABLE area_history ADD CONSTRAINT area_history_user_account_id FOREIGN KEY (user_account_id) REFERENCES user_account_information(id);
