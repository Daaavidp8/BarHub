using BarHub.Lib;
using BarHub.ViewModel.Login;
using System.Diagnostics;

namespace BarHub.Pages;

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
            try
            {
                var user = await _posts.Login(Preferences.Get("username", ""), Preferences.Get("password", ""));
                if (user is not null)
                {
                    Application.Current.MainPage = new AppShell(user);
                    return;
                }
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error during login: {ex.Message}");
            }
        }

        Application.Current.MainPage = _services.GetService<Login.Login>();
    }
}