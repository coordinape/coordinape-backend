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

--- 

## Start in [Coordinape](https://github.com/coordinape/coordinape)

We are migrating from Laravel to Hasura GraphQL.

See https://github.com/coordinape/coordinape
Where Laravel is included as a submodule and the docker-compose runs this as well as the other services.

# Legacy setup 

 1. Install Docker: https://docs.docker.com/engine/install/

 2. To Initialze the db standalone from hasura, uncomment
`php artisan migrate` in `./services/start.sh`.

 3. `cp .env.example .env`

 4. `docker-compose up -d`

#### Populate your local db

* **Option 0:** create data through console command.
```bash
docker exec app php artisan ape:quickstart youraddresshere
```

* **Option 1:** create data in the database directly.
    1. Create a protocol
    2. Create a circle - link to protocol_id
    3. Create a user - link circle_id to circle, address=your_address,role = 1 (for admin access)
    4. Create profile - just need an address

# Debug

Configuration from: `docker-compose.yml` and `.env`

```bash
docker ps
docker logs nginx
docker logs app
docker logs laravel_postgres_1
# Or connect with a shell
docker exec -it app bash
# Now you have access to:
php artisan
```