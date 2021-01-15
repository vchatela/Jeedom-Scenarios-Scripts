# Comment cela fonctionne

Toutes les 3 minutes (configuré dans le scénario), tous les capteurs définis dans la variable `data_base` sont questionnés pour récupérer leur valeur et leur date de prise en compte.

J'ai choisi arbitrairement, de ne garder la valeur que si elle est récente de moins de 1200sec (soit 20min) et valide (<40°C pour la température et <80% pour l'humidité). 

Ensuite je fais une moyenne par pièce des valeurs acceptables et je les dépose dans un objet virtuel qui représente la température virtuelle de la pièce.

# Comment l'utiliser

## Remplir avec ses capteurs
J'ai factorisé avec de n'avoir à modifier que la variable `data_base` qui permet de gérer :
- Autant de zone que voulue (par zone j'entends un capteur virtuel qui représente température et humidité), et on lui donnera le nom `[NomDeLaPiece][NomDeLobjetVirtuel]` 
- Pour chaque zone on peut définir 2 listes de capteurs :
	- **First_category** : la première liste utilisée. Si jamais aucun capteur n'est disponible dans cette zone, alors la liste **Second_category** sera utilisée. A partir d'un capteur utilisé alors la **Second_category** n'est pas utilisée
	- **Second_category** : j'utilise cette catégorie pour mes capteurs de secours. Pour mon cas ils sont filaires mais trop près du sol et ont des écarts de températures importants avec les autres capteurs. C'est pourquoi je ne les utilise que lorsque je n'ai plus le choix
- On peut ensuite définir autant de capteurs que voulu dans chaque catégorie pour chaque usage (température, humidité)

## Où vont les valeurs calculées

J'utilise le plugin *Virtuel* afin de proposer un capteur de température virtuel. 

**Il est nécessaire d'avoir la même syntaxe que moi pour le nom de l'attribut**.

Pour le nom de la pièce et le nom de l'objet c'est à vous de choisir, et il sera à remplir au début de la zone dans la variable `data_base`

| Attribut de l'objet                                 |
| :-------------------------------------------------- |
| [`Bureau][TH Virtuel][Température]`                 |
| [`Bureau][TH Virtuel][temperature_ok]`              |
| [`Bureau][TH Virtuel][temperature_errors_tooold]`   |
| [`Bureau][TH Virtuel][temperature_errors_badvalue]` |
| `[Bureau][TH Virtuel][Humidité]`                    |
| [`Bureau][TH Virtuel][humidity_ok]`                 |
| `[Bureau][TH Virtuel][humidity_errors_tooold]`      |
| [`Bureau][TH Virtuel][humidity_errors_badvalue]`    |
| [`Bureau][TH Virtuel][Rafraichir]`                  |
| `[Bureau][TH Virtuel][last_update]`                 |

On créé autant d'objet virtuel avec ces 10 attributs que de pièce à gérer.

## Au final j'ai quoi à faire 

1. Créer 1 objet virtuel avec les 10 attributs par zone/pièce
2. Remplir la variable `data_base` avec 
   1. Le début du virtuel `[NomDeLaPiece][NomDeLobjetVirtuel]` 
   2. Ajouter les capteurs dans la ou les catégories
3. Programmer le scénario
4. Choisir le virtuel en source du plugin *Thermostat*



En image :

1. Le premier virtuel

<img src=".\virtuel.png" alt="Virtuel1" style="zoom:80%;" />

2. Son rendu

<img src=".\virtuel_2.png" alt="Virtuel1" style="zoom:90%;" />

3. Dans le thermostat

<img src=".\thermostat.png" alt="Virtuel1" style="zoom:80%;" />



4. Le scénario

<img src=".\scenario.png" alt="Virtuel1" style="zoom:80%;" />



### Exemple avec 1 pièce

J'ai un virtuel `[Séjour][TH Virtuel]` qui possède 2 capteurs de températures et 2 capteurs d'humidités et je souhaite les utiliser tous dès que disponible (pas de backup donc pas de *Second_category*)

```php
$data_base = array(
    "[Séjour][TH Virtuel]" => array(
        "SensorsState" => new SensorsState(),
        "Temperature_list" => array(
            "First_category" => array(
                "sensors" => array(
                    "[Séjour][Capteur TH][Température]" => array(),
                    "[Séjour][Capteur TH Hauteur][Température]" => array()
                )
            ),
            "Second_category" => array(
                "sensors" => array()
            )
        ),
        "Humidity_list" => array(
            "First_category" => array(
                "sensors" => array(
                    "[Séjour][Capteur TH][Humidité]" => array(),
                    "[Séjour][Capteur TH Hauteur][Humidité]" => array()
                )
            ),
            "Second_category" => array(
                "sensors" => array()
            )
        )
    )
);
```

### Exemple avec 1 pièce et des capteurs de secours

```php
$data_base = array(
	"[Séjour][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Température]" => array(),
					"[Séjour][Capteur TH Hauteur][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Température]" => array(),
					"[Séjour][DS18B20][Température]" => array()
				)
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Humidité]" => array(),
					"[Séjour][Capteur TH Hauteur][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Humidité]" => array()
				)
			)
		)
	)
);
```

### Exemple avec 3 pièces et des capteurs de secours que dans le séjour

```php
$data_base = array(
	"[Séjour][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Température]" => array(),
					"[Séjour][Capteur TH Hauteur][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Température]" => array(),
					"[Séjour][DS18B20][Température]" => array()
				)
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Séjour][Capteur TH][Humidité]" => array(),
					"[Séjour][Capteur TH Hauteur][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array(
					"[Séjour][DHT22][Humidité]" => array()
				)
			)
		)
	),
	"[Bureau][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Bureau][Capteur TH][Température]" => array(),
					"[Bureau][Capteur TH Haut][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Bureau][Capteur TH][Humidité]" => array(),
					"[Bureau][Capteur TH Haut][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		)
	),
	"[Chambre][TH Virtuel]" => array(
		"SensorsState" => new SensorsState(),
		"Temperature_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Chambre][Capteur TH][Température]" => array(),
					"[Chambre][Capteur TH Haut][Température]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		),
		"Humidity_list" => array(
			"First_category" => array(
				"sensors" => array(
					"[Chambre][Capteur TH][Humidité]" => array(),
					"[Chambre][Capteur TH Haut][Humidité]" => array()
				)
			),
			"Second_category" => array(
				"sensors" => array()
			)
		)
	)
);
```

## Les logs du scénario

Par défaut le type de log est le suivant

```
[2021-01-15 12:30:36][SCENARIO] Start : Scenario lance manuellement.
[2021-01-15 12:30:36][SCENARIO] Exécution du sous-élément de type [action] : code
[2021-01-15 12:30:36][SCENARIO] Exécution d'un bloc code
[2021-01-15 12:30:36][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] virtual : [Séjour][TH Virtuel]
[2021-01-15 12:30:36][SCENARIO] final_temperature : 19.705
[2021-01-15 12:30:36][SCENARIO] final_humidity : 57.245
[2021-01-15 12:30:36][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] virtual : [Bureau][TH Virtuel]
[2021-01-15 12:30:36][SCENARIO] final_temperature : 20.205
[2021-01-15 12:30:36][SCENARIO] final_humidity : 58.435
[2021-01-15 12:30:36][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:30:36][SCENARIO] virtual : [Chambre][TH Virtuel]
[2021-01-15 12:30:36][SCENARIO] final_temperature : 18.15
[2021-01-15 12:30:36][SCENARIO] final_humidity : 56.595
[2021-01-15 12:30:36][SCENARIO] Fin correcte du scénario
```

On peut activer le niveau verbose avec `LOG=1` dans le code de la fonction `log_if_verbose`, ce qui donnera :

```
[2021-01-15 12:33:14][SCENARIO] Start : Scenario lance manuellement.
[2021-01-15 12:33:14][SCENARIO] Exécution du sous-élément de type [action] : code
[2021-01-15 12:33:14][SCENARIO] Exécution d'un bloc code
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:24:54
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 500
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][Capteur TH][Température] - Date : 2021-01-15 12:24:54 - Valeur : 19.62 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:26:04
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 430
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][Capteur TH Hauteur][Température] - Date : 2021-01-15 12:26:04 - Valeur : 19.79 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:13:36
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 1178
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][DHT22][Température] - Date : 2021-01-15 12:13:36 - Valeur : 19.4 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 11:53:36
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 2378
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][DS18B20][Température] - Date : 2021-01-15 11:53:36 - Valeur : 19.3 accepté !
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:24:54
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 500
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][Capteur TH][Humidité] - Date : 2021-01-15 12:24:54 - Valeur : 58.09 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:26:04
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 430
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][Capteur TH Hauteur][Humidité] - Date : 2021-01-15 12:26:04 - Valeur : 56.4 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:28:36
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 278
[2021-01-15 12:33:14][SCENARIO] Sensor : [Séjour][DHT22][Humidité] - Date : 2021-01-15 12:28:36 - Valeur : 59.3 accepté !
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:24:50
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 504
[2021-01-15 12:33:14][SCENARIO] Sensor : [Bureau][Capteur TH][Température] - Date : 2021-01-15 12:24:50 - Valeur : 20.44 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:32:01
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 73
[2021-01-15 12:33:14][SCENARIO] Sensor : [Bureau][Capteur TH Haut][Température] - Date : 2021-01-15 12:32:01 - Valeur : 20.03 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:24:50
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 504
[2021-01-15 12:33:14][SCENARIO] Sensor : [Bureau][Capteur TH][Humidité] - Date : 2021-01-15 12:24:50 - Valeur : 56.96 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:32:01
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 73
[2021-01-15 12:33:14][SCENARIO] Sensor : [Bureau][Capteur TH Haut][Humidité] - Date : 2021-01-15 12:32:01 - Valeur : 59.88 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:30:29
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 165
[2021-01-15 12:33:14][SCENARIO] Sensor : [Chambre][Capteur TH][Température] - Date : 2021-01-15 12:30:29 - Valeur : 17.83 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:28:42
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 272
[2021-01-15 12:33:14][SCENARIO] Sensor : [Chambre][Capteur TH Haut][Température] - Date : 2021-01-15 12:28:42 - Valeur : 18.47 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] category :First_category
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:30:29
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 165
[2021-01-15 12:33:14][SCENARIO] Sensor : [Chambre][Capteur TH][Humidité] - Date : 2021-01-15 12:30:29 - Valeur : 59.59 accepté !
[2021-01-15 12:33:14][SCENARIO] Date capture: 2021-01-15 12:28:42
[2021-01-15 12:33:14][SCENARIO] Diff between now and date capture : 272
[2021-01-15 12:33:14][SCENARIO] Sensor : [Chambre][Capteur TH Haut][Humidité] - Date : 2021-01-15 12:28:42 - Valeur : 53.6 accepté !
[2021-01-15 12:33:14][SCENARIO] category :Second_category
[2021-01-15 12:33:14][SCENARIO] cat1_array :Array
(
[[Séjour][Capteur TH][Température]] => 19.62
[[Séjour][Capteur TH Hauteur][Température]] => 19.79
)
[2021-01-15 12:33:14][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:14][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:15][SCENARIO] virtual : [Séjour][TH Virtuel]
[2021-01-15 12:33:15][SCENARIO] final_temperature : 19.705
[2021-01-15 12:33:15][SCENARIO] final_humidity : 57.245
[2021-01-15 12:33:15][SCENARIO] cat1_array :Array
(
[[Bureau][Capteur TH][Température]] => 20.44
[[Bureau][Capteur TH Haut][Température]] => 20.03
)
[2021-01-15 12:33:15][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:15][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:15][SCENARIO] virtual : [Bureau][TH Virtuel]
[2021-01-15 12:33:15][SCENARIO] final_temperature : 20.235
[2021-01-15 12:33:15][SCENARIO] final_humidity : 58.42
[2021-01-15 12:33:15][SCENARIO] cat1_array :Array
(
[[Chambre][Capteur TH][Température]] => 17.83
[[Chambre][Capteur TH Haut][Température]] => 18.47
)
[2021-01-15 12:33:15][SCENARIO] TH Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:15][SCENARIO] Hum Cat1 nombre capteur utilisés : 2
[2021-01-15 12:33:15][SCENARIO] virtual : [Chambre][TH Virtuel]
[2021-01-15 12:33:15][SCENARIO] final_temperature : 18.15
[2021-01-15 12:33:15][SCENARIO] final_humidity : 56.595
[2021-01-15 12:33:15][SCENARIO] Fin correcte du scénario
```

