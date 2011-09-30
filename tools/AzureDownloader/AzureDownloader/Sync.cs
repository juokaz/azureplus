﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Ionic.Zip;
using System.IO;
using System.Net;
using System.Threading;

namespace AzureDownloader
{
    class Sync
    {
        private String url;
        private String unpackDirectory;
        private int interval;

        private Thread syncingThread;

        private String currentEtag;

        public Sync(String url, String directory, int interval)
        {
            this.url = url;
            this.unpackDirectory = directory;
            this.interval = interval;
        }

        public void Start()
        {
            syncingThread = new Thread(new ThreadStart(() =>
            {
                while (true)
                {
                    SyncAll();
                    Thread.Sleep(interval);
                }
            }));
            syncingThread.Start();
        }

        public void Stop()
        {
            syncingThread.Abort();
        }

        public void SyncAll()
        {
            Console.WriteLine("Checking");

            WebRequest req = HttpWebRequest.Create(url);
            req.Method = "HEAD";

            String Etag;
            using (WebResponse resp = req.GetResponse())
            {
                // get package etag
                Etag = resp.Headers.Get("ETag");
            }

            if (currentEtag != Etag)
            {
                Console.WriteLine("New etag: " + Etag);
                String folder = GetTempFolder();

                WebRequest req2 = WebRequest.Create(url);

                using (WebResponse resp = req2.GetResponse())
                {
                    byte[] data = ReadFully(resp.GetResponseStream());

                    using (ZipFile zip1 = ZipFile.Read(new MemoryStream(data)))
                    {
                        foreach (ZipEntry e in zip1)
                        {
                            e.Extract(folder, ExtractExistingFileAction.OverwriteSilently);
                        }
                    }
                }

                SyncFolders(folder, unpackDirectory);

                currentEtag = Etag;
            }
        }

        private void SyncFolders(String folder1, String folder2)
        {
            string loc_robocopy = @"Robocopy.exe";
            string arg_robocopy = folder1 + " " + folder2 + " /E /PURGE /NJS /NJH";
            System.Diagnostics.Process proc_robocopy = new System.Diagnostics.Process();

            proc_robocopy.StartInfo.Arguments = arg_robocopy;
            proc_robocopy.StartInfo.FileName = loc_robocopy;
            proc_robocopy.StartInfo.CreateNoWindow = true;
            proc_robocopy.StartInfo.UseShellExecute = false;
            proc_robocopy.StartInfo.RedirectStandardError = true;
            proc_robocopy.StartInfo.RedirectStandardInput = true;
            proc_robocopy.Start();
            proc_robocopy.WaitForExit();
        }

        private byte[] ReadFully(Stream stream)
        {
            byte[] buffer = new byte[32768];
            using (MemoryStream ms = new MemoryStream())
            {
                while (true)
                {
                    int read = stream.Read(buffer, 0, buffer.Length);
                    if (read <= 0)
                        return ms.ToArray();
                    ms.Write(buffer, 0, read);
                }
            }
        }

        private String GetTempFolder()
        {
            String folder = Path.Combine(Path.GetTempPath(), Path.GetRandomFileName());
            while (Directory.Exists(folder))
            {
                folder = Path.Combine(Path.GetTempPath(), Path.GetRandomFileName());
            }

            return folder;
        }
    }
}
