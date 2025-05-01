using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.Pages.Propietario;
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
using Section = BarHub.Models.Section;

namespace BarHub.ViewModel.Owner
{
    public partial class OwnerViewModel : ObservableObject
    {

        private readonly Gets _gets;
        private readonly Deletes _deletes;

        [ObservableProperty]
        private string state = "Success";

        [ObservableProperty]
        private ObservableCollection<SectionViewModel> sections;

        public OwnerViewModel(Gets gets, Deletes deletes, User user)
        {
            _gets = gets;
            _deletes = deletes;
            SetSections(gets, user);
        }

        [RelayCommand]
        private async Task GoToCreateSection(Section section)
        {
            try
            {
                if (section is null)
                {
                    section = new Section();
                }
                await Shell.Current.GoToAsync(nameof(ManageSection), true, new Dictionary<string, object>
                {
                    { "restaurantJson" , JsonSerializer.Serialize(section) },
                    { "viewModel" , this }
                });
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error en GoToCreateSection: {ex.Message}");
            }
        }

        private async void SetSections(Gets gets, User user)
        {
            if (user.Restaurant != null)
            {
                Trace.WriteLine($"SetSections: {user.Restaurant.Value}");
                Sections = new ObservableCollection<SectionViewModel>(
                    (await gets.GetSections(user.Restaurant.Value)).Select(r => new SectionViewModel(r)));
            }
        }
    }
}
