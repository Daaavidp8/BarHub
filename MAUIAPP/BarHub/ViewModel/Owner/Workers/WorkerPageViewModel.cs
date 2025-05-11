using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.Pages.Propietario.Articles;
using BarHub.Pages.Propietario.Workers;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using static System.Collections.Specialized.BitVector32;

namespace BarHub.ViewModel.Owner.Workers
{
    public partial class WorkersPageViewModel : ObservableObject
    {
        [ObservableProperty]
        ObservableCollection<WorkerViewModel> workers;


        [ObservableProperty]
        private string state = "Success";

        public WorkersPageViewModel( Gets gets, Deletes deletes, Puts puts, Posts posts, User user, IContext<WorkerViewModel> articleContext)
        {
            SetActions(articleContext, gets, deletes, puts, posts, user);
            SetWorkers(user.Restaurant, gets);
        }

        private async Task SetWorkers(int? restaurant, Gets gets)
        {
            Workers = new ObservableCollection<WorkerViewModel>(
                    (await gets.GetWorkers(restaurant.Value)).Select(r => new WorkerViewModel(r)));
        }

        private void SetActions(IContext<WorkerViewModel> articleContext, Gets gets, Deletes deletes, Puts puts, Posts posts, User user)
        {
            articleContext.ObjectCreated += async worker =>
            {
                var response = await posts.CreateWorker(worker.ToModel(), user.Restaurant);
                if (response is not null && response.Id != 0)
                {
                    worker.Id = response.Id;
                    Workers.Add(worker);
                }
            };

            articleContext.ObjectModified += async worker =>
            {
                await puts.ModifyWorker(worker.ToModel());
                var oldWorker = Workers.FirstOrDefault(x => x.Id == worker.Id);
                if (oldWorker is not null)
                {
                    oldWorker.Name = worker.Name;
                }
            };

            articleContext.ObjectDeleted += async worker =>
            {
                await deletes.DeleteWorker(worker.Id);
                var itemToRemove = Workers.FirstOrDefault(s => s.Id == worker.Id);
                if (itemToRemove is not null)
                {
                    Workers.Remove(itemToRemove);
                }
            };
        }

        [RelayCommand]
        private async Task GoToManageWorker(User user)
        {
            if (user is null)
            {
                user = new User();
            }

            await Shell.Current.GoToAsync(nameof(ManageWorker), true, new Dictionary<string, object>
                {
                    { "workerJson" , JsonSerializer.Serialize(user) },
                });
        }
    }
}
