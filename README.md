# News Aggregator API

## Overview

This is a RESTful API for a news aggregator service built with Laravel. The API fetches articles from various sources, stores them locally, and allows users to access personalized news feeds based on their preferences. It supports user authentication, article management, search functionality, and integrates data from multiple news APIs. The project also includes Docker setup for easy deployment and testing.

## Features

1. **User Authentication:**
   - User registration and login with API token authentication using Laravel Sanctum.
   - Endpoints for user logout and password reset.

2. **Article Management:**
   - Pagination support for fetching articles.
   - Search functionality by keyword, date, category, and source.
   - Endpoint for retrieving detailed information about a single article.

3. **User Preferences:**
   - Endpoints to set and retrieve user preferences for news sources, categories, and authors.
   - Personalized news feed based on user preferences.

4. **Data Aggregation:**
   - Scheduled tasks to regularly fetch articles from multiple news sources (e.g., NewsAPI, BBC News, The Guardian).
   - Data is stored locally for optimized search and retrieval.
   
5. **API Documentation:**
   - Comprehensive API documentation using Swagger/OpenAPI.

## SETUP INSTRUCTIONS

## Prerequisites

- PHP =  8.3.10
- Composer
- MySQL or SQLite database
- Docker (for containerization)

## Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd <project-directory>

2. **copy the .env file and paste it by renaming it as .env and add the variables as per your machine requirment the name and password**

3. **Do the same thing docker-compose.yml file. Rename the mysql user name and password as per your machine**

4. **Create docker containers with below command**
   ```bash
   docker composer up

5. **To run commands directly inside the PHP container, you can open an interactive shell. This is useful for running Artisan commands, installing dependencies, or troubleshooting**
   ```bash
   docker-compose exec news-aggregator bash

6. **Ensure all PHP dependencies for Laravel are installed. Inside the container shell**
   ```bash
   composer install

7. **Set Up the Database - Ensure your database configuration in the .env file matches your Docker MySQL Replace with your MySQL container service name in docker-compose.yml**
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

8. **Run the migration script for DB table creation**
   ```bash
   php artisan migrate

9. **Start the Laravel Development Server**
   ```bash
   php artisan serve

10. **Access the Application with the APIs as per curl requests given in Swagger Documentation. By default the service will run in port 8000**
   ```bash
   http://localhost:8000 
   ```


11. **Check logs and status of each containers with below commands**
   ```bash
   docker-compose logs news-aggregator-app          # For PHP/Laravel logs
   docker-compose logs news-aggregator-mysql        # For MySQL logs
   docker-compose logs news-aggregator-redis        # For Redis logs
   ```

## API DOCUMENTATION - SWAGGER 

Swagger Link - [ Swagger Documentation ](https://app.swaggerhub.com/apis-docs/2000SSSHARMA/News-aggregator/1#/)


## VIDEO LINK OF BACKEND API EXPLINATION 
