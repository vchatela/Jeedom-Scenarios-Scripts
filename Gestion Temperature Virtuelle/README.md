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

