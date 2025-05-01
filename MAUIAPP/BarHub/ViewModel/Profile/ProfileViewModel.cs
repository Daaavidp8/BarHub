using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Profile
{
    public partial class ProfileViewModel : ObservableObject
    {
        [ObservableProperty]
         UserViewModel user;

        public ProfileViewModel(User user)
        {
            this.user = new UserViewModel(user);
        }


        [RelayCommand]
        private void LogOut()
        {
            Trace.WriteLine("Logging out...");
        }
    }
}
