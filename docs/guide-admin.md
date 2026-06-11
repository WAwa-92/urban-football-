# Guide admin — Urban Center

## Accès
- Ouvrir la page de connexion administrateur.
- Se connecter avec le compte administrateur prévu pour le back office.

## Tableau de bord
- Voir les statistiques globales.
- Consulter les réservations récentes.
- Voir les chiffres par sport.

## Gestion des réservations
- Ouvrir **Gérer les réservations**.
- Filtrer par sport, statut ou recherche.
- Utiliser la pagination si la liste est longue.
- Modifier le statut avec les badges colorés.
- Exporter les données en CSV si besoin.

## Gestion des événements
- Ouvrir **Gérer les événements**.
- Ajouter, modifier, publier ou supprimer un événement.

## Gestion des actualités
- Ouvrir **Gérer les actualités**.
- Ajouter les promotions, nouveautés ou annonces.
- Publier ou masquer selon le besoin.

## Sécurité
- Les formulaires utilisent un token CSRF.
- Éviter de garder les identifiants par défaut.

## Vérification API (OpenAI + Ayrshare)

### Setup Préalable

Défini les variables d'environnement dans le terminal MAMP ou `.env` :

```bash
export OPENAI_API_KEY="sk-..."
export OPENAI_MODEL="gpt-4o-mini"
export AYRSHARE_API_KEY="your_ayrshare_key"
```

### 1) Vérifier l'API de génération (OpenAI + Fallback)

**Étape 1** : Accéder au formulaire
1. Ouvrir **Social CMS > Générateur de contenu** (role: content_manager ou admin)
2. Remplir le formulaire : activité (Foot), date, public (Tous), plateforme (Instagram)

**Étape 2** : Générer et vérifier réponse
1. Cliquer **Générer**.
2. Ouvrir DevTools (F12) → Network tab.
3. Relancer la génération.
4. Chercher requête `POST /social-cms/api/generate-content.php`.
5. Vérifier réponse JSON contient :
   - `title` : string (ex: "Tournoi Foot Intra ⚽")
   - `content` : string 3-6 lignes avec emojis sport
   - `hashtags` : array 6-12 items (ex: ["#Foot", "#Urban", "#InstagramFootball"])
   - `source` : "ai" (si OpenAI OK) ou "generated" (fallback)
   - `platform` : string (Instagram, Facebook, TikTok, LinkedIn)

**Étape 3** : Vérifier platform-specific content
1. Générer pour **Instagram** → vérifier ton = casual, emojis présents, hashtags Instagram (#Instagram)
2. Générer pour **Facebook** → vérifier format = plus détails pratiques, sérieux
3. Générer pour **TikTok** → vérifier ton = jeunesse, trend, hook accrocheur
4. Générer pour **LinkedIn** → vérifier ton = corporate, professionnel

**Codes d'erreur API** :
- `500` : erreur serveur (PHP exception, check logs `/Applications/MAMP/logs/`)
- `422` : données manquantes ou invalides
- `419` : session expirée (relogin)

### 2) Vérifier la publication Ayrshare

**Pré-requis** :
- `AYRSHARE_API_KEY` défini en env
- Post créé en DB (cms_social_posts)

**Étape 1** : Créer un post
1. Aller à **Générateur de contenu**
2. Générer un contenu simple (ex: "Test publication Ayrshare")
3. Post est automatiquement sauvé en DB avec ID (note le post_id)

**Étape 2** : Publier via Ayrshare (manual cURL ou page publish futur)
```bash
curl -X POST http://localhost:8888/social-cms/api/publish-ayrshare-post.php \
  -H "Content-Type: application/json" \
  -d '{
    "post_id": 1,
    "platforms": ["facebook", "instagram"]
  }'
```

**Étape 3** : Vérifier réponse
- Status HTTP 200
- Response JSON : 
  ```json
  {
    "success": true,
    "id": "ayrshare_post_id_xxx",
    "platform_results": {...}
  }
  ```
- Si post_id fourni, vérifier en DB : colonnes `ayrshare_post_id` + `ayrshare_synced_at` populées

**Codes d'erreur Ayrshare** :
- `422` : clé API manquante ou plateforme invalide
- `502` : Ayrshare API rejection (token expiré, plateforme pas active)
- `404` : post_id n'existe pas en DB

### 3) Vérifier le formulaire media (upload)

1. Aller à **Bibliothèque Multimédia**
2. Tester uploads :
   - **Succès** : image.jpg (~2MB), video.mp4 (~5MB), document.pdf
   - **Rejet** : malware.exe (format non supporté), huge.mp4 (>50MB)
3. Vérifier messages erreur affichés clairement
4. Vérifier files stockées en `/social-cms/uploads/`
5. Vérifier enregistrement en DB table `cms_media_library`

### 4) Vérifier sécurité

**CSRF Protection** :
1. Désactiver JavaScript
2. Accéder formulaire POST (ex: créer user en `/social-cms/admin/users.php`)
3. Soumettre sans token CSRF
4. Vérifier rejet 419 ou erreur CSRF

**SQL Injection** :
1. Aller à **Bibliothèque Multimédia** → recherche
2. Entrer `'; DROP TABLE cms_media_library; --`
3. Vérifier pas de suppression, requête échappée correctement

**XSS** :
1. Créer post avec titre : `<script>alert('XSS')</script>`
2. Vérifier en affichage que script n'exécute pas, HTML échappé (visible comme texte)

### 5) Vérifier gestion users & permissions

1. Aller **Social CMS > Gestion des utilisateurs** (admin only)
2. Create user : username=test, role=content_manager
3. Login avec ce user
4. Vérifier accès à : Bibliothèque, Calendrier, Générateur, Analytics
5. Vérifier BLOQUÉ sur : Gestion users, Admin pages
6. Logout user, relogin admin, delete ce user

### Logs & Debugging

**Accéder aux logs PHP** :
```bash
tail -f /Applications/MAMP/logs/apache_error.log
tail -f /Applications/MAMP/logs/php_error.log
```

**Vérifier config env** :
```bash
php -r "echo getenv('OPENAI_API_KEY') ? 'OK' : 'MISSING';"
php -r "echo getenv('AYRSHARE_API_KEY') ? 'OK' : 'MISSING';"
```

**Tester syntaxe PHP** :
```bash
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/generate-content.php
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/publish-ayrshare-post.php
```
