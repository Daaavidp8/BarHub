using BarHub.ViewModel.Waiter;

namespace BarHub.Pages.Waiter;

public partial class TableDetails : BarHubBaseContentPage
{
	public TableDetails(DetailsTableViewModel vm)
	{
		InitializeComponent();
        BindingContext = vm;
    }
}