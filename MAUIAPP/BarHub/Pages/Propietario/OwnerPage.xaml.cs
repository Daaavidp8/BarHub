using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.ViewModel.Owner;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;

namespace BarHub.Pages.Propietario;

public partial class OwnerPage : BarHubBaseContentPage
{
    private readonly IServiceProvider _services;

    public OwnerPage(User user, IServiceProvider services)
    {
        InitializeComponent();
        _services = services;

        var gets = _services.GetService<Gets>();
        var deletes = _services.GetService<Deletes>();

        var ownerViewModel = new OwnerViewModel(gets, deletes, user);
        BindingContext = ownerViewModel;
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
    private async void ResetSwipeView(Image image, SwipeView swipeView)
    {
        image.TranslateTo(0, 0, 200);
        swipeView.Close();
    }
}