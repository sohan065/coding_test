## Project Setup Guide

First, Clone the repository.

```bash
git clone https://github.com/sohan065/coding_test.git
```

Go to the project directory.

```bash
cd coding_test
```

Install the composer & npm dependencies.

```bash
composer install
```

#### Env Configuration.

Copy the `.env.example` file to `.env` and update the database credentials.

Generate artisan key.

```bash
php artisan key:generate
```

#### Database Migration & Seeding.

Configure your database in `.env` file.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coding_test
DB_USERNAME=root
DB_PASSWORD=your_db_password
```

Then run the database migration command to create the tables.

```bash
php artisan migrate
```

Run the server.

```bash
php artisan serve
```

It will serve the app on `http://127.0.0.1:8000` by default.

I have created two API endpoints for the task and two addition API route to get the tasks which is given below.

#### API Endpoints

##### 1. Create

```bash
POST /api/users
form-data:
       name: required,
       account_type:Individual or Business,
       balance: numeric,
       email: email,
       password: any
```

##### log in

```bash
POST /api/login
form-data:
       email: email,
       password: password,

```

##### 2. show all transaction and balance

```bash
get /api/show
```

##### show all deposit transaction

```bash
GET /api/deposit
```

## balance deposit

```bash

POST api/deposit
 form-data:
     id: user_id,
     amount: numeric

```

## get all withdrawal transaction

`GET api/withdrawal`

## withdraw balance

```bash
POST api/withdrawal
 form-data:
    id: user_id,
    amount: numeric

```
