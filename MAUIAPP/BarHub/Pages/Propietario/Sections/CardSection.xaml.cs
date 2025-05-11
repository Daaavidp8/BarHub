using BarHub.Models;
using BarHub.Pages.Propietario.Articles;
using BarHub.ViewModel.Owner;
using System.Diagnostics;
using System.Text.Json;

namespace BarHub.Pages.Propietario;

public partial class CardSection : ContentView
{

    public static readonly BindableProperty SectionProperty =
            BindableProperty.Create(
                nameof(Section),
                typeof(SectionViewModel),
                typeof(CardSection),
                null
            );

    public SectionViewModel Section
    {
        get => (SectionViewModel)GetValue(SectionProperty);
        set => SetValue(SectionProperty, value);
    }
    public CardSection()
	{
		InitializeComponent();
	}

    private async void TapGestureRecognizer_Tapped(object sender, TappedEventArgs e)
    {
        await Shell.Current.GoToAsync(nameof(ManageSection), true, new Dictionary<string, object>
        {
            { "sectionJson" , JsonSerializer.Serialize(Section.Section) }
        });
    }

    private async void TapGestureRecognizer_Tapped_1(object sender, TappedEventArgs e)
    {
        await Shell.Current.GoToAsync(nameof(ArticlesPage), true, new Dictionary<string, object>
        {
            { "articles" , Section.Articles },
            { "section" , Section }
        });
    }
}