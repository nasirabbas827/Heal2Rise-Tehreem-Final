# Heal2Rise - Mental Health Support

Heal2Rise-Tehreem-Final is a support platform for case management, counselling, and NGO coordination.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admin`, `cases`, `counseling_sessions`, `counselors`, `ngos`, `skill_development`, `team_members`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/admin_add_team.php`](admin/admin_add_team.php), [`admin/admin_case_list.php`](admin/admin_case_list.php), [`admin/admin_dashboard.php`](admin/admin_dashboard.php), [`admin/admin_edit_team.php`](admin/admin_edit_team.php), [`admin/admin_team_list.php`](admin/admin_team_list.php) |
| Root Pages | [`adminlogin.php`](adminlogin.php), [`case_details.php`](case_details.php), [`dashboard.php`](dashboard.php), [`edit_case.php`](edit_case.php), [`login.php`](login.php) |
| Counselor | [`counselor/case_view.php`](counselor/case_view.php), [`counselor/counselor_dashboard.php`](counselor/counselor_dashboard.php), [`counselor/sessions_manage.php`](counselor/sessions_manage.php), [`counselor/skills_assignment.php`](counselor/skills_assignment.php) |
| Ngo | [`ngo/case_details.php`](ngo/case_details.php), [`ngo/ngo_dashboard.php`](ngo/ngo_dashboard.php), [`ngo/update_profile.php`](ngo/update_profile.php) |
| Team | [`team/case_view.php`](team/case_view.php), [`team/manage_counselors.php`](team/manage_counselors.php), [`team/team_dashboard.php`](team/team_dashboard.php), [`team/update_profile.php`](team/update_profile.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/Heal2Rise-Tehreem-Final.git
cd Heal2Rise-Tehreem-Final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/h2rtdatabase.sql`](Database/h2rtdatabase.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`counselor/config.php`](counselor/config.php), [`ngo/config.php`](ngo/config.php), [`team/config.php`](team/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`CSS`](CSS) | Stylesheets |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
