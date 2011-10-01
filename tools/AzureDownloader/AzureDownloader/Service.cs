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
            this.EventLog.Log = "Azure";

            this.CanShutdown = true;
            this.CanStop = true;
        }

        static void Main(String[] args)
        {
            var eLog = SetupLog();

            eLog.WriteEntry("Starting Service setup"); 

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

            eLog.WriteEntry("Enabled LoadUserProfile setting"); 

            // application folder
            var applicationRoot = site.Applications.Where(a => a.Path == "/").Single();
            var virtualRoot = applicationRoot.VirtualDirectories.Where(v => v.Path == "/").Single();
            String folder = virtualRoot.PhysicalPath;

            eLog.WriteEntry("Sync with URL \"" + url + "\", directory \"" + folder + "\" and interval \"" + interval + "\"");

            var sync = new Sync(eLog, url, folder, interval);
            var service = new Service(sync);

            ServiceBase.Run(service);
        }

        protected override void OnStart(string[] args)
        {
            this.EventLog.WriteEntry("Starting");
            sync.Start();
            this.EventLog.WriteEntry("Started");
        }

        protected override void OnStop()
        {
            this.EventLog.WriteEntry("Stopping");
            sync.Stop();
            this.EventLog.WriteEntry("Stopped");
        }

        private static EventLog SetupLog()
        {
            String source = "Azure Downloader";
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
