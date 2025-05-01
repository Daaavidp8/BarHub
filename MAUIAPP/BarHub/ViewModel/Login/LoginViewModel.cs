using BarHub.Lib;
using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using Microsoft.Maui.ApplicationModel;
using Newtonsoft.Json;
using System;
using System.Diagnostics;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Login
{
    public partial class LoginViewModel : ObservableObject
    {
        private readonly Posts _posts;
        private readonly IServiceProvider _services;

        [ObservableProperty]
        string username, password;

        [ObservableProperty]
        bool isReminding = false;

        public LoginViewModel(Posts posts, IServiceProvider services)
        {
            _posts = posts;
            _services = services;
        }

        [RelayCommand]
        async Task Login()
        {
            try
            {
                User user = await _posts.Login(username, password);
                if (user.Token != null)
                {
                    Preferences.Set("IsLoggedIn", IsReminding);

                    user.Password = Password;
                    string serializedUser = JsonConvert.SerializeObject(user);
                    Preferences.Set("user", serializedUser);

                    await MainThread.InvokeOnMainThreadAsync(() =>
                    {
                        Application.Current.MainPage = new AppShell(user, _services);
                    });
                }
            }
            catch (Exception ex)
            {
                Trace.WriteLine(ex.Message);
            }
        }
    }
}
