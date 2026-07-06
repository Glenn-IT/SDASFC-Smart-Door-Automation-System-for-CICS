# Database Schema (MySQL)

## `admins`
Single-role admin accounts (only role in the system).

| Column        | Type              | Notes                        |
|---------------|-------------------|-------------------------------|
| id            | INT PK AI         |                               |
| username      | VARCHAR(50) UNIQUE|                               |
| password_hash | VARCHAR(255)      | `password_hash()` / bcrypt   |
| full_name     | VARCHAR(100)      |                               |
| created_at    | DATETIME          | default CURRENT_TIMESTAMP    |

## `users`
The RFID cardholders being tracked (not panel logins).

| Column      | Type              | Notes                                  |
|-------------|-------------------|------------------------------------------|
| id          | INT PK AI         |                                          |
| full_name   | VARCHAR(100)      |                                          |
| id_number   | VARCHAR(50)       | student/employee ID, unique             |
| rfid_uid    | VARCHAR(50) UNIQUE| card's RFID tag UID                     |
| role        | ENUM('student','faculty','staff') | for filtering/reporting |
| status      | ENUM('active','inactive') | inactive users always denied    |
| created_at  | DATETIME          |                                          |

## `schedules`
Multiple allowed windows per user (many-to-one).

| Column      | Type                          | Notes                                    |
|-------------|-------------------------------|-------------------------------------------|
| id          | INT PK AI                     |                                            |
| user_id     | INT FK -> users.id             | ON DELETE CASCADE                         |
| day_of_week | ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') |                      |
| time_start  | TIME                          | e.g., 07:00:00                            |
| time_end    | TIME                          | e.g., 09:00:00                            |
| is_active   | TINYINT(1)                    | allows disabling a window without deleting|

A user can have several rows here (e.g., Mon–Fri 07:00–09:00 = 5 rows, or one row
per day so exceptions are easy to add/remove individually).

## `access_logs`
Every RFID tap, regardless of outcome.

| Column       | Type                                  | Notes                                  |
|--------------|----------------------------------------|------------------------------------------|
| id           | INT PK AI                              |                                          |
| user_id      | INT FK -> users.id, NULLABLE            | NULL if UID not registered              |
| rfid_uid     | VARCHAR(50)                            | raw UID scanned, even if unknown        |
| scanned_at   | DATETIME                               | default CURRENT_TIMESTAMP               |
| result       | ENUM('granted','denied')               |                                          |
| reason       | VARCHAR(100)                           | 'unknown_uid','inactive_user','outside_schedule','ok' |

## Relationships
- `users` 1—N `schedules`
- `users` 1—N `access_logs`

## `database/schema.sql` will contain the literal `CREATE TABLE` statements plus
indexes on `users.rfid_uid`, `access_logs.scanned_at`, and `schedules.user_id`
for fast dashboard/report queries.
