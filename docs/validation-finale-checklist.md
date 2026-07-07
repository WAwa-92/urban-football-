# Validation finale — checklist opérationnelle

Date: 25/06/2026
Objectif: fermer la validation fonctionnelle avec un passage rapide, reproductible et documentable.

## 1) Pré-contrôles techniques
- [ ] Ouvrir la page d'accueil et vérifier que le menu charge correctement.
- [ ] Vérifier que l'offre affichée reste: Football, Padel, Fitness.
- [ ] Vérifier que la page réservations ne montre pas de sport supprimé comme option de filtre.

## 2) Parcours authentification (rôle par rôle)

### A. Compte `admin` / `manager`
- [ ] Connexion via page login.
- [ ] Redirection attendue vers `/admin/dashboard.php`.
- [ ] Bouton "Dashboard" dans la nav pointe vers l'admin dashboard.
- [ ] Déconnexion renvoie à un état non connecté.

### B. Compte `content_manager`
- [ ] Connexion via page login.
- [ ] Redirection attendue vers `/social-cms/dashboard.php`.
- [ ] Accès pages CMS: calendrier éditorial, media, générateur contenu.
- [ ] Déconnexion propre côté CMS.

### C. Compte utilisateur standard (site)
- [ ] Connexion utilisateur standard.
- [ ] Accès espace compte / mes réservations.
- [ ] Déconnexion propre côté site.

## 3) Parcours réservation
- [ ] Créer une nouvelle réservation Football.
- [ ] Créer une nouvelle réservation Padel.
- [ ] Créer une nouvelle réservation Fitness.
- [ ] Vérifier affichage dans "Mes réservations".
- [ ] Vérifier filtre statut (confirmée / en attente / annulée).
- [ ] Vérifier annulation d'une réservation.

## 4) Contrôle cohérence UI
- [ ] Un seul style cohérent de boutons dans la navigation.
- [ ] Aucun doublon de CTA "Réserver" en nav.
- [ ] Liens dashboard/logout visibles selon l'état de session.

## 5) Contrôle legacy
- [ ] Si anciennes réservations Tennis existent, elles sont affichées comme "Activité archivée".
- [ ] Aucun nouveau formulaire ne propose Tennis.

## 6) Critères de sortie (GO)
- [ ] Aucun blocage sur connexion/redirect.
- [ ] Aucun endpoint critique en erreur 500.
- [ ] Parcours de réservation complet validé de bout en bout.
- [ ] Captures prises pour le dossier de stage.
