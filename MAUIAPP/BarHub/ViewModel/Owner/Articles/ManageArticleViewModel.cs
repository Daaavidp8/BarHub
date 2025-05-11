using BarHub.Models;
using BarHub.Resources.Languages;
using BarHub.Utils.UI.General;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Owner.Articles
{
    [QueryProperty(nameof(ArticleJson), "articleJson")]
    public partial class ManageArticleViewModel: ObservableObject
    {
        private readonly FunctionsUI _functionsUI;
        private readonly IContext<ArticleViewModel> _articleContext;

        [ObservableProperty]
        private string articleJson;

        [ObservableProperty]
        private ArticleViewModel article;

        [ObservableProperty]
        private string title = AppResources.AddRestaurantText, buttonText = AppResources.CreateRestaurantText;


        public ManageArticleViewModel(FunctionsUI fUI, IContext<ArticleViewModel> articleContext)
        {
            _functionsUI = fUI;
            _articleContext = articleContext;
        }


        partial void OnArticleJsonChanged(string value)
        {
            try
            {
                if (!string.IsNullOrWhiteSpace(value))
                {
                    var model = JsonSerializer.Deserialize<Article>(value);
                    if (model != null)
                    {
                        Article = new ArticleViewModel(model);
                        if (model.Id != 0)
                        {
                            Title = AppResources.ModifyRestaurantText;
                            ButtonText = AppResources.SaveChangesText;
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                Debug.WriteLine($"Error deserializing restaurantJson: {ex.Message}");
            }
        } 

        [RelayCommand]
        private async Task PickFile()
        {
            try
            {
                Article.Image = await _functionsUI.PickFile(AppResources.SelectImageText);
            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }



        [RelayCommand]
        private async Task ActionButtonPressed()
        {
            try
            {
                if (Article.Id == 0)
                {
                    await _articleContext.NotifyObjectCreated(Article);
                }
                else
                {
                    await _articleContext.NotifyObjectModified(Article);
                }


                await Shell.Current.GoToAsync("..");

            }
            catch (Exception ex)
            {
                Trace.WriteLine($"Error en PickFile: {ex.Message}");
            }
        }
    }
}

