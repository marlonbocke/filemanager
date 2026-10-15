# Filemanager

A PHP-based web file manager and learning/portfolio project, currently under
development.

README written with the assistance of GitHub Copilot.

## Features

- MySQL-backed login
- Browse, upload, download, preview, rename, move, and delete files
- Create and manage folders
- Experimental two-factor authentication

Features are still being developed and have not all been tested.

## Requirements

- PHP 8.3+
- A PHP-enabled web server
- MySQL or MariaDB, with the `mysqli` extension
- Composer for the experimental 2FA component

PHP 8.3 is required for `json_validate()` used in `index.php`.

## Setup

Installation is not yet documented. The app expects a configured MySQL
database with a `users` table; a schema is not included.

Keep database credentials out of the repository and use development-only
credentials.

## Security

This unfinished prototype is **not suitable for production or public
deployment**. Authentication, file access, and configuration need further
security review. Remove any sample credentials and rotate exposed credentials.

Automated tests and a complete setup guide are not currently available.
