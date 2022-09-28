#!make
path=`pwd`

ifeq ($(OS),Windows_NT)
	APP=winpty docker exec -it sfvue
else
	APP=docker exec -it sfvue
	DB=docker exec -it mysql
endif

bsh:
	$(APP) bash

npm:
	$(APP) npm run watch

serv:
	symfony serve --no-tls -d

inst:
	$(APP) composer install -n
	$(APP) npm install
	make mim

enti:
	$(APP) php bin/console make:entity


diff:
	$(APP) php bin/console do:mi:diff

mim:
	$(APP) symfony console do:mi:mi -n --no-debug

dbld:
	docker build . -t symfony:vue

dcrt:
	docker run --name sfvue -p 80:8000 -v "$(pwd)/sfvue:/var/www/app" --link mailcatcher --link mysql -d symfony:vue