CREATE TABLE app_configuration_information (
	extension_id VARCHAR(255) NOT NULL,
	configuration_key VARCHAR(255) NOT NULL,
	value_text TEXT NULL,
	PRIMARY KEY (extension_id, configuration_key)
);

