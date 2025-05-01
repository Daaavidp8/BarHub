using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Owner
{
    public partial class ArticleViewModel : ObservableObject
    {
        [ObservableProperty]
        private Article article;

        public ArticleViewModel(Article article)
        {
            this.article = article;
        }
    }
}
