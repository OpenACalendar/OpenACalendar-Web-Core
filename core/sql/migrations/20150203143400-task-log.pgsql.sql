CREATE TABLE task_log (
	extension_id VARCHAR(255) NOT NULL,
	task_id VARCHAR(255) NOT NULL,
	started_at timestamp without time zone NOT NULL,
	ended_at timestamp  without time zone NULL,
	result_data TEXT NULL,
	exception_data TEXT NULL
);
CREATE INDEX task_log_task ON task_log(extension_id, task_id);
CREATE INDEX task_log_latest ON task_log(started_at);

