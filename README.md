# HCPVision

Application web de suivi et de visualisation des enquêtes de terrain, développée en PHP et PostgreSQL.

## Fonctionnalités

- Tableau de bord des indicateurs et de l'avancement des enquêtes.
- Suivi des enquêteurs et de leurs performances.
- Analyse des résultats par région et par milieu.
- Consultation des anomalies et génération de rapports.
- Authentification, gestion des comptes et rôles utilisateurs.

## Technologies

PHP avec une architecture MVC, PostgreSQL, PDO, HTML, CSS, JavaScript, Bootstrap, Chart.js et DataTables. Les bibliothèques front-end sont chargées depuis des CDN : une connexion Internet est nécessaire pour ces ressources.

## Installation locale

Prérequis : PHP 8.0 ou supérieur, extension `pdo_pgsql`, PostgreSQL et client `psql`.

```powershell
git clone https://github.com/idriss-aaidoun/hcpvison.git
cd hcpvison
createdb -U postgres hcpvision
psql -U postgres -d hcpvision -v ON_ERROR_STOP=1 -f database/schema.sql
```

Définir les variables de connexion dans le terminal qui lancera PHP :

```powershell
$env:PGHOST = 'localhost'
$env:PGPORT = '5432'
$env:PGDATABASE = 'hcpvision'
$env:PGUSER = 'postgres'
$env:PGPASSWORD = 'votre_mot_de_passe_local'
php -S localhost:8000 -t public public/index.php
```

Ouvrir `http://localhost:8000/login`. Le fichier `.env.example` décrit les variables attendues ; les fichiers `.env` ne sont pas chargés automatiquement.

Le code existant crée un compte de démonstration `admin` / `admin123` lorsque la table des utilisateurs est vide. Modifier immédiatement ce mot de passe dans la gestion du compte. Supprimer ou remplacer cet amorçage automatique avant toute mise en production.

## Base de données

`database/schema.sql` contient les tables, séquences, vues et contraintes, sans données personnelles ni comptes exportés. Les tableaux restent vides tant que des données d'enquête compatibles n'ont pas été importées. Le dump original et les archives restent locaux et sont exclus par `.gitignore`.

## Organisation

```text
app/controllers/    Contrôleurs et traitements
app/models/         Accès aux données
app/views/          Interfaces et mises en page
config/             Configuration
database/schema.sql Structure PostgreSQL sans données
public/             Point d'entrée et ressources CSS/JavaScript
routes.php          Routage
```

## Vérification et limites

Vérifier la syntaxe des fichiers PHP avec `php -l chemin/du/fichier.php`. Un contrôle de syntaxe ne remplace pas un essai fonctionnel avec PostgreSQL et des données de test.

Ce dépôt publie le code source. L'hébergement de l'application nécessite un serveur PHP, PostgreSQL et une configuration adaptée ; GitHub Pages ne peut pas exécuter cette application PHP.
