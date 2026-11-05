-- Apply once after migration 001, with application writes paused.
ALTER TABLE users
    ADD COLUMN school_student_id VARCHAR(11) CHARACTER SET ascii COLLATE ascii_bin NULL,
    ADD UNIQUE KEY uq_users_school_student_id (school_student_id),
    ADD CONSTRAINT ck_users_school_student_id CHECK (
        school_student_id IS NULL OR (
            role = 'student' AND CHAR_LENGTH(school_student_id) = 11
            AND school_student_id REGEXP '^[0-9]{2}-[0-9]{4}-[0-9]{3}$'
        )
    );
