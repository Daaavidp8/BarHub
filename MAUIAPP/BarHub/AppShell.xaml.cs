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
            MainThread.BeginInvokeOnMainThread(async () => await ConfigureTabsAsync(user));
        }
        public async Task ConfigureTabsAsync(User user)
        {
            try
            {
                var homeIcon = new FontImageSource
                {
                    FontFamily = "FASolid",
                    Glyph = Icons.Home,
                    Size = 20,
                };

                tabbar.Items.Clear();

                if (user.Roles.Contains(Roles.ADMIN))
                {
                    AddTab<AdminPage>(homeIcon);
                    Routing.RegisterRoute(nameof(ManageRestaurant), typeof(ManageRestaurant));
                }
                else
                {
                    if (user.Roles.Contains(Roles.PROPIETARIO))
                        AddTab<OwnerPage>(homeIcon);

                    if (user.Roles.Contains(Roles.CAMARERO) || user.Roles.Contains(Roles.PROPIETARIO))
                        AddTab<WaiterPage>(new FontImageSource
                        {
                            FontFamily = "FASolid",
                            Glyph = Icons.Chair,
                            Size = 20,
                        });
                }

                //AddTab<ProfilePage>(new FontImageSource
                //{
                //    FontFamily = "FASolid",
                //    Glyph = Icons.Profile,
                //    Size = 20,
                //});

                tabbar.Items.Add(new ShellContent
                {
                    Route = "ProfilePage",
                    Content = new ProfilePage(user),
                    Icon = new FontImageSource
                    {
                        FontFamily = "FASolid",
                        Glyph = Icons.Profile,
                        Size = 20,
                    }
                });

                if (tabbar is not null && tabbar.Items.Count > 0)
                {
                    tabbar.CurrentItem = tabbar.Items[0];
                }

            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error configuring tabs: {ex.Message}");
                await Application.Current.MainPage.DisplayAlert("Error", ex.Message, "OK");
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
