# Liste générale des scénarios
- *Gestion du chauffage* : programmation automatique 
- *Gestion température virtuelle* : généralise 4 capteurs en 1 seul 'intelligent'
- *Gestion des notifications* : centralise les envois de notifications par type et importance
- *Gestion des messages* d'erreurs : permet de récupérer les erreurs et alertes jeedom ainsi que des les filtrer
- *Prévision course à pied* : envoi un condensé des conditions météorologiques et retourne une liste de matériel nécessaire
- *Notification anniversaire* : pour recevoir une notification le jour d'un anniversaire
- *Notification fête* : pour recevoir une notification le jour d'une fête

# Liste détaillée
## Gestion Chauffage
#### Fonctionnalités
Algorithme "intelligent" qui permet à partir des événements du calendrier (ici google) de déterminer les présences et absences et de créer automatiquement les entrées dans le chauffage.
#### Pourquoi
Chaque jour est différent et il ne m'était pas possible de faire un calendrier de chauffage général.

*Plus de détails dans le readme dédié.*
## Gestion Température Virtuelle
#### Fonctionnalités 
Permet de créer une température virtuelle à partir des 4 capteurs température de la pièce. 
#### Pourquoi 
Cela dans le but de réduire les fortes variations, ainsi que les erreurs. Egalement permet d'avoir des capteurs de backups (valeurs moins fiable mais toujours disponibles).

De plus les capteurs sont différents :
- 2 capteurs en zigbee en hauteur : valeur + fiable mais moins souvent remontées
- 2 capteurs en filaire sur une raspberry distant : valeur - fiable car + proche du sol et des variations mais aucun souci dans la remontée. 

*Plus de détails dans le readme dédié.*
## Gestion des notifications
#### Fonctionnalités
Prise en compte :
- Du type de message -- catégorie : reporting, alerte, electricite, alarme, erreur
- De l'importance du message : 
	- 0 : Slack uniquement
	- > 1 & < 10 : 0 + Slack + Mail 
	- > 10 : Slack + Mail + SMS
- La possibilité de l'ajout d'un titre au message

*Plus de détails dans le readme dédié.*
## Gestion des messages d'erreurs / timeout / alerte / danger
#### Fonctionnalités
Permet soit de : 
- Supprimer les messages d'une liste de plugin donnée : useless_plugins_errors 
- De réduire les messages qui contiennent un texte de la liste : msg_to_reduce_array 
- Supprimer les messages qui contiennent : useless_errors

*Plus de détails dans le readme dédié.*
## Prévision Course à pied
#### Fonctionnalités
Propose un condensé des informations suivantes :
- La durée de soleil restant : pour frontale/lumière
- La probabilité de pluie et le moment : pour k-way
- La qualité de l'air : pour un parcours en forêt plutôt qu'en ville
En proposant 3 infos :
- Les risques : pluie, nuit, ...
- Le matériel : k-way, lumière, ...
- Les conditions : la température, ...

## Notification Anniversaires
#### Fonctionnalités 
Une variable jeedom stocke la liste des anniversaires à souhaiter (nom) et si un objet existe dans le calendrier du jour alors une notification est envoyée.

*Plus de détails dans le readme dédié.*
## Notification Fêtes
#### Fonctionnalités 
Une variable jeedom stocke la liste des prénoms des fêtes à souhaiter et si un objet existe dans l'éphéméride du jour alors un notification est envoyée.

*Plus de détails dans le readme dédié.*

## Plex Webhook
#### Fonctionnalités  
Permet de récupérer les infos du webhook vers Jeedom afin de déclencher des événements dans Jeedom en fonction des intéractions plex.
Nécessite le plexpass.