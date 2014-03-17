CREATE TABLE country (
	id SERIAL,
	two_char_code VARCHAR(2) NULL,
	title VARCHAR(255) NULL,
	timezones TEXT NULL,
	PRIMARY KEY(id)
);
CREATE UNIQUE INDEX country_two_char_code ON country(two_char_code);


