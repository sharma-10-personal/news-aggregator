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
