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
            this.Committed += new InstallEventHandler(ServiceInstaller_AfterInstall);

            this.Installers.Add(processInstaller);
            this.Installers.Add(serviceInstaller);
        }

        void ServiceInstaller_AfterInstall(object sender, InstallEventArgs e)
        {
            var timeout = TimeSpan.FromSeconds(5);
            try
            {
                ServiceController sc = new ServiceController("Azure Downloader");
                sc.Start();
                sc.WaitForStatus(ServiceControllerStatus.Running, timeout);
            }
            catch (Exception ex)
            {
                Console.WriteLine("Failed to start: " + ex.Message);
            }
        }
    }
}
