using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using MPowerKit.VirtualizeListView;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Admin
{
    public partial class AdminViewModel : ObservableObject
    {
        private readonly Gets _gets;

        [ObservableProperty] ObservableRangeCollection<Restaurant> restaurants;
        public AdminViewModel(Gets gets) {
            _gets = gets;
            SetRestaurants(gets);
        }

        [RelayCommand]
        public async Task GoToCreateRestaurant(Restaurant restaurant)
        {
            try
            {
                await Shell.Current.GoToAsync(nameof(ManageRestaurant), true, new Dictionary<string, object>
                {
                    { nameof(Restaurant), new Restaurant(){ Id = 0} }
                });
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error en GoToManageRestaurant: {ex.Message}");
            }
        }

        private async void SetRestaurants(Gets get)
        {
            Restaurants = new(await get.GetOwners());
        }
    }
}
