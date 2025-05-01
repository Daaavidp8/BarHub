using CommunityToolkit.Maui.Views;

namespace BarHub.Utils.UI.Popups
{
    public class MyPopUp : Popup
    {
        public MyPopUp(ContentView view)
        {
            Content = view;
            Color = Colors.Transparent;
        }
    }
}
