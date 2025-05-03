using BarHub.Lib;
using BarHub.Models;
using BarHub.ViewModel.Login;
using CommunityToolkit.Maui.Behaviors;
using CommunityToolkit.Maui.Core;
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

        var statusBarBehavior = new StatusBarBehavior
        {
            StatusBarColor = (Color)Application.Current.Resources["Black"],
            StatusBarStyle = StatusBarStyle.LightContent
        };

        this.Behaviors.Add(statusBarBehavior);
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();

        //Preferences.Set("user", null);

        var isLogged = Preferences.Get("IsLoggedIn", false);

        if (isLogged)
        {
            var serializedUser = Preferences.Get("user", null);
            if (!string.IsNullOrEmpty(serializedUser))
            {
                var user = JsonConvert.DeserializeObject<User>(serializedUser);
                if (user != null)
                {
                    var isLoginSuccess = await _posts.Login(user.Username,user.Password);
                    if (isLoginSuccess is not null)
                    {
                        Application.Current.MainPage = new AppShell(user, _services);
                        return;
                    }
                }
            }
        }

        Application.Current.MainPage = _services.GetService<Login.Login>();
    }

}