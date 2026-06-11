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

## Vérifier que tout marche (Admin / Maintenance)

Bon, vous êtes admin et vous voulez vous assurer que le CMS fonctionne comme il faut. Voici comment vérifier les API et les workflows.

### Setup de base (avant de tester)

Avant toute chose, dites au serveur quelles clés API utiliser. Dans votre terminal où tourne MAMP :

```bash
export OPENAI_API_KEY="sk-votre-clé-openai"
export OPENAI_MODEL="gpt-4o-mini"
export AYRSHARE_API_KEY="votre-clé-ayrshare"
```

Ou si vous préférez un `.env` file, c'est aussi possible (cherchez où le code lit les variables).

### Tester la génération de contenu

**Le scénario simple** :
1. Se connecter au CMS (role admin ou manager)
2. Aller dans **Générateur de contenu**
3. Remplissez vite fait (Foot, demain, Tous publics, Instagram)
4. Cliquez **Générer**
5. Vous devez voir un résultat avec titre + texte + hashtags

**Pour regarder ce qui se passe vraiment** :
- Ouvrez F12 dans le navigateur
- Onglet **Network**
- Refaites la génération
- Cherchez l'appel `POST /social-cms/api/generate-content.php`
- Cliquez dessus et regardez la réponse JSON

**Ce que vous devez voir dans la réponse** :
```json
{
  "title": "Tournoi Foot Urban ⚽",
  "content": "Venez tenter votre chance ce samedi...",
  "hashtags": ["#Foot", "#Urban", "#Football", "#Sport"],
  "source": "ai",
  "platform": "Instagram"
}
```

Si `source = "ai"` : Super, OpenAI a marché.
Si `source = "generated"` : Pas grave, le fallback local a pris le relais (clé API manquante ou API indisponible).

**Tester différentes plateformes** :
Essayez de générer pour Instagram, puis Facebook, puis TikTok. Le `content` devrait être sensiblement différent. Instagram plus casual, Facebook plus détails pratiques, TikTok plus accrocheur.

**Codes d'erreur possibles** :
- `500` : Bug serveur. Allez voir les logs PHP (`/Applications/MAMP/logs/php_error.log`)
- `422` : Des données manquent dans le formulaire
- `419` : Votre session a expiré, reconnectez-vous

### Tester la publication Ayrshare (multi-réseaux)

Ayrshare, c'est le service qui publie vos posts sur Facebook, Instagram, TikTok et LinkedIn en une seule requête.

**Pour tester** :
1. Générez un contenu (l'étape d'avant)
2. Notez le `post_id` qu'il reçoit en DB (normalement auto-généré)
3. Dans un terminal, lancez cette commande :

```bash
curl -X POST http://localhost:8888/social-cms/api/publish-ayrshare-post.php \
  -H "Content-Type: application/json" \
  -d '{
    "post_id": 1,
    "platforms": ["facebook", "instagram"]
  }'
```

Vous devez recevoir une réponse JSON comme :
```json
{
  "success": true,
  "id": "ayrshare_post_id_abc123",
  "platform_results": {
    "facebook": "posted_ok",
    "instagram": "posted_ok"
  }
}
```

Si `success = true` : Tout va bien. Le post a été publié sur les réseaux.

**Si ça échoue** :
- `422` : Vous avez pas donné la clé API Ayrshare (env var manquante) ou la liste de plateformes est vide
- `502` : Ayrshare a rejeté la requête (clé API expiré, format bizarre, compte Ayrshare pas actif)
- `404` : Le `post_id` n'existe pas en base de données

### Tester la bibliothèque média

Juste s'assurer que les uploads fonctionnent et que les validations sont en place.

1. Aller **Bibliothèque Multimédia**
2. Essayez d'uploader :
   - ✅ Une image (JPG, PNG) → doit marcher
   - ✅ Une vidéo (MP4) → doit marcher
   - ✅ Un PDF → doit marcher
   - ❌ Un .exe ou .bat → doit être rejeté (message "Format non supporté")
   - ❌ Un fichier de 60MB → doit être rejeté (max 50MB)

Vérifiez que les fichiers valides se retrouvent bien :
- En grille visuelle
- En base de données (`cms_media_library`)
- Sur le disque (`/social-cms/uploads/`)

### Tester la sécurité

**CSRF (attaque de formulaire)** :
1. Éteignez le JavaScript (en dev tools)
2. Allez créer un nouveau post en calendrier
3. Essayez de soumettre sans le token CSRF
4. Ça devrait être rejeté avec une erreur 419 ou CSRF error

**SQL injection** :
1. Aller **Bibliothèque Multimédia**
2. Dans la barre de recherche, tapez : `'; DROP TABLE cms_media_library; --`
3. Si la table ne disparaît pas → c'est bon, c'est protégé
4. Ça devrait juste faire une recherche qui retourne rien

**XSS (injection de code)** :
1. Créer un nouveau post en calendrier
2. Dans le titre, mettez : `<script>alert('XSS')</script>`
3. Sauvegardez et consultez le post
4. Le code ne doit pas s'exécuter. Vous devez voir le texte littéralement à l'écran

### Tester les permissions et rôles

Un user `content_manager` ne doit pas pouvoir accéder à "Gestion des utilisateurs". Un admin oui.

1. Créez un nouveau user avec role `content_manager`
2. Connectez-vous avec ce user
3. Essayez d'accéder à `/social-cms/admin/users.php`
4. Ça devrait vous renvoyer 403 Forbidden ou redirect au login

### Déboguer si ça marche pas

**Voir les erreurs PHP** :
```bash
tail -f /Applications/MAMP/logs/php_error.log
```

**Tester la syntaxe PHP** :
```bash
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/generate-content.php
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/publish-ayrshare-post.php
```

**Vérifier que les env vars sont présentes** :
```bash
php -r "echo getenv('OPENAI_API_KEY') ? 'OPENAI OK' : 'OPENAI MISSING';"
php -r "echo getenv('AYRSHARE_API_KEY') ? 'AYRSHARE OK' : 'AYRSHARE MISSING';"
```

**Vérifier la BD** :
```bash
# Voir si les tables existent
mysql -u root -proot -h localhost -e "SHOW TABLES FROM your_db;"

# Voir les posts créés
mysql -u root -proot -h localhost -e "SELECT id, title, created_date FROM your_db.cms_social_posts LIMIT 5;"
```
