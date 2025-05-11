using BarHub.ViewModel.Owner.Articles;

namespace BarHub.Pages.Propietario.Articles;

public partial class ManageArticle : BarHubBaseContentPage
{
	public ManageArticle(ManageArticleViewModel vm)
	{
		InitializeComponent();
        BindingContext = vm;
    }
}