
using System.Diagnostics;
using System.Windows.Input;
using UraniumUI.Pages;
namespace BarHub.Pages;

public partial class BarHubBaseContentPage : UraniumContentPage
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

    public static readonly BindableProperty FabRouteProperty =
    BindableProperty.Create(
        nameof(FabRoute),
        typeof(ICommand),
        typeof(BarHubBaseContentPage),
        default(ICommand));


    public ICommand FabRoute
    {
        get => (ICommand)GetValue(FabRouteProperty);
        set => SetValue(FabRouteProperty, value);
    }

    public BarHubBaseContentPage()
	{
		InitializeComponent();
    }



}