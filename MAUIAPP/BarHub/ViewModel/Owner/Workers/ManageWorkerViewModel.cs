using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.Resources.Languages;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using Microsoft.Maui.Controls;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using UraniumUI.Dialogs;

namespace BarHub.ViewModel.Owner.Workers
{
    [QueryProperty(nameof(WorkerJson), "workerJson")]
    public partial class ManageWorkerViewModel: ObservableObject
    {
        private readonly FunctionsUI _functionsUI;
        private readonly IDialogService _dialogService;
        private readonly IContext<WorkerViewModel> _articleContext;

        [ObservableProperty]
        private string workerJson;

        [ObservableProperty]
        private ObservableCollection<Roles> rolesRemaining;

        [ObservableProperty]
        private WorkerViewModel worker;

        [ObservableProperty]
        private string title = AppResources.AddRestaurantText, buttonText = AppResources.CreateRestaurantText;

        [ObservableProperty]
        private bool isButtonAddRoleVisible = true;


        public ManageWorkerViewModel(FunctionsUI fUI, IContext<WorkerViewModel> articleContext, IDialogService dialogService)
        {
            _functionsUI = fUI;
            _articleContext = articleContext;
            _dialogService = dialogService;
        }


        partial void OnWorkerJsonChanged(string value)
        {
            try
            {
                if (!string.IsNullOrWhiteSpace(value))
                {
                    var model = JsonSerializer.Deserialize<User>(value);
                    if (model != null)
                    {
                        Worker = new WorkerViewModel(model);
                        if (model.Id != 0)
                        {
                            Title = AppResources.ModifyRestaurantText;
                            ButtonText = AppResources.SaveChangesText;
                        }
                    }
                }
                GetRemainingRoles();
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error deserializing restaurantJson: {ex.Message}");
            }
        } 


        [RelayCommand]
        private async Task ActionButtonPressed()
        {
            try
            {
                if (Worker.Id == 0)
                {
                    await _articleContext.NotifyObjectCreated(Worker);
                }
                else
                {
                    await _articleContext.NotifyObjectModified(Worker);
                }


                await Shell.Current.GoToAsync("..");

            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }


        [RelayCommand]
        private async void AddRole()
        {
            var option = await _dialogService.DisplayRadioButtonPromptAsync("Selecciona un rol", RolesRemaining);

            var selectedRole = (Roles)option;
            if (selectedRole != 0 && !Worker.RolesUsuario.Contains(selectedRole))
            {
                Worker.RolesUsuario.Add(selectedRole);
                RolesRemaining.Remove(selectedRole);

                if (RolesRemaining.Count == 0)
                {
                    IsButtonAddRoleVisible = false;
                }
            }
        }

        [RelayCommand]
        private async void ChangeRole(Roles role)
        {
            var option = await _dialogService.DisplayRadioButtonPromptAsync("Selecciona un rol", RolesRemaining);
            if (option != 0)
            {
                var position = Worker.RolesUsuario.IndexOf(role);
                Worker.RolesUsuario.Remove(role);
                Worker.RolesUsuario.Insert(position, option);
            }
        }

        [RelayCommand]
        private async void DeleteRole(Roles role)
        {
            Worker.RolesUsuario.Remove(role);
            GetRemainingRoles();

            if (!IsButtonAddRoleVisible)
            {
                IsButtonAddRoleVisible = true;
            }
        }

        private void GetRemainingRoles()
        {
            var assignedRoles = new ObservableCollection<Roles>(Worker?.RolesUsuario) ?? new ObservableCollection<Roles>();
            var allRoles = GetAllRoles();
            RolesRemaining = new ObservableCollection<Roles>(allRoles.Except(assignedRoles));
        }

        private List<Roles> GetAllRoles()
        {
            return Enum.GetValues(typeof(Roles)).Cast<Roles>().ToList();
        }
    }
}

