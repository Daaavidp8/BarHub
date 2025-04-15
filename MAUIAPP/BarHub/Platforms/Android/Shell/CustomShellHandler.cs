using Microsoft.Maui.Controls.Handlers.Compatibility;
using Microsoft.Maui.Controls.Platform.Compatibility;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Platforms.Android.Shell
{
    public class CustomShellHandler : ShellRenderer
    {
        protected override IShellBottomNavViewAppearanceTracker CreateBottomNavViewAppearanceTracker(ShellItem shellItem)
        {
            return new CustomShellBottomNavViewAppearanceTracker(this, shellItem.CurrentItem);
        }
    }
}
