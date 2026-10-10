# Luma Customer Registration

A lightweight customer-registration application built with PHP, MySQL, and PDO. Visitors can submit their name, email address, phone number, and a short message through a responsive web form. Valid submissions are stored in MySQL; invalid submissions return to the form with helpful field-level feedback.

## Features

- Responsive registration form with client-side feedback and a live message character count.
- Server-side validation; browser validation is an enhancement, not a security boundary.
- CSRF token verification and secure session-cookie options (`HttpOnly` and `SameSite=Lax`; cookies are marked `Secure` for HTTPS requests).
- Prepared PDO statements and UTF-8 database connections.
- Unique email addresses, with a clear duplicate-registration message.
- Post/Redirect/Get flow to prevent accidental form resubmission on refresh.
- Form values retained after validation or database errors and safely escaped before they are rendered.
- Database details supplied through environment variables rather than committed credentials.

## Requirements

- PHP **8.1 or newer** with the `PDO` and `pdo_mysql` extensions enabled.
- MySQL **8.0 or newer** (or a compatible MySQL server).
- A web server or PHP's built-in development server.

Check your PHP version and required extensions with:

```sh
php -v
php -m | grep -E '^(PDO|pdo_mysql)$'
```

## Quick start

### 1. Create the database and table

From the project root, create the database and apply the schema:

```sh
mysql -u root -p -e \
  "CREATE DATABASE customer_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

mysql -u root -p customer_registration < database/schema.sql
```

If your MySQL administrator account is not named `root`, replace it with the appropriate account. The schema creates the `customers` table and a unique index on email addresses.

### 2. Configure database access

The PHP process reads these environment variables:

| Variable | Required | Default | Description |
| --- | --- | --- | --- |
| `DB_HOST` | No | `127.0.0.1` | MySQL server host |
| `DB_PORT` | No | `3306` | MySQL server TCP port |
| `DB_NAME` | Yes | — | Database name; letters, digits, and underscores only |
| `DB_USER` | Yes | — | MySQL application username |
| `DB_PASSWORD` | Yes | — | Non-empty MySQL application password |

For local development, export the settings in the same shell used to start PHP:

```sh
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=customer_registration
export DB_USER=your_application_user
export DB_PASSWORD='your-local-database-password'
```

Use an application-specific MySQL user in preference to the database administrator account. The application only inserts into `customers`; grant that account the minimum privileges necessary for its deployment. Do not commit credentials or place production secrets in this README.

### 3. Start the development server

From the project root:

```sh
php -S 127.0.0.1:8000
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser. The form posts to `submit.php`, which redirects back to the registration page with a success message or relevant errors.

The PHP built-in server is for local development only. Use a production web server and PHP runtime for deployment.

## Form validation

The server validates every submission regardless of browser-side checks:

| Field | Rules |
| --- | --- |
| Full name | 2–100 Unicode characters; control/format characters are rejected |
| Email address | Valid email format, maximum 254 bytes; normalized to lowercase and unique in the database |
| Phone number | 7–30 characters; 7–15 digits; digits, spaces, parentheses, periods, hyphens, and an optional leading `+` |
| Message | 10–500 Unicode characters |

Inputs are trimmed before validation and storage. Invalid values are returned to the form with field-specific messages; rendered values and messages are HTML-escaped.

## Database schema

The schema is maintained in [`database/schema.sql`](database/schema.sql):

| Column | Type | Description |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | Auto-incrementing primary key |
| `full_name` | `VARCHAR(100)` | Customer's full name |
| `email` | `VARCHAR(254)` | Unique email address |
| `phone` | `VARCHAR(30)` | Submitted phone number |
| `message` | `VARCHAR(500)` | Customer's message |
| `created_at` | `TIMESTAMP` | Automatically assigned registration time |

For additional database setup notes, see [`database/README.md`](database/README.md).

## Project structure

```text
.
├── assets/
│   ├── css/style.css       # Responsive layout and visual styling
│   └── js/script.js        # Client-side validation and character count
├── config/
│   └── database.php        # PDO connection using environment configuration
├── database/
│   ├── README.md           # Database setup notes
│   └── schema.sql          # Customer table definition
├── includes/
│   └── bootstrap.php       # Session, CSRF, escaping, and redirect helpers
├── index.php               # Registration form
└── submit.php              # POST handling, validation, and persistence
```

## Troubleshooting

- **“Registration is temporarily unavailable.”** Check that all required `DB_*` variables are set in the PHP process environment, then confirm that the MySQL server is reachable and the configured user can connect.
- **Database table errors.** Ensure `database/schema.sql` has been applied to the database named by `DB_NAME`.
- **“An account with this email address is already registered.”** Each email address may only be registered once.
- **CSRF/session error.** Reload the form and submit again. Confirm that browser cookies are enabled and that the PHP runtime can store session data.
- **PDO driver error.** Install or enable the PHP `pdo_mysql` extension and restart the web server/PHP process.

Database connection and unexpected server errors are written to the PHP error log; internal details are not displayed to visitors.

## Production considerations

Before deploying publicly:

- Serve the site over HTTPS and configure database credentials as secrets in the hosting environment.
- Use a dedicated database account with only the required permissions.
- Configure PHP error logging and monitor failures without exposing logs publicly.
- Add operational protections appropriate to the deployment, such as request rate limiting, backups, and a data-retention policy.

This project currently handles registration and stores the submitted details. It does not implement login, email verification, password management, a customer dashboard, or consent-management workflows.
