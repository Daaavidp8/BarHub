using BarHub.Models;
using BarHub.ViewModel.Profile;
using Newtonsoft.Json;

namespace BarHub.Pages.Profile;

public partial class ProfilePage : ContentPage
{
    public ProfilePage(User user)
	{
		InitializeComponent();
        var profileViewModel = new ProfileViewModel(user);
        BindingContext = profileViewModel;
    }

    protected override void OnAppearing()
    {
        base.OnAppearing();

        var serializedUser = Preferences.Get("user", null);
        if (!string.IsNullOrEmpty(serializedUser))
        {
            var user = JsonConvert.DeserializeObject<User>(serializedUser);
            if (user != null)
            {
                ((ProfileViewModel)BindingContext).User = new UserViewModel(user);
            }
        }
    }
}