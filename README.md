# ☕ CaféStore — Site de Vente en Ligne de Café

Projet PHP/MySQL réalisé dans le cadre du cours Programmation Web II.

---

## 📁 Structure du projet

```
cafestore/
├── index.php                  # Page d'accueil
├── database.sql               # Script SQL (à importer en premier)
├── includes/
│   ├── config.php             # Configuration BDD + constantes
│   ├── functions.php          # Fonctions auth, session, panier, flash
│   ├── header.php             # En-tête commun (front-office)
│   └── footer.php             # Pied de page commun
├── pages/                     # FRONT-OFFICE (accessible aux clients)
│   ├── login.php              # Connexion
│   ├── inscription.php        # Création de compte
│   ├── logout.php             # Déconnexion (détruit la session)
│   ├── produits.php           # Liste + détail des produits
│   ├── panier.php             # Panier + commande
│   ├── commandes.php          # Historique des commandes
│   └── profil.php             # Modification du profil
├── admin/                     # BACK-OFFICE (admin uniquement)
│   ├── header.php             # En-tête admin avec sidebar
│   ├── footer.php             # Pied de page admin
│   ├── index.php              # Tableau de bord (statistiques)
│   ├── produits.php           # CRUD produits
│   ├── categories.php         # CRUD catégories
│   ├── commandes.php          # Gestion + statut des commandes
│   └── utilisateurs.php       # Gestion des utilisateurs
└── assets/
    ├── css/style.css          # Feuille de style principale
    └── images/                # Images produits (à ajouter)
```

---

## 🚀 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web : Apache/Nginx (XAMPP, WAMP, Laragon...)

### Étapes

1. **Copier le dossier** dans le répertoire web :
   - XAMPP : `C:/xampp/htdocs/cafestore/`
   - WAMP  : `C:/wamp64/www/cafestore/`

2. **Créer la base de données** :
   - Ouvrir phpMyAdmin → http://localhost/phpmyadmin
   - Importer le fichier `database.sql`
   - La base `cafestore` sera créée automatiquement

3. **Configurer la connexion** dans `includes/config.php` :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');      // votre identifiant MySQL
   define('DB_PASS', '');          // votre mot de passe MySQL
   define('DB_NAME', 'cafestore');
   define('SITE_URL', 'http://localhost/cafestore');
   ```

4. **Accéder au site** : http://localhost/cafestore

---

## 🔐 Comptes de démonstration

| Rôle      | Email                  | Mot de passe |
|-----------|------------------------|--------------|
| **Admin** | admin@cafestore.com    | admin123     |
| **Client**| sami@email.com         | client123    |

> ⚠️ Ces mots de passe sont hachés en base avec `password_hash()`.
> Pour les recréer, utiliser : `echo password_hash('admin123', PASSWORD_DEFAULT);`

---

## ⚙️ Fonctionnalités

### Front-office (Clients)
- ✅ Inscription avec validation des champs
- ✅ Connexion / Déconnexion (session PHP)
- ✅ Navigation protégée (accès refusé sans compte)
- ✅ Liste des produits avec filtres et recherche
- ✅ Fiche détaillée par produit
- ✅ Panier géré en session (ajout, suppression, vidage)
- ✅ Passation de commande avec adresse de livraison
- ✅ Historique des commandes
- ✅ Modification du profil et du mot de passe

### Back-office (Administrateur)
- ✅ Authentification admin sécurisée
- ✅ Protection de toutes les pages admin
- ✅ Tableau de bord avec statistiques
- ✅ Gestion des produits : liste, ajout, modification, suppression
- ✅ Gestion des catégories : liste, ajout, modification, suppression
- ✅ Gestion des commandes : liste, détail, changement de statut
- ✅ Gestion des utilisateurs : liste, rôles, suppression

---

## 🗄️ Modèle de la base de données

```
utilisateurs (id, nom, prenom, email, mot_de_passe, telephone, adresse, role, date_inscription)
      │
      └──< commandes (id, utilisateur_id, date_commande, statut, total, adresse_livraison)
                │
                └──< lignes_commande (id, commande_id, produit_id, quantite, prix_unitaire)
                                              │
categories (id, nom, description)            │
      │                                       │
      └──< produits (id, nom, description, prix, stock, image, categorie_id, origine, intensite, date_ajout)
```

---

## 🔒 Sécurité

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Requêtes préparées (protection contre SQL Injection)
- `htmlspecialchars()` sur toutes les sorties (protection XSS)
- Vérification de session sur chaque page protégée
- Séparation front-office / back-office

---

## 👨‍💻 Technologies utilisées

- **PHP 8+** — Logique serveur, sessions, formulaires
- **MySQL** — Base de données relationnelle
- **HTML5 / CSS3** — Structure et style natifs (sans framework CSS)
- **Google Fonts** — Playfair Display + DM Sans

---

*Projet réalisé pour le cours Programmation Web II — 2025/2026*
