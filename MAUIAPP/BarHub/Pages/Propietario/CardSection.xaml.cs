using BarHub.Models;
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
        var ownerViewModel = FindOwnerViewModel(sender);
        if (ownerViewModel is null)
        {
            Debug.WriteLine("OwnerViewModel not found in visual tree.");
            return;
        }

        await Shell.Current.GoToAsync(nameof(ManageSection), true, new Dictionary<string, object>
        {
            { "sectionJson" , JsonSerializer.Serialize(Section.Section) },
            { "viewModel" , ownerViewModel }
        });
    }

    private OwnerViewModel? FindOwnerViewModel(object sender)
    {
        if (sender is not Element element)
            return null;

        while (element != null)
        {
            if (element.BindingContext is OwnerViewModel ownerVm)
                return ownerVm;

            element = element.Parent;
        }

        return null;
    }

}