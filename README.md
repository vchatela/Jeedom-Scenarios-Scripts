# Gestion Chauffage
## Fonctionnalités
Algorithme "intelligent" qui permet à partir des événements du calendrier (ici google) de déterminer les présences et absences et de créer automatiquement les entrées dans le chauffage.
## Pourquoi
Chaque jour est différent et il ne m'était pas possible de faire un calendrier de chauffage général.

Plus de détails dans le readme dédié.
# Gestion Température Virtuelle
## Fonctionnalités 
Permet de créer une température virtuelle à partir des 4 capteurs température de la pièce. 
## Pourquoi 
Cela dans le but de réduire les fortes variations, ainsi que les erreurs. Egalement permet d'avoir des capteurs de backups (valeurs moins fiable mais toujours disponibles).

De plus les capteurs sont différents :
- 2 capteurs en zigbee en hauteur : valeur + fiable mais moins souvent remontées
- 2 capteurs en filaire sur une raspberry distant : valeur - fiable car + proche du sol et des variations mais aucun souci dans la remontée. 

Plus de détails dans le readme dédié.
# Notification Anniversaires
## Fonctionnalités 
Une variable jeedom stocke la liste des anniversaires à souhaiter (nom) et si un objet existe dans le calendrier du jour alors une notification est envoyée.

Plus de détails dans le readme dédié.
# Notification Fêtes
## Fonctionnalités  
Une variable jeedom stocke la liste des prénoms des fêtes à souhaiter et si un objet existe dans l'éphéméride du jour alors un notification est envoyée.

Plus de détails dans le readme dédié.