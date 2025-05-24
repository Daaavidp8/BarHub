using BarHub.Lib;
using BarHub.Models;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Order;

namespace BarHub.Pages.Orders;

public partial class OrdersPage : BarHubBaseContentPage
{
	public OrdersPage(User user, IServiceProvider services)
	{
		InitializeComponent();
        var gets = services.GetService<Gets>();
        var deletes = services.GetService<Deletes>();
        var puts = services.GetService<Puts>();
        var posts = services.GetService<Posts>();
        var orderLineContext = services.GetService<IContext<OrderLineViewModel>>();

        var waiterViewModel = new OrderPageViewModel(orderLineContext,gets, puts, user);

        BindingContext = waiterViewModel;
    }
}