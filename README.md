
# 🩺 HealthBridge

HealthBridge est une application de santé collaborative qui facilite l'interaction entre les patients et les professionnels de santé. Elle offre une plateforme centralisée pour la prise de rendez-vous, le diagnostic assisté, la discussion communautaire et l'achat de produits pharmaceutiques.

🔗 Dépôt GitHub : [https://github.com/Therealcydex/HeathAPP]
---

## 📑 Table des matières

- [🧠 Description](#🧠-description)
- [🚀 Fonctionnalités](#🚀-fonctionnalités)
- [🛠️ Installation](#🛠️-installation)
- [📦 Utilisation](#📦-utilisation)
- [🤝 Contribution](#🤝-contribution)
- [🛡️ Licence](#🛡️-licence)
- [👥 Équipe](#👥-équipe)

---

## 🧠 Description

HealthBridge vise à moderniser les services de santé en connectant efficacement médecins et patients à travers une application multiplateforme :

- 💻 Version Java (JavaFX) : pour poste bureau — structure MVC, interface ergonomique
- 🌐 Version Web (Symfony + XAMPP) : accès via navigateur pour utilisateurs et administrateurs

Elle permet :

- La réservation de rendez-vous médicaux à distance
- Un système de quiz basé sur les symptômes
- Un forum entre patients et médecins
- Une boutique de produits pharmaceutiques avec système de commande

---

## 🚀 Fonctionnalités

- 📅 Prise de rendez-vous avec sélection du médecin et du créneau
- 🧪 Quiz santé interactif basé sur les symptômes
- 💬 Forum santé entre utilisateurs et médecins
- 🛍️ Commande en ligne de produits pharmaceutiques
- 🔐 Authentification sécurisée avec rôles (Patient, Médecin, Admin)
- 🌍 Traduction automatique des messages (via API Google Translate)

---

## 🛠️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Therealcydex/HeathAPP.git
```

### 2. Version Java (JavaFX)

- Ouvrir le dossier `HealthBridge-Java` avec IntelliJ IDEA
- Configurer la base de données MySQL nommée `finale`
- Vérifier la connexion JDBC dans les fichiers de configuration

### 3. Version Web (Symfony)

- Ouvrir le dossier `HealthBridge-Web` avec VS Code
- Utiliser XAMPP pour :
  - Démarrer Apache et MySQL
  - Créer une base de données nommée `finale`
- Modifier le fichier `.env` de Symfony pour pointer vers votre base

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/finale"
```

- Installer les dépendances PHP :

```bash
composer install
```

- Lancer le serveur Symfony :

```bash
symfony server:start
```

---

## 📦 Utilisation

### Java (JavaFX)

- Lancer le fichier `MainApp.java` depuis IntelliJ
- Naviguer entre les modules : authentification, rendez-vous, quiz, forum, pharmacie

### Web (Symfony)

- Accéder à `http://localhost:8000` via le navigateur
- Utiliser les rôles définis (Patient / Docteur / Admin) pour explorer l'interface

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork du dépôt
2. Créer une nouvelle branche : `git checkout -b feature/nouvelle-fonctionnalité`
3. Commit : `git commit -m "Ajout de fonctionnalité"`
4. Push : `git push origin feature/nouvelle-fonctionnalité`
5. Créer une Pull Request

---

## 🛡️ Licence

Projet pédagogique réalisé à l’école **ESPRIT – 3A64**.  
Toute utilisation ou diffusion en dehors du cadre académique nécessite une autorisation préalable.

---

## 👥 Équipe

- 🎓 Khouloud Abdelmalek  
- 🎓 Wejdane Telli  
- 🎓 Ahmed Wassim Hamouda  
- 🎓 Makhtoumi Mohamed Ali  
- 🎓 Jaouher Bziouech  
- 🎓 Houssem Eddine Abdelal  
