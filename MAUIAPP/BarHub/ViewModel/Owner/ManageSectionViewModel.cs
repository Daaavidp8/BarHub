using BarHub.Lib;
using BarHub.Models;
using BarHub.Resources.Languages;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
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
    public partial class ManageSectionViewModel : ObservableObject
    {
        private readonly FunctionsUI _functionsUI;
        private readonly IContext<SectionViewModel> _sectionContext;

        [ObservableProperty]
        private int restaurantId;

        [ObservableProperty]
        private string sectionJson;

        [ObservableProperty]
        private SectionViewModel section;

        [ObservableProperty]
        private string title = AppResources.AddRestaurantText, buttonText = AppResources.CreateRestaurantText;


        public ManageSectionViewModel(FunctionsUI fUI,IContext<SectionViewModel> sectionContext)
        {
            _functionsUI = fUI;
            _sectionContext = sectionContext;
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
                if (Section.Id == 0)
                {
                    await _sectionContext.NotifyObjectCreated(Section);
                }
                else
                {
                    await _sectionContext.NotifyObjectModified(Section);
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
