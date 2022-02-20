
# Petshop

Pet shop base don laravel + MySQL.


## Run Locally

Clone the project

```bash
  git clone https://github.com/joblesspoet/petshop
```

Go to the project directory

```bash
  cd petshop
```
copy the .env.exmaple file and rename it to .env file.


Install dependencies

```bash
  composer install
```
After dependencies install use the following command for migration.

```bash
php artisan migrate
```
After migration it's time run the seeder then use the following command

```bash
php artisan db:seed
```
After database seeder, you need to create a storage link:

```bash
php artisan storage:link
````
On everything successful, time to run the application.

Start the server

```bash
  php artisan serve
```
