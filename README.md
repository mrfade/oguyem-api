# oguyem-api

oguyem-api is a RESTful API for managing [oguyem](https://github.com/mrfade/oguyem). It is built on [Lumen](https://lumen.laravel.com/docs/8.x) and uses [Laravel's Eloquent ORM](https://laravel.com/docs/8.x/eloquent).

## Installation

Clone the project via git clone or download the zip file.

### .env

Copy contents of .env.example file to .env file. Create a database and connect your database in .env file.

### Composer Install

Run the following command to install composer packages.

`composer install`

### Generate Key

Then run the following command to generate fresh key.

`php artisan key:generate`

### Run Migration

Then run the following command to create migrations in the database.

`php artisan migrate`

### API EndPoints

#### Devices

* Devices POST `/devices/register`

#### Cron

* Cron GET `/cron/fetch-menus`

#### Menus

* Menus GET All `/menus`
* Menus GET Single `/menus/{date}`
* Menu Comments GET All `/menus/{date}/comments`
* Menu Comments GET Single `/menus/{date}/comments/{id}`
* Menu Comment POST `/menus/{date}/comments`
* Menu Comment DELETE `/menus/{date}/comments/{id}`
* Menu Comment Vote POST `/menus/{date}/comments/{id}/vote`
