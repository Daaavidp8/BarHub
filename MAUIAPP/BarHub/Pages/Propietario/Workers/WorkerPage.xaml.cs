using BarHub.Lib;
using BarHub.Models;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Owner.Workers;

namespace BarHub.Pages.Propietario.Workers;

public partial class WorkerPage : BarHubBaseContentPage
{

    private readonly IServiceProvider _services;
    private readonly IContext<WorkerViewModel> _context;
    private readonly FunctionsUI _functionsUI;
    public WorkerPage(User user, IServiceProvider services)
	{
		InitializeComponent();


        _services = services;

        var gets = _services.GetService<Gets>();
        var deletes = _services.GetService<Deletes>();
        var puts = _services.GetService<Puts>();
        var posts = _services.GetService<Posts>();
        var sectionContext = _services.GetService<IContext<WorkerViewModel>>();

        var workerViewModel = new WorkersPageViewModel(gets, deletes, puts, posts, user, sectionContext);
        BindingContext = workerViewModel;

        _context = sectionContext;
        _functionsUI = _services.GetService<FunctionsUI>();
    }



    private async void SwipeView_SwipeEnded(object sender, SwipeEndedEventArgs e)
    {
        if (!e.IsOpen) return;
        var section = (WorkerViewModel)((SwipeView)sender).BindingContext;
        _functionsUI.DeleteObjectWithPopUp<WorkerViewModel>(this, sender, _context, section.Name);
    }
}