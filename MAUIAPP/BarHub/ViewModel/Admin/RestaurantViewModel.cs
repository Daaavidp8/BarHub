using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using System.Collections.Generic;

public partial class RestaurantViewModel : ObservableObject
{
    [ObservableProperty]
    private Restaurant restaurant;

    public RestaurantViewModel(Restaurant restaurant)
    {
        this.restaurant = restaurant;
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

    public List<Section> Sections
    {
        get => restaurant.Sections;
        set => SetProperty(restaurant.Sections, value, restaurant, (r, v) => r.Sections = v);
    }

    public List<User> Users
    {
        get => restaurant.Users;
        set => SetProperty(restaurant.Users, value, restaurant, (r, v) => r.Users = v);
    }

    public Restaurant ToModel() => restaurant;
}
