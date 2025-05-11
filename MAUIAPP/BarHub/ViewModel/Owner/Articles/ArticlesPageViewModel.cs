using BarHub.Lib;
using BarHub.Models;
using BarHub.Pages.Propietario.Articles;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using static System.Collections.Specialized.BitVector32;

namespace BarHub.ViewModel.Owner.Articles
{
    [QueryProperty(nameof(Articles), "articles")]
    [QueryProperty(nameof(Section), "section")]
    public partial class ArticlesPageViewModel : ObservableObject
    {
        [ObservableProperty]
        ObservableCollection<ArticleViewModel> articles;

        [ObservableProperty]
        private SectionViewModel section;

        [ObservableProperty]
        private string idRestaurant;


        [ObservableProperty]
        private string state = "Success";

        public ArticlesPageViewModel(IContext<ArticleViewModel> articleContext, Gets gets, Deletes deletes, Puts puts, Posts posts)
        {
            SetActions(articleContext, gets, deletes, puts, posts);
        }

        private void SetActions(IContext<ArticleViewModel> articleContext, Gets gets, Deletes deletes, Puts puts, Posts posts)
        {
            articleContext.ObjectCreated += async article =>
            {
                var response = await posts.CreateArticle(article.ToModel(),Section.Id);
                if (response is not null && response.Id != 0)
                {
                    article.Id = response.Id;
                    Articles.Add(article);
                }
            };

            articleContext.ObjectModified += async article =>
            {
                await puts.ModifyArticle(article.ToModel());
                var oldArticle = Articles.FirstOrDefault(x => x.Id == article.Id);
                if (oldArticle is not null)
                {
                    oldArticle.Name = article.Name;
                    oldArticle.Image = article.Image;
                }
            };

            articleContext.ObjectDeleted += async article =>
            {
                await deletes.DeleteArticle(article.Id);
                var itemToRemove = Articles.FirstOrDefault(s => s.Id == article.Id);
                if (itemToRemove is not null)
                {
                    Articles.Remove(itemToRemove);
                }
            };
        }

        [RelayCommand]
        private async Task GoToManageArticle(Article article)
        {
            if (article is null)
            {
                article = new Article();
            }

            await Shell.Current.GoToAsync(nameof(ManageArticle), true, new Dictionary<string, object>
                {
                    { "articleJson" , JsonSerializer.Serialize(article) },
                });
        }
    }
}
