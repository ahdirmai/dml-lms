# Database Documentation

## Core Tables

### `users`

Stores user account information.

-   `id` (BigInt, PK): Unique identifier.
-   `name` (String): User's full name.
-   `email` (String, Unique): User's email address.
-   `password` (String): Hashed password.
-   `email_verified_at` (Timestamp, Nullable): Email verification timestamp.
-   `created_at`, `updated_at` (Timestamp): Standard timestamps.

### `user_profiles`

Stores additional user details.

-   `id` (BigInt, PK): Unique identifier.
-   `user_id` (BigInt, FK): References `users.id`.
-   `department` (String, Nullable): User's department.
-   `job_title` (String, Nullable): User's job title.
-   `is_employee` (Boolean): Flag for employee status.
-   `is_hr` (Boolean): Flag for HR status.
-   `raw_payload` (JSON, Nullable): Additional data.

## Course Management

### `courses`

Stores course information.

-   `id` (UUID, PK): Unique identifier.
-   `title` (String): Course title.
-   `slug` (String, Unique): URL-friendly slug.
-   `subtitle` (String, Nullable): Course subtitle.
-   `description` (LongText): Detailed description.
-   `thumbnail_path` (String, Nullable): Path to thumbnail image.
-   `status` (String): Course status (draft, published, archived).
-   `difficulty` (String): Difficulty level (beginner, intermediate, advanced).
-   `instructor_id` (BigInt, FK, Nullable): References `users.id` (Instructor).
-   `created_by` (BigInt, FK, Nullable): References `users.id` (Creator).
-   `published_at` (Timestamp, Nullable): Publication timestamp.

### `modules`

Groups lessons within a course.

-   `id` (UUID, PK): Unique identifier.
-   `course_id` (UUID, FK): References `courses.id`.
-   `title` (String): Module title.
-   `slug` (String, Nullable, Unique): URL-friendly slug.
-   `order` (Integer): Display order.

### `lessons`

Individual learning units.

-   `id` (UUID, PK): Unique identifier.
-   `course_id` (UUID, FK): References `courses.id`.
-   `module_id` (UUID, FK): References `modules.id`.
-   `title` (String): Lesson title.
-   `slug` (String, Nullable, Unique): URL-friendly slug.
-   `description` (Text, Nullable): Lesson description.
-   `kind` (String): Content type (youtube, gdrive, quiz, pdf, text, external).
-   `content_url` (String, Nullable): URL for external content.
-   `youtube_video_id` (String, Nullable): YouTube video ID.
-   `gdrive_file_id` (String, Nullable): Google Drive file ID.
-   `order_no` (SmallInteger): Order within the module.
-   `duration_minutes` (SmallInteger): Estimated duration.
-   `is_preview` (Boolean): Preview availability.

### `categories`

Course categories.

-   `id` (UUID, PK): Unique identifier.
-   `name` (String): Category name.
-   `slug` (String, Unique): URL-friendly slug.
-   `description` (Text, Nullable): Description.
-   `created_by` (BigInt, FK, Nullable): References `users.id`.

### `tags`

Course tags.

-   `id` (UUID, PK): Unique identifier.
-   `name` (String, Unique): Tag name.
-   `slug` (String, Unique): URL-friendly slug.

### `category_courses` (Pivot)

Links courses to categories.

-   `category_id` (UUID, FK): References `categories.id`.
-   `course_id` (UUID, FK): References `courses.id`.

### `course_tags` (Pivot)

Links courses to tags.

-   `course_id` (UUID, FK): References `courses.id`.
-   `tag_id` (UUID, FK): References `tags.id`.

## Enrollment & Progress

### `enrollments`

Tracks user enrollment in courses.

-   `id` (BigInt, PK): Unique identifier.
-   `user_id` (BigInt, FK): References `users.id`.
-   `course_id` (UUID, FK): References `courses.id`.
-   `status` (Enum): assigned, active, completed, cancelled.
-   `enrolled_at` (Timestamp, Nullable): Enrollment timestamp.
-   `completed_at` (Timestamp, Nullable): Completion timestamp.

### `lesson_progress`

Tracks progress for individual lessons.

-   `id` (BigInt, PK): Unique identifier.
-   `enrollment_id` (BigInt, FK): References `enrollments.id`.
-   `lesson_id` (UUID, FK): References `lessons.id`.
-   `status` (Enum): not_started, in_progress, completed.
-   `started_at` (Timestamp, Nullable): Start timestamp.
-   `completed_at` (Timestamp, Nullable): Completion timestamp.
-   `last_activity_at` (Timestamp, Nullable): Last activity timestamp.
-   `duration_seconds` (Integer): Time spent.

### `certificates`

Certificates issued upon course completion.

-   `id` (UUID, PK): Unique identifier.
-   `enrollment_id` (BigInt, FK): References `enrollments.id`.
-   `certificate_number` (String, Unique): Unique certificate number.
-   `issued_at` (Timestamp): Issuance timestamp.

## Assessment (Quiz)

### `quizzes`

Quiz definitions.

-   `id` (UUID, PK): Unique identifier.
-   `quizzable_id` (UUID, Nullable): Polymorphic ID (Course/Lesson).
-   `quizzable_type` (String, Nullable): Polymorphic Type.
-   `quiz_kind` (String): pretest, posttest, regular.
-   `title` (String): Quiz title.
-   `time_limit_seconds` (Integer): Time limit.
-   `passing_score` (Decimal, Nullable): Passing score.
-   `shuffle_questions` (Boolean): Shuffle questions flag.
-   `shuffle_options` (Boolean): Shuffle options flag.

### `quiz_questions`

Questions within a quiz.

-   `id` (UUID, PK): Unique identifier.
-   `quiz_id` (UUID, FK): References `quizzes.id`.
-   `question_text` (Text): The question.
-   `question_type` (String): mcq, truefalse, shortanswer.
-   `score` (Decimal): Score value.
-   `order` (Integer): Display order.

### `quiz_options`

Options for questions.

-   `id` (UUID, PK): Unique identifier.
-   `question_id` (UUID, FK): References `quiz_questions.id`.
-   `option_text` (Text): Option text.
-   `is_correct` (Boolean): Correctness flag.

### `quiz_attempts`

User attempts at taking a quiz.

-   `id` (UUID, PK): Unique identifier.
-   `quiz_id` (UUID, FK): References `quizzes.id`.
-   `user_id` (BigInt, FK): References `users.id`.
-   `attempt_no` (Integer): Attempt number.
-   `started_at` (Timestamp, Nullable): Start time.
-   `finished_at` (Timestamp, Nullable): Finish time.
-   `score` (Decimal): Score achieved.
-   `passed` (Boolean): Pass status.
-   `duration_seconds` (Integer, Nullable): Duration taken.

### `quiz_answers`

Answers provided in an attempt.

-   `id` (UUID, PK): Unique identifier.
-   `attempt_id` (UUID, FK): References `quiz_attempts.id`.
-   `question_id` (UUID, FK): References `quiz_questions.id`.
-   `selected_option_id` (UUID, FK, Nullable): References `quiz_options.id`.
-   `answer_text` (Text, Nullable): Text answer (for short answer).
-   `is_correct` (Boolean): Correctness flag.
-   `score_awarded` (Decimal): Score awarded.
