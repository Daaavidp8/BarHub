using BarHub.Lib;
using BarHub.Pages;
using BarHub.Pages.Login;
using BarHub.Utils.Translation;
using System.Globalization;

namespace BarHub
{
    public partial class App : Application
    {
        private IServiceProvider _services;
        public App(IServiceProvider services, Posts posts)
        {
            InitializeComponent();
            _services = services;
            SetLanguage();
        }

        private void SetLanguage()
        {
            string savedLanguage = Preferences.Get("AppLanguage", CultureInfo.CurrentCulture.Name);
            LocalizationResourceManager.Instance.SetCulture(new CultureInfo(savedLanguage));
        }

        protected override Window CreateWindow(IActivationState? activationState)
        {

            return new Window(new SplashPage(_services));
            //var isLogged = Preferences.Get("IsLoggedIn", false);



            //if (isLogged)
            //{
            //    return new Window(new SplashPage(_posts));
            //}
            //return new Window(_services.GetService<Login>());
        }
    }
}
