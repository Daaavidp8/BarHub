using BarHub.Models;
using BarHub.Models.Enums;
using CommunityToolkit.Mvvm.ComponentModel;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Profile
{
    public partial class UserViewModel : ObservableObject
    {
        [ObservableProperty]
        private User user;

        public UserViewModel(User user)
        {
            this.user = user;
        }

        public int Id
        {
            get => user.Id;
            set => SetProperty(user.Id, value, user, (r, v) => r.Id = v);
        }

        public string Name
        {
            get => user.Name;
            set => SetProperty(user.Name, value, user, (r, v) => r.Name = v);
        }

        public string UserName
        {
            get => user.Username;
            set => SetProperty(user.Username, value, user, (r, v) => r.Username = v);
        }

        public List<Roles> Roles
        {
            get => user.Roles;
            set => SetProperty(user.Roles, value, user, (r, v) => r.Roles = v);
        }

        public string RolesString
        {
            get => RolesToString();
        }

        public string RolesToString()
        {
            if (Roles == null || Roles.Count == 0)
                return string.Empty;

            var roleNames = Roles.Select(r => GetRoleDisplayName(r)).ToList();

            if (roleNames.Count == 1)
                return roleNames[0];

            if (roleNames.Count == 2)
                return $"{roleNames[0]} y {roleNames[1]}";

            return string.Join(", ", roleNames.Take(roleNames.Count - 1)) + " y " + roleNames.Last();
        }

        private string GetRoleDisplayName(Roles role)
        {
            return role switch
            {
                Models.Enums.Roles.ADMIN => "Administrador",
                Models.Enums.Roles.PROPIETARIO => "Propietario",
                Models.Enums.Roles.CAMARERO => "Camarero",
                _ => role.ToString()
            };
        }




        public User ToModel() => user;
    }
}
