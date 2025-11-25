# SSO & User Integration Documentation

This document describes how the Internal System integrates with the LMS for User Synchronization and Single Sign-On (SSO).

## Overview

The integration consists of two parts:

1.  **User Synchronization (Import)**: The LMS Admin pulls user data from the Internal System API.
2.  **Single Sign-On (SSO)**: Users log in to the LMS via a JWT-based link from the Internal System.

> **Note**: Users must be imported into the LMS before they can use SSO. The SSO endpoint does not auto-provision users.

---

## 1. User Synchronization (Import)

The LMS periodically (or manually via Admin UI) fetches user data from the Internal System to keep user accounts in sync.

### API Endpoint (Internal System)

The LMS expects the Internal System to provide an endpoint that returns user data.

-   **Method**: `GET`
-   **Path**: `/lms/users` (Configurable)
-   **Query Parameters**:
    -   `department` (optional): Filter by department.
    -   `status` (optional): Filter by status (e.g., 'active').
    -   `limit` (optional): Limit number of records.

### Response Format

The Internal System should return a JSON response with a list of users.

```json
{
    "data": [
        {
            "employee_id": "EMP001", // Required (mapped to external_id)
            "full_name": "John Doe", // Required
            "email": "john@example.com", // Required
            "department": "IT",
            "job_title": "Developer",
            "manager_external_id": "EMP999",
            "is_employee": true, // Determines 'student' role
            "is_hr": false, // Determines 'instructor' role
            "status": "active" // active, inactive, resigned, etc.
        }
    ]
}
```

### Data Mapping

| Internal System Field         | LMS Field                  | Notes                                         |
| :---------------------------- | :------------------------- | :-------------------------------------------- |
| `employee_id` / `external_id` | `users.external_id`        | **Unique Identifier**. Used for SSO matching. |
| `full_name` / `name`          | `users.name`               |                                               |
| `email`                       | `users.email`              |                                               |
| `department`                  | `user_profiles.department` |                                               |
| `job_title`                   | `user_profiles.job_title`  |                                               |
| `is_employee`                 | Role: `student`            | If `true`, user gets `student` role.          |
| `is_hr`                       | Role: `instructor`         | If `true`, user gets `instructor` role.       |
| `status`                      | `users.lms_status`         | Mapped to `active` or `inactive`.             |

---

## 2. Single Sign-On (SSO)

Users can log in to the LMS by clicking a special link in the Internal System.

### SSO Endpoint

-   **URL**: `https://lms-domain.com/sso/login?token={JWT}`
-   **Method**: `GET` or `POST`

### JWT Requirements

The `token` parameter must be a signed JWT (JSON Web Token) with the following claims:

| Claim | Description | Required | Validation                                             |
| :---- | :---------- | :------- | :----------------------------------------------------- |
| `iss` | Issuer      | Yes      | Must match LMS config (`SSO_ISS`).                     |
| `aud` | Audience    | Yes      | Must match LMS config (`SSO_AUD`).                     |
| `sub` | Subject     | Yes      | **Must match `users.external_id`** in LMS.             |
| `jti` | JWT ID      | Yes      | Unique ID to prevent replay attacks.                   |
| `iat` | Issued At   | Yes      | Timestamp. Token must not be too old (max age config). |
| `exp` | Expiration  | Yes      | Token must not be expired.                             |

### Example JWT Payload

```json
{
    "iss": "internal-system-app",
    "aud": "dml-lms",
    "sub": "EMP001",
    "jti": "unique-uuid-123456",
    "iat": 1700000000,
    "exp": 1700000060
}
```

### Security

-   **Algorithm**: RS256 (Recommended) or HS256.
-   **Replay Protection**: The LMS tracks `jti` claims. A token can only be used once.
-   **User Existence**: The user (identified by `sub`) must already exist in the LMS (via Import). If not found, login fails with `403 User not provisioned`.

---

## 3. Configuration

Ensure the `.env` file in the LMS is configured to match the Internal System settings.

```env
# Internal System API for Import
INTERNAL_USERS_BASE_URL=https://internal-system.com/api
INTERNAL_USERS_TOKEN=your-api-token

# SSO Configuration
SSO_ALGO=RS256
SSO_ISS=internal-system-app
SSO_AUD=dml-lms
SSO_PUBLIC_KEY="-----BEGIN PUBLIC KEY...-----"
# OR if using HS256
# SSO_SECRET=your-secret-key
```
