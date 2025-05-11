using BarHub.Lib;
using BarHub.Models;
using BarHub.ViewModel.Waiter;

namespace BarHub.Pages.Camarero;

public partial class WaiterPage : BarHubBaseContentPage
{
	public WaiterPage(User user, IServiceProvider services)
	{
		InitializeComponent();
        var gets = services.GetService<Gets>();
        var deletes = services.GetService<Deletes>();
        var puts = services.GetService<Puts>();
        var posts = services.GetService<Posts>();

        var waiterViewModel = new WaiterViewModel(deletes, posts, gets, user);

        BindingContext = waiterViewModel;
    }
}