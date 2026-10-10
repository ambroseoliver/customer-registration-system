# Customer registration database

The application uses MySQL through PDO. Create the database, apply the schema, and configure the PHP process with these environment variables:

- `DB_HOST` (optional; defaults to `127.0.0.1`)
- `DB_PORT` (optional; defaults to `3306`)
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD` (set this even when the database user has an empty password)

Create the database and apply the table schema:

```sh
mysql -u YOUR_DATABASE_USER -p -e "CREATE DATABASE customer_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u YOUR_DATABASE_USER -p customer_registration < database/schema.sql
```

Set the variables in the PHP-FPM/Apache environment or your local development environment. Do not put real database credentials in source control. The database user needs `INSERT` permission on the `customers` table. Registrations are stored in that table, and email addresses are unique.
