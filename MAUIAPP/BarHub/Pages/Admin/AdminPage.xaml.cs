using BarHub.Lib;
using BarHub.ViewModel.Admin;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;
using Mopups.Interfaces;
using System.Diagnostics;
using System.Threading.Tasks;
using UraniumUI.Dialogs;

namespace BarHub.Pages.Admin;

public partial class AdminPage : BarHubBaseContentPage
{
    private readonly AdminViewModel _adminViewModel;
    public AdminPage(AdminViewModel vm ,AdminViewModel adminvm)
	{
		InitializeComponent();
		BindingContext = vm;
        _adminViewModel = adminvm;
    }

    private void SwipedItem(object sender, EventArgs e)
    {
        this.ShowPopup(new ConfirmDeletePopUp());
    }

    private async void SwipeView_SwipeEnded(object sender, SwipeEndedEventArgs e)
    {
        if (!e.IsOpen) return;

        var swipeView = sender as SwipeView;


        var swipeItemView = swipeView?.RightItems.FirstOrDefault() as SwipeItemView;

        if (swipeItemView?.Content is Layout layout)
        {
            var image = layout.Children
                              .OfType<Image>()
                              .FirstOrDefault(i => i.StyleId == "iconTrash");

            if (image != null)
            {
                await image.TranslateTo(-380, 0, 300);

            
                var isConfirmed = await this.ShowPopupAsync(new ConfirmDeletePopUp());

                if (isConfirmed is null || !(bool)isConfirmed)
                {
                    ResetSwipeView(image, swipeView);
                    }
                else
                {
                    try
                    {
                        var restaurant = (RestaurantViewModel)swipeView.BindingContext;
                        await _adminViewModel.DeleteRestaurant(restaurant);

                        await this.DisplaySnackbar("Restaurante eliminado correctamente");
                    }
                    catch (Exception exception)
                    {
                        await this.DisplaySnackbar("No se ha podido eliminar el restaurante");
                        ResetSwipeView(image, swipeView);
                    }
                }
            }
        }
    }
    private async void ResetSwipeView(Image image,SwipeView swipeView)
    {
        image.TranslateTo(0, 0, 200);
        swipeView.Close();
    }
}