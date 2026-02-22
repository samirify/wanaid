.PHONY: help dev website-nextjs website-nextjs-dev website-nextjs-down website-nextjs-dev-down website-nextjs-logs website-nextjs-dev-logs website-nextjs-rebuild
.PHONY: cms-api cms-api-down cms-api-clean cms-api-logs cms-api-migrate cms-api-install

# Default target
help: ## Show available commands

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

cms-api-install: ## Run composer install in cms-api container
	docker compose -f docker/cms-api/docker-compose.yml run --rm cms-api composer install --no-interaction
