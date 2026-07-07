# Urban Center

Site vitrine et back office pour le complexe sportif Urban Center.

## Démarrage rapide
- Lancer le projet avec MAMP.
- Ouvrir `Urban Center.html` dans le navigateur.
- Accéder au back office depuis `admin/login.php`.

## Documentation
- [Guide utilisateur](docs/guide-utilisateur.md)
- [Guide admin](docs/guide-admin.md)

## Fonctionnalités principales
- Présentation du complexe
- Réservation en ligne en 3 étapes
- Galerie photos
- Événements et actualités
- Back office de gestion

## Module Social CMS

Le projet inclut une application séparée pour la communication digitale:
- URL: `/social-cms/pages/login.php`
- Rôles: `super_admin`, `admin`, `manager`, `content_manager`
- Modules: bibliothèque média, calendrier éditorial, générateur de contenu, analytics, gestion utilisateurs

### Variables d'environnement (option IA)

Le générateur de contenu fonctionne sans API externe (fallback local). Pour activer la génération IA:

- `OPENAI_API_KEY` : clé API OpenAI
- `OPENAI_MODEL` : modèle (par défaut `gpt-4o-mini`)
- `OPENAI_API_BASE` : base URL API (par défaut `https://api.openai.com/v1`)

Exemple (MAMP / terminal):

`export OPENAI_API_KEY="votre_cle"`

Si la clé est absente ou si l'API est indisponible, le système revient automatiquement au générateur local.

### Variables d'environnement (option Buffer)

Pour publier une idée via Buffer GraphQL (`createIdea`):

- `BUFFER_API_TOKEN` : token Buffer (Bearer)
- `BUFFER_ORGANIZATION_ID` : ID d'organisation Buffer
- `BUFFER_GRAPHQL_ENDPOINT` : endpoint GraphQL (par défaut `https://api.buffer.com/graphql`)

Endpoint CMS disponible:
- `POST /social-cms/api/publish-buffer-idea.php`

Payload minimal:
- `csrf_token`
- `organization_id` (ou variable d'environnement)
- `title`, `text` (ou `post_id` pour reprendre un post déjà enregistré)

Important sécurité:
- ne jamais committer le token en dur,
- régénérer immédiatement un token exposé en conversation ou dans un commit.

### Variables d'environnement (option Ayrshare - remplaçant Buffer)

Pour publier un post via Ayrshare:

- `AYRSHARE_API_KEY` : clé API Ayrshare (Bearer)
- `AYRSHARE_API_BASE` : base API (par défaut `https://app.ayrshare.com/api`)

Endpoint CMS disponible:
- `POST /social-cms/api/publish-ayrshare-post.php`

Payload minimal:
- `csrf_token`
- `platforms` (tableau, ex: `['facebook', 'instagram']`)
- `post` (texte) ou `post_id` (reprendre un post local)

Le système peut sauvegarder l'identifiant de publication Ayrshare dans la table des posts CMS.
