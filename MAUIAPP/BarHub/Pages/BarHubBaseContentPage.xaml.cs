
using System.Diagnostics;

namespace BarHub.Pages;

public partial class BarHubBaseContentPage : ContentPage
{
    public static readonly BindableProperty PageTitleProperty =
        BindableProperty.Create(
            nameof(PageTitle),
            typeof(string),
            typeof(BarHubBaseContentPage),
            default(string));


    public string PageTitle
    {
        get => (string)GetValue(PageTitleProperty);
        set => SetValue(PageTitleProperty, value);
    }

    public static readonly BindableProperty IconTemplateProperty =
    BindableProperty.Create(
        nameof(Icon),
        typeof(View),
        typeof(BarHubBaseContentPage),
        default(View));


    public View Icon
    {
        get => (View)GetValue(IconTemplateProperty);
        set => SetValue(IconTemplateProperty, value);
    }

    public BarHubBaseContentPage()
	{
		InitializeComponent();
    }



}