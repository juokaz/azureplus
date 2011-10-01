using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Configuration.Install;
using System.ComponentModel;
using System.ServiceProcess;
using System.Threading;

namespace AzureDownloader
{
    [RunInstaller(true)]
    public class ServiceInstaller : Installer
    {
        public ServiceInstaller()
        {
            var processInstaller = new ServiceProcessInstaller();
            var serviceInstaller = new System.ServiceProcess.ServiceInstaller();

            //set the privileges
            processInstaller.Account = ServiceAccount.LocalSystem;

            serviceInstaller.DisplayName = Service.name;
            serviceInstaller.StartType = ServiceStartMode.Automatic;

            //must be the same as what was set in Program's constructor
            serviceInstaller.ServiceName = Service.name;

            this.Installers.Add(processInstaller);
            this.Installers.Add(serviceInstaller);
        }
    }
}
