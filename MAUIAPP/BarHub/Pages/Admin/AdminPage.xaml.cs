using BarHub.Lib;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Admin;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;
using Mopups.Interfaces;
using System.Diagnostics;
using System.Threading.Tasks;
using UraniumUI.Dialogs;

namespace BarHub.Pages.Admin;

public partial class AdminPage : BarHubBaseContentPage
{
    private readonly IContext<RestaurantViewModel> _context;
    private readonly FunctionsUI _functions;
    public AdminPage(AdminViewModel vm ,AdminViewModel adminvm, IContext<RestaurantViewModel> context, FunctionsUI functions)
	{
		InitializeComponent();
		BindingContext = vm;
        _context = context;
        _functions = functions;
    }

    private async void SwipeView_SwipeEnded(object sender, SwipeEndedEventArgs e)
    {
        if (!e.IsOpen) return;
        var restaurant = (RestaurantViewModel)((SwipeView)sender).BindingContext;
        _functions.DeleteObjectWithPopUp<RestaurantViewModel>(this, sender, _context, restaurant.Name);
    }
}