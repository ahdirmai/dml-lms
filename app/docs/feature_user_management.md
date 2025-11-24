# Feature: User Management (Admin)

This document details the User Management features available to Administrators in the LMS.

## Overview

Administrators can manage user accounts, assign roles, and handle user integration from external systems.

## 1. User List & Search

-   **Route**: `/admin/users`
-   **Controller**: `App\Http\Controllers\Admin\UsersController@index`
-   **Features**:
    -   **Pagination**: Displays users in pages of 10.
    -   **Search**: Filter users by name or email using the search bar.
    -   **Role Filter**: Filter users by specific roles (e.g., Student, Instructor, Admin).
    -   **Display**: Shows Name, Email, Roles, and Actions (Edit/Delete).

## 2. Create User

-   **Route**: `/admin/users/create` (GET), `/admin/users` (POST)
-   **Controller**: `App\Http\Controllers\Admin\UsersController@create`, `store`
-   **Fields**:
    -   `Name`: Full name of the user.
    -   `Email`: Unique email address.
    -   `Password`: Minimum 8 characters.
    -   `Roles`: Checkboxes to assign roles.
-   **Logic**:
    -   Automatically assigns the `student` role if not selected.
    -   Sets `active_role` to the first assigned role (or `student`) if empty.
    -   Hashes the password before saving.
    -   Sets `email_verified_at` to current time (auto-verified).

## 3. Edit User

-   **Route**: `/admin/users/{user}/edit` (GET), `/admin/users/{user}` (PUT)
-   **Controller**: `App\Http\Controllers\Admin\UsersController@edit`, `update`
-   **Features**:
    -   Update Name and Email.
    -   Update Password (leave blank to keep current).
    -   **Role Management**: Add or remove roles.
    -   **Active Role**: Select which role is currently active for the user (affects dashboard view).
-   **Logic**:
    -   Ensures `student` role is never removed if it's the fallback.
    -   Validates that `active_role` belongs to the user's assigned roles.

## 4. Delete User

-   **Route**: `/admin/users/{user}` (DELETE)
-   **Controller**: `App\Http\Controllers\Admin\UsersController@destroy`
-   **Logic**:
    -   Prevents users from deleting their own account.
    -   Uses database transactions and locking (`lockForUpdate`) to ensure data integrity.

## 5. User Integration (Import)

> Detailed in [SSO & User Integration Docs](sso_integration_docs.md).

-   **Route**: `/admin/integrations/users`
-   **Controller**: `App\Http\Controllers\Admin\UserIntegrationController`
-   **Features**:
    -   **Preview**: Fetch user data from the Internal System API.
    -   **Import**: Select and sync users into the LMS.
    -   **Sync Logic**: Updates existing users or creates new ones, syncs Profile data (Department, Job Title), and maps roles based on flags (`is_employee` -> Student, `is_hr` -> Instructor).
