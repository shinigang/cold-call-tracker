## About Call Tracker

Call Tracker is a Mini CRM app for tracking cold calls to customers.

## Installation

1. Run Docker Desktop
2. Clone this git repo to your Project folder (Using WSL2 file system for Windows Docker)
3. Setup config file by copy-pasting .env.example to .env and update the socialite and pusher credentials:

    ```
    cd cold-call-tracker
    cp .env.example .env
    ```

4. Run docker file:

    ```
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php82-composer:latest \
        composer install --ignore-platform-reqs
    ```

5. Run migrations:

    ```
    alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
    sail artisan migrate
    sail artisan db:seed
    sail artisan db:seed --class=WorldSeeder
    ```

6. Run Factory and update meilisearch indexes (optional)

    ```
    sail artisan tinker
    Company::factory()->count(1000)->create();
    exit
    sail artisan scout:import "App\Models\Company"
    ```

7. Run google calendar quickstart script to generate oauth-token.json:
    ```
    sail artisan google-calendar:quickstart
    ```

## Development

1. Make sure Docker Desktop is running
2. Open a terminal and run:
    ```
    ./vendor/bin/sail up
    ```
3. Open another terminal and run the following:
    ```
    alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
    sail npm install
    sail npm run dev
    ```
4. Open localhost in your browser. Default user and password is in .env file
