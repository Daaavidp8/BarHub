using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    using Newtonsoft.Json;

    public class Table
    {
        [JsonProperty("table_number")]
        public int TableNumber { get; set; }

        [JsonProperty("codenumber")]
        public int CodeNumber { get; set; }

        [JsonProperty("id_restaurant")]
        public int IdRestaurant { get; set; }

        [JsonProperty("qr_image")]
        public string QrImage { get; set; }

        [JsonProperty("url")]
        public string Url { get; set; }
    }

}
