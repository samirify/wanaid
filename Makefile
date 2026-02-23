.PHONY: help dev website-nextjs website-nextjs-dev website-nextjs-down website-nextjs-dev-down website-nextjs-logs website-nextjs-dev-logs website-nextjs-rebuild
.PHONY: reset cms-api cms-api-down cms-api-clean cms-api-logs cms-api-migrate cms-api-migrate-fresh cms-api-seed cms-api-passport-install cms-api-passport-client-password cms-api-install cms-api-dump-autoload cms-api-composer-update

# Default target
help: ## Show available commands

reset: ## Stop all, then start dev stack (clean slate). One command: install, migrate, Passport, serve.
	$(MAKE) website-nextjs-dev-down
	$(MAKE) cms-api-down
	$(MAKE) dev
	@echo "  ✓ Reset complete."
	@echo ""

dev: ## Start website + cms-api (one command for both)
	$(MAKE) website-nextjs-dev
	$(MAKE) cms-api
	@echo ""
	@echo "  ✓ Website  http://localhost:$${PORT:-9331}"
	@echo "  ✓ cms-api  http://localhost:$${CMS_API_PORT:-9332}"
	@echo "  ✓ phpMyAdmin http://localhost:$${PHPMYADMIN_PORT:-8080}"
	@echo "  ✓ Mailpit  http://localhost:8025"
	@echo ""
	@echo ""
	@echo "  Available commands:"
	@echo "  ────────────────────────────────────────────"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo ""

# ── Website (Next.js) – Production ──────────────────────────────────────────

website-nextjs: ## Build & run the website in production mode (Docker)
	docker compose -f docker/website-nextjs/docker-compose.yml up --build -d
	@echo ""
	@echo "  ✓ Website is running at http://localhost:$${PORT:-9331}"
	@echo ""

website-nextjs-down: ## Stop the production containers
	docker compose -f docker/website-nextjs/docker-compose.yml down

website-nextjs-logs: ## Tail production container logs
	docker compose -f docker/website-nextjs/docker-compose.yml logs -f

website-nextjs-rebuild: ## Force-rebuild production image (no cache)
	docker compose -f docker/website-nextjs/docker-compose.yml build --no-cache
	docker compose -f docker/website-nextjs/docker-compose.yml up -d
	@echo ""
	@echo "  ✓ Website rebuilt and running at http://localhost:$${PORT:-9331}"
	@echo ""

# ── Website (Next.js) – Development ─────────────────────────────────────────

website-nextjs-dev: ## Build & run the website in dev mode with hot-reload (Docker)
	docker compose -f docker/website-nextjs/docker-compose.dev.yml up --build -d
	@echo ""
	@echo "  ✓ Dev server running at http://localhost:$${PORT:-9331}"
	@echo "    Source files are mounted – edits reload automatically."
	@echo ""

website-nextjs-dev-down: ## Stop the dev containers
	docker compose -f docker/website-nextjs/docker-compose.dev.yml down

website-nextjs-dev-logs: ## Tail dev container logs
	docker compose -f docker/website-nextjs/docker-compose.dev.yml logs -f

# ── CMS API (Laravel) – shared API for all apps (Docker, project "wanaid") ─
#    All cms-api commands run INSIDE the cms-api container. Do not run composer/artisan on the host.

cms-api: cms-api-clean ## Build & run cms-api + MySQL + phpMyAdmin + Mailpit (Docker)
	docker compose -f docker/cms-api/docker-compose.yml up --build -d
	@echo ""
	@echo "  ✓ cms-api      http://localhost:$${CMS_API_PORT:-9332}"
	@echo "  ✓ phpMyAdmin   http://localhost:$${PHPMYADMIN_PORT:-8080}"
	@echo "  ✓ Mailpit (UI) http://localhost:8025"
	@echo ""

cms-api-down: ## Stop cms-api stack
	docker compose -f docker/cms-api/docker-compose.yml down

cms-api-clean: ## Remove leftover cms-api containers by name (fixes name conflicts)
	-docker rm -f cms-api-mailpit cms-api-mysql cms-api-phpmyadmin 2>/dev/null || true

cms-api-logs: ## Tail cms-api container logs
	docker compose -f docker/cms-api/docker-compose.yml logs -f cms-api

cms-api-migrate: ## Run Laravel migrations in cms-api container
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api php artisan migrate --force

cms-api-migrate-fresh: ## Drop all tables and re-run migrations (destructive)
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api php artisan migrate:fresh --force

cms-api-seed: ## Run database seeders in cms-api container
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api php artisan db:seed --force

cms-api-passport-install: ## Install Passport (keys + default OAuth clients). Run once after migrate.
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api php artisan passport:install --force
	@echo ""
	@echo "  ✓ Passport installed. Add the client IDs and secrets above to your .env (PASSPORT_*)."
	@echo "    For password grant run: make cms-api-passport-client-password"
	@echo ""

cms-api-passport-client-password: ## Create Passport password grant client (output id/secret for .env)
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api php artisan passport:client --password --no-interaction

cms-api-install: ## Run composer install inside cms-api container (Docker only)
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api composer install --no-interaction

cms-api-dump-autoload: ## Regenerate Composer autoload inside cms-api container (Docker only)
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api composer dump-autoload

cms-api-composer-update: ## Run composer update inside cms-api container (Docker only)
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api composer update --no-interaction
