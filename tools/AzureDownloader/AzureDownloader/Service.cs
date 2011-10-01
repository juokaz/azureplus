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
                EventLog.WriteEntry("Configuring the Sync process");

                // values required for Sync service
                String url, folder;
                int interval;

                try
                {
                    // url to fetch and how often
                    url = RoleEnvironment.GetConfigurationSettingValue("APP_URL");
                    interval = Convert.ToInt32(RoleEnvironment.GetConfigurationSettingValue("APP_INTERVAL"));

                    // site configured in IIS on Azure, should be only one
                    var serverManager = new ServerManager();
                    var site = serverManager.Sites.First();

                    // application folder
                    var applicationRoot = site.Applications.Where(a => a.Path == "/").Single();
                    var virtualRoot = applicationRoot.VirtualDirectories.Where(v => v.Path == "/").Single();
                    folder = virtualRoot.PhysicalPath;
                }
                catch (Exception e)
                {
                    EventLog.WriteEntry("Configuration failed: " + e.Message, EventLogEntryType.Error);
                    throw e;
                }

                EventLog.WriteEntry("Syncing with URL \"" + url + "\", directory \"" + folder + "\" and interval \"" + interval + "\"");

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
