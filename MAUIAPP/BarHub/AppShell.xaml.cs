using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.Pages.Admin;
using BarHub.Pages.Camarero;
using BarHub.Pages.Profile;
using BarHub.Pages.Propietario;
using BarHub.Utils;
using Microsoft.Maui.Controls;
using System.Diagnostics;

namespace BarHub
{
    public partial class AppShell : Shell
    {
        private readonly Posts _posts;

        public AppShell(IServiceProvider services = null, User user = null)
        {
            InitializeComponent();
            _posts = services.GetService<Posts>();
            ConfigureTabsAsync(user);
        }

        public async Task ConfigureTabsAsync(User? user = null)
        {
            try
            {
                var HomeIcon = new FontImageSource
                {
                    FontFamily = "FASolid",
                    Glyph = Icons.Home,
                    Size = 20,
                    Color = App.Current.Resources["Primary"] as Color
                };

                if (user == null)
                {
                    var username = Preferences.Get("username", string.Empty);
                    var password = Preferences.Get("password", string.Empty);
                    user = await _posts.Login(username, password);
                }

                tabbar.Items.Clear();
                if (user.Roles.Contains(Roles.ADMIN))
                {
                    AddTab<AdminPage>(HomeIcon);
                }
                else
                {
                    if (user.Roles.Contains(Roles.PROPIETARIO))
                        AddTab<OwnerPage>(HomeIcon);

                    if (user.Roles.Contains(Roles.CAMARERO) || user.Roles.Contains(Roles.PROPIETARIO))
                        AddTab<WaiterPage>(new FontImageSource
                        {
                            FontFamily = "FASolid",
                            Glyph = Icons.Chair,
                            Size = 20,
                            Color = App.Current.Resources["Primary"] as Color
                        });
                }


                tabbar.CurrentItem = tabbar.Items[0];


                AddTab<ProfilePage>(new FontImageSource
                {
                    FontFamily = "FASolid",
                    Glyph = Icons.Profile,
                    Size = 20,
                    Color = App.Current.Resources["Primary"] as Color
                });



            }
            catch (Exception ex)
            {
                Debug.WriteLine($"❌ Error configurando tabs: {ex.Message}");
            }
        }

        private void AddTab<TPage>(FontImageSource icon) where TPage : Page
        {
            var shellContent = new ShellContent
            {
                Icon = icon,
                Route = nameof(TPage),
                ContentTemplate = new DataTemplate(typeof(TPage))
            };

            tabbar.Items.Add(shellContent);
        }


    }
}
