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
            var eLog = SetupLog();

            if (!RoleEnvironment.IsAvailable)
            {
                eLog.WriteEntry("Service cannot be started, because it's not running on Azure", EventLogEntryType.Error); 
                throw new Exception("This is not running on Windows Azure");
            }
            // url to fetch and how often
            String url = RoleEnvironment.GetConfigurationSettingValue("APP_URL");
            int interval = Convert.ToInt32(RoleEnvironment.GetConfigurationSettingValue("APP_INTERVAL"));

            // site configured in IIS on Azure, should be only one
            var serverManager = new ServerManager();
            var site = serverManager.Sites.First();

            // this is needed so IIS could access ENV properties like RoleRoot
            var applicationPool = serverManager.ApplicationPools[site.Applications.First().ApplicationPoolName];
            applicationPool.ProcessModel.LoadUserProfile = true;
            serverManager.CommitChanges();

            // application folder
            var applicationRoot = site.Applications.Where(a => a.Path == "/").Single();
            var virtualRoot = applicationRoot.VirtualDirectories.Where(v => v.Path == "/").Single();
            String folder = virtualRoot.PhysicalPath;

            var sync = new Sync(eLog, url, folder, interval);
            var service = new Service(sync);

            ServiceBase.Run(service);
        }

        protected override void OnStart(string[] args)
        {
            sync.Start();
        }

        protected override void OnStop()
        {
            sync.Stop();
        }

        private static EventLog SetupLog()
        {
            String source = "Logger";
            String log = "Azure";

            if (!System.Diagnostics.EventLog.SourceExists(source))
            {
                System.Diagnostics.EventLog.CreateEventSource(source, log);
            }

            EventLog eLog = new EventLog();
            eLog.Source = source;
            eLog.Log = log;

            return eLog;
        }
    }
}
