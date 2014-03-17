
ALTER TABLE user_account_information ADD is_closed_by_sys_admin boolean default '0' NOT NULL;
ALTER TABLE user_account_information ADD closed_by_sys_admin_reason TEXT NULL;

