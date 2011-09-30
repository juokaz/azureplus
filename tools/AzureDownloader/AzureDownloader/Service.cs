using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.ServiceProcess;
using Microsoft.WindowsAzure.ServiceRuntime;
using Microsoft.Web.Administration;
using System.Diagnostics;

namespace AzureDownloader
{
    class Service : ServiceBase
    {
        Sync sync;

        public Service(Sync sync)
        {
            this.sync = sync;

            this.ServiceName = "Azure Downloader";
            this.EventLog.Log = "Application";

            this.CanShutdown = true;
            this.CanStop = true;
        }

        static void Main(String[] args)
        {
            if (!RoleEnvironment.IsAvailable)
            {
                throw new Exception("This is not running on Windows Azure");
            }

            String url = RoleEnvironment.GetConfigurationSettingValue("APP_URL");
            int interval = Convert.ToInt32(RoleEnvironment.GetConfigurationSettingValue("APP_INTERVAL"));

            var serverManager = new ServerManager();
            var site = serverManager.Sites.ToArray()[0];

            var applicationRoot = site.Applications.Where(a => a.Path == "/").Single();
            var virtualRoot = applicationRoot.VirtualDirectories.Where(v => v.Path == "/").Single();

            String folder = virtualRoot.PhysicalPath;
            String source = "Logger";
            String log = "Azure";

            if (!System.Diagnostics.EventLog.SourceExists(source))
            {
                System.Diagnostics.EventLog.CreateEventSource(source, log);
            }

            EventLog eLog = new EventLog();
            eLog.Source = source;
            eLog.Log = log;

            ServiceBase.Run(new Service(new Sync(eLog, url, folder, interval)));
        }

        protected override void OnStart(string[] args)
        {
            base.OnStart(args);

            sync.Start();
        }

        protected override void OnStop()
        {
            base.OnStop();

            sync.Stop();
        }
    }
}
