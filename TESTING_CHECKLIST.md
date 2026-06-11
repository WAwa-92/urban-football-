# Testing Checklist - Social CMS
## Date: 11 Juin 2026

### 1. AUTHENTIFICATION & RÔLES (6 tests)

#### Test 1.1: Login super_admin
- **Endpoint**: `/social-cms/pages/login.php`
- **Données**: username=`admin` (super_admin), password=`demo`
- **Résultat attendu**: Redirect vers `/social-cms/index.php`, session active
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 1.2: Login content_manager
- **Endpoint**: `/social-cms/pages/login.php`
- **Données**: username=`content1`, password=`test123`
- **Résultat attendu**: Redirect vers `/social-cms/index.php`, permissions restreintes
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 1.3: Login refusé - compte désactivé
- **Endpoint**: `/social-cms/pages/login.php`
- **Données**: username=`disabled_user`, password=`pass`
- **Résultat attendu**: Message erreur "Compte désactivé" ou "Identifiants incorrects"
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 1.4: CSRF protection on POST
- **Endpoint**: `/social-cms/api/schedule-post.php`
- **Données**: POST sans token CSRF
- **Résultat attendu**: 419 (session invalid) ou erreur CSRF
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 1.5: Session timeout
- **Action**: Login → attendre 30min → accéder page protégée
- **Résultat attendu**: Redirect vers login
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: (skipped si pas 30min dispo)

#### Test 1.6: Access denied - role insuffisant
- **Endpoint**: `/social-cms/admin/users.php` (sans droits admin)
- **Résultat attendu**: 403 Forbidden ou redirect login
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 2. GESTION UTILISATEURS (5 tests)

#### Test 2.1: Create user
- **Endpoint**: `/social-cms/admin/users.php` (form POST)
- **Données**: username=`newuser`, email=`test@test.com`, role=`content_manager`
- **Résultat attendu**: User créé, visible en liste
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 2.2: Read users list
- **Endpoint**: `/social-cms/admin/users.php` (GET)
- **Résultat attendu**: Liste de tous les users avec colonnes (id, username, email, role, status)
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 2.3: Update user
- **Endpoint**: `/social-cms/admin/users.php` (form PUT/POST edit)
- **Données**: Update user role admin → manager
- **Résultat attendu**: Role changé en DB, visible en liste
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 2.4: Delete user
- **Endpoint**: `/social-cms/admin/users.php` (delete button)
- **Données**: Delete user `newuser`
- **Résultat attendu**: User supprimé, plus visible en liste
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 2.5: Duplicate username rejected
- **Endpoint**: `/social-cms/admin/users.php` (form POST)
- **Données**: Create user avec username existant
- **Résultat attendu**: Erreur "Username exists" ou feedback erreur
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 3. BIBLIOTHÈQUE MÉDIA (8 tests)

#### Test 3.1: Upload image (JPEG)
- **Endpoint**: `/social-cms/pages/media-library.php` (drag-drop)
- **Fichier**: image.jpg (~1MB)
- **Résultat attendu**: Upload réussi, image visible en grid, métadonnées en DB
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.2: Upload vidéo (MP4)
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Fichier**: video.mp4 (~10MB)
- **Résultat attendu**: Upload réussi, vidéo visible en grid avec icône video
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.3: Upload PDF
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Fichier**: document.pdf (~2MB)
- **Résultat attendu**: Upload réussi, PDF visible en grid
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.4: Reject unsupported format
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Fichier**: malware.exe ou audio.mp3 (non supporté)
- **Résultat attendu**: Erreur "Format non supporté"
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.5: Reject file > 50MB
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Fichier**: huge.mp4 (>50MB)
- **Résultat attendu**: Erreur "Fichier trop volumineux"
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.6: Filter by category
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Action**: Sélectionner catégorie "Photos"
- **Résultat attendu**: Grille filtrée, seulement images
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.7: Search by title
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Action**: Chercher "logo"
- **Résultat attendu**: Affiche media avec "logo" dans le titre
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 3.8: Delete media
- **Endpoint**: `/social-cms/pages/media-library.php`
- **Action**: Delete button sur un média
- **Résultat attendu**: Média supprimé de la grille et DB
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 4. CALENDRIER ÉDITORIAL (4 tests)

#### Test 4.1: Create post
- **Endpoint**: `/social-cms/pages/editorial-calendar.php` (form)
- **Données**: title="Tournoi foot", content="Samedi 14h", platforms=[Facebook, Instagram], status=draft, date=2026-06-15
- **Résultat attendu**: Post créé, visible en calendrier et liste
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 4.2: Update post status draft → scheduled
- **Endpoint**: `/social-cms/pages/editorial-calendar.php` (edit)
- **Données**: Change status = scheduled
- **Résultat attendu**: Status changé, date d'envoi définie
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 4.3: List posts with filters
- **Endpoint**: `/social-cms/pages/editorial-calendar.php`
- **Action**: Filter par status=draft
- **Résultat attendu**: Affiche seulement posts en draft
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 4.4: Delete post
- **Endpoint**: `/social-cms/pages/editorial-calendar.php`
- **Action**: Delete button
- **Résultat attendu**: Post supprimé, plus visible
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 5. GÉNÉRATEUR DE CONTENU (3 tests)

#### Test 5.1: Generate local (sans IA)
- **Endpoint**: `/social-cms/pages/content-generator.php`
- **Données**: activity="Foot", date="2026-06-15", audience="Tout public", platform="Instagram"
- **Résultat attendu**: Content généré avec titre, contenu, hashtags, source="generated"
- **DevTools**: Vérifier Network → response JSON avec fields: title, content, hashtags, source
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 5.2: Generate with platform-specific styling
- **Endpoint**: `/social-cms/pages/content-generator.php`
- **Action**: Générer pour 3 platforms différentes (Facebook, Instagram, TikTok)
- **Résultat attendu**: Content DISTINCT par platform (emojis, tone, hashtags différents)
- **DevTools**: Comparer 3 responses
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 5.3: Generate with OpenAI (si OPENAI_API_KEY défini)
- **Endpoint**: `/social-cms/pages/content-generator.php`
- **OPENAI_API_KEY**: exporté avant test
- **Résultat attendu**: Content généré, source="ai", réponse plus longue/détaillée
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 6. PUBLICATION AYRSHARE (3 tests)

#### Test 6.1: Publish post - préparation
- **Endpoint**: `/social-cms/pages/content-generator.php` → generate → save
- **Résultat attendu**: Post sauvegardé en DB (cms_social_posts), obtenir post_id
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 6.2: Publish via Ayrshare API
- **Endpoint**: `/social-cms/api/publish-ayrshare-post.php` (POST)
- **Données**: post_id=1, platforms=[facebook, instagram]
- **AYRSHARE_API_KEY**: défini en env
- **Résultat attendu**: Response JSON {success: true, id: "..."}
- **DevTools**: Vérifier status 200, response fields
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 6.3: Ayrshare error handling
- **Endpoint**: `/social-cms/api/publish-ayrshare-post.php`
- **Données**: Requête sans API key (env AYRSHARE_API_KEY="")
- **Résultat attendu**: Erreur 422 (missing required fields)
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

### 7. SÉCURITÉ (2 tests)

#### Test 7.1: SQL injection attempt
- **Endpoint**: `/social-cms/pages/media-library.php` (search)
- **Données**: search="'; DROP TABLE cms_media_library; --"
- **Résultat attendu**: Requête echappée, pas de suppression, résultat vide ou aucun match
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

#### Test 7.2: XSS attempt in content
- **Endpoint**: `/social-cms/pages/editorial-calendar.php` (create post)
- **Données**: title="<script>alert('XSS')</script>", content="Test"
- **Résultat attendu**: Script échappé en affichage, pas d'exécution, affiche literal HTML
- **Status**: [ ] PASS [ ] FAIL
- **Notes**: 

---

## SUMMARY

**Total Tests**: 31
**PASS**: ____ / 31
**FAIL**: ____ / 31
**SKIPPED**: ____ / 31

**Critical Bugs Found**:
1. 
2. 
3. 

**Blocking Issues for Tomorrow**:
1. 
2. 

**Notes Générales**:
- Tests lancés à: 
- Environnement: MAMP, macOS
- Version PHP: `/Applications/MAMP/bin/php/php/bin/php`
