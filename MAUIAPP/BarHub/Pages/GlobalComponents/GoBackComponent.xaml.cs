using System.Threading.Tasks;
using UraniumUI.Views;
using Microsoft.Maui.Controls;
using System.Diagnostics;

namespace BarHub.Pages.GlobalComponents
{
    public partial class GoBackComponent : ContentView
    {
        public GoBackComponent()
        {
            InitializeComponent();
        }

        private async void TapGestureRecognizer_Tapped(object sender, TappedEventArgs e)
        {
            await Shell.Current.GoToAsync("..", true);
        }
    }
}
