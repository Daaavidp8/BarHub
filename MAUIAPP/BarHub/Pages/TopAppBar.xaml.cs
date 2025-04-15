
using System.Diagnostics;

namespace BarHub.Pages;

public partial class TopAppBar : ContentView
{
    public static readonly BindableProperty PageTitleProperty =
    BindableProperty.Create(
        nameof(PageTitle),         
        typeof(string),          
        typeof(TopAppBar),    
        default(string));

    public string PageTitle
    {
        get => (string)GetValue(PageTitleProperty);
        set
        {
            SetValue(PageTitleProperty, value);
        }
    }

    public static readonly BindableProperty IconTemplateProperty =
    BindableProperty.Create(
        nameof(IconTemplate),
        typeof(View),
        typeof(TopAppBar),
        default(View));

    public View IconTemplate
    {
        get => (View)GetValue(IconTemplateProperty);
        set => SetValue(IconTemplateProperty, value);
    }



    public TopAppBar()
	{
		InitializeComponent();
	}
}