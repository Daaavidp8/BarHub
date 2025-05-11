using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;

namespace BarHub.ViewModel.Owner.Articles
{
    public partial class ArticleViewModel : ObservableObject
    {
        [ObservableProperty]
        private Article article;

        public ArticleViewModel(Article article)
        {
            this.article = article;
        }

        public int Id
        {
            get => Article.Id;
            set => SetProperty(Article.Id, value, Article, (r, v) => r.Id = v);
        }

        public float Price
        {
            get => Article.Price;
            set => SetProperty(Article.Price, value, Article, (r, v) => r.Price = v);
        }

        public string Name
        {
            get => Article.Name;
            set => SetProperty(Article.Name, value, Article, (r, v) => r.Name = v);
        }

        public string Image
        {
            get => Article.Image;
            set => SetProperty(Article.Image, value, Article, (r, v) => r.Image = v);
        }

        public Article ToModel() => Article;

    }
}
