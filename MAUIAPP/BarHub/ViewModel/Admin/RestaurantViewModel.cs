using BarHub.Models;
using BarHub.ViewModel.Admin;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Owner;
using CommunityToolkit.Mvvm.ComponentModel;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using static System.Collections.Specialized.BitVector32;

public partial class RestaurantViewModel : ObservableObject
{
    [ObservableProperty]
    private Restaurant restaurant;


    [ObservableProperty]
    private ObservableCollection<SectionViewModel> sections;

    public RestaurantViewModel(Restaurant restaurant)
    {
        this.restaurant = restaurant;

        if (restaurant.Sections is not null)
        {
            Sections = new ObservableCollection<SectionViewModel>(
                    restaurant.Sections.Select(s => new SectionViewModel(s)));
        }

    }

    public int Id
    {
        get => restaurant.Id;
        set => SetProperty(restaurant.Id, value, restaurant, (r, v) => r.Id = v);
    }

    public string Name
    {
        get => restaurant.Name;
        set => SetProperty(restaurant.Name, value, restaurant, (r, v) => r.Name = v);
    }

    public string Cif
    {
        get => restaurant.Cif;
        set => SetProperty(restaurant.Cif, value, restaurant, (r, v) => r.Cif = v);
    }

    public string Phone
    {
        get => restaurant.Phone;
        set => SetProperty(restaurant.Phone, value, restaurant, (r, v) => r.Phone = v);
    }

    public string Email
    {
        get => restaurant.Email;
        set => SetProperty(restaurant.Email, value, restaurant, (r, v) => r.Email = v);
    }

    public string Logo
    {
        get => restaurant.Logo;
        set => SetProperty(restaurant.Logo, value, restaurant, (r, v) => r.Logo = v);
    }

    public List<BarHub.Models.Section> GetSectionModels() => Sections.Select(svm => svm.ToModel()).ToList();


    public List<User> Users
    {
        get => restaurant.Users;
        set => SetProperty(restaurant.Users, value, restaurant, (r, v) => r.Users = v);
    }

    public Restaurant ToModel() => restaurant;
}
