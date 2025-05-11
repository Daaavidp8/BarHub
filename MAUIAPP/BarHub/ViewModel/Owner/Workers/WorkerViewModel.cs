using BarHub.Models;
using BarHub.Models.Enums;
using CommunityToolkit.Mvvm.ComponentModel;
using Microsoft.Maui.Controls;
using System.Collections.ObjectModel;

namespace BarHub.ViewModel.Owner.Workers
{
    public partial class WorkerViewModel : ObservableObject
    {
        [ObservableProperty]
        private User worker;


        [ObservableProperty]
        private ObservableCollection<Roles> rolesUsuario;

        public WorkerViewModel(User worker)
        {
            this.worker = worker;
            RolesUsuario = worker.Roles is not null ? new ObservableCollection<Roles>(worker.Roles) : new();
        }

        public int Id
        {
            get => Worker.Id;
            set => SetProperty(Worker.Id, value, Worker, (r, v) => r.Id = v);
        }

        public string Username
        {
            get => Worker.Username;
            set => SetProperty(Worker.Username, value, Worker, (r, v) => r.Username = v);
        }

        public string Name
        {
            get => Worker.Name;
            set => SetProperty(Worker.Name, value, Worker, (r, v) => r.Name = v);
        }

        public string Password
        {
            get => Worker.Password;
            set => SetProperty(Worker.Password, value, Worker, (r, v) => r.Password = v);
        }


        public User ToModel()
        {
            Worker.Roles = RolesUsuario.ToList();
            return Worker;
        }


    }
}
