<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

# Laravel TMDB
App that retrieves movies for current day and their genres from TMDB

## Requirements
 - PHP >= 5.6.4
 - OpenSSL PHP Extension
 - PDO PHP Extension
 - Mbstring PHP Extension
 - Tokenizer PHP Extension
 - XML PHP Extension

## Install
0. Copy `.env.example` to `.env`
0. Edit `.env` file with environment variables
0. Run `make init-dev` / `make init-prod` (development / production)

## Commands
0. `php artisan api:get:genres` - Retrieves movies for current date
0. `php artisan api:get:movies` - Retrieves all genres
0. `make` 
0. `make install` - Installs dependencies
0. `make production` - Production build
0. `make dev` - Dev build
0. `make dev-run` - Start dev server (port `8000`)
0. `make init-dev` - Initial dev setup
0. `make init-prod` - Initial production setup