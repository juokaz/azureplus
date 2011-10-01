using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.ServiceProcess;
using Microsoft.WindowsAzure.ServiceRuntime;
using Microsoft.Web.Administration;
using System.Diagnostics;
using System.Threading;

namespace AzureDownloader
{
    class Service : ServiceBase
    {
        private Thread syncingThread;
        public static String name = "Azure Downloader";

        public Service()
        {
            this.ServiceName = Service.name;
            this.EventLog.Log = "Application";

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

            // site configured in IIS on Azure, should be only one
            var serverManager = new ServerManager();
            var site = serverManager.Sites.First();

            // this is needed so IIS could access ENV properties like RoleRoot
            var applicationPool = serverManager.ApplicationPools[site.Applications.First().ApplicationPoolName];
            applicationPool.ProcessModel.LoadUserProfile = true;
            serverManager.CommitChanges();

            eLog.WriteEntry("Enabled LoadUserProfile setting");

            // run the service
            ServiceBase[] ServicesToRun;
            ServicesToRun = new ServiceBase[] {
                new Service()
            };
            ServiceBase.Run(ServicesToRun);
        }

        protected override void OnStart(string[] args)
        {
            syncingThread = new Thread(new ThreadStart(() =>
            {
                // url to fetch and how often
                String url = RoleEnvironment.GetConfigurationSettingValue("APP_URL");
                int interval = Convert.ToInt32(RoleEnvironment.GetConfigurationSettingValue("APP_INTERVAL"));

                // site configured in IIS on Azure, should be only one
                var serverManager = new ServerManager();
                var site = serverManager.Sites.First();

                EventLog.WriteEntry("Enabled LoadUserProfile setting");

                // application folder
                var applicationRoot = site.Applications.Where(a => a.Path == "/").Single();
                var virtualRoot = applicationRoot.VirtualDirectories.Where(v => v.Path == "/").Single();
                String folder = virtualRoot.PhysicalPath;

                EventLog.WriteEntry("Sync with URL \"" + url + "\", directory \"" + folder + "\" and interval \"" + interval + "\"");

                var sync = new Sync(EventLog, url, folder, interval);

                while (true)
                {
                    sync.SyncAll();
                    Thread.Sleep(interval);
                }
            }));
            syncingThread.Start();
        }

        protected override void OnStop()
        {
            syncingThread.Abort();
        }

        private static EventLog SetupLog()
        {
            String source = Service.name;
            String log = "Application";

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
