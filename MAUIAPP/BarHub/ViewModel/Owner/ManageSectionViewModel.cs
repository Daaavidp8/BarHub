using BarHub.Lib;
using BarHub.Models;
using BarHub.Resources.Languages;
using BarHub.Utils.UI.General;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Owner
{
    [QueryProperty(nameof(SectionJson), "sectionJson")]
    [QueryProperty(nameof(OwnerViewModel), "viewModel")]
    public partial class ManageSectionViewModel : ObservableObject
    {
        private readonly FunctionsUI _functionsUI;
        private readonly Puts _puts;
        private readonly Posts _posts;

        public OwnerViewModel OwnerViewModel
        {
            get => _ownerViewModel;
            set => _ownerViewModel = value;
        }

        private OwnerViewModel _ownerViewModel;

        [ObservableProperty]
        private string sectionJson;

        [ObservableProperty]
        private SectionViewModel section;

        [ObservableProperty]
        private string title = AppResources.AddRestaurantText, buttonText = AppResources.CreateRestaurantText;


        public ManageSectionViewModel(Puts puts, Posts posts, FunctionsUI fUI)
        {
            _puts = puts;
            _posts = posts;
            _functionsUI = fUI;
        }


        partial void OnSectionJsonChanged(string value)
        {
            try
            {
                if (!string.IsNullOrWhiteSpace(value))
                {
                    var model = JsonSerializer.Deserialize<Section>(value);
                    if (model != null)
                    {
                        Section = new SectionViewModel(model);
                        if (model.Id != 0)
                        {
                            Title = AppResources.ModifyRestaurantText;
                            ButtonText = AppResources.SaveChangesText;
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
        private async Task PickFile()
        {
            try
            {
                Section.Image = await _functionsUI.PickFile(AppResources.SelectImageText);
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
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
        private async Task ActionButtonPressed()
        {
            try
            {
                Trace.WriteLine(OwnerViewModel);
                Trace.WriteLine($"ActionButtonPressed: {SectionJson}");

                //if (sectionJson is null)
                //{
                //    _posts.CreateRestaurant(Restaurant.ToModel());
                //    _adminViewModel.Restaurants.Add(Restaurant);
                //}
                //else
                //{
                //    _puts.ModifyRestaurant(Restaurant.ToModel());
                //    var restaurant = _adminViewModel.Restaurants.FirstOrDefault(x => x.Id == originalRestaurant.Id);
                //    if (restaurant is not null)
                //    {
                //        restaurant.Name = Restaurant.Name;
                //        restaurant.Cif = Restaurant.Cif;
                //        restaurant.Email = Restaurant.Email;
                //        restaurant.Phone = Restaurant.Phone;
                //        restaurant.Logo = Restaurant.Logo;
                //    }
                //}


                GoBack();

            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }
    } 
}
