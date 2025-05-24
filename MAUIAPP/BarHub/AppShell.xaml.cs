using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.Pages.Admin;
using BarHub.Pages.Camarero;
using BarHub.Pages.Login;
using BarHub.Pages.Orders;
using BarHub.Pages.Profile;
using BarHub.Pages.Propietario;
using BarHub.Pages.Propietario.Articles;
using BarHub.Pages.Propietario.Workers;
using BarHub.Pages.Waiter;
using BarHub.Utils;
using BarHub.Utils.Shell;
using CommunityToolkit.Mvvm.Messaging;
using Microsoft.Maui.Controls;
using System.Diagnostics;

namespace BarHub
{
    public partial class AppShell : Shell
    {
        private readonly IServiceProvider _services;

        public AppShell(User user, IServiceProvider services)
        {
            InitializeComponent();
            _services = services;
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
                    {

                        tabbar.Items.Add(new ShellContent
                        {
                            Route = "OwnerPage",
                            Content = new OwnerPage(user, _services),
                            Icon = homeIcon
                        });

                        tabbar.Items.Add(new ShellContent
                        {
                            Route = "WorkerPage",
                            Content = new WorkerPage(user, _services),
                            Icon = new FontImageSource
                            {
                                FontFamily = "FASolid",
                                Glyph = Icons.Workers,
                                Size = 20,
                            }
                        });

                        Routing.RegisterRoute(nameof(ManageSection), typeof(ManageSection));
                        Routing.RegisterRoute(nameof(ArticlesPage), typeof(ArticlesPage));
                        Routing.RegisterRoute(nameof(ManageArticle), typeof(ManageArticle));
                        Routing.RegisterRoute(nameof(ManageWorker), typeof(ManageWorker));
                    }

                    if (user.Roles.Contains(Roles.CAMARERO) || user.Roles.Contains(Roles.PROPIETARIO))
                    {
                        tabbar.Items.Add(new ShellContent
                        {
                            Route = "WaiterPage",
                            Content = new WaiterPage(user, _services),
                            Icon = user.Roles.Contains(Roles.PROPIETARIO) ? new FontImageSource
                            {
                                FontFamily = "FASolid",
                                Glyph = Icons.Chair,
                                Size = 20,
                            } : homeIcon
                        });

                        Routing.RegisterRoute(nameof(TableDetails), typeof(TableDetails));
                    }

                    if (user.Roles.Contains(Roles.ENCARGADOBARRA) || user.Roles.Contains(Roles.COCINERO) || user.Roles.Contains(Roles.PROPIETARIO))
                    {
                        tabbar.Items.Add(new ShellContent
                        {
                            Route = "OrderPage",
                            Content = new OrdersPage(user, _services),
                            Icon = user.Roles.Contains(Roles.PROPIETARIO) ? new FontImageSource
                            {
                                FontFamily = "FASolid",
                                Glyph = Icons.Orders,
                                Size = 20,
                            } : homeIcon
                        });

                        Routing.RegisterRoute(nameof(TableDetails), typeof(TableDetails));
                    }
                }

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
