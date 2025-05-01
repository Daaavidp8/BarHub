using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using Microsoft.Maui.Controls;
using System;
using System.IO;
using System.Threading.Tasks;
using System.Diagnostics;
using BarHub.Resources.Languages;
using System.Text.Json;
using BarHub.Lib;
using System.Collections.ObjectModel;

namespace BarHub.ViewModel.Admin
{
    [QueryProperty(nameof(RestaurantJson), "restaurantJson")]
    public partial class ManageRestaurantViewModel : ObservableObject
    {
        private readonly Puts _puts;
        private readonly Posts _posts;

        private readonly AdminViewModel _adminViewModel;


        [ObservableProperty]
        private string restaurantJson;

        private List<RestaurantViewModel> Restaurants;

        [ObservableProperty]
        private string title = AppResources.AddRestaurantText, buttonText = AppResources.CreateRestaurantText;

        [ObservableProperty]
        private RestaurantViewModel restaurant;

        private Restaurant originalRestaurant;


        public ManageRestaurantViewModel(Posts posts, AdminViewModel vm, Puts puts)
        {
            _posts = posts;
            _puts = puts;
            _adminViewModel = vm;
        }

        partial void OnRestaurantJsonChanged(string value)
        {
            try
            {
                if (!string.IsNullOrWhiteSpace(value))
                {
                    var model = JsonSerializer.Deserialize<Restaurant>(value);
                    if (model != null)
                    {
                        Restaurant = new RestaurantViewModel(model);
                        if (model.Id != 0)
                        {
                            originalRestaurant = restaurant.ToModel()?.Clone();
                            if (originalRestaurant != null)
                            {
                                Title = AppResources.ModifyRestaurantText;
                                ButtonText = AppResources.SaveChangesText;
                            }
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error deserializing restaurantJson: {ex.Message}");
            }
        }

        [RelayCommand]
        private async Task GoBack()
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

        [RelayCommand]
        private async Task PickFile()
        {
            try
            {
                var result = await FilePicker.PickAsync(new PickOptions
                {
                    PickerTitle = AppResources.SelectImageText,
                    FileTypes = FilePickerFileType.Png,
                });
                if (result is not null)
                {
                    using var stream = await result.OpenReadAsync();
                    using var memoryStream = new MemoryStream();
                    await stream.CopyToAsync(memoryStream);
                    var base64 = $"data:image/jpeg;base64,{Convert.ToBase64String(memoryStream.ToArray())}";
                    Restaurant.Logo = base64;
                }
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }


        [RelayCommand]
        private async Task ActionButtonPressed()
        {
            try
            {
                if (originalRestaurant is null)
                {
                    _posts.CreateRestaurant(Restaurant.ToModel());
                    _adminViewModel.Restaurants.Add(Restaurant);
                }
                else
                {
                    _puts.ModifyRestaurant(Restaurant.ToModel());
                    var restaurant = _adminViewModel.Restaurants.FirstOrDefault(x => x.Id == originalRestaurant.Id);
                    if (restaurant is not null)
                    {
                        restaurant.Name = Restaurant.Name;
                        restaurant.Cif = Restaurant.Cif;
                        restaurant.Email = Restaurant.Email;
                        restaurant.Phone = Restaurant.Phone;
                        restaurant.Logo = Restaurant.Logo;
                    }
                }


                GoBack();

            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }

       
    }
}
