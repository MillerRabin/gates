APP_NAME=gates

install:
	composer install
	php artisan migrate
  php artisan db:seed
  php artisan hotwallet:sync

test:
	php artisan test

lint:
	./vendor/bin/pint

serve:
	php artisan serve --port=8080

build:
	docker build -t $(APP_NAME) .

run:
	docker run \
		--rm \
		-p 8080:8080 \
		--env-file .env \
    -e WALLET_URL=http://host.docker.internal:8000 \
    -e APP_ENV=production \
    -e APP_DEBUG=false \
    --add-host=host.docker.internal:host-gateway \
		$(APP_NAME)

index:
	php artisan blockchain:index --base_gate=eth_sepolia

fresh:
	php artisan migrate:fresh --seed
