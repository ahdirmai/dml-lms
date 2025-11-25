# Feature: Learning Experience (Student)

This document details the features available to Students for learning, progress tracking, and assessment.

## 1. Student Dashboard

-   **Route**: `/dashboard`
-   **Controller**: `App\Http\Controllers\User\DashboardController@index`
-   **Features**:
    -   **User Info**: Displays name, position, and vessel/department.
    -   **My Courses Summary**: Lists enrolled courses with progress bars.
    -   **Performance Stats**: Calculates completion rates and average scores.
    -   **Leaderboard**: (Currently Dummy) Shows top performers.

## 2. My Courses

-   **Route**: `/my-courses`
-   **Controller**: `App\Http\Controllers\User\CourseController@index`
-   **Tabs**:
    -   **Sedang Dipelajari (In Progress)**: Courses with status `assigned` or `active`.
    -   **Telah Selesai (Completed)**: Courses with status `completed`.
    -   **Kursus Private**: Courses marked as `private`.

## 3. Course Detail & Syllabus

-   **Route**: `/courses/{course}`
-   **Controller**: `App\Http\Controllers\User\CourseController@show`
-   **Features**:
    -   **Access Control**: Checks if the user is enrolled.
    -   **Due Date Check**: Blocks access if the current date is outside the assigned `Start Date` and `End Date` (if configured).
    -   **Syllabus**: Lists Modules and Lessons with completion status indicators.
    -   **Action Buttons**:
        -   **Start Pre-test**: Visible if course has Pre-test and it's not taken.
        -   **Start Learning**: Visible if Pre-test passed (or not required).
        -   **Start Post-test**: Visible only after all lessons are completed.
        -   **Download Certificate**: Visible after Post-test passed and Review submitted.

## 4. Lesson Player & Progress

-   **Route**: `/lessons/{lesson}`
-   **Controller**: `App\Http\Controllers\User\LessonController@show`
-   **Features**:
    -   **Content Viewer**: Supports Video (YouTube), Text, PDF, and Quizzes.
    -   **Sidebar Navigation**:
        -   Lists all modules and lessons.
        -   **Sequential Locking**: Next lesson is locked until the current lesson is completed.
    -   **Progress Tracking**:
        -   **Auto-Save**: Pings server every 10s to save `duration_seconds` and `last_watched_second` (for video).
        -   **Completion Logic**:
            -   **Video**: Must watch until < 30s remaining.
            -   **Text/PDF**: Must spend at least (Duration - 60s) on page.
        -   **Mark as Complete**: Button appears only when time requirement is met.

## 5. Assessments (Quizzes)

-   **Pre-test & Post-test**:
    -   **Submission**: Handled via `CourseController@submitTest`.
    -   **Logic**: Calculates score immediately.
    -   **Pre-test Effect**: If `Require Pre-test` is on, passing unlocks the course content.
    -   **Post-test Effect**: Passing allows moving to the Review stage.
-   **Lesson Quizzes**:
    -   Embedded within the lesson flow.

## 6. Course Completion & Certificate

-   **Review**:
    -   User must submit a star rating review after passing the Post-test.
    -   Submitting review marks the enrollment as `completed`.
-   **Certificate**:
    -   **Route**: `/courses/{course}/certificate`
    -   **Generation**: Generates a PDF certificate with a unique number (`DML-YYYYMMDD-RANDOM`).
    -   **Requirement**: Only available if course is completed and reviewed.
