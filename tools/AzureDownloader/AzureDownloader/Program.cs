using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace AzureDownloader
{
    class Program
    {
        static void Main(string[] args)
        {
            if (args.Length != 2)
            {
                Console.WriteLine("Please enter a app url and folder to extract to.");
                return;
            }

            String url = args[0];
            String folder = args[1];
            var sync = new Sync(url, folder);
            sync.Run();
        }
    }
}
