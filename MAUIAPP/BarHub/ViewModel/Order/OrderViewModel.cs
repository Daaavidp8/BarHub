using BarHub.Models.Enums;
using BarHub.Models;
using CommunityToolkit.Mvvm.ComponentModel;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Collections.ObjectModel;

namespace BarHub.ViewModel.Order
{
    public partial class OrderViewModel : ObservableObject
    {
        [ObservableProperty]
        private Models.Order order;

        [ObservableProperty]
        private ObservableCollection<OrderLineViewModel> orderLines;

        public OrderViewModel(Models.Order order)
        {
            this.order = order;
            orderLines = new(order.OrderLines?.Select(ol => new OrderLineViewModel(ol)).ToList());
        }

        public int Id
        {
            get => order.Id;
            set => SetProperty(order.Id, value, order, (o, v) => o.Id = v);
        }

        public string RestaurantName
        {
            get => order.RestaurantName;
            set => SetProperty(order.RestaurantName, value, order, (o, v) => o.RestaurantName = v);
        }

        public int TableNumber
        {
            get => order.TableNumber;
            set => SetProperty(order.TableNumber, value, order, (o, v) => o.TableNumber = v);
        }

        public string Total
        {
            get => order.Total;
            set => SetProperty(order.Total, value, order, (o, v) => o.Total = v);
        }

    }
}
