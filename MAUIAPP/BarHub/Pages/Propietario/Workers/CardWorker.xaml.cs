using BarHub.ViewModel.Owner.Workers;
using System.Text.Json;

namespace BarHub.Pages.Propietario.Workers;

public partial class CardWorker : ContentView
{
    public static readonly BindableProperty WorkerProperty =
           BindableProperty.Create(
               nameof(Worker),
               typeof(WorkerViewModel),
               typeof(CardSection),
               null
           );

    public WorkerViewModel Worker
    {
        get => (WorkerViewModel)GetValue(WorkerProperty);
        set => SetValue(WorkerProperty, value);
    }
    public CardWorker()
	{
		InitializeComponent();
	}

    private async void TapGestureRecognizer_Tapped(object sender, TappedEventArgs e)
    {
        await Shell.Current.GoToAsync(nameof(ManageWorker), true, new Dictionary<string, object>
        {
            { "workerJson" , JsonSerializer.Serialize(Worker.ToModel()) }
        });
    }
}