APP_NAME=gates

install:
	composer install
	php artisan migrate
  php artisan db:seed
  php artisan hotwallet:sync --gate=eth_sepolia
test:
	php artisan test

lint:
	./vendor/bin/pint

serve:
	php artisan serve

build:
	docker build -t $(APP_NAME) .

run:
	docker run \
		--rm \
		-p 8080:8080 \
		--env-file .env \
		$(APP_NAME)

shell:
	docker run \
		-it \
		--rm \
		--env-file .env \
		$(APP_NAME) sh

index:
	php artisan blockchain:index --base_gate=eth_sepolia

fresh:
	php artisan migrate:fresh --seed
