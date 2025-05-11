using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Owner.Articles;

namespace BarHub.Pages.Propietario.Articles;

public partial class ArticlesPage : BarHubBaseContentPage
{
    private readonly FunctionsUI _functionsUI;
    private readonly IContext<ArticleViewModel> _context;
    public ArticlesPage(ArticlesPageViewModel vm, FunctionsUI functions, IContext<ArticleViewModel> context)
	{
		InitializeComponent();
		BindingContext = vm;
        _functionsUI = functions;
        _context = context;
    }

    private void SwipeView_SwipeEnded(object sender, SwipeEndedEventArgs e)
    {
        if (!e.IsOpen) return;
        var article = (ArticleViewModel)((SwipeView)sender).BindingContext;
        _functionsUI.DeleteObjectWithPopUp<ArticleViewModel>(this, sender, _context, article.Name);
    }
}