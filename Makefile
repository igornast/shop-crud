.PHONY: db-reset jwt-keys

jwt-keys:
	@echo "Generating JWT keys..."
	@docker compose exec php sh -c ' \
		set -e; \
		php bin/console lexik:jwt:generate-keypair; \
	'
	@echo "JWT keys generated successfully."

db-reset:
	@echo "Resetting the database..."
	@docker compose exec php sh -c ' \
		set -e; \
		php bin/console doctrine:database:drop --force --if-exists; \
		php bin/console doctrine:database:create; \
		php bin/console doctrine:schema:update --force; \
		php bin/console doctrine:fixtures:load --no-interaction; \
	'
	@echo "Database reset complete."
