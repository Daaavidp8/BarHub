using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Waiter
{

    [QueryProperty(nameof(Table), "table")]
    public partial class DetailsTableViewModel : ObservableObject
    {
        [ObservableProperty]
        private Table table;

        [ObservableProperty]
        public string pageTitle;

        [ObservableProperty]
        private ImageSource qrImageSource;


        partial void OnTableChanged(Table value)
        {
            Trace.WriteLine(value.QrImage);

            PageTitle = $"Mesa {value.TableNumber}";

            if (!string.IsNullOrWhiteSpace(value.QrImage))
            {
                try
                {
                    byte[] imageBytes = Convert.FromBase64String(value.QrImage);
                    QrImageSource = ImageSource.FromStream(() => new MemoryStream(imageBytes));
                }
                catch (Exception ex)
                {
                    Trace.WriteLine($"Error al cargar imagen QR: {ex.Message}");
                }
            }
        }

        [RelayCommand]
        private async void GoToOrder()
        {
            try
            {
                Uri uri = new Uri(Table.Url);
                await Browser.Default.OpenAsync(uri, BrowserLaunchMode.SystemPreferred);
            }
            catch (Exception ex)
            {
                // An unexpected error occurred. No browser may be installed on the device.
            }
        }


    }
}
