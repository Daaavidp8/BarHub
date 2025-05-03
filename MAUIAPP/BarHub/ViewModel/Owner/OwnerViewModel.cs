using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.Pages.Propietario;
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
using Section = BarHub.Models.Section;

namespace BarHub.ViewModel.Owner
{
    public partial class OwnerViewModel : ObservableObject
    {
        [ObservableProperty]
        private string state = "Success";

        [ObservableProperty]
        private ObservableCollection<SectionViewModel> sections;

        public OwnerViewModel(Gets gets, Deletes deletes, Puts puts, Posts posts, User user, IContext<SectionViewModel> sectionContext)
        {
            SetActions(sectionContext, gets, deletes, puts, posts, user);
            SetSections(user.Restaurant, gets);
        }

        private void SetActions(IContext<SectionViewModel> sectionContext, Gets gets, Deletes deletes, Puts puts, Posts posts, User user)
        {
            sectionContext.ObjectCreated += async section =>
            {
                var response = await posts.CreateSection(section.ToModel(),user.Restaurant);
                if (response is not null && response.Id != 0)
                {
                    section.Id = response.Id;
                    Sections.Add(section);
                }
            };

            sectionContext.ObjectModified += async section =>
            {
                await puts.ModifySection(section.ToModel(),section.Id);
                var oldSection = Sections.FirstOrDefault(x => x.Id == section.Id);
                if (oldSection is not null)
                {
                    oldSection.Name = section.Name;
                    oldSection.Image = section.Image;
                }
            };

            sectionContext.ObjectDeleted += async section =>
            {
                await deletes.DeleteSection(section.Id);
                var itemToRemove = Sections.FirstOrDefault(s => s.Id == section.Id);
                if (itemToRemove is not null)
                {
                    Sections.Remove(itemToRemove);
                }
            };
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
                    { "sectionJson" , JsonSerializer.Serialize(section) }
                });
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error en GoToCreateSection: {ex.Message}");
            }
        }

        private async void SetSections(int? restaurant, Gets gets)
        {
            if (restaurant != null)
            {
                Sections = new ObservableCollection<SectionViewModel>(
                    (await gets.GetSections(restaurant.Value)).Select(r => new SectionViewModel(r)));
            }
        }
    }
}
