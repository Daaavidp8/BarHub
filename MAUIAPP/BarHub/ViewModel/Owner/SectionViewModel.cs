using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using MPowerKit.VirtualizeListView;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Owner
{
    public partial class SectionViewModel : ObservableObject
    {
        [ObservableProperty]
        private Section section;

        [ObservableProperty]
        private ObservableCollection<ArticleViewModel> articles;

        public SectionViewModel(Section section)
        {
            this.section = section;
            articles = new ObservableCollection<ArticleViewModel>(
                section.Articles.Select(a => new ArticleViewModel(a))
            );
        }

        public int Id
        {
            get => section.Id;
            set => SetProperty(section.Id, value, section, (r, v) => r.Id = v);
        }

        public string Name
        {
            get => section.Name;
            set => SetProperty(section.Name, value, section, (r, v) => r.Name = v);
        }

        public string Image
        {
            get => section.Image;
            set => SetProperty(section.Image, value, section, (r, v) => r.Image = v);
        }



        public Section ToModel() => section;
    }
}
