using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using BarHub.Resources.Languages;

namespace BarHub.Utils.Translation
{
    public class LocalizationResourceManager : INotifyPropertyChanged
    {

        private static readonly LocalizationResourceManager _instance = new();
        public static LocalizationResourceManager Instance => _instance;
        private LocalizationResourceManager()
        {
            AppResources.Culture = CultureInfo.CurrentCulture;
        }

        public string this[string key] => AppResources.ResourceManager.GetString(key, AppResources.Culture) ?? key;

        public event PropertyChangedEventHandler? PropertyChanged;

        public void SetCulture(CultureInfo culture)
        {
            if (AppResources.Culture != culture)
            {
                AppResources.Culture = culture;
                PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(null));
            }
        }
    }
}
