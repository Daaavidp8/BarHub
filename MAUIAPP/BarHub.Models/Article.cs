using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class Article
    {
        [JsonProperty("id_article")]
        public int Id { get; set; }
        public string Name { get; set; }

        public string Image { get; set; }
        public float Price { get; set; }
        public Section Section { get; set; }
    }
}
