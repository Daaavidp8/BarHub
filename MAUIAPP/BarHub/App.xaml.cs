using BarHub.Lib;
using BarHub.Pages.Login;
using BarHub.Utils.Translation;
using System.Globalization;

namespace BarHub
{
    public partial class App : Application
    {
        private IServiceProvider _services;
        public App(IServiceProvider services)
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
            var isLogged = Preferences.Get("IsLoggedIn", false);

            if (!isLogged)
            {
                return new Window(_services.GetService<Login>());
            }
            else
            {
                return new Window(new AppShell(_services));
            }
        }
    }
}
