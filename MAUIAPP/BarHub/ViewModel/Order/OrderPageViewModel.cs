using BarHub.Lib;
using BarHub.Models;
using BarHub.Models.Enums;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Order;
using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Timers;

namespace BarHub.ViewModel.Order
{
    public partial class OrderPageViewModel : ObservableObject
    {
        private readonly Puts _puts;
        private readonly Gets _gets;
        private readonly User _user;
        private readonly IContext<OrderLineViewModel> _articleContext;

        private HashSet<int> existingOrderLineIds = new();
        private System.Timers.Timer _timer;

        [ObservableProperty]
        private ObservableCollection<TableGroup> orders;

        [ObservableProperty]
        private string state;

        public OrderPageViewModel(IContext<OrderLineViewModel> articleContext, Gets gets, Puts puts, User user)
        {
            _puts = puts;
            _gets = gets;
            _user = user;
            _articleContext = articleContext;

            Orders = new ObservableCollection<TableGroup>();

            _ = GetOrders(_gets, _user); // Initial call
          StartPeriodicRefresh();
        }

        private void StartPeriodicRefresh()
        {
            _timer = new System.Timers.Timer(5000); // 5 seconds
            _timer.Elapsed += async (s, e) => await GetOrders(_gets, _user);
            _timer.AutoReset = true;
            _timer.Enabled = true;
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
                .Where(x => !existingOrderLineIds.Contains(x.OrderLineVM.Id)) // only new
                .ToList();

            if (!allOrderLines.Any() && !Orders.Any())
            {
                State = "Empty";
                return;
            }

            if (!allOrderLines.Any()) return;

            // Save new IDs
            foreach (var line in allOrderLines)
            {
                existingOrderLineIds.Add(line.OrderLineVM.Id);
            }

            List<TableGroup> newGroups;

            if (IsTablet())
            {
                newGroups = new List<TableGroup>();

                foreach (var tableGroup in allOrderLines.GroupBy(x => x.TableNumber))
                {
                    foreach (var sectionGroup in tableGroup.GroupBy(x => x.SectionName))
                    {
                        var chunk = new List<OrderLineViewModel>();
                        foreach (var item in sectionGroup)
                        {
                            chunk.Add(item.OrderLineVM);

                            if (chunk.Count == 4)
                            {
                                var sg = new SectionGroup(sectionGroup.Key, chunk.ToList());
                                var tg = new TableGroup(tableGroup.Key, new[] { sg });
                                newGroups.Add(tg);
                                chunk.Clear();
                            }
                        }

                        if (chunk.Any())
                        {
                            var sg = new SectionGroup(sectionGroup.Key, chunk.ToList());
                            var tg = new TableGroup(tableGroup.Key, new[] { sg });
                            newGroups.Add(tg);
                        }
                    }
                }

                // ⚠️ En tablet, agregamos sin fusionar para mantener chunks independientes
                foreach (var newGroup in newGroups)
                {
                    Orders.Add(newGroup);
                }
            }
            else
            {
                newGroups = allOrderLines
                    .GroupBy(x => x.TableNumber)
                    .Select(tableGroup => new TableGroup(tableGroup.Key,
                        tableGroup
                        .GroupBy(x => x.SectionName)
                        .Select(sectionGroup => new SectionGroup(sectionGroup.Key, sectionGroup.Select(x => x.OrderLineVM)))
                    ))
                    .ToList();

                // En no-tablet, sí hacemos merge de secciones en mesas existentes
                foreach (var newGroup in newGroups)
                {
                    var existingTable = Orders.FirstOrDefault(tg => tg.TableNumber == newGroup.TableNumber);
                    if (existingTable == null)
                    {
                        Orders.Add(newGroup);
                    }
                    else
                    {
                        foreach (var newSection in newGroup)
                        {
                            var existingSection = existingTable.FirstOrDefault(sg => sg.SectionName == newSection.SectionName);
                            if (existingSection == null)
                            {
                                existingTable.Add(newSection);
                            }
                            else
                            {
                                foreach (var line in newSection)
                                {
                                    existingSection.Add(line);
                                }
                            }
                        }
                    }
                }
            }

            State = "Full";
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
                foreach (var sectionGroup in tableGroup.ToList())
                {
                    if (sectionGroup.Contains(orderLineToRemove))
                    {
                        sectionGroup.Remove(orderLineToRemove);
                        existingOrderLineIds.Remove(orderLineToRemove.Id);

                        if (!sectionGroup.Any())
                            tableGroup.Remove(sectionGroup);

                        if (!tableGroup.Any())
                            Orders.Remove(tableGroup);

                        return;
                    }
                }
            }
        }

        public static bool IsTablet()
        {
            var width = DeviceDisplay.MainDisplayInfo.Width / DeviceDisplay.MainDisplayInfo.Density;
            return width >= 600;
        }
    }
}
