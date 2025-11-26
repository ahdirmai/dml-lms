# Manual Test Cases - Learning Management System (LMS)

This document provides detailed manual test cases for verifying the functionality of the LMS application, including comprehensive edge cases to prevent bugs.

## 1. Authentication & Profile

### AUTH-01: User Login (Success)

-   **Preconditions:** User account exists and is active.
-   **Steps:**
    1.  Navigate to the login page (`/login`).
    2.  Enter valid email and password.
    3.  Click "Login".
-   **Expected Result:** User is redirected to the dashboard corresponding to their active role (Student/Instructor/Admin).

### AUTH-02: User Login (Failure)

-   **Preconditions:** None.
-   **Steps:**
    1.  Navigate to the login page.
    2.  Enter invalid credentials.
    3.  Click "Login".
-   **Expected Result:** Error message "These credentials do not match our records." is displayed. User remains on login page.

### AUTH-03: Profile Update

-   **Preconditions:** User is logged in.
-   **Steps:**
    1.  Navigate to Profile Settings (`/profile`).
    2.  Change Name or Email.
    3.  Click "Save".
-   **Expected Result:** Success message displayed. Profile information is updated in the database.

## 2. Admin Features

### ADM-01: Dashboard Overview

-   **Preconditions:** Logged in as Admin.
-   **Steps:**
    1.  Navigate to Admin Dashboard (`/admin/dashboard`).
-   **Expected Result:** Dashboard loads with correct statistics for Total Users, Total Courses, and Recent Activity.

### ADM-02: Create New User

-   **Preconditions:** Logged in as Admin.
-   **Steps:**
    1.  Navigate to User Management (`/admin/users`).
    2.  Click "Create User".
    3.  Fill in Name, Email, Password, and select Role(s).
    4.  Click "Save".
-   **Expected Result:** User is created and appears in the user list.

### ADM-03: Create Course (Draft)

-   **Preconditions:** Logged in as Admin.
-   **Steps:**
    1.  Navigate to Course Management (`/admin/courses`).
    2.  Click "Create Course".
    3.  Fill in Title, Description, Category, and Instructor.
    4.  Click "Save".
-   **Expected Result:** Course is created with status 'Draft'. User is redirected to Course Builder/Edit page.

### ADM-04: Add Module & Lesson

-   **Preconditions:** Admin is on Course Builder page.
-   **Steps:**
    1.  Click "Add Module". Enter Module Title. Save.
    2.  Inside the new module, click "Add Lesson".
    3.  Select Lesson Type (Video/Text/GDrive).
    4.  Fill in Lesson Title and Content URL.
    5.  Click "Save".
-   **Expected Result:** Module and Lesson are added to the course structure.

### ADM-05: Publish Course

-   **Preconditions:** Course has at least 1 Module and 1 Lesson.
-   **Steps:**
    1.  On Course Edit page, click "Publish".
-   **Expected Result:** Course status changes to 'Published'. Success message displayed.

### ADM-06: View User Activity

-   **Preconditions:** Logged in as Admin.
-   **Steps:**
    1.  Navigate to "User Activity" in sidebar.
-   **Expected Result:** Table displays recent user activities (logins, enrollments, etc.) with timestamps and IP addresses.

## 3. Instructor Features

### INS-01: Instructor Dashboard

-   **Preconditions:** Logged in as Instructor.
-   **Steps:**
    1.  Navigate to Instructor Dashboard.
-   **Expected Result:** Dashboard shows courses taught by the instructor and student enrollment stats.

### INS-02: Manage Own Course

-   **Preconditions:** Logged in as Instructor.
-   **Steps:**
    1.  Navigate to My Courses.
    2.  Select a course to edit.
    3.  Update description or add a new lesson.
-   **Expected Result:** Changes are saved. Instructor cannot edit courses belonging to others.

## 4. Student Features

### STU-01: Course Enrollment

-   **Preconditions:** Logged in as Student. Course is Published.
-   **Steps:**
    1.  Navigate to Course Catalog.
    2.  Click on a Course.
    3.  Click "Start Learning" (or "Enroll").
-   **Expected Result:** Student is enrolled. Redirected to Course Player/First Lesson.

### STU-02: Lesson Progress

-   **Preconditions:** Student enrolled in course.
-   **Steps:**
    1.  Open a Video Lesson.
    2.  Watch video for required duration (e.g., 30s).
    3.  Click "Mark as Complete" (or auto-complete).
-   **Expected Result:** Lesson marked as completed. Progress bar updates. Next lesson unlocks (if sequential).

### STU-03: Take Quiz (Pretest/Posttest)

-   **Preconditions:** Course has a quiz.
-   **Steps:**
    1.  Navigate to Quiz section.
    2.  Answer questions.
    3.  Click "Submit".
-   **Expected Result:** Score is calculated and displayed. Pass/Fail status is shown.

### STU-04: Course Completion

-   **Preconditions:** All lessons and required quizzes completed.
-   **Steps:**
    1.  Complete final lesson/posttest.
-   **Expected Result:** Course marked as 100% complete. "Download Certificate" button appears (if applicable).

## 5. Edge Cases & Bug Prevention

### 5.1 Authentication & Authorization

| ID           | Feature       | Test Case                                                                                    | Expected Result                                                 |
| :----------- | :------------ | :------------------------------------------------------------------------------------------- | :-------------------------------------------------------------- |
| EDGE-AUTH-01 | Authorization | Student attempts to access Admin Dashboard URL directly (`/admin/dashboard`)                 | Access Denied (403 Forbidden) or Redirect to Student Dashboard. |
| EDGE-AUTH-02 | Authorization | Instructor attempts to edit a course ID belonging to another instructor via URL manipulation | Access Denied (403 Forbidden).                                  |
| EDGE-AUTH-03 | Session       | User attempts to access protected route after session timeout                                | Redirect to Login page.                                         |
| EDGE-AUTH-04 | Registration  | Register with an email that already exists                                                   | Validation error: "Email already taken".                        |

### 5.2 Course Logic & Progression

| ID          | Feature    | Test Case                                                                          | Expected Result                                                      |
| :---------- | :--------- | :--------------------------------------------------------------------------------- | :------------------------------------------------------------------- |
| EDGE-CRS-01 | Due Date   | Student accesses course exactly at the expiration time (boundary testing)          | Access denied immediately after expiration.                          |
| EDGE-CRS-02 | Due Date   | Student attempts to submit a quiz after course expiration                          | Submission rejected, error message displayed.                        |
| EDGE-CRS-03 | Pretest    | Student fails required Pretest and attempts to access Lesson 1 via direct URL      | Access Denied. Redirect to Pretest page.                             |
| EDGE-CRS-04 | Sequential | Student attempts to access Lesson 2 via URL before completing Lesson 1             | Access Denied. Redirect to Lesson 1.                                 |
| EDGE-CRS-05 | Completion | Student watches video for 29s (requirement 30s) and tries to complete              | "Mark as Complete" button remains disabled or action fails.          |
| EDGE-CRS-06 | Enrollment | Student attempts to enroll in a course they are already enrolled in (double click) | System handles gracefully, redirects to course player without error. |

### 5.3 Quizzes & Assessments

| ID         | Feature  | Test Case                                                       | Expected Result                                                                     |
| :--------- | :------- | :-------------------------------------------------------------- | :---------------------------------------------------------------------------------- |
| EDGE-QZ-01 | Timer    | Student lets quiz timer run out without submitting              | Quiz auto-submits with current answers or closes with 0 score (depending on logic). |
| EDGE-QZ-02 | Retake   | Student attempts to retake a quiz after max attempts reached    | Retake button disabled/hidden.                                                      |
| EDGE-QZ-03 | Posttest | Student attempts to take Posttest before completing all lessons | Access Denied. Message "Complete all lessons first".                                |
| EDGE-QZ-04 | Grading  | Instructor enters a grade > 100 or < 0 manually                 | Validation error.                                                                   |

### 5.4 Input Validation & Security

| ID          | Feature      | Test Case                                                                  | Expected Result                                          |
| :---------- | :----------- | :------------------------------------------------------------------------- | :------------------------------------------------------- |
| EDGE-INP-01 | XSS          | User enters `<script>alert('XSS')</script>` in Course Title or Description | Input is sanitized; script does not execute when viewed. |
| EDGE-INP-02 | File Upload  | User uploads an executable (`.exe`) as a course thumbnail                  | Upload rejected. Only image formats allowed.             |
| EDGE-INP-03 | File Upload  | User uploads a file larger than server limit (e.g., >10MB)                 | Error message: "File too large".                         |
| EDGE-INP-04 | Empty Fields | Admin tries to publish a course with empty title/description               | Validation error.                                        |

### 5.5 Concurrency

| ID          | Feature     | Test Case                                                                         | Expected Result                                          |
| :---------- | :---------- | :-------------------------------------------------------------------------------- | :------------------------------------------------------- |
| EDGE-CON-01 | Quiz Submit | User clicks "Submit" button multiple times rapidly                                | Only one submission is recorded.                         |
| EDGE-CON-02 | Enrollment  | Two users enroll in the last available seat simultaneously (if seat limit exists) | System handles race condition (one succeeds, one fails). |
