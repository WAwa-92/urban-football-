# Guide utilisateur — Urban Center

## Réserver un terrain
1. Ouvrir la page d'accueil.
2. Cliquer sur **Réserver**.
3. Choisir le sport.
4. Choisir le terrain puis la date et le créneau.
5. Renseigner nom, prénom, téléphone, email et nombre de joueurs.
6. Valider la réservation.

## Consulter ses réservations
- Ouvrir **Mes réservations**.
- Filtrer par sport ou statut si besoin.
- Consulter l'historique et les actions possibles.

## Contacter le complexe
- Utiliser le formulaire **Contactez-nous** en bas de page.
- Les demandes sont envoyées directement à l'administration.

## Astuces
- Préparer votre numéro de téléphone avant la réservation.
- Vérifier la disponibilité du créneau choisi.
- Utiliser la galerie et les pages installations pour choisir l'activité.

---

## Espace Social CMS (Content Manager)

### Accès
1. Accéder à `/social-cms/pages/login.php`
2. Se connecter avec compte `content_manager`
3. Redirection vers dashboard CMS

### Modules disponibles
- **Bibliothèque multimédia** : upload & gestion des images/vidéos/PDFs
- **Calendrier éditorial** : planification des publications
- **Générateur de contenu** : création intelligente de posts
- **Analytics** : suivi des publications

### 1) Utiliser la Bibliothèque multimédia
1. Cliquer sur **Bibliothèque Multimédia**.
2. Drag-drop ou sélectionner des fichiers (JPEG, PNG, MP4, PDF max 50MB).
3. Renseigner catégorie (Photos, Vidéos, Flyers, Logos, Affiches).
4. Attendre confirmation d'upload.
5. Retrouver le média en grille (filtrer par catégorie, chercher par titre).
6. Supprimer si besoin avec le bouton trash.

### 2) Générer du contenu (avec IA)
1. Cliquer sur **Générateur de contenu**.
2. Renseigner le formulaire :
   - **Activité** : Foot, Tennis, Padel, Yoga, Fitness, etc.
   - **Date** : date de publication prévue
   - **Public visé** : Tous publics, Enfants, Adultes, Confirmés
   - **Plateforme** : cocher Facebook / Instagram / TikTok / LinkedIn
3. Cliquer **Générer**.
4. Vérifier le résultat :
   - **Titre** : accroche courte
   - **Contenu** : texte adapté à la plateforme
   - **Hashtags** : mots-clés pertinents (6-12)
   - **Source** : `ai` (OpenAI) ou `generated` (local)
5. Copier ou modifier avant de programmer.

**Note** : Chaque plateforme a un ton/style différent :
- **Instagram** : focus sur le visuel, CTA engagement, emojis sport
- **Facebook** : format événement, détails pratiques
- **TikTok** : hook accrocheur, trend-focused, jeunesse
- **LinkedIn** : ton corporate, professionnalisme

### 3) Planifier une publication
1. Cliquer sur **Calendrier Éditorial**.
2. Créer nouveau post :
   - Titre et contenu (copier-coller du générateur)
   - Plateforme(s) cibles
   - Date et heure prévues
   - Statut : `draft` (brouillon) ou `scheduled` (programmé)
3. Sauvegarder.
4. Voir en calendrier mensuel ou liste.
5. Avant publication, passer status à `scheduled` ou `published`.

### 4) Consulter les statistiques
1. Cliquer sur **Analytics**.
2. Voir graphiques :
   - Nombre total de posts
   - Répartition par plateforme (Facebook/IG/TikTok/LinkedIn)
   - Répartition par activité (Foot/Tennis/Padel/etc.)
3. Tableaux d'engagement (si API synchronisation active).

### Vérifier que la génération fonctionne (pour Admin)
- Ouvrir les outils navigateur (F12) puis l'onglet **Network**.
- Relancer une génération.
- Vérifier la requête vers `/api/generate-content.php`.
- Contrôler la réponse JSON : `title`, `content`, `hashtags`, `source`.

**Interprétation du champ `source`** :
- `ai` = génération via OpenAI (IA activée, plus créative).
- `generated` = fallback local (IA indisponible, générateur templé).