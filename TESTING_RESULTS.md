# Liste de Test - Social CMS
## 11 Juin 2026

OK, vous trouvez ci-dessous 31 points à vérifier pour s'assurer que tout marche. C'est pas un test d'ingénieur nasa, juste des vérifications pratiques.

---

## Partie 1 : Connexion & Accès (6 tests)

**1. Vous pouvez vous connecter en admin**
- [ ] PASS [ ] FAIL
- Allez sur `/social-cms/pages/login.php`, entrez login admin, vous devez arriver sur le dashboard
- Problème rencontré ? 

**2. Vous pouvez vous connecter en content_manager**
- [ ] PASS [ ] FAIL
- Même chose avec un compte content_manager (ex: content1/test123)
- Problème rencontré ? 

**3. Un compte désactivé ne peut pas se connecter**
- [ ] PASS [ ] FAIL
- Essayez avec un compte "disabled" → vous devez avoir un message d'erreur
- Problème rencontré ? 

**4. Les formulaires POST sont protégés contre les attaques (CSRF)**
- [ ] PASS [ ] FAIL
- Ouvrez la console (F12), éteignez JavaScript, essayez de soumettre un formulaire
- Ça doit rejeter avec une erreur (ou rediriger vers login)
- Problème rencontré ? 

**5. Un user content_manager ne peut pas accéder à "Gestion des utilisateurs"**
- [ ] PASS [ ] FAIL
- Connecté en content_manager, allez sur `/social-cms/admin/users.php`
- Ça doit vous rejeter (403 ou redirect login)
- Problème rencontré ? 

**6. Les sessions expirent (optionnel, skip si pas le temps)**
- [ ] PASS [ ] FAIL [ ] SKIP
- Connectez-vous, attendez 30min, essayez d'accéder une page protégée
- Vous devez être redirigé vers login
- Problème rencontré ? 

---

## Partie 2 : Gestion des Utilisateurs (5 tests)

**7. Créer un nouvel utilisateur**
- [ ] PASS [ ] FAIL
- Admin : Allez dans "Gestion des utilisateurs", créez un user (ex: testuser / test123)
- Le user doit apparaître dans la liste
- Problème rencontré ? 

**8. Modifier un utilisateur (changer le rôle)**
- [ ] PASS [ ] FAIL
- Modifiez le user créé : changez son rôle de manager à content_manager
- La modification doit être sauvée et visible en liste
- Problème rencontré ? 

**9. Supprimer un utilisateur**
- [ ] PASS [ ] FAIL
- Supprimez le user testuser
- Il ne doit plus être dans la liste
- Problème rencontré ? 

**10. Pas de doublon de username**
- [ ] PASS [ ] FAIL
- Essayez de créer un user avec un username qui existe déjà
- Le système doit rejeter (message d'erreur ou feedback)
- Problème rencontré ? 

**11. Voir la liste de tous les users**
- [ ] PASS [ ] FAIL
- Allez dans "Gestion des utilisateurs", vous devez voir tous les users avec leurs colonnes (id, username, email, rôle, statut)
- Ça doit être lisible et clair
- Problème rencontré ? 

---

## Partie 3 : Bibliothèque Média (8 tests)

**12. Uploader une image (JPEG ou PNG)**
- [ ] PASS [ ] FAIL
- Allez dans "Bibliothèque Multimédia", drag-drop une image (~2MB), selectionnez catégorie "Photos"
- L'image doit apparaître en grille après upload
- Problème rencontré ? 

**13. Uploader une vidéo (MP4)**
- [ ] PASS [ ] FAIL
- Même procédé avec une vidéo (~5MB), catégorie "Vidéos"
- La vidéo doit apparaître avec une icône différente des photos
- Problème rencontré ? 

**14. Uploader un PDF**
- [ ] PASS [ ] FAIL
- Upload un PDF, catégorie "Flyers"
- Le PDF doit être uploadé et visible
- Problème rencontré ? 

**15. Rejeter un format non supporté**
- [ ] PASS [ ] FAIL
- Essayez d'uploader un .exe ou un .mp3
- Le système doit le rejeter avec un message "Format non supporté" ou similaire
- Problème rencontré ? 

**16. Rejeter un fichier trop volumineux (>50MB)**
- [ ] PASS [ ] FAIL
- Essayez un fichier de 60MB
- Ça doit être rejeté : "Fichier trop volumineux" ou similaire
- Problème rencontré ? 

**17. Filtrer les médias par catégorie**
- [ ] PASS [ ] FAIL
- Créez quelques médias (photos, vidéos), puis sélectionnez "Vidéos" dans le dropdown de catégorie
- Seules les vidéos doivent s'afficher
- Problème rencontré ? 

**18. Chercher un média par titre**
- [ ] PASS [ ] FAIL
- Dans la barre de recherche, tapez un titre partiel (ex: "logo")
- Ça doit afficher seulement les médias avec "logo" dans le nom
- Problème rencontré ? 

**19. Supprimer un média**
- [ ] PASS [ ] FAIL
- Cliquez delete sur un média
- Il disparaît de la grille
- Problème rencontré ? 

---

## Partie 4 : Calendrier Éditorial (4 tests)

**20. Créer un post programmé**
- [ ] PASS [ ] FAIL
- Allez dans "Calendrier Éditorial", créez un post :
  - Titre : "Tournoi foot samedi"
  - Contenu : "Venez jouer samedi 14h"
  - Plateforme : Facebook + Instagram
  - Date : demain
  - Statut : draft
- Le post doit être créé et visible en liste/calendrier
- Problème rencontré ? 

**21. Voir les posts en calendrier mensuel**
- [ ] PASS [ ] FAIL
- Allez dans la vue calendrier (vue mensuelle)
- Vos posts doivent être affichés aux bonnes dates
- Problème rencontré ? 

**22. Filtrer les posts par statut**
- [ ] PASS [ ] FAIL
- Créez quelques posts (draft, scheduled), puis filtrez par statut=draft
- Seuls les brouillons s'affichent
- Problème rencontré ? 

**23. Modifier et supprimer un post**
- [ ] PASS [ ] FAIL
- Modifiez un post (changez le titre par exemple)
- La modification est sauvée
- Supprimez-le ensuite → il disparaît
- Problème rencontré ? 

---

## Partie 5 : Générateur de Contenu (3 tests)

**24. Générer du contenu (sans OpenAI, juste les templates)**
- [ ] PASS [ ] FAIL
- Allez dans "Générateur de contenu"
- Remplissez : activité=Foot, date=demain, public=Tous, plateforme=Instagram
- Cliquez "Générer"
- Vous devez voir un résultat avec titre, texte, hashtags, et source="generated" (ou "ai" si OpenAI marche)
- Problème rencontré ? 

**25. Chaque plateforme génère un contenu DISTINCT**
- [ ] PASS [ ] FAIL
- Générez 3 fois : pour Instagram, Facebook, TikTok
- Regardez les 3 résultats → ils doivent être sensiblement DIFFÉRENTS
  - Instagram : plus casual, fun
  - Facebook : plus formel, détails pratiques
  - TikTok : hook accrocheur, ton jeunesse
- Si tous les 3 sont identiques, c'est un problème
- Problème rencontré ? 

**26. OpenAI fonctionne (si clé API configurée)**
- [ ] PASS [ ] FAIL [ ] SKIP
- Avant : exportez `export OPENAI_API_KEY="votre_clé"`
- Générez du contenu
- Si ça marche : vous devez voir source="ai" (pas "generated")
- Le contenu devrait être plus long/détaillé
- Si pas de clé : skip ce test
- Problème rencontré ? 

---

## Partie 6 : Publication Ayrshare (3 tests)

**27. Créer et sauver un post**
- [ ] PASS [ ] FAIL
- Générez du contenu, il doit être automatiquement sauvé en BD
- Allez dans "Calendrier", vous devez voir le post créé
- Problème rencontré ? 

**28. Publier un post via Ayrshare**
- [ ] PASS [ ] FAIL
- Avant : exportez `export AYRSHARE_API_KEY="votre_clé"`
- Prenez un post, essayez de le publier sur Facebook + Instagram via l'API Ayrshare
- Le système doit répondre avec un ID et "success": true
- Problème rencontré ? 

**29. Gestion d'erreur Ayrshare**
- [ ] PASS [ ] FAIL
- Essayez de publier SANS que la clé API soit configurée
- Le système doit vous donner une erreur claire (pas un crash PHP)
- Problème rencontré ? 

---

## Partie 7 : Sécurité (2 tests)

**30. SQL injection : la recherche est protégée**
- [ ] PASS [ ] FAIL
- Allez dans "Bibliothèque Multimédia"
- Dans la barre de recherche, tapez : `'; DROP TABLE cms_media_library; --`
- La table ne doit PAS être supprimée
- Ça devrait juste faire une recherche qui retourne rien
- Problème rencontré ? 

**31. XSS : les scripts ne s'exécutent pas**
- [ ] PASS [ ] FAIL
- Créez un post avec titre : `<script>alert('XSS')</script>`
- Sauvegardez-le, puis consultez le post
- Le code ne doit PAS s'exécuter
- Vous devez voir le texte littéralement à l'écran (pas de popup)
- Problème rencontré ? 

---

## Récapitulatif

**Total tests** : 31
**PASS** : ___ / 31
**FAIL** : ___ / 31
**SKIP** : ___ / 31

---

## Bugs trouvés (à décrire)

### Bug #1
- Test(s) affectés : 
- Description : 
- Sévérité : (Critique / Important / Mineur)
- À corriger demain ? (OUI / NON)

### Bug #2
- Test(s) affectés : 
- Description : 
- Sévérité : (Critique / Important / Mineur)
- À corriger demain ? (OUI / NON)

---

## Notes générales

- Comment ça se passe ? (Bon ? Pas terrible ?)
- Surprises positives ?
- Points à améliorer ?

---

**Testé le** : ___/___/_____ à ___:___ par _____________
