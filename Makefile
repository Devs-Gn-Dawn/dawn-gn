.PHONY: install build dev watch clean

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
	@echo "  make install  - Installe les dépendances PHP et JS"
	@echo "  make build   - Build les assets pour la production"
	@echo "  make dev     - Lance le build en mode développement"
	@echo "  make watch   - Lance le build en mode watch"
	@echo "  make clean   - Nettoie les dépendances et le cache" 