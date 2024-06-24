# Dockerized PHP Development Environment with MySQL and phpMyAdmin

This repository contains configurations to set up a Docker-based PHP development environment with MySQL database and phpMyAdmin.

## Prerequisites

Make sure you have Docker installed on your machine:
- [Docker](https://www.docker.com/get-started)

## Getting Started

1. **Clone the Repository**
   ```bash
   git clone https://github.com/AkshataKulkarni03/main.git
   
   cd phpdockercomposer

2. **Start Docker Containers**
   ```bash
     docker-compose up -d
3. **Access phpMyAdmin**
    
      Open your web browser and go to
     ```bash
     http://localhost:8585
    Username: user
    Password: userpassword
4. **PostMan Collection to hit the API**
   ```bash
   phpdockercomposer/Collection/Invoices.postman_collection.json

5. **Stop and Remove Containers**
   ```bash
     docker-compose down
   


