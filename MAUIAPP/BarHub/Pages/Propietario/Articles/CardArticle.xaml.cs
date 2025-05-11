using BarHub.ViewModel.Owner.Articles;
using System.Text.Json;

namespace BarHub.Pages.Propietario.Articles;

public partial class CardArticle : ContentView
{
    public static readonly BindableProperty ArticleProperty =
            BindableProperty.Create(
                nameof(Article),
                typeof(ArticleViewModel),
                typeof(CardArticle),
                null
            );

    public ArticleViewModel Article
    {
        get => (ArticleViewModel)GetValue(ArticleProperty);
        set => SetValue(ArticleProperty, value);
    }

    public CardArticle()
	{
		InitializeComponent();
	}

    private async void TapGestureRecognizer_Tapped(object sender, TappedEventArgs e)
    {
        await Shell.Current.GoToAsync(nameof(ManageArticle), true, new Dictionary<string, object>
        {
            { "articleJson" , JsonSerializer.Serialize(Article) }
        });
    }
}