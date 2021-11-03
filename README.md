# Coordinate Backend Repo: ape-backend
```
┏━━━┓━━━━━━━━━━━━━┏┓━━━━━━━━━━━━━━━━━━━━━━━┏━━┓━━━━━━━━━━┏┓━━━━━━━━━━━━┏┓
┃┏━┓┃━━━━━━━━━━━━━┃┃━━━━━━━━━━━━━━━━━━━━━━━┃┏┓┃━━━━━━━━━━┃┃━━━━━━━━━━━━┃┃
┃┃━┗┛┏━━┓┏━━┓┏━┓┏━┛┃┏┓┏━┓━┏━━┓━┏━━┓┏━━┓━━━━┃┗┛┗┓┏━━┓━┏━━┓┃┃┏┓┏━━┓┏━┓━┏━┛┃
┃┃━┏┓┃┏┓┃┃┏┓┃┃┏┛┃┏┓┃┣┫┃┏┓┓┗━┓┃━┃┏┓┃┃┏┓┃━━━━┃┏━┓┃┗━┓┃━┃┏━┛┃┗┛┛┃┏┓┃┃┏┓┓┃┏┓┃
┃┗━┛┃┃┗┛┃┃┗┛┃┃┃━┃┗┛┃┃┃┃┃┃┃┃┗┛┗┓┃┗┛┃┃┃━┫━━━━┃┗━┛┃┃┗┛┗┓┃┗━┓┃┏┓┓┃┃━┫┃┃┃┃┃┗┛┃
┗━━━┛┗━━┛┗━━┛┗┛━┗━━┛┗┛┗┛┗┛┗━━━┛┃┏━┛┗━━┛━━━━┗━━━┛┗━━━┛┗━━┛┗┛┗┛┗━━┛┗┛┗┛┗━━┛
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┃┃━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┗┛━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

                                                    .--.
                                                   .-    \
                                                  /_      \
                                                 (o        )
                                               _/          |
                                              (c       .-. |
             ___                  ;;          /      .'   \
          .''   ``.               ;;         O)     |      \
        _/ .-. .-. \_     () ()  / _          `.__  \       \
       (o|( O   O )|o)   ()(O)() |/ )           /    \       \
        .'         `.      ()\  _|_            /      \       \
       /    (c c)    \        \(_  \          /        \       \
       |             |        (__)  `.______ ( ._/      \       )
       \     (o)     /        (___)`._      .'           )     /
        `.         .'         (__)  ______ /            /     /
          `-.___.-'            /|\         |           /     /
          ___)(___            /  \         \          /     /
       .-'        `-.                       `.      .'     /
      / .-.      .-. \                        `-  /.'     /
     / /  ( .  . )  \ \                         / \)| | | |
    / /    \    /    \ \                       /     \_\_\_)
    \ \     )  (     / /                     (    /
     \ \   ( __ )   / /                        \   \ \  \
    /   )  //  \\  (   \                        \   \ \  \
(\ / / /\) \\  // (/\ \ \ /)                     )   \ \  \
 -'-'-'  .'  )(  `.  `-`-`-                     .'   |.'   |
       .'_ .'  `. _`.                      _.--'     (     (
MJP  oOO(_)      (_)OOo                   (__.--._____)_____)
```
[img src](https://www.asciiart.eu/animals/monkeys) [font src](https://textpaint.net/)

## Summary

This repository contains the ape-backend codebase which serves the Coordinape API.

This is a project written in PHP using the [Laravel](https://laravel.com/) PHP framework. The database is mysql. Docker is used to setup the environment.


## Getting Started
Welcome! We're so glad you're reading this and want to contribute! This section will get your dev environment setup. 

This app uses Docker.

 1.  Install Docker: https://docs.docker.com/engine/install/

 Once docker is installed and and the docker daemon is running you can check your docker version:

 ```bash
 > docker --version
Docker version 20.10.7, build f0df350
 ```

 2. Setup your `.env` file by copying the example environment file:

 ```
 cp .env.example .env
 ```


 3. Use docker compose to launch the three containers required for the app to run:
 ```bash
cd coordinape-backend
docker compose up
```

This can take a while to build the Dockerfile and install all the app dependencies into `vendor/`.

4. Now you need to populate your local database with some data.

* **Option 0:** create data through console command.
```bash
docker exec app php artisan ape:quickstart youraddresshere
```
* **Option 1:** create data in the database directly.
    1. Create a protocol
    2. Create a circle - link to protocol_id
    3. Create a user - link circle_id to circle, address=your_address,role = 1 (for admin access)
    4. Create profile - just need an address

* **Option 2:** restore database from a staging db dump (get from another dev)
```bash
docker exec -i mysql mysql -uroot -psecret laravel < database/db_dumps/staging_db_dump.sql
```

## Connect to database.

If you want to explore the MySQL database you can download a UI for MySQL such as [Sequel Ace](https://sequel-ace.com/get-started/).

You can connect to the database with Sequel Ace using this default connection details:

```
Name: coordinape mysql
Host: 127.0.0.1
Username: root
Password: secret
Database: laravel
Port: 3306
```

## Potential Errors (and Solutions):
Ok, so you followed this guide but you're getting an error. Oops. If you see your error below, try the solution posted below. If you do not see your error below, and after some research you find a solution, consider adding it to this section. It will help the next person.


* ### Fatal error: Uncaught Error: Failed opening required

If you're getting an error from the API like:
```
Warning: require(/var/www/public/../vendor/autoload.php): Failed to open stream: No such file or directory in /var/www/public/index.php on line 34

Fatal error: Uncaught Error: Failed opening required '/var/www/public/../vendor/autoload.php' (include_path='.:/usr/local/lib/php') in /var/www/public/index.php:34 Stack trace: #0 {main} thrown in /var/www/public/index.php on line 34
```

This is probably because you don' thave all the packages installed properly in laravel. Fix with running `composer update`
```bash
 > docker exec -it app composer update
 ```

* ### SQLSTATE[42S02]: Base table or view not found
This error likely is a result of the MySQL database not being properly setup and migrated

```
    "message": "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel.protocols' doesn't exist (SQL: select * from `protocols`)",
```

Try doing a full fresh migrate and seed with:

> docker exec -it app php artisan migrate:fresh --seed


## Laravel Error Logs

Laravel logs errors to `/var/www/storage/logs/laravel.log` on the docker host.
View detailed error logs by running this docker command:

> docker exec -it app tail -f /var/www/storage/logs/laravel.log