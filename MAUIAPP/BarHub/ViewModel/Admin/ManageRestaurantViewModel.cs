using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.Resources.Languages;
using BarHub.Utils.Shell;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using CommunityToolkit.Mvvm.Messaging;
using Microsoft.Maui.Controls;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Admin
{
    [QueryProperty(nameof(Restaurant), nameof(Restaurant))]
    public partial class ManageRestaurantViewModel : ObservableObject
    {
        [ObservableProperty]
        private string title = AppResources.AddRestaurantText,buttonText = AppResources.CreateRestaurantText;

        [ObservableProperty]
        private Restaurant restaurant;


        [RelayCommand]
        public async Task GoBack()
        {
            try
            {
                await Shell.Current.GoToAsync("..", true);
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en GoBack: {ex.Message}");
            }
        }

        partial void OnRestaurantChanged(Restaurant restaurant)
        {
            if (restaurant.Id != 0)
            {
                Title = AppResources.ModifyRestaurantText;
                ButtonText = AppResources.SaveChangesText;
            }
        }
    }
}
