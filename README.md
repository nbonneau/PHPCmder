# PHPCmder
PHPCmder est un framework permettant de créer des commandes PHP facilement sans avoir à se soucier de la gestion des arguments ou des options.

Ce framework est basé sur le package [Primer console](https://github.com/alex-phillips/Primer-Console) auquel sont rajoutées des fonctionnalités et des services.

Le fonctionnement de base est calqué sur le système de commande de Symfony2.

# L'application

Contrairement à Primer console, vous n'avez pas besoin de créer une application par commande. L'application se situe dans le répertoire `app` et correspond au fichier `cmd`.

Pour lancer l'application il suffit d'exécuter la command suivante depuis le répertoire racine de l'application : `php app/cmd [command_name]`

# Créer une commande

La création de commande est simplifier avec PHPCmder. Il vous suffit seulement de créer une classe dans le répertoire `Command` puis d'ajouter le chemin de la classe dans le fichier de configuration de l'application `app/config/config.json` afin que la commande soit reconnue.

Commande classe `Command/TestCommand.php`: 
```php
<?php
namespace Command;

use Primer\Console\Command\BaseCommand;
use Primer\Console\Input\DefinedInput;

class TestCommand extends BaseCommand {

    public function configure() {
        $this->setName('test');
        $this->setDescription('this is a test command, it can be remove.');
        $this->addArgument('arg_test', DefinedInput::VALUE_REQUIRED, 'this is a test argument');
        $this->addOption('opt_test', 'o', DefinedInput::VALUE_OPTIONAL, 'this is a test option', 'this is a test option');
        
    }
    public function run() {
        // do what you want...
    }
}
```
Fichier de config `app/config/config.json` :
```json
{
    "commands": [
        "Command\/TestCommand"
    ],

    ...
}
```

PHPCmder met également à disposition un utilitaire de création de command qui vous permet de créer des commandes via votre terminal.
Pour cela exécuter la commande suivante :

`php app/cmd create:command`

Vous pouvez passer le nom de la commande en argument :

`php app/cmd create:command my_awesome_command`

Pour plus d'information sur la configuration d'un commande, allez sur [Primer console](https://github.com/alex-phillips/Primer-Console).

# Exécuter une commande

L'exécution d'une commande est très simple : 

`php app/cmd my_awesome_command [args] [--opts]`

# Les services

Pour récuperer un service il vous suffit simplement d'appeler la fonction suivante : 

`$this->get("my_service_name")`

Vous pouvez également créer vos propres service afin de les réutiliser dans d'autres commandes comme bon vous semble.
Pour cela créer la classe du service dans le répertoire `Service` puis déclarer le dans le fichier de config `app/config/services.json`.

Classe du service `Service/MyCustomService.php` :
```php
<?php
namespace Service;

use Database\ConnectDB;

class MyCustomService{
    
    protected $conn;
    
    public function __construct(ConnectDB $conn) {
        $this->conn = $conn;
    }
}
```
Fichier de configuration des service `app/config/services.json` :
```json
{

    ...

    "service_name": {
        "class": "Service/MyCustomService",
        "arguments" : ["@connection"],
        "description": "Example of a simple service",
        "__comment__": "this service is a example, you can remove it and 'Service/MyCustomService.php' file too."
    },

    ...

}
```

PHPCmder met également à disposition des services par défaut :

* _logger_ : permet de gérer un fichier de log pour la commande, voir [service logger documentation](http://todo.com)
* _connection_ : permet de gérer une connexion à une base de donnée, voir [service connection documentation](http://todo.com)

## Service logger

## Service connection