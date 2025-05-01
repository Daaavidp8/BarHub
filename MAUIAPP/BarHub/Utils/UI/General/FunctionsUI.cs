using BarHub.Models;
using BarHub.Resources.Languages;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Utils.UI.General
{
    public class FunctionsUI
    {
        public async Task<string> PickFile(string text)
        {
            try
            {
                var result = await FilePicker.PickAsync(new PickOptions
                {
                    PickerTitle = text,
                    FileTypes = FilePickerFileType.Png,
                });
                if (result is not null)
                {
                    using var stream = await result.OpenReadAsync();
                    using var memoryStream = new MemoryStream();
                    await stream.CopyToAsync(memoryStream);
                    var base64 = $"data:image/jpeg;base64,{Convert.ToBase64String(memoryStream.ToArray())}";
                    return base64;
                }
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }

            return string.Empty;
        }
    }
}
