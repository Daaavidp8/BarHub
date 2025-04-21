using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.Pages.Admin;
using BarHub.Pages.Camarero;
using BarHub.Pages.Login;
using BarHub.Pages.Profile;
using BarHub.Pages.Propietario;
using BarHub.Utils;
using BarHub.Utils.Shell;
using CommunityToolkit.Mvvm.Messaging;
using Microsoft.Maui.Controls;
using System.Diagnostics;

namespace BarHub
{
    public partial class AppShell : Shell
    {

        public AppShell(User user)
        {
            InitializeComponent();
            ConfigureTabsAsync(user);
            WeakReferenceMessenger.Default.Register<ChangeTabMessage>(this, (r, m) =>
            {
                var tabToGo = tabbar.Items.FirstOrDefault(tab => tab.Route == m.Value);
                if (tabToGo != null)
                {
                    tabbar.CurrentItem = tabToGo;
                }
                else
                {
                    Trace.WriteLine($"No se encontró el tab: {m.Value}");
                }
            });
        }

        public async Task ConfigureTabsAsync(User user)
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

                tabbar.Items.Clear();
                if (user.Roles.Contains(Roles.ADMIN))
                {
                    AddTab<AdminPage>(HomeIcon);
                    Routing.RegisterRoute(nameof(ManageRestaurant), typeof(ManageRestaurant));
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
                Trace.WriteLine($"Error configuring tabs: {ex.Message}");
            }
        }

        private void AddTab<TPage>(FontImageSource icon) where TPage : Page
        {
            var pageType = typeof(TPage);
            var routeName = pageType.Name;

            var shellContent = new ShellContent
            {
                Route = routeName,
                Icon = icon,
                ContentTemplate = new DataTemplate(typeof(TPage))
            };

            tabbar.Items.Add(shellContent);

            Routing.RegisterRoute(routeName, pageType);
        }





    }
}
