.PHONY: install build dev watch clean

# Démarrer les conteneurs Docker
up:
	docker compose up -d

# Arrêter les conteneurs Docker
down:
	docker compose down

# Exécuter les migrations Doctrine
migrate:
	docker compose exec php bin/console doctrine:migrations:migrate

# Afficher le statut des migrations
migration-status:
	docker compose exec php bin/console doctrine:migrations:status

# Installation des dépendances
install:
	cd app && composer install
	cd app && npm install

# Build pour la production
build:
	cd app && npm run build

# Développement
dev:
	cd app && npm run dev

# Watch mode pour le développement
watch:
	cd app && npm run watch

# Nettoyage
clean:
	cd app && rm -rf node_modules vendor var/cache/*

# Aide
help:
	@echo "Commandes disponibles:"
	@echo "  make up        - Démarre les conteneurs Docker"
	@echo "  make down      - Arrête les conteneurs Docker"
	@echo "  make migrate   - Exécute les migrations Doctrine"
	@echo "  make migration-status - Affiche le statut des migrations"
	@echo "  make build     - Build les assets pour la production"
	@echo "  make dev       - Lance le build en mode développement"
	@echo "  make watch     - Lance le build en mode watch"
	@echo "  make clean     - Nettoie les dépendances et le cache" 