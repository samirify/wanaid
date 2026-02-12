.PHONY: help website-nextjs website-nextjs-dev website-nextjs-down website-nextjs-dev-down website-nextjs-logs website-nextjs-dev-logs website-nextjs-rebuild

# Default target
help: ## Show available commands
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
