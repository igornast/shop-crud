.PHONY: jwt-keys

jwt-keys:
	@echo "Generating JWT keys..."
	@docker compose exec php sh -c ' \
		set -e; \
		php bin/console lexik:jwt:generate-keypair; \
	'
	@echo "JWT keys generated successfully."
