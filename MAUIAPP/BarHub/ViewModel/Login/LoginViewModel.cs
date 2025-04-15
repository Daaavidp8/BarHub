using BarHub.Lib;
using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using Microsoft.Maui.Controls;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Login
{
    public partial class LoginViewModel : ObservableObject
    {
        private readonly Posts _posts;
        private readonly IServiceProvider _services;

        [ObservableProperty]
        string username,password;

        [ObservableProperty]
        bool isReminding = false;

        public LoginViewModel(Posts posts,IServiceProvider services)
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
                    Preferences.Set("username", Username);
                    Preferences.Set("password", Password);
                    App.Current.MainPage = new AppShell(_services,user);
                }
            }
            catch (Exception ex)
            {
               Trace.WriteLine(ex.Message);
            }
            
        }
    }
}
