# Vhost Apache et fichier hosts

Configuration locale mise en place le 29.10.2026.

## Prérequis installés

| Élément | Valeur |
|---|---|
| Apache | 2.4.55 (Chocolatey), service Windows `Apache` |
| Racine Apache | `C:\Users\Admin\AppData\Roaming\Apache24` |
| PHP | 8.5.3 NTS, `C:\tools\php85` |
| Mode PHP | CGI via `php-cgi.exe` (PHP NTS, donc pas de `mod_php`) |
| URL locale | <http://achievement-tracker.local> |
| DocumentRoot | `C:\Users\Admin\Documents\projet\achievement-tracker\public` |

## Fichier hosts

Ajout dans `C:\Windows\System32\drivers\etc\hosts` :

```text
# achievement-tracker (developpement local)
127.0.0.1 achievement-tracker.local
127.0.0.1 www.achievement-tracker.local
```

## Configuration Apache

Modules activés dans `conf/httpd.conf` :

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

`actions_module`, `alias_module` et `cgi_module` sont déjà chargés par la
configuration par défaut d'Apache.

`Listen 80` remplace `Listen 8080`, et le fichier de vhosts est inclus :

```apache
Include conf/extra/httpd-vhosts.conf
```

Bloc ajouté dans `conf/extra/httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    ServerName achievement-tracker.local
    ServerAlias www.achievement-tracker.local
    DocumentRoot "C:/Users/Admin/Documents/projet/achievement-tracker/public"

    <Directory "C:/Users/Admin/Documents/projet/achievement-tracker/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php

        <FilesMatch "\.php$">
            SetHandler application/x-httpd-php
        </FilesMatch>
    </Directory>

    # PHP 8.5 NTS : execution via php-cgi en mode CGI.
    ScriptAlias /php-cgi/ "C:/tools/php85/"
    <Directory "C:/tools/php85">
        AllowOverride None
        Options None
        Require local
    </Directory>
    Action application/x-httpd-php "/php-cgi/php-cgi.exe"

    ErrorLog "logs/achievement-tracker-error.log"
    CustomLog "logs/achievement-tracker-access.log" combined
</VirtualHost>
```

## Commandes utiles

```powershell
# Verifier la syntaxe Apache
C:\Users\Admin\AppData\Roaming\Apache24\bin\httpd.exe -t

# Redemarrer Apache
Restart-Service Apache

# Tester l'application
Invoke-WebRequest http://achievement-tracker.local/test -UseBasicParsing
```

## Alternative sans Apache

Le projet peut aussi être lancé avec le serveur intégré de PHP :

```powershell
php -S localhost:8080 -t public public/index.php
```

ou, depuis Composer :

```powershell
composer start
```

L'URL est alors <http://localhost:8080>.
