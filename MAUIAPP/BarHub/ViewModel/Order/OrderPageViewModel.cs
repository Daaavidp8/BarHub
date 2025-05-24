using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.ViewModel.Interfaces;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using static System.Collections.Specialized.BitVector32;

namespace BarHub.ViewModel.Order
{
    public partial class OrderPageViewModel : ObservableObject
    {
        private readonly Puts _puts;

        [ObservableProperty]
        private ObservableCollection<TableGroup> orders;


        public OrderPageViewModel(IContext<OrderLineViewModel> articleContext, Gets gets,Puts puts, User user)
        {
            _puts = puts;
            _ = GetOrders(gets, user);
        }

        private async Task GetOrders(Gets gets, User user)
        {
            var response = new ObservableCollection<OrderViewModel>(
                (await gets.GetOrder(user, OrderState.PREPARACION))
                .Select(o => new OrderViewModel(o)));

            var allOrderLines = response
                .SelectMany(order => order.OrderLines.Select(line => new
                {
                    OrderLineVM = line,
                    TableNumber = order.TableNumber,
                    SectionName = line.SectionName
                }))
                .ToList();

            var groupedByTable = allOrderLines
                .GroupBy(x => x.TableNumber)
                .Select(tableGroup => new TableGroup(tableGroup.Key,
                    tableGroup
                    .GroupBy(x => x.SectionName)
                    .Select(sectionGroup => new SectionGroup(sectionGroup.Key, sectionGroup.Select(x => x.OrderLineVM)))
                ))
                .ToList();

            Orders = new ObservableCollection<TableGroup>(groupedByTable);
        }



        [RelayCommand]
        private async Task SetOrderReady(OrderLineViewModel orderLine)
        {
            RemoveOrderLine(orderLine);
            await _puts.ModifyOrderLine(orderLine.OrderLine, OrderState.SERVIDO);
        }


        [RelayCommand]
        private async Task SetOrderCancelled(OrderLineViewModel orderLine)
        {
            RemoveOrderLine(orderLine);
            await _puts.ModifyOrderLine(orderLine.OrderLine, OrderState.CANCELADO);
        }

        void RemoveOrderLine(OrderLineViewModel orderLineToRemove)
        {
            foreach (var tableGroup in Orders.ToList())
            {
                foreach (var sectionGroup in tableGroup)
                {
                    if (sectionGroup.Contains(orderLineToRemove))
                    {
                        sectionGroup.Remove(orderLineToRemove);

                        if (!sectionGroup.Any())
                        {
                            tableGroup.Remove(sectionGroup);
                        }

                        if (!tableGroup.Any())
                        {
                            Orders.Remove(tableGroup);
                        }

                        return; 
                    }
                }
            }
        }

    }
}
