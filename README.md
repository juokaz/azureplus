# Azure+

This is called Azure+ as a codename, because it extends what Azure currently supports. 

I have worked with Windows and PHP for longer than I probably should have had to and I have great ideas how Azure can be leveraged for PHP projects. This is some of those :)

Currently this is in development stage and not all the features are present yet, but they should be there quite soon. I'm yet to decide whether I want to invest in this or leave it as a freetime project, this need to happen with a connection to Microsoft anyway. 

## Problems with Windows Azure when using for PHP projects

Two major problems exist: deploying to Windows Azure requires Windows machine for packaging and overall the process is way too complicated and somewhat confusing. From my personal experience I have never seen anyone succeeding without spending a day or so trying to get even a simple app working. There is nothing wrong with Azure itself, it's just too *raw* and hard to use. 

Furthermore deployment time is very long, reaching 20-30 mins. This is not something PHP developers are used for and thus removes the effect of having a scripting language. This is especially frustrating when something goes wrong, because even a simple change requires full redeploy.

In conclusion, I believe, no one uses Azure for PHP projects and never will. This project solves that and makes using Azure for PHP projects as easy as any other cloud solution. Dead simple.

## How these problems can be solved

The proposed service is an abstraction/layer on top of Azure services which makes it a trully platform as a service (PaaS) - very similarly to Orchestra.io, PHPfog etc. You have your app, you upload it and it works. The fact that it runs on Azure is irrelevant from application developer point of view.

For this a managing server(-s) exist which serves as an end-point to the service. Website allows to do all the tasks from an user-friendly interface (so a simple website can be deployed by simply uploading a file(-s)), but also an API exists which allows to talk to the service programatically and/or integrate to build scripts.

As an example for Symfony2 app, deploying can be simplified to:

    ./app/console azure:deploy
    
This comand pulls the settings for the specific deployment from the configuration file, then packages app as a normal archive and sends to the API. API replies about the status and application is running. 

Deployment time can be reduced also, this is not final yet as how it will behave, but the plan is that initial deployment can take as long as 30 mins, but any later updates take as long as a file upload, that is seconds. This achieved by some custom logic.

## Functionality supported 

### Deploying

* Deploying via Git/Svn repository   
Periodicly live instance is updated from the code in the repository

        // Create the deployment, only required once
        curl -XPOST http://api.azureplus.com/deployment/myapp -d '
            {"deployment": {
                "php": "5.2",
                "location": "Europe",
                "repo": "http://github.com/juokaz/sampleapp"
                }
            }'

* Deploying via REST API   
Archive (in any of the supported formats) is created and sent over to the API

        // Create the deployment, only required once
        curl -XPOST http://api.azureplus.com/deployment/myapp -d '
            {"deployment": {
                "php": "5.2",
                "location": "Europe"
                }
            }'
        // Deploy application code
        curl -XPUT http://api.azureplus.com/deployment/myapp -d @package.zip
        // .. do some hacking
        // Deploy application code again
        curl -XPUT http://api.azureplus.com/deployment/myapp -d @package.zip

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

*&copy; 2011 Juozas Kaziukėnas / Web Species Ltd. No affiliations with Microsoft Inc.*
