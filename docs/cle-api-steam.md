# Obtenir la clé API Steam

La clé API est personnelle et liée à un compte Steam. Elle ne peut pas être
générée à ta place : il faut être connecté à Steam dans un navigateur.

## Étapes

1. Se connecter sur <https://steamcommunity.com> avec le compte Steam concerné.
2. Ouvrir <https://steamcommunity.com/dev/apikey>.
3. Renseigner un nom de domaine. En développement local, `localhost` suffit
   (Steam ne vérifie pas le domaine pour un usage local).
4. Accepter les conditions, puis cliquer sur **Register**.
5. Copier la clé affichée (32 caractères hexadécimaux).
6. Ouvrir le fichier `.env` à la racine du projet et renseigner :

   ```dotenv
   STEAM_API_KEY=0123456789ABCDEF0123456789ABCDEF
   ```

7. Vérifier que l'application la détecte :

   <http://achievement-tracker.local/test>

   La réponse JSON doit contenir `"steam_api_key": "configuree"`.

## Remarques

- Le fichier `.env` est ignoré par Git. Ne jamais publier la clé ni la mettre
  dans `.env.example`.
- Le compte Steam doit avoir un **profil public** pour que la bibliothèque et
  les succès soient lisibles par l'API (sinon `GetOwnedGames` renvoie un profil
  privé ou une liste vide).
- Les succès d'un jeu ne sont accessibles que si le joueur possède le jeu et si
  les statistiques du jeu sont publiques.
- Quota indicatif : 100 000 appels par jour et par clé. En cas de dépassement,
  l'API renvoie une erreur 403.
- Documentation officielle : <https://steamcommunity.com/dev>.
