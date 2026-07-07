# Rapport de stage — Urban Center

**Intitulé académique**  
Conception et développement d'une plateforme de gestion et de diffusion de contenus pour les réseaux sociaux d'une entreprise de sport et loisirs.

**Période** : [à compléter]  
**Stagiaire** : wael bakkay  
**Entreprise** : Urban Center

---

## 1. Contexte général

Urban Center est une entreprise de sport et loisirs avec plusieurs activités (Urban Center / Urban Fitness / Urban Kids).  
L'entreprise dispose d'un site vitrine, mais la gestion des contenus réseaux sociaux était majoritairement manuelle.

Le besoin principal était de créer un outil simple et exploitable pour centraliser:
- la préparation des publications,
- la planification éditoriale,
- la diffusion multi-réseaux,
- le suivi des actions marketing.

---

## 2. Problématique

Avant le projet:
- contenus dispersés,
- pas de workflow unique,
- peu d'automatisation,
- faible traçabilité des publications.

Question de départ:  
**Comment construire une plateforme web légère, utilisable en interne, qui facilite la création et la gestion de contenus sociaux pour une entreprise sportive ?**

---

## 3. Objectifs du stage

### Objectif général
Développer une application web de gestion et de diffusion de contenus réseaux sociaux.

### Objectifs spécifiques
1. Créer une bibliothèque multimédia (photos, vidéos, flyers, logos).
2. Implémenter un calendrier éditorial (création, planification, statut).
3. Mettre en place un générateur de contenu (titre + texte + hashtags).
4. Ajouter une intégration IA (OpenAI) avec fallback local.
5. Permettre la publication multi-réseaux (Ayrshare).
6. Fournir un tableau de bord marketing.

---

## 4. Méthodologie de travail

Le travail a été réalisé par itérations:
- cadrage besoin,
- développement backend/frontend,
- intégration API,
- stabilisation,
- documentation.

Approche adoptée:
- livrer d'abord un MVP fonctionnel,
- améliorer ensuite la qualité (UX, sécurité, documentation),
- garder des améliorations bonus pour la fin de stage.

---

## 5. Réalisations techniques

## 5.1 Stack technique
- Frontend: HTML, CSS, JavaScript, Bootstrap
- Backend: PHP
- Base de données: MySQL
- APIs: OpenAI + Ayrshare

## 5.2 Modules livrés
- Authentification et rôles utilisateurs
- Bibliothèque multimédia (upload/filtre/recherche/suppression)
- Calendrier éditorial (CRUD + statuts)
- Générateur de contenus (local + IA)
- Publication multi-plateforme
- Analytics dashboard
- Notifications

## 5.3 Points techniques notables
- Fallback local si l'API OpenAI est indisponible
- Adaptation du ton selon la plateforme (Instagram/Facebook/TikTok/LinkedIn)
- Publication via Ayrshare
- Vérification syntaxe PHP sur l'ensemble du module

---

## 6. Résultats obtenus

### Fonctionnel
Le module Social CMS est utilisable et couvre les besoins essentiels du cahier des charges.

### Métier
L'entreprise dispose d'un outil directement exploitable pour la communication digitale.

### Qualité
- documentation utilisateur/admin en place,
- audit cahier des charges disponible,
- audit réservation 5.2 disponible.

---

## 7. Difficultés rencontrées et solutions

### Difficultés
- Uniformiser les besoins réels vs les besoins académiques
- Choisir une API de publication adaptée au contexte stage
- Rendre les contenus générés plus naturels par plateforme

### Solutions
- Passage de Buffer à Ayrshare (intégration plus simple)
- Prompting + logique locale améliorée
- Documentation progressive et simplifiée

---

## 8. État actuel et reste à faire

## Déjà fait
- Développement principal terminé
- Intégrations IA et publication finalisées
- Documentation principale rédigée

## Reste à faire
1. Exécuter la campagne complète de tests manuels
2. Corriger les anomalies critiques/majeures
3. Finaliser le cahier des charges avec résultats de test
4. Préparer la soutenance (slides + script)

---

## 9. Pistes d'amélioration (post-stage)

- Module IA "suggestions" (accept/reject/edit)
- Publication automatique planifiée
- Intégration Meta API (bonus)
- Analytics d'engagement plus poussés
- Monitoring et durcissement production

---

## 10. Conclusion

Ce stage a permis de livrer une plateforme utile, concrète et directement exploitable par l'entreprise.  
Le cœur du projet est abouti. La dernière phase concerne la validation finale, les corrections ciblées et la préparation de soutenance.

---

## Annexes (à compléter)

- A. Schéma base de données
- B. Captures d'écran des modules
- C. Exemples de contenus générés
- D. Checklist de tests (31 cas)
- E. Glossaire
