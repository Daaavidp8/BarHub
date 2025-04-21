using BarHub.ViewModel.Admin;

namespace BarHub.Pages.Admin;

public partial class AdminPage : BarHubBaseContentPage
{
	public AdminPage(AdminViewModel vm)
	{
		InitializeComponent();
		BindingContext = vm;
    }
}