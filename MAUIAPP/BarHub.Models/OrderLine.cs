using BarHub.Models.Enums;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class OrderLine
    {
        [JsonProperty("id_order_line")]
        public int Id { get; set; }

        [JsonProperty("article")]
        public Article Article { get; set; }
        [JsonProperty("article_name")]
        public string ArticleName { get; set; }
        [JsonProperty("article_price")]
        public string ArticlePrice { get; set; }
        [JsonProperty("state")]
        public Enums.OrderState Estado { get; set; }

        [JsonProperty("order")]
        public Order Order { get; set; }
    }
}
