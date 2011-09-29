using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Ionic.Zip;
using System.IO;
using System.Net;

namespace AzureDownloader
{
    class Sync
    {
        private String url;
        private String unpackDirectory;

        private String currentEtag;

        public Sync(String url, String directory)
        {
            this.url = url;
            this.unpackDirectory = directory;
        }

        public void Run()
        {
            WebRequest req = HttpWebRequest.Create(url);
            req.Method = "HEAD";
            WebResponse resp = req.GetResponse();

            // get package etag
            String Etag = resp.Headers.Get("ETag");

            if (currentEtag != Etag)
            {
                Console.WriteLine("New etag: " + Etag);
                String folder = GetTempFolder();

                WebRequest req2 = WebRequest.Create(url);
                WebResponse resp2 = req2.GetResponse();

                byte[] data = ReadFully(resp2.GetResponseStream());

                using (ZipFile zip1 = ZipFile.Read(new MemoryStream(data)))
                {
                    foreach (ZipEntry e in zip1)
                    {
                        e.Extract(folder, ExtractExistingFileAction.OverwriteSilently);
                    }
                }

                SyncFolders(folder, unpackDirectory);
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
