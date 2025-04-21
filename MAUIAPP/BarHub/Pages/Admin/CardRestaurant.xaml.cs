using BarHub.Models;
using System.Threading.Tasks;
using System.Windows.Input;

namespace BarHub.Pages.Admin;

public partial class CardRestaurant : ContentView
{
    public static readonly BindableProperty RestaurantProperty =
            BindableProperty.Create(
                nameof(Restaurant),              
                typeof(Restaurant),                  
                typeof(CardRestaurant),          
                null
            );

    // Exponer la Bindable Property como una propiedad normal
    public Restaurant Restaurant
    {
        get => (Restaurant)GetValue(RestaurantProperty);
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
            { nameof(Restaurant), Restaurant }
        });
    }
}