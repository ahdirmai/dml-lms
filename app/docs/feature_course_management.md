# Feature: Course Management (Admin)

This document details the Course Management features available to Administrators.

## Overview

Administrators can create, structure, and manage courses, including curriculum building, student assignment, and publication settings.

## 1. Course Listing

-   **Route**: `/admin/courses`
-   **Controller**: `App\Http\Controllers\Admin\CourseController@index`
-   **Features**:
    -   **Filtering**: Search by title/description, filter by Status (Draft, Published, Archived), Category, or Instructor.
    -   **Sorting**: Sort by Date, Title, or Status.
    -   **Stats**: Shows counts of Modules and Lessons per course.

## 2. Course Creation & Settings

-   **Route**: `/admin/courses/create`
-   **Controller**: `App\Http\Controllers\Admin\CourseController@store`, `update`
-   **Key Settings**:
    -   **Basic Info**: Title, Subtitle, Description, Thumbnail, Difficulty Level.
    -   **Instructor**: Assign an instructor to the course.
    -   **Category**: Assign a category.
    -   **Configuration Flags**:
        -   `Has Pre-test`: Enables Pre-test functionality.
        -   `Has Post-test`: Enables Post-test functionality.
        -   `Require Pre-test`: Forces students to pass Pre-test before accessing content.
        -   `Using Due Date`: Enables date-based access restrictions.

## 3. Curriculum Builder

The Course Edit page acts as a Curriculum Builder.

-   **Modules**:
    -   Group lessons into logical sections.
    -   Manage order of modules.
-   **Lessons**:
    -   Add lessons to modules.
    -   **Types**: Video (YouTube), Text, PDF, Quiz, External Link, Google Drive.
    -   **Duration**: Set estimated duration (used for progress tracking).
    -   **Preview**: Mark lessons as free preview.

## 4. Student Assignment (Enrollment)

-   **Route**: `/admin/courses/{course}/assign`
-   **Controller**: `App\Http\Controllers\Admin\CourseAssignController`
-   **Features**:
    -   **Assign Students**: Select users (with 'student' role) to enroll in the course.
    -   **Due Dates**: If `Using Due Date` is enabled, Admin **must** specify `Start Date` and `End Date` for the selected students.
    -   **Unassign**: Remove students from the course (deletes enrollment).

## 5. Publication Flow

-   **Route**: `/admin/courses/{course}/publish`
-   **Controller**: `App\Http\Controllers\Admin\CourseController@publish`
-   **Validation Rules**:
    -   Course must have at least **1 Module**.
    -   Course must have at least **1 Lesson**.
-   **Logic**:
    -   Changes status to `published`.
    -   Sets `published_at` timestamp.
    -   Prevents modification of critical structure once published (optional/future enhancement).

## 6. Deletion

-   **Route**: `/admin/courses/{course}` (DELETE)
-   **Logic**:
    -   **Restriction**: Cannot delete a course if it has active enrollments (students assigned). Admin must unassign students first.
    -   **Cleanup**: Deletes associated Modules, Lessons, and detach Categories/Tags.
