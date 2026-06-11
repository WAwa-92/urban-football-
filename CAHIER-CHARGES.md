# Cahier des Charges - Module Social CMS
## Urban Center Sport & Leisure Complex

**Date**: 11 Juin 2026  
**Auteur**: [À remplir - Developpeur Stagiaire]  
**Client**: Urban Center Management  
**Signature**: _______________ (Employeur/Maître de stage)

---

## 1. CONTEXTE & BESOINS INITIAUX

### 1.1 Objectif du projet
Créer un système de gestion de contenu social (Social CMS) pour centraliser la création, la planification et la publication de posts sur les réseaux sociaux (Facebook, Instagram, TikTok, LinkedIn) de l'Urban Center.

### 1.2 Problématique initiale
- Avant : Publications manuelles, sans cohérence
- Besoin : Plateforme unifiée avec générateur IA
- Timeline : Développé en 2 mois (internship)

### 1.3 Profils utilisateurs
- **Super Admin** : Accès complet, gestion infrastructure
- **Admin** : Gestion users, configuration APIs
- **Manager** : Validation contenu, suivi analytics
- **Content Manager** : Création contenu, programmation posts
- **Disabled** : Compte désactivé, accès refusé

---

## 2. SOLUTIONS APPORTÉES

### 2.1 Architecture globale
- **Backend** : PHP 7.4+ (MAMP)
- **Database** : MySQL via PDO
- **Frontend** : HTML/CSS/JavaScript, Bootstrap 5
- **APIs intégrées** : OpenAI (GPT-4o-mini), Ayrshare (multi-platform publishing)

### 2.2 Modules implémentés

#### **Bibliothèque Multimédia**
- Upload drag-drop (images, vidéos, PDFs)
- Validation fichiers (type MIME, taille max 50MB)
- Stockage sécurisé `/social-cms/uploads/`
- Filtrage par catégorie (Photos, Vidéos, Flyers, Logos, Affiches)
- Recherche par titre
- Suppression avec confirmation

#### **Calendrier Éditorial**
- Vue mensuelle (FullCalendar.js)
- Vue liste avec filtres (status: draft/scheduled/published)
- CRUD complet (Create, Read, Update, Delete posts)
- Programmation par date/heure
- Statuts : draft → scheduled → published

#### **Générateur de Contenu (IA-augmenté)**
- Formulaire : activité, date, public, plateforme(s)
- Génération locale (fallback) : templates + hashtags
- Génération OpenAI : 3-6 lignes, 6-12 hashtags, copywriting pro
- **Platform-specific output** :
  - Instagram : casual, emojis, #Instagram tags
  - Facebook : détails pratiques, format événement
  - TikTok : ton jeunesse, trend, hook accrocheur
  - LinkedIn : ton corporate, professionnel
- Emojis sport : ⚽🎾💪🔥 adapté à l'activité

#### **Publication Multi-plateforme (Ayrshare)**
- Single-endpoint REST API
- Support : Facebook, Instagram, TikTok, LinkedIn (+ Twitter, Pinterest, etc.)
- Stockage tracking : `ayrshare_post_id`, `ayrshare_synced_at`
- Gestion erreurs : 422, 502, 404

#### **Analytics Dashboard**
- Graphiques Chart.js :
  - Nombre de posts créés (timeline)
  - Répartition par plateforme (pie chart)
  - Répartition par activité (bar chart)
- Statut publications (brouillons, programmés, publiés)

#### **Gestion Utilisateurs CMS**
- CRUD pour content_managers
- Assignation de rôles
- Activation/désactivation comptes
- Permissions par rôle (RBAC)

#### **Notifications Système**
- Table `cms_notifications`
- Déclencheurs : "Publication programmée", "Contenu à préparer", "Post publié"
- Affichage dashboard

### 2.3 Sécurité
- **CSRF Protection** : tokens validés sur tous POST/PUT/DELETE
- **SQL Injection** : requêtes paramétrées (PDO prepared)
- **XSS Prevention** : HTML escaping à l'affichage
- **Auth/Autho** : session-based, role-based access control
- **Env Variables** : secrets via `cmsEnv()` helper, pas de hardcoding

---

## 3. FEATURES IMPLÉMENTÉES vs. DEMANDÉES

| Feature | Demandé | Implémenté | Status |
|---------|---------|-----------|--------|
| Media library (upload, grid, filter) | ✓ | ✓ | ✅ FAIT |
| Editorial calendar (CRUD, views) | ✓ | ✓ | ✅ FAIT |
| Content generator (local) | ✓ | ✓ | ✅ FAIT |
| OpenAI integration | ✓ | ✓ | ✅ FAIT (avec fallback) |
| Platform-specific content | - | ✓ | ✅ BONUS |
| Multi-platform publishing (Buffer→Ayrshare) | ✓ | ✓ | ✅ FAIT |
| User management CMS | ✓ | ✓ | ✅ FAIT |
| Analytics dashboard | ✓ | ✓ | ✅ FAIT |
| Notifications system | ✓ | ✓ | ✅ FAIT |
| IA suggestions page | (optionnel) | - | 🔄 PENDING |
| Meta Graph API (facebook publish) | (bonus) | - | 🔄 NOT STARTED |

---

## 4. LIMITATIONS & ÉCARTS

### 4.1 Limitations techniques
1. **Ayrshare cost** : Service payant, testé en sandbox
2. **OpenAI fallback** : Si API indisponible, utilise templates (moins créatif)
3. **Calendar UI** : FullCalendar.js basique, pas de drag-drop scheduling
4. **Analytics** : Données locales seulement, pas de sync réseaux sociaux en temps réel
5. **Media library** : Stockage local uniquement, pas de cloud (S3/CDN)

### 4.2 Non-implémentations (justifiées)
- **IA Suggestions module** : Déferred (complexité > bénéfice pour stage)
- **Meta Graph API** : Time constraints
- **Scheduling auto-publisher** : Nécessiterait cron/worker, hors scope
- **Content approval workflow** : Simple statut-based suffisant pour usage stage

---

## 5. ARCHITECTURE DATABASE

### Tables créées

1. **cms_media_library**
   - id, filename, category, path, file_type, title, date_upload

2. **cms_editorial_calendar**
   - id, title, content, platform(s), activity, audience, scheduled_date, scheduled_time, status, created_at, updated_at

3. **cms_social_posts**
   - id, content, hashtags, platform, source (ai/generated), created_date, ayrshare_post_id, ayrshare_synced_at

4. **cms_content_templates**
   - id, name, template_text, platform

5. **cms_analytics**
   - id, post_id, likes, shares, comments, date

6. **cms_notifications**
   - id, message, type, read_at, created_at

7. **admin_users** (extended)
   - id, username, email, password_hash, role, status, created_at
   - Rôles : super_admin, admin, manager, content_manager

---

## 6. RECOMMANDATIONS FUTURES (Post-Stage)

### Court terme (1-2 mois après stage)
1. Mettre en place scheduling auto (cron job + publier posts programmés)
2. Intégrer webhook Ayrshare pour sync engagements (likes, comments)
3. Ajouter page "IA Suggestions" (historique + accept/reject)
4. Dashboard engagement en temps réel

### Moyen terme (3-6 mois)
1. Content approval workflow (content_manager → manager → published)
2. Multi-language support (EN/FR)
3. Media CDN (AWS S3 ou similar)
4. Bulk scheduling (import CSV posts)
5. Mobile app (PWA ou native iOS/Android)

### Long terme (6+ mois)
1. Sentiment analysis (analyse engagement qualité)
2. AI best-time-to-post (suggestions horaires)
3. Competitor analysis (monitoring réseaux concurrents)
4. Integration CRM (sales funnel tracking)
5. API publique (tiers external apps)

---

## 7. RÉSULTATS TESTS

**À remplir après exécution testing checklist (11 juin)**

- Tests lancés : _____ / 31
- PASS : _____ / 31
- FAIL : _____ / 31
- Blockers trouvés : 
  1. ____________________
  2. ____________________
  3. ____________________

---

## 8. LIVRABLES & DOCUMENTATION

### Fichiers livrés
- `/social-cms/` : Dossier complet CMS
- `README.md` : Setup & features
- `docs/guide-utilisateur.md` : User manual
- `docs/guide-admin.md` : Admin setup & verification
- `TESTING_CHECKLIST.md` : 31 tests (checklist + results)
- `RAPPORT_STAGE.md` : Rapport final (TODO)
- `PRESENTATION.pptx` : Slides démo (TODO)

### État documentation (11 juin)
- ✅ Code + commentaires
- ✅ README (Social CMS section)
- ✅ Guide utilisateur (4 modules détaillés)
- ✅ Guide admin (vérification APIs, debug)
- ✅ Checklist tests (31 items)
- 🔄 Cahier des charges (ce document, skeleton)
- ⏳ Rapport final (TODO - après tests)
- ⏳ PowerPoint présentation (TODO)

---

## 9. BUDGET TEMPS (Prévisionnel)

| Phase | Tâche | Prévu | Réel | Status |
|-------|-------|-------|------|--------|
| Week 1 | Structure + auth | 8h | 8h | ✅ |
| Week 1-2 | Core CMS (media, calendar, generator) | 16h | 18h | ✅ |
| Week 2 | OpenAI + Ayrshare | 8h | 10h | ✅ |
| Week 2 | Tests & debugging | 4h | TBD | 🔄 |
| Week 3 | Doc + IA suggestions | 8h | TBD | 🔄 |
| Week 4+ | Report + Presentation | 12h | TBD | ⏳ |
| **TOTAL** | | **56h** | **~36h + tests** | |

---

## 10. SIGNATURE & APPROBATION

| Rôle | Nom | Signature | Date |
|------|-----|-----------|------|
| Developer | [Stagiaire] | _____________ | ___/___/___ |
| Employer | [Patron] | _____________ | ___/___/___ |

---

## APPENDIX : Commandes Debug Utiles

```bash
# Démarrer MAMP
open -a MAMP

# Vérifier syntaxe PHP
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/generate-content.php

# Voir logs
tail -f /Applications/MAMP/logs/apache_error.log

# Tester API généra
curl -X POST http://localhost:8888/social-cms/api/generate-content.php \
  -H "Content-Type: application/json" \
  -d '{"activity":"Foot","platform":"Instagram"}'

# Exporter env vars (avant tests)
export OPENAI_API_KEY="sk-..."
export AYRSHARE_API_KEY="..."
```

---

**Document créé le 11 juin 2026 - À compléter après tests**
