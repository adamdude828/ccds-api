#!/bin/bash

# Simple script to create the Laravel database in SQL Server

echo "Creating the Laravel database in SQL Server container..."

# Check if containers are running
if [ "$(docker-compose ps -q db)" == "" ]; then
    echo "Starting SQL Server container..."
    docker-compose up -d db
    
    # Give SQL Server time to initialize
    echo "Waiting for SQL Server to start up (10 seconds)..."
    sleep 10
fi

# Create the database
echo "Creating 'laravel' database if it doesn't exist..."
docker-compose exec db /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "abcDEF123#" -C -Q "IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'laravel') BEGIN CREATE DATABASE [laravel]; PRINT 'Database laravel created successfully.' END ELSE BEGIN PRINT 'Database laravel already exists.' END"

echo "Database creation complete!"
echo "You can now run migrations with: docker-compose exec app php artisan migrate" 