# SQL Server Database Container

This directory contains the SQL Server configuration for the Laravel application.

## Setup

The SQL Server container is configured with the following credentials:
- **Username**: sa
- **Password**: abcDEF123#
- **Database**: laravel (needs to be created)

## Creating the Database

The SQL Server container doesn't automatically create the database on startup. Use the provided script to create it:

```bash
# Run from the project root directory:
./docker/database/create-db.sh
```

This script will:
1. Start the SQL Server container if it's not already running
2. Create the 'laravel' database if it doesn't exist

## Using with Laravel

After the database is created, run Laravel migrations to set up the schema:

```bash
# Run Laravel migrations
docker-compose exec app php artisan migrate

# Optionally seed the database
docker-compose exec app php artisan db:seed
```

## Connecting to the Database

You can connect to the database using SQL Server Management Studio or any other SQL client with:
- **Server**: localhost,1433
- **Authentication**: SQL Server Authentication
- **Username**: sa
- **Password**: abcDEF123#
- **Database**: laravel 