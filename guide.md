## Install Website Guide


### 1. APM Install
#### Before to setup, you must install Apache, PHP7 and MySQL5 on server.
You can use xampp or other applications.
https://www.apachefriends.org/download.html

#### Once you install xampp, it will have http root folder at .../xampp/htdocs.
Remove htdocs folder.

### 2. Project setup
#### Download project sources from Git, https://github.com/arangde/lumen-jwt-api
```
$ cd xampp - [xampp installed path]
$ git clone https://github.com/arangde/lumen-jwt-api.git htdocs
```
It will download sources in htdocs folder.

#### Copy .env.example to .env at same path.

#### Change website config setting in .env
```
APP_TIMEZONE: server timezone
APP_HOST: website hosting url

DB_USERNAME: mysql user
DB_PASSWORD: mysql password

JWT_SECRET: set random string
```

OK, we are ready to install project now.

### 3. Install Project requirements
#### Composer download
https://getcomposer.org/download/

#### Composer install
```
$ composer install
```

#### DB migration
```
$ php artisan migrate
```

#### Create super administrator
```
$ php artisan db:seed --class=UsersTableSeeder
```
It will create administrator `admin@test.com`, password: `12345`.

Done, you can check website in browser http://127.0.0.1/ or APP_HOST url

***Remember***, once you log in to admin http://127.0.0.1/admin/, you must change `Super Manager` password in users page.

### 4. Setup Task scheduler
Because, this project will use task system in background, you should set in Windows Task Scheduler.

#### Open scheduler.bat in project source and update content in it.
Replace text "C:\xampp" with xampp path on your server.

#### Create a task via Windows Task Scheduler.
This blog will help how to create Windows Task.
You only need to change Program/script value with scheduler.bat path in Step 7, such as C:\xampp\htdocs\scheduler.bat


All done, Happy!