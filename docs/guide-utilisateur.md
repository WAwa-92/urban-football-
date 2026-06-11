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

## Espace Social CMS (Pour les Community Managers)

### Comment se connecter
Rendez-vous sur `/social-cms/pages/login.php` avec votre login de content manager. Une fois connecté, vous arrivez sur le dashboard avec tous les outils pour gérer les réseaux sociaux de Urban Center.

### Les 4 outils à connaître

**Bibliothèque Multimédia** — C'est votre classeur photo/vidéo. Vous y mettez tous les visuels (photos des terrains, vidéos de tournois, logos, affiches).

**Générateur de Contenu** — L'outil malin qui crée automatiquement les posts pour vous. Vous dites "foot ce samedi" et il génère un texte prêt à poster sur Insta, Facebook, etc.

**Calendrier Éditorial** — Vous programmez vos publications ici. Les posts en brouillon, ceux à venir, ceux déjà publiés. Tout au même endroit.

**Analytics** — Pour voir si ça marche. Combien de posts créés cette semaine, quelle plateforme cartonne le plus, etc.

---

### Stocker vos médias (images, vidéos)

La première chose : avoir de beaux visuels. C'est simple.

1. Allez dans **Bibliothèque Multimédia**
2. Glissez-déposez vos fichiers (ou cliquez pour sélectionner)
3. Sélectionnez la catégorie : Photos, Vidéos, Flyers, etc.
4. C'est uploadé ! Le fichier apparaît en grille avec les autres

Vous pouvez ensuite filtrer par catégorie pour retrouver vos images rapidement, ou chercher par titre si vous avez nommé vos fichiers intelligemment (ex: "Tournoi foot juin", "Logo Urban").

**Points importants** :
- Taille max : 50MB (normalement c'est large pour vos fichiers)
- Formats supportés : JPEG, PNG, MP4 (vidéo), PDF
- Si vous envoyez un fichier .exe ou un truc bizarrre, ça sera rejeté pour des raisons de sécu

---

### Générer du contenu pour vos réseaux

C'est le cœur du système. Vous décrivez ce que vous voulez, et l'IA génère un post adapté à chaque plateforme.

**Le workflow** :
1. Cliquez sur **Générateur de contenu**
2. Remplissez rapidement :
   - **Activité** : Foot, Tennis, Padel, Yoga, Fitness (ce que vous proposez)
   - **Date** : quand ça a lieu ou quand vous voulez publier
   - **Public** : Tous, Enfants, Adultes, ou Confirmés
   - **Plateforme(s)** : cochez Facebook, Instagram, TikTok, LinkedIn (ou plusieurs)
3. Tapez **Générer**
4. Hop ! Un post s'affiche avec :
   - Un titre accrocheur
   - Un texte adapté à la plateforme
   - Des hashtags pertinents (6-12 selon le réseau)
   - La source : "ai" si OpenAI a généré, "generated" si c'est le fallback

**Exemple concret** :
- Vous générez pour Instagram → ton fun, emojis, call-to-action "Venez tenter !"
- Vous générez pour Facebook → plus formel, détails d'horaires, infos pratiques
- Vous générez pour TikTok → hook accrocheur "Vous pensez faire 5-0 au padel ?", ton jeunesse
- Vous générez pour LinkedIn → ton professionnel "Rejoignez notre communauté d'athlètes"

Chaque plateforme a sa personnalité. Le système le sait et adapte.

**Astuce** : Vous pouvez copier le résultat et le modifier avant de programmer. C'est jamais 100% parfait, mais c'est un super point de départ pour gagner du temps.

---

### Programmer vos publications

Une fois que vous avez un texte qui vous plaît, vous le mettez en calendrier.

1. Allez dans **Calendrier Éditorial**
2. Créez un nouveau post :
   - Collez votre texte
   - Choisissez la/les plateforme(s)
   - Dites quelle date/heure
   - Statut : "draft" (brouillon) si vous êtes pas encore sûr, "scheduled" sinon
3. Sauvegardez
4. Voir votre post au calendrier (vue mensuelle ou liste)
5. Avant de publier, vérifiez que c'est bon, puis changez le statut à "published"

Vous pouvez aussi modifier un post déjà programmé si vous changez d'avis.

---

### Regarder les stats

Pour savoir si ça marche ou pas.

1. Allez dans **Analytics**
2. Vous verrez des graphiques :
   - Combien de posts créés
   - Quelle plateforme revient le plus souvent
   - Quel type d'activité (foot, tennis, etc.) est le plus publié

C'est simple mais utile pour repérer les tendances et voir si vous communiquez équilibré sur toutes les activités.

---

### Déboguer la génération (si quelque chose cloche)

Si la génération ne marche pas comme prévu, voici comment regarder ce qui se passe sous le capot.

1. Ouvrez les outils du navigateur avec F12
2. Allez dans l'onglet **Network**
3. Lancez une génération
4. Cherchez la requête `POST /social-cms/api/generate-content.php`
5. Regardez la réponse. Ça devrait montrer :
   - `title` : le titre généré
   - `content` : le texte
   - `hashtags` : les mots-clés
   - `source` : "ai" ou "generated"

**Si source = "ai"** : L'API OpenAI a marché, c'est du vrai contenu IA.
**Si source = "generated"** : L'API OpenAI n'était pas dispo (ou clé manquante), donc le système utilise des templates prédéfinis. C'est moins créatif mais ça marche.