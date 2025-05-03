using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.ViewModel.Interfaces;
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
using static System.Collections.Specialized.BitVector32;

namespace BarHub.ViewModel.Admin
{
    public partial class AdminViewModel : ObservableObject
    {

        [ObservableProperty]
        private string state = "Success";

        [ObservableProperty] ObservableCollection<RestaurantViewModel> restaurants;
        public AdminViewModel(Gets gets, Deletes deletes, Posts posts, Puts puts, IContext<RestaurantViewModel> RestaurantContext)
        {
            SetRestaurants(gets);
            SetActions(RestaurantContext, deletes, posts, puts);
        }

        private void SetActions(IContext<RestaurantViewModel> RestaurantContext, Deletes deletes, Posts posts, Puts puts)
        {
            RestaurantContext.ObjectCreated += async restaurant =>
            {
                var response = await posts.CreateRestaurant(restaurant.ToModel());
                if (response is not null && response.Id != 0)
                {
                    restaurant.Id = response.Id;
                    Restaurants.Add(restaurant);
                }
            };

            RestaurantContext.ObjectModified += async restaurant =>
            {
                await puts.ModifyRestaurant(restaurant.ToModel());
                var oldRestaurant = Restaurants.FirstOrDefault(x => x.Id == restaurant.Id);
                
                if (oldRestaurant is not null)
                {
                    oldRestaurant.Name = restaurant.Name;
                    oldRestaurant.Cif = restaurant.Cif;
                    oldRestaurant.Email = restaurant.Email;
                    oldRestaurant.Phone = restaurant.Phone;
                    oldRestaurant.Logo = restaurant.Logo;
                }
            };

            RestaurantContext.ObjectDeleted += async restaurant =>
            {
                await deletes.DeleteRestaurant(restaurant.Id);
                var itemToRemove = Restaurants.FirstOrDefault(s => s.Id == restaurant.Id);
                Restaurants.Remove(itemToRemove);
            };
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


        private async void SetRestaurants(Gets get)
        {
            Restaurants = new ObservableCollection<RestaurantViewModel>(
             (await get.GetOwners()).Select(r => new RestaurantViewModel(r)));

        }
    }
}
