using BarHub.Lib;
using System.Diagnostics;

namespace WinFormsApp1
{
    internal static class Program
    {
        /// <summary>
        ///  The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            ApplicationConfiguration.Initialize();
            Testing();
            Application.Run(new Form1());
            
        }


        public async static void Testing()
        {
            var posts = new Posts(new Methods());

            var loginSuccess = await posts.Login("dsad", "dsad");
            Trace.WriteLine(loginSuccess);
        }
    }
}