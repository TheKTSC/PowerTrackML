# Déploiement sur Railway

Ce document décrit les étapes pour déployer l'application Laravel et l'API Flask sur Railway.

## Résumé

- Service principal : Laravel (dossier racine)
- Service ML : Flask (dossier `ml_api/`) — déployer comme service séparé
- Base de données : MySQL (service fourni par Railway)

## Variables d'environnement requises

- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_KEY` (générer localement puis copier)
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `FLASK_API_URL` — URL publique du service Flask déployé
- `MODEL_URL` — URL publique du modèle (optionnel ; utilisé pour télécharger le `.pkl` au build)

## Étapes (Laravel)

1. Sur https://railway.app, créer un compte si nécessaire.
2. Nouveau projet → `Deploy from GitHub` → sélectionnez le dépôt `PowerTrackML`.
3. Ajouter un service MySQL depuis le catalogue Railway.
4. Dans les settings du projet Railway, ajouter les variables d'environnement listées ci-dessus (copier depuis votre `.env`).
5. Configurer la commande de build (Railway exécute la build avant le démarrage) :

```
composer install --optimize-autoloader --no-dev
npm ci
npm run build
bash scripts/download_model.sh || powershell -File scripts\download_model.ps1 || true
php artisan migrate --force
```

6. Commande de démarrage (Start Command) :

```
php artisan serve --host=0.0.0.0 --port=$PORT
```

Railway fournit la variable `$PORT` à l'exécution.

## Service Flask (`ml_api/`)

1. Créer un second service (même projet ou projet séparé) pointant sur le dépôt, mais en sélectionnant la racine `ml_api/` si demandé.
2. Variables d'environnement : `PORT` (Railway le fournit), et **si** vous stockez le modèle ailleurs, `MODEL_URL` ou les identifiants S3.
3. Commande de build :

```
pip install -r requirements.txt
bash ../scripts/download_model.sh || powershell -File ../scripts\download_model.ps1 || true
```

4. Commande de démarrage :

```
gunicorn app:app --bind 0.0.0.0:$PORT
```

## Modèle ML (fichier `.pkl`)

- NE PAS pousser le fichier `.pkl` vers GitHub (interdit >100MB et alourdit l'historique).
- Héberger le modèle sur un stockage externe (S3, Google Cloud Storage, Railway storage, Hugging Face, etc.) et définir `MODEL_URL` dans Railway.
- Le dépôt contient `scripts/download_model.sh` et `scripts/download_model.ps1` qui téléchargent `MODEL_URL` vers `ml_api/model_elec.pkl` pendant la build.

## Vérifications après déploiement

- Laravel : accéder à l'URL fournie par Railway, vérifier les pages, logs et migrations.
- Flask : tester l'endpoint principal /health ou la route utilisée par Laravel, mettre à jour `FLASK_API_URL` si besoin.

## Alternatives

- Git LFS : si vous voulez versionner le modèle dans Git (mais contraignant pour CI et stockage gratuit). 
- GitHub Releases / Asset : publier le modèle en release et télécharger via `MODEL_URL`.

---

Si tu veux, je peux automatiser l'upload du modèle vers S3 ou configurer Git LFS dans le dépôt.
