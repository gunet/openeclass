-- AI Evaluation Schema for Exercise Module
-- Adds AI-based evaluation capability for free text responses in exercises

-- Create exercise_ai_config table to store AI evaluation configuration per question
CREATE TABLE exercise_ai_config (
    id INT(11) NOT NULL AUTO_INCREMENT,
    question_id INT(11) NOT NULL COMMENT 'Reference to exercise_question.id',
    course_id INT(11) NOT NULL COMMENT 'Reference to course.id for easier querying',
    enabled TINYINT(1) DEFAULT 1 COMMENT 'Enable AI evaluation for this question',
    evaluation_prompt TEXT NOT NULL COMMENT 'Evaluation criteria for AI assessment',
    sample_responses TEXT NULL COMMENT 'JSON array of sample responses for AI reference',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_question (question_id),
    KEY idx_course (course_id),
    KEY idx_enabled (enabled),
    
    FOREIGN KEY (question_id) REFERENCES exercise_question(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI evaluation configuration for exercise questions';

-- Create exercise_ai_evaluation table to store AI assessment results
CREATE TABLE exercise_ai_evaluation (
    id INT(11) NOT NULL AUTO_INCREMENT,
    answer_record_id INT(11) NOT NULL COMMENT 'Reference to exercise_answer_record.answer_record_id',
    question_id INT(11) NOT NULL COMMENT 'Reference to exercise_question.id',
    exercise_id INT(11) NOT NULL COMMENT 'Reference to exercise.id for easier querying',
    student_record_id INT(11) NOT NULL COMMENT 'Reference to exercise_user_record.eurid',
    ai_suggested_score DECIMAL(5,2) NOT NULL COMMENT 'AI suggested score',
    ai_max_score DECIMAL(5,2) NOT NULL COMMENT 'Maximum possible score for this question',
    ai_reasoning TEXT NOT NULL COMMENT 'AI explanation of the score',
    ai_confidence DECIMAL(3,2) NOT NULL COMMENT 'AI confidence level (0.0-1.0)',
    ai_provider VARCHAR(50) NOT NULL COMMENT 'AI provider used (openai, anthropic, etc.)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When AI evaluation was performed',
    
    PRIMARY KEY (id),
    KEY idx_answer_record (answer_record_id),
    KEY idx_question (question_id),
    KEY idx_exercise (exercise_id),
    KEY idx_student_record (student_record_id),
    KEY idx_confidence (ai_confidence),
    
    FOREIGN KEY (answer_record_id) REFERENCES exercise_answer_record(answer_record_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES exercise_question(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercise(id) ON DELETE CASCADE,
    FOREIGN KEY (student_record_id) REFERENCES exercise_user_record(eurid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores AI evaluation results for exercise free text answers';