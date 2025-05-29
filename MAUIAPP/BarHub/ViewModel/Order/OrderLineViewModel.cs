using BarHub.Models;
using BarHub.Models.Enums;
using CommunityToolkit.Mvvm.ComponentModel;
using System;

namespace BarHub.ViewModel.Order
{
    public partial class OrderLineViewModel : ObservableObject
    {
        [ObservableProperty]
        private OrderLine orderLine;


        [ObservableProperty]
        private int numberItems = 1;


        public OrderLineViewModel(OrderLine orderLine)
        {
            this.orderLine = orderLine;
        }

        public string SectionName
        {
            get => OrderLine.Article.Section.Name;
            set => SetProperty(OrderLine.Article.Section.Name, value, OrderLine, (o, v) => o.Article.Section.Name = v);
        }

        public int Id
        {
            get => OrderLine.Id;
            set => SetProperty(OrderLine.Id, value, OrderLine, (o, v) => o.Id = v);
        }

        public Article Article 
        {
            get => orderLine.Article;
            set => SetProperty(OrderLine.Article, value, OrderLine, (o, v) => o.Article = v);
        }

        public string ArticleName
        {
            get => orderLine.ArticleName;
            set => SetProperty(OrderLine.ArticleName, value, OrderLine, (o, v) => o.ArticleName = v);
        }

        public string ImageUrl
        {
            get => orderLine.Article.Image;
            set => SetProperty(OrderLine.Article.Image, value, OrderLine, (o, v) => o.Article.Image = v);
        }

        public string ArticlePrice
        {
            get => orderLine.ArticlePrice;
            set => SetProperty(OrderLine.ArticlePrice, value, OrderLine, (o, v) => o.ArticlePrice = v);
        }

        public OrderState Estado
        {
            get => orderLine.Estado;
            set => SetProperty(OrderLine.Estado, value, OrderLine, (o, v) => o.Estado = v);
        }

        public Models.Order Order
        {
            get => orderLine.Order;
            set => SetProperty(OrderLine.Order, value, OrderLine, (o, v) => o.Order = v);
        }

        public OrderLine ToModel() => orderLine;
    }
}
