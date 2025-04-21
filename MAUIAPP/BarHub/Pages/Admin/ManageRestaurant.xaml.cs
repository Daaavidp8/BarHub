using Microsoft.Maui.Controls;
using BarHub.Models;
using System.Diagnostics;
using BarHub.ViewModel.Admin;

namespace BarHub.Pages.Admin;

public partial class ManageRestaurant : BarHubBaseContentPage
{
    public ManageRestaurant(ManageRestaurantViewModel viewModel)
    {
        InitializeComponent();
        BindingContext = goBackComponent.BindingContext = viewModel;
    }
}
