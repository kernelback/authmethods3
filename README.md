# 🔐 Projet Final — Méthodes d’Authentification 1 (IC3 S2) — Efrei 2024/2025

## 🏫 Informations générales

- **École :** Efrei Paris  
- **Intervenant :** Mr. Raouf Amdouni  
- **Promotion :** L3 Cyber & IA - X-BAC-ICS-3 - XICS601 - Groupe PAR01 – 2425P
- **Groupe :** Chaymaa, Nourhan, Abdelhak, Thibault, Abdelhadi

---

## 🎯 Objectif du projet

Améliorer la sécurité d’un blog Symfony existant en intégrant un système d’authentification avancé **et** une **API externe météo**. Le but est de sécuriser les routes sensibles et d’enrichir l’expérience utilisateur via des services modernes.

---

## 1. 🔐 Connexion sécurisée à GitHub depuis Codespaces

- Création et ajout d’une clé SSH dans GitHub
- Test de fonctionnement via un commit + push
---

## 2. 🧩 Authentification sécurisée via GitHub (OAuth2) 

- Remplacement complet du login formulaire par un système OAuth2 via **GitHub**
- Intégration de `HWIOAuthBundle`
- Création automatique de l’utilisateur s’il n’existe pas
- Attribution du rôle `ROLE_USER` par défaut, possibilité de promotion `ROLE_ADMIN`
- Système de **Rate Limiting** (`login_throttling`)
- Sécurité des credentials via le fichier `.env.local`

### ✅ Résultat :
- Les routes `/admin` sont maintenant protégées par `#[IsGranted('ROLE_ADMIN')]`
- Gestion des erreurs d’authentification incluse

### 📦 Technologies :
- Symfony 7
- HWIOAuthBundle
- Doctrine
- CSRF + RateLimiter

---

## 3. ☀️ Intégration de l’API OpenWeatherMap 

- Création d’un service `WeatherService` en Symfony
- Appel API sécurisé avec une clé stockée dans `.env.local`
- Ajout d’un formulaire pour que l’utilisateur choisisse une **ville dynamique**
- Affichage en temps réel de :
  - La température actuelle
  - La condition météo (ex : pluie, ciel dégagé...)

### ✅ Résultat :
- Affichage météo visible sur la page d’accueil `/blog`
- Exemple de réponse affichée :  
  > Météo à Paris — Température : 18°C — Conditions : ciel dégagé

### 🔐 Variables d’environnement :
```dotenv
GITHUB_CLIENT_ID=xxx
GITHUB_CLIENT_SECRET=xxx
OPENWEATHER_API_KEY=xxx
