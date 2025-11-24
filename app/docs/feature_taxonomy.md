# Feature: Taxonomy (Categories & Tags)

This document details the classification systems used to organize courses.

## 1. Categories

-   **Purpose**: Broad grouping of courses (e.g., "Health & Safety", "Engineering", "Soft Skills").
-   **Management**:
    -   **Route**: `/admin/categories`
    -   **Controller**: `App\Http\Controllers\Admin\CategoryController`
    -   **Fields**: Name, Slug (auto-generated), Description.
-   **Usage**:
    -   Courses can belong to one or more categories.
    -   Used for filtering in the Course List.

## 2. Tags

-   **Purpose**: Specific keywords or topics (e.g., "Fire Safety", "Leadership", "Python").
-   **Management**:
    -   **Route**: `/admin/tags` (Assuming standard resource controller).
    -   **Fields**: Name, Slug.
-   **Usage**:
    -   Courses can have multiple tags.
    -   Helps in search and discovery.
