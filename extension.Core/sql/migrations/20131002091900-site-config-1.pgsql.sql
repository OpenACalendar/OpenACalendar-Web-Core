ALTER TABLE site_information ADD prompt_emails_days_in_advance smallint default '30' NOT NULL;

ALTER TABLE site_history ADD prompt_emails_days_in_advance smallint default '30' NOT NULL;

