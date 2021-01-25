# Blog

Test technique demandé par AXECIBLES

### Installation

```
composer install
```

### Connection à la base donnée

Pour vous connectez à la base de donnée il faudra modifier cette ligne dans le fichier ``.env``

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:8889/blog_axecibles"
```

### Création de la base de donnée

```
php bin/console d:d:c
php bin/console d:s:u --force
```
### Lancement du serveur

```
php -S 0.0.0.0:8000 -t public  
```