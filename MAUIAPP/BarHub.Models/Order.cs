using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class Order
    {
        [JsonProperty("id_order")]
        public int Id { get; set; }

        [JsonProperty("id_restaurant")]
        public int Restaurant { get; set; }

        [JsonProperty("restaurant_name")]
        public string RestaurantName { get; set; }

        [JsonProperty("order_date")]
        public string OrderDate { get; set; }

        [JsonProperty("client_ipaddress")]
        public string ClientIpAddress { get; set; }

        [JsonProperty("table_number")]
        public int TableNumber { get; set; }

        [JsonProperty("total")]
        public string Total { get; set; }

        [JsonProperty("order_lines")]
        public List<OrderLine> OrderLines { get; set; }
    }
}
