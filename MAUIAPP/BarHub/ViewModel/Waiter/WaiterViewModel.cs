using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Waiter;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Waiter
{
    public partial class WaiterViewModel : ObservableObject
    {
        private readonly Deletes _deletes;
        private readonly Posts _posts;
        private readonly Gets _gets;
        private readonly User _user;

        [ObservableProperty]
        private ObservableCollection<Table> tables;


        [ObservableProperty]
        private string state = "Success";


        public WaiterViewModel(Deletes deletes, Posts posts, Gets gets, User user)
        {
            GetTables(gets,user);
            _deletes = deletes;
            _posts = posts;
            _gets = gets;
            _user = user;
        }
        public async void GetTables(Gets gets,User user)
        {
            var tablesTemp = await gets.GetTables(user.Restaurant.Value);

            Tables = new ObservableCollection<Table>(tablesTemp);

        }

        [RelayCommand]
        public async void AddTable()
        {
            var response = await _posts.CreateTable(_user.Restaurant.Value);
            Tables.Add(response);
        }

        [RelayCommand]
        public void RemoveTable()
        {
            Tables.RemoveAt(Tables.Count - 1);
            _deletes.DeleteTable(_user.Restaurant.Value);
        }

        [RelayCommand]
        private async Task GoToTableDetail(Table table)
        {
            await Shell.Current.GoToAsync(nameof(TableDetails), true, new Dictionary<string, object>
                {
                    { "table" , table },
                });
        }

    }
}
