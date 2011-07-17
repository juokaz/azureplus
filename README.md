# Azure+

This is called Azure+ as a codename, because it extends what Azure currently supports. 

## Problems with Windows Azure when using for PHP projects

Two major problems exist: deploying to Windows Azure requires Windows machine for packaging and overall the process is way too complicated and somewhat confusing. From my personal experience I have never seen anyone succeeding without spending a day or so trying to get even a simple app working. 

Furthermore deployment time is very long, reaching 20-30 mins. This is not something PHP developers are used for and thus removes the effect of having a scripting language.

## How these problems can be solved

The proposed service is an abstraction/layer on top of Azure services which makes it a trully platform as a service (PaaS) - very similarly to Orchestra.io, PHPfog etc. You have your app, you upload it and it works.

For this a managing server(-s) exist which serve as an end-point to the service. Website allows to do all the tasks from an user-friendly interface (so a simple website can be deployed by simply uploading a file(-s)), but also an API exists which allows to talk to the service programatically and/or integrate to build scripts.

As an example for Symfony2 app, deploying can be simplified to:

    ./app/console azure:deploy
    
This comand pulls the settings for the specific deployment from the configuration file, then packages app as a normal archive and sends to the API. API replies about the status and application is running. 

Deployment time can be reduced also, this is not final yet as how it will behave, but the plan is that initial deployment can take as long as 30 mins, but any later updates take as long as file upload. 

## Functionality supported 

### Deploying

* Deploying via Git/Svn repository   
Periodicly live instance is updated from the code in the repository

* Deploying via REST API   
Archive (in any of the supported formats) is created and sent over to the API

* Deploying via Web Deploy   
A project can be deployed from WebMatrix for example using the Web Deploy protocol

### PHP

* PHP version can be chosen when deploying
* Custom configuration can be asked for

### Additional servers

* MySQL server
* Microsoft SQL Server/SQL Azure
* MongoDB
* CouchDB
* Anything what runs on Windows
