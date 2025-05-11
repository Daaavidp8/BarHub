using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Owner;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;

namespace BarHub.Pages.Propietario;

public partial class OwnerPage : BarHubBaseContentPage
{
    private readonly IServiceProvider _services;
    private readonly IContext<SectionViewModel> _context;
    private readonly FunctionsUI _functionsUI;

    public OwnerPage(User user, IServiceProvider services)
    {
        InitializeComponent();
        _services = services;

        var gets = _services.GetService<Gets>();
        var deletes = _services.GetService<Deletes>();
        var puts = _services.GetService<Puts>();
        var posts = _services.GetService<Posts>();
        var sectionContext = _services.GetService<IContext<SectionViewModel>>();

        var ownerViewModel = new SectionsPageViewModel(gets, deletes, puts, posts, user, sectionContext);
        BindingContext = ownerViewModel;
        _context = sectionContext;
        _functionsUI = _services.GetService<FunctionsUI>();
    }



    private async void SwipeView_SwipeEnded(object sender, SwipeEndedEventArgs e)
    {
        if (!e.IsOpen) return;
        var section = (SectionViewModel)((SwipeView)sender).BindingContext;
        _functionsUI.DeleteObjectWithPopUp<SectionViewModel>(this, sender, _context, section.Name);
    }
}