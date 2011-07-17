# Azure+

## Problems with Windows Azure when using for PHP projects

Two major problems exist: deploying to Windows Azure requires Windows machine for packaging and overall the process is way too complicated and somewhat confusing. From my personal experience I have never seen anyone succeeding without spending a day or so trying to get even a simple app working. 

## How these problems can be solved

The proposed service is an abstraction/layer on top of Azure services which makes it a trully platform as a service (PaaS). You have your app, you upload it and it works. For this a managing server(-s) exist which serve as end-points to the service. 

## Functionality supported 

* Deploying via Git/Svn repository
Periodicly live instance is updated from the code in repository
* Deploying via REST API
Archive (in any of the supported formats) is created and sent over to the API
* Deploying via Web Deploy
A project can be deployed from WebMatrix for example using the Web Deploy protocol
