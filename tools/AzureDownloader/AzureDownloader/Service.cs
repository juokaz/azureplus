using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.ServiceProcess;

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
            if (args.Length != 3)
            {
                Console.WriteLine("Please enter a app url, folder to extract to and refresh interval.");
                return;
            }

            String url = args[0];
            String folder = args[1];
            int interval = Convert.ToInt32(args[2]);

            ServiceBase.Run(new Service(new Sync(url, folder, interval)));
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
