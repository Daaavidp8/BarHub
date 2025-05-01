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
using System.Text.Json;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Admin
{
    public partial class AdminViewModel : ObservableObject
    {
        private readonly Gets _gets;
        private readonly Deletes _deletes;

        [ObservableProperty]
        private string state = "Success";

        [ObservableProperty] ObservableCollection<RestaurantViewModel> restaurants;
        public AdminViewModel(Gets gets, Deletes deletes)
        {
            _gets = gets;
            SetRestaurants(gets);
            _deletes = deletes;
        }


        [RelayCommand]
        public async Task GoToManageRestaurant(Restaurant restaurant)
        {
            try
            {
                if (restaurant is null)
                {
                    restaurant = new Restaurant();
                }

                Trace.WriteLine($"Restaurant: {restaurant.Name}");


                await Shell.Current.GoToAsync(nameof(ManageRestaurant), true, new Dictionary<string, object>
                {
                    { "restaurantJson" , JsonSerializer.Serialize(restaurant.Clone()) }
                });

            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error en GoToManageRestaurant: {ex.Message}");
            }
        }

        public void AddRestaurant(RestaurantViewModel restaurant)
        {
            Restaurants.Add(restaurant);
        }

        private async void SetRestaurants(Gets get)
        {
            Restaurants = new ObservableCollection<RestaurantViewModel>(
             (await get.GetOwners()).Select(r => new RestaurantViewModel(r)));

        }

        public async Task DeleteRestaurant(RestaurantViewModel restaurant)
        {
            try
            {
                _deletes.DeleteRestaurant(restaurant.Id.ToString());
                Restaurants.Remove(restaurant);
            }
            catch (Exception ex)
            {
                throw new Exception($"Error deleting restaurant: {ex.Message}");
            }
        }
    }
}
