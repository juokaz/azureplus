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

            serviceInstaller.DisplayName = "Azure Downloader";
            serviceInstaller.StartType = ServiceStartMode.Automatic;

            //must be the same as what was set in Program's constructor
            serviceInstaller.ServiceName = "Azure Downloader";

            // auto start the service
            this.AfterInstall += new InstallEventHandler(ServiceInstaller_AfterInstall);

            this.Installers.Add(processInstaller);
            this.Installers.Add(serviceInstaller);
        }

        void ServiceInstaller_AfterInstall(object sender, InstallEventArgs e)
        {
            ServiceController sc = new ServiceController("Azure Downloader");

            // try starting the service until it starts
            do {
                try
                {
                    sc.Start();
                }
                catch (Exception)
                {
                    //retry`
                    Thread.Sleep(1000);
                }
            } while (sc.Status != ServiceControllerStatus.StartPending);
        }
    }
}
