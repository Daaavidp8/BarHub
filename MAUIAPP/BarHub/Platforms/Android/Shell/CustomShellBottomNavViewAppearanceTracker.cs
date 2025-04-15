using Android.Graphics.Drawables;
using Google.Android.Material.BottomNavigation;
using Microsoft.Maui.Controls.Platform;
using Microsoft.Maui.Controls.Platform.Compatibility;
using Microsoft.Maui.Platform;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Platforms.Android.Shell
{
    public class CustomShellBottomNavViewAppearanceTracker : ShellBottomNavViewAppearanceTracker
    {
        private readonly IShellContext shellContext;
        public CustomShellBottomNavViewAppearanceTracker(IShellContext shellContext, ShellItem shellItem) : base(shellContext, shellItem)
        {
            this.shellContext = shellContext;
        }
        public override void SetAppearance(BottomNavigationView bottomView, IShellAppearanceElement appearance)
        {
            base.SetAppearance(bottomView, appearance);

            var backgroundDrawable = new GradientDrawable();
            backgroundDrawable.SetShape(ShapeType.Rectangle);
            backgroundDrawable.SetColor(Colors.White.ToPlatform());
            backgroundDrawable.SetStroke(1, Colors.Black.ToPlatform());
            bottomView.SetBackground(backgroundDrawable);

            bottomView.SetPadding(0, 0, 0, 0);
            bottomView.LabelVisibilityMode = LabelVisibilityMode.LabelVisibilityUnlabeled;

            bottomView.RequestLayout();
        }






        protected override void SetBackgroundColor(BottomNavigationView bottomView, Color color)
        {
            base.SetBackgroundColor(bottomView, color);
            bottomView.RootView?.SetBackgroundColor(shellContext.Shell.CurrentPage.BackgroundColor.ToPlatform());
        }
    }
}
