.PHONY: start dev stop get-issues get-cycletime pgsql-dump list ngrok
start:
	docker compose up php redis worker pgsql nginx nginx-proxy node

.PHONY: dev
dev:
	docker compose up php redis worker pgsql-testing node

stop:
	docker compose down

get-issues:
	docker compose exec php php artisan cycletime:issues

get-cycletime:
	docker compose exec php php artisan cycletime:calculate

pgsql-dump:
	docker compose exec pgsql pg_dump laravel > backup.gz

ngrok:
	docker run -it -e NGROK_AUTHTOKEN=ABCD ngrok/ngrok:latest http host.docker.internal:80

clear-caches:
	docker compose run -it php php artisan cache:clear
	docker compose run -it php php artisan config:clear

list:
	@LC_ALL=C $(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/(^|\n)# Files(\n|$$)/,/(^|\n)# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'
