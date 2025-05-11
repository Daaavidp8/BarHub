using BarHub.Models;
using BarHub.Pages.Admin;
using BarHub.Resources.Languages;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Maui.Alerts;
using CommunityToolkit.Maui.Views;
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



        public async void DeleteObjectWithPopUp<T>(Page page,object sender,IContext<T> context,string objectToDeleteName,string deleteMessage = "¿Desea eliminar este elemento?")
        {
            if (sender is not SwipeView swipeView)
                return;

            var swipeItemView = swipeView.RightItems.FirstOrDefault() as SwipeItemView;

            if (swipeItemView?.Content is not Layout layout)
                return;

            var image = layout.Children
                              .OfType<Image>()
                              .FirstOrDefault(i => i.StyleId == "iconTrash");

            if (image == null)
                return;

            await image.TranslateTo(-380, 0, 300);

            if (swipeView.BindingContext is not T item)
            {
                ResetSwipeView(image, swipeView);
                return;
            }

            var isConfirmed = await page.ShowPopupAsync(new ConfirmDeletePopUp($"{deleteMessage} {objectToDeleteName}?"));

            if (isConfirmed is not bool confirmed || !confirmed)
            {
                ResetSwipeView(image, swipeView);
                return;
            }

            try
            {
                await context.NotifyObjectDeleted(item);
                await page.DisplaySnackbar("Elemento eliminado correctamente");
            }
            catch (Exception)
            {
                await page.DisplaySnackbar("No se ha podido eliminar el elemento");
                ResetSwipeView(image, swipeView);
            }

            swipeView.Close();
        }



        private async void ResetSwipeView(Image image, SwipeView swipeView)
        {
            image.TranslateTo(0, 0, 200);
            swipeView.Close();
        }
    }
}
