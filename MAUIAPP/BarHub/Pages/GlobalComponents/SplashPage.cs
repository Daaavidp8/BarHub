using BarHub.Lib;
using BarHub.Models;
using BarHub.ViewModel.Login;
using Newtonsoft.Json;
using System.Diagnostics;

namespace BarHub.Pages.GlobalComponents;

public class SplashPage : ContentPage
{
    private IServiceProvider _services;
    private Posts _posts;
    public SplashPage(IServiceProvider services)
    {
        _services = services;
        _posts = services.GetService<Posts>();
        Content = new StackLayout
        {
            VerticalOptions = LayoutOptions.Center,
            Children =
            {
                new ActivityIndicator { IsRunning = true },
                new Label { Text = "Cargando...", HorizontalOptions = LayoutOptions.Center }
            }
        };
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();


        var isLogged = Preferences.Get("IsLoggedIn", false);

        if (isLogged)
        {
            var serializedUser = Preferences.Get("user", null);
            if (!string.IsNullOrEmpty(serializedUser))
            {
                var user = JsonConvert.DeserializeObject<User>(serializedUser);
                if (user != null)
                {
                    Application.Current.MainPage = new AppShell(user);
                    return;
                }
            }
        }

        Application.Current.MainPage = _services.GetService<Login.Login>();
    }

}