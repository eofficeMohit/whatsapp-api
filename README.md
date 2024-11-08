## Run Locally

# Clone the project

```bash
  git clone https://github.com/eofficeMohit/whatsapp-api.git
```

# Go to the project directory

```bash
  cd whatsapp-api
```

# Composer install

```bash
  composer install
```

# Duplicate the .env.example file and rename it to .env

```bash
  cp .env.example .env
```

# Then configure the environment variables in .env for database and Redis:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

NOTE: setup pusher and websocket in broadcasting,php in configs and change the BROADCAST_DRIVER in .env file.

# Install Frontend Dependencies (optional)

```bash
npm install
npm run dev
```

# Migrate the tables

```bash
  php artisan migrate
```

# Generate the Key

```bash
  php artisan key:generate
```

# Start the server

```bash
  php artisan serve
```

# Start the websocket

```bash
  php artisan websockets:serve
```

## Contributing

Contributions are always welcome!

## License

[MIT](https://choosealicense.com/licenses/mit/)

<br/>
<br/>

<p align="center">If you liked the repository, show your  ❤️  by starring and forking it.</p>
