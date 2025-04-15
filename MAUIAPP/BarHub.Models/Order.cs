using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class Order
    {
        public Guid Id { get; set; }
        public Restaurant Restaurant { get; set; }
        public string RestaurantName { get; set; }
        public string OrderDate { get; set; }
        public string ClientIpAddress { get; set; }
        public string TableNumber { get; set; }
        public string Total { get; set; }
        public List<OrderLine> OrderLines { get; set; }
    }
}
