using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Owner;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;

namespace BarHub.Pages.Propietario;

public partial class OwnerPage : BarHubBaseContentPage
{
    private readonly IServiceProvider _services;
    private readonly IContext<SectionViewModel> _context;

    public OwnerPage(User user, IServiceProvider services)
    {
        InitializeComponent();
        _services = services;

        var gets = _services.GetService<Gets>();
        var deletes = _services.GetService<Deletes>();
        var puts = _services.GetService<Puts>();
        var posts = _services.GetService<Posts>();
        var sectionContext = _services.GetService<IContext<SectionViewModel>>();

        var ownerViewModel = new OwnerViewModel(gets, deletes, puts, posts, user, sectionContext);
        BindingContext = ownerViewModel;
        _context = sectionContext;
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

                var section = swipeView.BindingContext as SectionViewModel;


                var isConfirmed = await this.ShowPopupAsync(new ConfirmDeletePopUp($"Desea Eliminar {section.Name}?"));

                if (isConfirmed is null || !(bool)isConfirmed)
                {
                    ResetSwipeView(image, swipeView);
                }
                else
                {
                    try
                    {
                        await _context.NotifyObjectDeleted(section);

                        await this.DisplaySnackbar("Sección eliminada correctamente");
                    }
                    catch (Exception exception)
                    {
                        await this.DisplaySnackbar("No se ha podido eliminar la sección");
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