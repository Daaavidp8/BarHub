using BarHub.ViewModel.Order;
using System.Windows.Input;

namespace BarHub.Pages.Orders;

public partial class OrderCard : ContentView
{
    public static readonly BindableProperty OrderLineProperty =
        BindableProperty.Create(
            nameof(OrderLine),
            typeof(OrderLineViewModel),
            typeof(OrderCard),
            null
        );

    public OrderLineViewModel OrderLine
    {
        get => (OrderLineViewModel)GetValue(OrderLineProperty);
        set => SetValue(OrderLineProperty, value);
    }

    public static readonly BindableProperty ReadyCommandProperty =
        BindableProperty.Create(
            nameof(ReadyCommand),
            typeof(ICommand),
            typeof(OrderCard),
            null
        );

    public ICommand ReadyCommand
    {
        get => (ICommand)GetValue(ReadyCommandProperty);
        set => SetValue(ReadyCommandProperty, value);
    }

    public static readonly BindableProperty CancelCommandProperty =
        BindableProperty.Create(
            nameof(CancelCommand),
            typeof(ICommand),
            typeof(OrderCard),
            null
        );

    public ICommand CancelCommand
    {
        get => (ICommand)GetValue(CancelCommandProperty);
        set => SetValue(CancelCommandProperty, value);
    }

    public OrderCard()
    {
        InitializeComponent();
    }
}
