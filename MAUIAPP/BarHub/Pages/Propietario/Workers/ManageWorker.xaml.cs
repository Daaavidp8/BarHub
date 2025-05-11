using BarHub.ViewModel.Owner.Workers;
using System.Diagnostics;
using UraniumUI.Dialogs;

namespace BarHub.Pages.Propietario.Workers;

public partial class ManageWorker : BarHubBaseContentPage
{
    private readonly IDialogService _dialogService;
    private readonly ManageWorkerViewModel _viewModel;
    public ManageWorker(ManageWorkerViewModel vm, IDialogService dialog)
	{
		InitializeComponent();
        BindingContext = _viewModel = vm;
        _dialogService = dialog;
    }

}