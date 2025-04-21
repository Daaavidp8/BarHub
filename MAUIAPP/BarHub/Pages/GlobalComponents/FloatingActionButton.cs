using BarHub.Utils;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using UraniumUI.Pages;

namespace BarHub.Pages.GlobalComponents
{
    public class FloatingActionButton : ImageButton, IPageAttachment
    {
        public static readonly BindableProperty RutaProperty =
    BindableProperty.Create(
        nameof(Ruta),
        typeof(ICommand),
        typeof(FloatingActionButton),
        default(ICommand),
        propertyChanged: OnRutaChanged);

        public ICommand Ruta
        {
            get => (ICommand)GetValue(RutaProperty);
            set => SetValue(RutaProperty, value);
        }

        private static void OnRutaChanged(BindableObject bindable, object oldValue, object newValue)
        {
            var fab = (FloatingActionButton)bindable;
            if (newValue is ICommand ruta && ruta is not null)
            {
                fab.IsVisible = true;
                fab.Command = ruta;
            }
        }

        public FloatingActionButton()
        {
            this.Source = new FontImageSource
            {
                FontFamily = "FASolid",
                Glyph = Icons.Plus,
                Size = 15,
                Color = Colors.White,
            };
            this.BackgroundColor = (Color)App.Current.Resources["Primary"];
            this.BorderColor = ((SolidColorBrush)App.Current.Resources["InversedBackground"]).Color;
            this.BorderWidth = 0.2;
            this.VerticalOptions = LayoutOptions.End;
            this.HorizontalOptions = LayoutOptions.End;
            this.Margin = new Thickness(0,0,40,40);
            this.Padding = new Thickness(20);
            this.CornerRadius = 5;
        }
        public AttachmentPosition AttachmentPosition => AttachmentPosition.Front;

        public void OnAttached(UraniumContentPage attachedPage)
        {
            throw new NotImplementedException();
        }
    }
}
