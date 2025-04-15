using BarHub.ViewModel.Login;

namespace BarHub.Pages.Login;

public partial class Login : ContentPage
{
	public Login(LoginViewModel vm)
	{
		InitializeComponent();
		BindingContext = vm;
	}
}