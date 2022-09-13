# URL Shortener
## Deployment

Download project and go to project dir `cd path/to/codereview-url-shortener`

Run commands:

`cp .env.local .env && docker-compose up -d && docker-compose exec php composer install && docker-compose exec web php artisan migrate`

## Usage

**Host**: http://localhost:8083

`./Insomnia.yaml` - request collection for insomnia
