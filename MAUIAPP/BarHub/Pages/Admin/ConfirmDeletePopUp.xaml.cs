using CommunityToolkit.Maui.Views;

namespace BarHub.Pages.Admin;

public partial class ConfirmDeletePopUp : Popup
{
	public ConfirmDeletePopUp()
	{
		InitializeComponent();
    }

    private void OnConfirmClicked(object sender, EventArgs e)
    {
        Close(true);
    }

    private void OnCancelClicked(object sender, EventArgs e)
    {
        Close(false);
    }
}