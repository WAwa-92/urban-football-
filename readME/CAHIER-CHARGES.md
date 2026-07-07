# Cahier des charges — Social CMS (version courte)

Date: 15 juin 2026  
Développé par: wael bakkay  
Client: Urban Center

## Objectif
Centraliser la communication réseaux sociaux de Urban Center: création de contenu, planification, publication multi-réseaux et suivi.

## Ce qui a été fait
- Bibliothèque média (upload, filtres, suppression).
- Calendrier éditorial (création, modification, statuts).
- Générateur de contenu (OpenAI + fallback local).
- Publication multi-plateforme via Ayrshare.
- Dashboard analytics + gestion des utilisateurs + notifications.

## Ce qui manque encore
- Exécuter les tests manuels complets et documenter les résultats.
- Corriger les bugs critiques/importants trouvés en test.
- Finaliser le rapport de stage et la présentation.

## Point d'avancement au 15 juin

### Ce qui est fait aujourd'hui
- Vérification technique rapide: lint PHP OK sur tous les fichiers `social-cms`.
- Audit réservation et cahier des charges maintenus en version courte + archive technique.
- Base projet stable et prête pour la phase de validation finale.

### Ce qu'il reste à faire pour clôturer
1. Exécuter les 31 tests manuels (checklist) et remplir les résultats.
2. Corriger seulement les bugs bloquants/majeurs.
3. Finaliser le cahier des charges avec le bilan réel PASS/FAIL.
4. Terminer les livrables académiques: rapport + présentation.

### Ce qui est envisageable (si temps)
- Module IA "suggestions" (accept/reject/edit + historique).
- Publication automatique planifiée (cron).
- Intégration Meta API en bonus.
- Dashboard engagement plus détaillé.

---

Le détail complet est conservé ci-dessous.

## Le contexte (pourquoi on a besoin de ça)

---

## Qui utilise quoi ?

- **Super Admin** : Accès complet, il configure les trucs techniques
- **Admin** : Gère les users, met à jour les clés API
- **Manager** : Valide les publications avant de les envoyer, regarde les stats
- **Content Manager** : C'est lui qui crée le contenu, utilise le CMS au quotidien
- **Disabled** : Compte désactivé, rien pour lui

---

## Ce qu'on a construit

### Architecture générale
- Backend en PHP (simple et efficace)
- Base de données MySQL
- Frontend avec HTML/CSS/JS (Bootstrap pour l'interface)
- API OpenAI pour l'IA (avec fallback si elle ne marche pas)
- API Ayrshare pour publier sur tous les réseaux en même temps

### Les 5 modules clés

**📁 Bibliothèque Multimédia**
- Uploadez vos images, vidéos, PDFs
- Drag-drop, c'est facile
- Organisez par catégorie (Photos, Vidéos, Flyers, Logos)
- Cherchez par titre
- Supprimez ce que vous n'aimez pas

**📅 Calendrier Éditorial**
- Programmez vos posts à l'avance
- Vue mensuelle (comme un vrai calendrier)
- Vue liste pour vérifier les statuts (brouillon, programmé, publié)
- Modifiez ou supprimez un post à tout moment

**✍️ Générateur de Contenu (avec IA)**
- Vous décrivez : "Je veux un post foot pour demain, target tous les publics, Instagram"
- Le système génère automatiquement un texte + titre + hashtags
- **Le truc cool** : Chaque plateforme reçoit un contenu adapté
  - Instagram = casual, emojis, call-to-action
  - Facebook = détails pratiques, format événement
  - TikTok = ton jeunesse, hook accrocheur
  - LinkedIn = ton professionnel
- S'il y a un problème avec OpenAI, le système utilise des templates (ça marche toujours, juste moins créatif)

**📊 Analytics**
- Voir combien de posts créés
- Quel réseau social revient le plus souvent
- Quel type d'activité (foot, tennis, etc.) est le plus publié
- Simple mais utile pour les tendances

**👥 Gestion des Utilisateurs**
- Admin peut créer/modifier/supprimer des comptes content_manager
- Assigner les rôles
- Activer/désactiver les comptes

**🔔 Notifications**
- "Ta publication est programmée pour demain"
- "N'oublie pas de préparer du contenu cette semaine"
- Tout ça s'affiche sur le dashboard

### Publication Multi-plateforme (Ayrshare)

On a remplacé Buffer (trop compliqué, trop cher) par Ayrshare. Pourquoi ? C'est plus simple : un clic et votre post va sur Facebook, Instagram, TikTok et LinkedIn en même temps.

---

## Fonctionnalités demandées vs ce qu'on a livré

| Fonctionnalité | Demandé ? | Fait ? | État |
|---|---|---|---|
| Upload média (images, vidéos) | ✓ | ✓ | ✅ LIVRÉ |
| Calendrier & programmation | ✓ | ✓ | ✅ LIVRÉ |
| Générateur contenu local | ✓ | ✓ | ✅ LIVRÉ |
| Intégration OpenAI | ✓ | ✓ | ✅ LIVRÉ |
| **BONUS** Contenu adapté par plateforme | - | ✓ | ✅ SURPRISE ! |
| Publication multi-réseaux | ✓ | ✓ | ✅ LIVRÉ (Ayrshare) |
| Gestion utilisateurs | ✓ | ✓ | ✅ LIVRÉ |
| Dashboard analytics | ✓ | ✓ | ✅ LIVRÉ |
| Notifications | ✓ | ✓ | ✅ LIVRÉ |
| Page suggestions IA | (optionnel) | - | 🔄 PAS URGENT |
| API Meta (Facebook) | (bonus) | - | 🔄 BONUS NON FAIT |

---

## Les limites (soyons honnêtes)

1. **Ayrshare coûte de l'argent** - C'est un service payant. On l'a testé mais en sandbox.

2. **L'IA a besoin d'une clé API** - Si OpenAI n'est pas dispo, on utilise des templates. C'est ok mais moins créatif.

3. **Le calendrier c'est basique** - C'est FullCalendar.js simple. Pas de drag-drop entre les jours (aurait pris trop de temps).

4. **Analytics locales seulement** - On compte juste vos posts créés. On ne récupère pas les vrais metrics depuis Facebook/Instagram (likes, comments) en temps réel. Ça se ferait après le stage.

5. **Stockage local** - Les fichiers, on les met sur le serveur. Pas de cloud (S3, etc.). Pour un stage, c'est parfait. Pour la production, faudrait changer.

6. **Pas de scheduler auto** - Les posts programmés, faut une personne pour cliquer "publier". Y a pas de cron qui le fait tout seul. Ça peut être amélioré après.

---

## La base de données (si vous voulez comprendre)

7 tables principales :

1. **cms_media_library** - Les fichiers uploadés (images, vidéos, PDFs)
2. **cms_editorial_calendar** - Les posts programmés (titre, date, plateforme, statut)
3. **cms_social_posts** - Les posts générés avec IA (texte, hashtags, source)
4. **cms_content_templates** - Templates pour fallback (si OpenAI ne marche pas)
5. **cms_analytics** - Stats (nombre de posts, par plateforme, par activité)
6. **cms_notifications** - Messages d'alerte pour les users
7. **admin_users** - Les comptes (username, password, rôle, statut)

---

## Idées pour après le stage

### Court terme (1-2 mois)
- Faire que les posts programmés se publient tout seuls (cron job)
- Récupérer les vrais stats depuis Ayrshare (likes, comments, réactions)
- Page "Suggestions IA" avec historique (accepter/refuser les suggestions)
- Dashboard temps réel avec engagement

### Moyen terme (3-6 mois)
- Workflow d'approbation (content_manager → manager → publié)
- Bilingue (EN/FR)
- Stocker les fichiers dans le cloud (AWS S3)
- Import CSV pour scheduler plein de posts d'un coup
- App mobile (PWA ou native iOS/Android)

### Long terme (6 mois+)
- Analyser le sentiment (les gens aiment vraiment ?)
- Suggestions "meilleur moment pour poster"
- Espionner les concurrents
- Lier à un CRM (vendre plus)
- API publique pour que d'autres apps se connectent

---

## Tests & Résultats

À remplir après avoir lancé la checklist (31 tests) → voir `TESTING_CHECKLIST.md`

- Tests lancés : _____ / 31
- PASS : _____ / 31
- FAIL : _____ / 31

**Bugs trouvés** :
1. (à remplir)
2. (à remplir)

---

## Documents livrés

✅ `/social-cms/` - Le code complet du CMS
✅ `README.md` - Comment c'est installé
✅ `docs/guide-utilisateur.md` - Pour les community managers
✅ `docs/guide-admin.md` - Pour les admins
✅ `TESTING_CHECKLIST.md` - 31 tests à vérifier
✅ `CAHIER-CHARGES.md` - Ce document
🔄 `RAPPORT_STAGE.md` - À écrire après (15-20 pages)
🔄 `PRESENTATION.pptx` - À créer (slides + démo)

---

## Budget temps

| Phase | Tâche | Prévu | Réel | Status |
|---|---|---|---|---|
| W1 | Structure + auth | 8h | 8h | ✅ |
| W1-2 | Core CMS (media, calendar, generator) | 16h | 18h | ✅ |
| W2 | OpenAI + Ayrshare | 8h | 10h | ✅ |
| W2 | Tests & debug | 4h | TBD | 🔄 |
| W3 | Doc + IA suggestions | 8h | TBD | 🔄 |
| W4+ | Rapport + Présentation | 12h | TBD | ⏳ |
| **TOTAL** | | **56h** | **~36h + tests** | |

On a respecté le planning (avec une petite avance même !).

---

## Signature

| Rôle | Nom | Signature | Date |
|---|---|---|---|
| Développeur | [Nom stagiaire] | _____________ | ___/___/___ |
| Employeur | [Patron] | _____________ | ___/___/___ |

---

## Cheat sheet pour déboguer

```bash
# Voir les erreurs
tail -f /Applications/MAMP/logs/php_error.log

# Vérifier que PHP marche
/Applications/MAMP/bin/php/php/bin/php -l social-cms/api/generate-content.php

# Tester une API (ex: génération)
curl -X POST http://localhost:8888/social-cms/api/generate-content.php \
  -H "Content-Type: application/json" \
  -d '{"activity":"Foot","platform":"Instagram"}'

# Exporter les clés API avant de tester
export OPENAI_API_KEY="sk-..."
export AYRSHARE_API_KEY="..."
```

---

**Document créé 11 juin 2026 - À finaliser après les tests**
