using BarHub.ViewModel.Owner;

namespace BarHub.Pages.Propietario;

public partial class ManageSection : BarHubBaseContentPage
{
	public ManageSection(ManageSectionViewModel viewModel)
	{
		InitializeComponent();
        BindingContext = goBackComponent.BindingContext = viewModel;
    }
}