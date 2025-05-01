using BarHub.Models;
using BarHub.ViewModel.Owner;
using System.Diagnostics;
using System.Text.Json;
using System.Threading.Tasks;
using System.Windows.Input;

namespace BarHub.Pages.Admin;

public partial class CardRestaurant : ContentView
{
    public static readonly BindableProperty RestaurantProperty =
            BindableProperty.Create(
                nameof(Restaurant),              
                typeof(RestaurantViewModel),                  
                typeof(CardRestaurant),          
                null
            );

    public RestaurantViewModel Restaurant
    {
        get => (RestaurantViewModel)GetValue(RestaurantProperty);
        set => SetValue(RestaurantProperty, value);
    }


    public CardRestaurant()
	{
		InitializeComponent();
	}
    private async void OnNavigateToEditRestaurant(object sender, TappedEventArgs e)
    {
        await Shell.Current.GoToAsync(nameof(ManageRestaurant), true, new Dictionary<string, object>
        {
            { "restaurantJson" , JsonSerializer.Serialize(Restaurant.Restaurant.Clone()) }
        });
    }




}