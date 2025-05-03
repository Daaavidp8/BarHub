using BarHub.Lib;
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
    public AdminPage(AdminViewModel vm ,AdminViewModel adminvm, IContext<RestaurantViewModel> context)
	{
		InitializeComponent();
		BindingContext = vm;
        _context = context;
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

            
                var isConfirmed = await this.ShowPopupAsync(new ConfirmDeletePopUp($"Desea eliminar {swipeView}"));

                if (isConfirmed is null || !(bool)isConfirmed)
                {
                    ResetSwipeView(image, swipeView);
                    }
                else
                {
                    try
                    {
                        var restaurant = (RestaurantViewModel)swipeView.BindingContext;
                        _context.NotifyObjectDeleted(restaurant);

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