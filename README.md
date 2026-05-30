# Powertrack

Application Laravel + API Flask pour la détection d’alertes et la visualisation.

## Aperçu

- Backend Laravel : application principale.
- API Flask : service ML séparé dans `ml_api/`.
- Base de données : MySQL (ou MariaDB).
- Frontend : Laravel + Vite.

## Installation locale

1. Copier la configuration environnementale :

   ```bash
   cp .env.example .env
   ```

2. Installer les dépendances PHP :

   ```bash
   composer install
   ```

3. Générer la clé d’application :

   ```bash
   php artisan key:generate
   ```

4. Installer les dépendances front-end :

   ```bash
   npm install
   npm run build
   ```

5. Configurer la base de données et migrer :

   - `.env` doit contenir vos variables MySQL.
   - Exemple :

     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=powertrack
     DB_USERNAME=root
     DB_PASSWORD=
     ```

   ```bash
   php artisan migrate
   ```

6. Lancer le serveur local Laravel :

   ```bash
   php artisan serve
   ```

7. Déployer le service Flask séparément si nécessaire.

## Variables d’environnement importantes

- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_KEY`
- `FLASK_API_URL` (à ajouter si votre application consomme l’API Flask)

> Ne versionnez jamais le fichier `.env`. Il est déjà ignoré dans `.gitignore`.

## Mise en ligne sur GitHub

1. Initialiser le dépôt Git local :

   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   ```

2. Créer un dépôt sur GitHub.
3. Ajouter le remote et pousser :

   ```bash
   git remote add origin https://github.com/<votre-compte>/<votre-repo>.git
   git push -u origin main
   ```

## Déploiement Railway (option simple)

1. Créer un compte sur https://railway.app.
2. Nouveau projet → `Deploy from GitHub` → sélectionner le dépôt.
3. Ajouter un service MySQL depuis le catalogue Railway.
4. Dans Railway, configurer les variables d’environnement exactement comme dans votre `.env` :
   - `APP_ENV`
   - `APP_DEBUG`
   - `APP_URL`
   - `DB_CONNECTION`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `FLASK_API_URL` (URL de l’API Flask déployée)
5. Railway détecte Laravel et peut exécuter les commandes de déploiement.
6. Si nécessaire, ajouter une commande de build ou de démarrage personnalisée dans Railway :

   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   php artisan migrate --force
   ```

7. L’application sera accessible via l’URL générée par Railway.

## Déploiement du service Flask

- Déployer `ml_api/` comme service séparé sur Railway ou Render.
- Sur Railway, vous pouvez créer un second service dans le même projet ou un projet distinct.
- Sur Render, utilisez le dossier `ml_api/` et configurez le runtime Python.
- Mettre à jour `FLASK_API_URL` dans le dashboard Laravel avec l’URL publique du service Flask.

Pour une procédure pas-à-pas et des scripts utiles (téléchargement du modèle, commandes de build), voir le guide dédié : [DEPLOY_RAILWAY.md](DEPLOY_RAILWAY.md)

## Suivi de marche à suivre

Sur GitHub, créez une issue ou un projet pour suivre :

- initialisation du dépôt
- configuration Railway
- création de la base MySQL
- déploiement Laravel
- déploiement Flask
- validation finale du `FLASK_API_URL`

---

## Notes

- `.gitignore` ignore déjà `.env`, `vendor`, `node_modules`, `public/build`, `storage/*.key`, etc.
- Assurez-vous que `APP_KEY` est générée avant le déploiement.
- Si vous utilisez un déploiement monorepo, vérifiez que Railway/Render sait lancer le bon sous-dossier pour le service Flask.
