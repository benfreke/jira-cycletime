.PHONY: start
start:
	docker compose up php redis worker pgsql

.PHONY: dev
dev:
	docker compose up php redis worker pgsql-testing

.PHONY: get-issues
get-issues:
	docker compose exec php php artisan cycletime:issues

.PHONY: get-cycletime
get-cycletime:
	docker compose exec php php artisan cycletime:calculate

.PHONY: pgsql-dump
pgsql-dump:
	docker compose exec pgsql pg_dump laravel > backup.gz

.PHONY: list
list:
	@LC_ALL=C $(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/(^|\n)# Files(\n|$$)/,/(^|\n)# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'
