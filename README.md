## Task Management App DEMO

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://taskapp.digitechproject.com/public/images/mainDashboard.png" width="auto" alt="Laravel Logo"></a></p>

## Requirements

| Tools   | Description |
|---------|-------------|
| PHP     | 8.0 >= 8.2  |
| Laravel | 10.*        |

  <br>

## See Demo App here

#### <a href="https://taskapp.digitechproject.com" target="_blank"> >>> Task Management App Demo <<< </a>

## Installation

Clone the repo locally:

```sh
git clone https://github.com/MoonRivers21/taskAppFilament.git
```

<br>
Navigate to the Project Directory:

```sh
cd taskAppFilament
```

Install PHP dependencies:

```sh
composer install
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

<br>
Note: Please update your database(MySQL) configuration accordingly.

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Create a symlink to the storage:

```sh
php artisan storage:link
```

Run the dev server (the output will give the address):

```sh
php artisan serve
```

<br><br>
You're ready to go! Visit the url in your browser, and signup:

## Features to explore

- #### Dashboard (Basic analytics)
    - Todo, In-progress, Done, Published, Draft, Trash
    - Tables that list only published tasks
    - Quick actions

<br>

- #### Manage Task (CRUD)
    - Many Quick Actions
    - CRUD Functions
    - Task records belongs to Authorize Owner
    - All action button are relying on LaravelPolicies

<br>

- #### Soft Delete Enabled, you can restore back the record if needed

<br>

- #### REST API

    - Just Generate/Create your Auth Token inside the web app and use the generated token as "Bearer Access Token" to
      access the API:

        <br>
    - You may also browse the API Documentation inside the web app just go to the url " http://localhost:8000/docs" and
      checkout the ENDPOINTS menu take note:ALL API Endpoints are protected by sanctum, so you may experience a failure
      test inside the API documentation, you can test the API via Postman or any other 3rd party tools API Tester and
      use the generated TOKEN from WebApp to test all the endpoints.

        <br>
      The routes will be :

                  [GET] '/api/tasks' - Return Records
                  [GET] '/api/tasks/{id}' - Return single record task
                  [PUT] '/api/tasks/{id}' - Update task
                  [POST] '/api/tasks' - Create task
                  [DELETE] '/api/tasks/{id}' - Delete task

### Important Reminder:

- To improve the performance of Web App try to run this command:

    ```sh
      php artisan icons:cache
    ```

  ```sh
    php artisan optimize:clear 
    ```

  ```sh
    php artisan optimize
    ```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
